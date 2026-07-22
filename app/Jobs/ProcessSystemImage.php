<?php

namespace App\Jobs;

use App\Models\SystemImageItem;
use App\Services\SystemImageBatchService;
use App\Services\SystemImageProcessor;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Throwable;

class ProcessSystemImage implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries = 1;

    public int $timeout = 900;

    public bool $failOnTimeout = true;

    public function __construct(public int $itemId)
    {
        $this->onConnection((string) config('system_images.connection', 'database'));
        $this->onQueue((string) config('system_images.queue', 'system-images'));
    }

    public function handle(SystemImageProcessor $processor, SystemImageBatchService $batchService): void
    {
        @set_time_limit(0);

        $item = SystemImageItem::query()->with('batch')->find($this->itemId);

        if (! $item || ! $item->batch || $item->status === 'completed') {
            return;
        }

        $batch = $item->batch;
        $disk = Storage::disk('local');
        $outputPath = null;

        try {
            $item->forceFill([
                'status' => 'processing',
                'error' => null,
                'attempts' => $item->attempts + 1,
            ])->save();
            $batchService->recalculate($batch);

            $processed = $processor->process($item);
            $outputPath = (string) ($processed['output_path'] ?? '');

            $item->forceFill([
                'output_name' => $processed['output_name'],
                'output_path' => $processed['output_path'],
                'processed_size' => $processed['processed_size'],
                'original_width' => $processed['original_width'],
                'original_height' => $processed['original_height'],
                'width' => $processed['width'],
                'height' => $processed['height'],
                'orientation' => $processed['orientation'],
                'extension' => $processed['format'],
                'warnings' => $processed['warnings'],
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
        $item = SystemImageItem::query()->with('batch')->find($this->itemId);

        if (! $item || ! $item->batch || $item->status === 'completed') {
            return;
        }

        $item->forceFill([
            'status' => 'failed',
            'error' => Str::limit('El proceso en cola falló: '.$exception->getMessage(), 4000),
            'processed_at' => now(),
        ])->save();

        app(SystemImageBatchService::class)->recalculate($item->batch);
    }
}
