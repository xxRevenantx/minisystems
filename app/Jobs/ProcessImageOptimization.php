<?php

namespace App\Jobs;

use App\Models\ImageOptimizerItem;
use App\Services\ImageOptimizerBatchService;
use App\Services\ImageOptimizerService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use RuntimeException;
use Throwable;

class ProcessImageOptimization implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries = 1;

    public int $timeout = 600;

    public bool $failOnTimeout = true;

    public function __construct(public int $itemId)
    {
        $this->onConnection((string) config('image_optimizer.connection', 'database'));
        $this->onQueue((string) config('image_optimizer.queue', 'image-optimizer'));
    }

    public function handle(
        ImageOptimizerService $optimizer,
        ImageOptimizerBatchService $batchService,
    ): void {
        @set_time_limit(0);

        $item = ImageOptimizerItem::query()->with('batch')->find($this->itemId);

        if (! $item || ! $item->batch || $item->status === 'completed') {
            return;
        }

        $batch = $item->batch;
        $disk = Storage::disk('local');
        $outputPath = null;

        try {
            if (! $item->source_path || ! $disk->exists($item->source_path)) {
                throw new RuntimeException('La copia original ya no está disponible. Vuelve a seleccionar la fotografía.');
            }

            $item->forceFill([
                'status' => 'processing',
                'error' => null,
                'attempts' => $item->attempts + 1,
            ])->save();
            $batchService->recalculate($batch);

            $settings = $batch->settings ?? [];
            $optimized = $optimizer->optimize($disk->path($item->source_path), [
                'format' => $settings['format'] ?? 'webp',
                'quality' => (int) ($settings['quality'] ?? 82),
                'max_width' => (int) ($settings['max_width'] ?? 1920),
                'max_height' => (int) ($settings['max_height'] ?? 1920),
                'target_kb' => filled($settings['target_kb'] ?? null)
                    ? (int) $settings['target_kb']
                    : null,
                'allow_upscale' => (bool) ($settings['allow_upscale'] ?? false),
                'preserve_transparency' => (bool) ($settings['preserve_transparency'] ?? true),
            ]);

            $outputName = $this->buildOutputName(
                item: $item,
                pattern: (string) ($settings['rename_pattern'] ?? '{name}-optimizada-{index}'),
                extension: $optimized['format'],
            );
            $outputPath = $batch->basePath().'/outputs/'.$outputName;

            if (! $disk->put($outputPath, $optimized['contents'])) {
                throw new RuntimeException('No fue posible guardar la imagen optimizada.');
            }

            $optimizedSize = strlen($optimized['contents']);
            $originalSize = (int) $item->original_size;
            $savedBytes = max(0, $originalSize - $optimizedSize);
            $reduction = $originalSize > 0
                ? round((1 - ($optimizedSize / $originalSize)) * 100, 2)
                : 0.0;

            if ($item->output_path && $item->output_path !== $outputPath && $disk->exists($item->output_path)) {
                $disk->delete($item->output_path);
            }

            $item->forceFill([
                'output_name' => $outputName,
                'output_path' => $outputPath,
                'optimized_size' => $optimizedSize,
                'saved_bytes' => $savedBytes,
                'original_width' => $optimized['original_width'],
                'original_height' => $optimized['original_height'],
                'width' => $optimized['width'],
                'height' => $optimized['height'],
                'format' => strtoupper($optimized['format']),
                'quality' => $optimized['quality'],
                'reduction' => $reduction,
                'warnings' => $optimized['warnings'],
                'status' => 'completed',
                'error' => null,
                'processed_at' => now(),
            ])->save();
        } catch (Throwable $exception) {
            if ($outputPath && $disk->exists($outputPath)) {
                $disk->delete($outputPath);
            }

            $item->forceFill([
                'status' => 'failed',
                'error' => Str::limit($exception->getMessage(), 4000),
                'processed_at' => now(),
            ])->save();
        } finally {
            $batchService->recalculate($batch);

            if (function_exists('gc_collect_cycles')) {
                gc_collect_cycles();
            }
        }
    }

    public function failed(Throwable $exception): void
    {
        $item = ImageOptimizerItem::query()->with('batch')->find($this->itemId);

        if (! $item || ! $item->batch || $item->status === 'completed') {
            return;
        }

        $item->forceFill([
            'status' => 'failed',
            'error' => Str::limit('El proceso en cola falló: '.$exception->getMessage(), 4000),
            'processed_at' => now(),
        ])->save();

        app(ImageOptimizerBatchService::class)->recalculate($item->batch);
    }

    private function buildOutputName(ImageOptimizerItem $item, string $pattern, string $extension): string
    {
        $baseName = Str::slug(pathinfo($item->original_name, PATHINFO_FILENAME), '-');
        $baseName = mb_substr($baseName !== '' ? $baseName : 'imagen', 0, 80);

        $paddedPosition = str_pad((string) $item->position, 3, '0', STR_PAD_LEFT);
        $containsIndex = str_contains($pattern, '{index}');
        $base = strtr($pattern, [
            '{name}' => $baseName,
            '{index}' => $paddedPosition,
            '{date}' => now()->format('Ymd'),
            '{format}' => $extension,
        ]);

        if (! $containsIndex) {
            $base .= '-'.$paddedPosition;
        }

        $base = trim((string) preg_replace('/[^a-zA-Z0-9_-]+/', '-', $base), '-_');
        $base = mb_substr($base !== '' ? $base : 'imagen-optimizada-'.$item->position, 0, 100);

        $disk = Storage::disk('local');
        $directory = $item->batch->basePath().'/outputs';
        $candidate = $base.'.'.$extension;
        $suffix = 2;

        while (
            $disk->exists($directory.'/'.$candidate)
            && $item->output_name !== $candidate
        ) {
            $candidate = $base.'-'.$suffix.'.'.$extension;
            $suffix++;
        }

        return $candidate;
    }
}
