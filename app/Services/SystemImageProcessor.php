<?php

namespace App\Services;

use App\Models\Marco;
use App\Models\SystemImageBatch;
use App\Models\SystemImageItem;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use RuntimeException;

class SystemImageProcessor
{
    public function __construct(private readonly ImagePipeline $pipeline)
    {
    }

    /**
     * Procesa una imagen del lote aplicando orientación, ajuste, marco y formato final.
     *
     * @return array<string,mixed>
     */
    public function process(SystemImageItem $item): array
    {
        $batch = $item->batch;

        if (! $batch instanceof SystemImageBatch) {
            throw new RuntimeException('El lote de la imagen no está disponible.');
        }

        $disk = Storage::disk('local');

        if (! $item->source_path || ! $disk->exists($item->source_path)) {
            throw new RuntimeException('La copia original ya no está disponible. Vuelve a seleccionar la imagen.');
        }

        $settings = $batch->settings ?? [];
        $itemSettings = $item->settings ?? [];
        $sourcePath = $disk->path($item->source_path);
        $manager = $this->pipeline->manager();

        if (! @getimagesize($sourcePath)) {
            throw new RuntimeException('El archivo no es una imagen válida.');
        }

        $source = $manager->read($sourcePath);
        $this->pipeline->autorotate($sourcePath, $source);
        $sourceWidth = $source->width();
        $sourceHeight = $source->height();
        unset($source);

        $orientation = $this->resolveOrientation($settings, $itemSettings, $sourceWidth, $sourceHeight);
        [$targetWidth, $targetHeight] = $orientation === 'mobile'
            ? [(int) ($settings['mobile_width'] ?? 1365), (int) ($settings['mobile_height'] ?? 2058)]
            : [(int) ($settings['desktop_width'] ?? 2058), (int) ($settings['desktop_height'] ?? 1365)];

        $fit = (string) (($itemSettings['fit'] ?? 'inherit') === 'inherit'
            ? ($settings['fit_mode'] ?? 'cover')
            : $itemSettings['fit']);
        $focus = (string) ($itemSettings['focus'] ?? 'center');
        $focusX = (int) ($itemSettings['focus_x'] ?? 50);
        $focusY = (int) ($itemSettings['focus_y'] ?? 50);
        $zoom = (int) ($itemSettings['zoom'] ?? 100);

        $image = $this->adjustImage(
            path: $sourcePath,
            width: $targetWidth,
            height: $targetHeight,
            fit: $fit,
            focus: $focus,
            focusX: $focusX,
            focusY: $focusY,
            zoom: $zoom,
        );

        $frameInfo = $this->resolveFrame($settings, $itemSettings, $orientation);
        $warnings = [];

        if ($frameInfo['path']) {
            $frame = $manager->read($frameInfo['path'])->resize($targetWidth, $targetHeight);
            $image->place($frame, 'top-left', 0, 0);

            if ($frameInfo['alternate']) {
                $warnings[] = 'Se utilizó la orientación alterna del marco.';
            }
        } elseif ($frameInfo['requested']) {
            $warnings[] = 'El marco no tenía una versión disponible para esta orientación; se procesó sin marco.';
        }

        $extension = $this->resolveOutputFormat((string) ($settings['format'] ?? 'jpg'), (string) ($item->extension ?: 'jpg'));
        $quality = (int) ($settings['quality'] ?? 88);

        try {
            $encoded = $image->encode($this->pipeline->encoderFor($extension, $quality));
        } catch (\Throwable $exception) {
            if ($extension === 'jpg') {
                throw $exception;
            }

            $warnings[] = "El servidor no pudo generar {$extension}; se utilizó JPG como respaldo.";
            $extension = 'jpg';
            $encoded = $image->encode($this->pipeline->encoderFor('jpg', min(92, $quality)));
        }

        $outputName = $this->buildOutputName($batch, $item, $orientation, $extension);
        $outputPath = $batch->basePath().'/outputs/'.$outputName;

        if ($item->output_path && $item->output_path !== $outputPath && $disk->exists($item->output_path)) {
            $disk->delete($item->output_path);
        }

        if (! $disk->put($outputPath, (string) $encoded)) {
            throw new RuntimeException('No fue posible guardar la imagen procesada.');
        }

        $processedSize = strlen((string) $encoded);

        if ($frameInfo['id']) {
            Marco::whereKey($frameInfo['id'])->increment('usos', 1, ['ultimo_uso_at' => now()]);
        }

        return [
            'output_name' => $outputName,
            'output_path' => $outputPath,
            'processed_size' => $processedSize,
            'original_width' => $sourceWidth,
            'original_height' => $sourceHeight,
            'width' => $targetWidth,
            'height' => $targetHeight,
            'orientation' => $orientation,
            'format' => $extension,
            'quality' => $quality,
            'warnings' => $warnings,
        ];
    }

    /** @param array<string,mixed> $settings @param array<string,mixed> $itemSettings */
    private function resolveOrientation(array $settings, array $itemSettings, int $width, int $height): string
    {
        $individual = (string) ($itemSettings['orientation'] ?? 'inherit');
        if (in_array($individual, ['desktop', 'mobile'], true)) {
            return $individual;
        }

        $mode = (string) ($settings['orientation_mode'] ?? 'auto');
        if (in_array($mode, ['desktop', 'mobile'], true)) {
            return $mode;
        }

        if ($width === $height) {
            return (string) ($settings['square_mode'] ?? 'desktop') === 'mobile' ? 'mobile' : 'desktop';
        }

        return $width > $height ? 'desktop' : 'mobile';
    }

    private function adjustImage(
        string $path,
        int $width,
        int $height,
        string $fit,
        string $focus,
        int $focusX,
        int $focusY,
        int $zoom,
    ) {
        $manager = $this->pipeline->manager();

        if ($fit === 'contain') {
            $image = $manager->read($path);
            $this->pipeline->autorotate($path, $image);

            return $image->contain($width, $height, 'ffffff', $focus);
        }

        if ($fit === 'blur') {
            $background = $manager->read($path);
            $this->pipeline->autorotate($path, $background);
            $this->coverWithFocus($background, $width, $height, $focusX, $focusY, 100)->blur(28);

            $foreground = $manager->read($path);
            $this->pipeline->autorotate($path, $foreground);
            $foreground->scale((int) round($width * 0.94), (int) round($height * 0.94));
            $background->place($foreground, 'center', 0, 0);

            return $background;
        }

        $image = $manager->read($path);
        $this->pipeline->autorotate($path, $image);

        return $this->coverWithFocus($image, $width, $height, $focusX, $focusY, $zoom);
    }

    private function coverWithFocus($image, int $width, int $height, int $focusX, int $focusY, int $zoom)
    {
        $sourceWidth = max(1, $image->width());
        $sourceHeight = max(1, $image->height());
        $zoom = max(100, min(180, $zoom));
        $scale = max($width / $sourceWidth, $height / $sourceHeight) * ($zoom / 100);
        $resizedWidth = max($width, (int) ceil($sourceWidth * $scale));
        $resizedHeight = max($height, (int) ceil($sourceHeight * $scale));

        $image->resize($resizedWidth, $resizedHeight);

        $maxX = max(0, $resizedWidth - $width);
        $maxY = max(0, $resizedHeight - $height);
        $offsetX = (int) round($maxX * (max(0, min(100, $focusX)) / 100));
        $offsetY = (int) round($maxY * (max(0, min(100, $focusY)) / 100));

        return $image->crop($width, $height, $offsetX, $offsetY);
    }

    /** @param array<string,mixed> $settings @param array<string,mixed> $itemSettings */
    private function resolveFrame(array $settings, array $itemSettings, string $orientation): array
    {
        $selection = (string) ($itemSettings['frame'] ?? 'global');
        $frameId = $selection === 'global'
            ? ($settings['marco_id'] ?? null)
            : ($selection === 'none' ? null : $selection);

        $result = [
            'requested' => (bool) $frameId,
            'id' => null,
            'file' => null,
            'path' => null,
            'alternate' => false,
        ];

        if (! $frameId || ! ($marco = Marco::query()->where('activo', true)->find((int) $frameId))) {
            return $result;
        }

        $file = $marco->archivoPara($orientation);
        $alternate = false;

        if (! $file && (string) ($settings['missing_frame_behavior'] ?? 'skip') === 'alternate') {
            $file = $marco->archivoAlterno($orientation);
            $alternate = (bool) $file;
        }

        if (! $file) {
            return $result;
        }

        $path = storage_path('app/public/imagenesMarcos/'.$file);
        if (! is_file($path)) {
            return $result;
        }

        return [
            'requested' => true,
            'id' => $marco->id,
            'file' => 'imagenesMarcos/'.$file,
            'path' => $path,
            'alternate' => $alternate,
        ];
    }

    private function resolveOutputFormat(string $format, string $original): string
    {
        if ($format !== 'original') {
            return $format;
        }

        return match (strtolower($original)) {
            'png' => 'png',
            'webp' => 'webp',
            default => 'jpg',
        };
    }

    private function buildOutputName(SystemImageBatch $batch, SystemImageItem $item, string $orientation, string $extension): string
    {
        $settings = $batch->settings ?? [];
        $pattern = (string) ($settings['rename_pattern'] ?? '{orig}_{index}');
        $baseOriginal = Str::of(pathinfo($item->original_name, PATHINFO_FILENAME))->slug('_')->toString();
        $baseOriginal = mb_substr($baseOriginal !== '' ? $baseOriginal : 'imagen', 0, 90);
        $index = str_pad((string) $item->position, 3, '0', STR_PAD_LEFT);
        $containsIndex = str_contains($pattern, '{index}');

        $base = strtr($pattern, [
            '{index}' => $index,
            '{date}' => now()->format('Ymd'),
            '{orig}' => $baseOriginal,
            '{orientation}' => $orientation,
        ]);

        if (! $containsIndex) {
            $base .= '_'.$index;
        }

        $base = trim((string) preg_replace('/[^a-zA-Z0-9_\-]+/', '_', $base), '_-');
        $base = mb_substr($base !== '' ? $base : 'imagen_'.$index, 0, 110);

        $folder = (bool) ($settings['organize_folders'] ?? true) ? $orientation.'/' : '';
        $directory = $batch->basePath().'/outputs/'.$folder;
        $candidate = $folder.$base.'.'.$extension;
        $suffix = 2;
        $disk = Storage::disk('local');

        while ($disk->exists($batch->basePath().'/outputs/'.$candidate) && $item->output_name !== $candidate) {
            $candidate = $folder.$base.'_'.$suffix.'.'.$extension;
            $suffix++;
        }

        return $candidate;
    }
}
