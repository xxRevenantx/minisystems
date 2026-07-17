<?php

namespace App\Services;

use Intervention\Image\Interfaces\ImageInterface;
use RuntimeException;

class ImageOptimizerService
{
    public const MAX_MEGAPIXELS = 60;

    public function __construct(private readonly ImagePipeline $pipeline)
    {
    }

    /**
     * @return array{
     *   contents:string,format:string,mime:string,width:int,height:int,quality:?int,
     *   original_width:int,original_height:int,original_mime:string,warnings:array<int,string>,used_original:bool
     * }
     */
    public function optimize(string $sourcePath, array $options): array
    {
        if (! is_file($sourcePath) || ! is_readable($sourcePath)) {
            throw new RuntimeException('No fue posible leer la imagen temporal.');
        }

        $imageInfo = @getimagesize($sourcePath);
        if (! is_array($imageInfo)) {
            throw new RuntimeException('El archivo seleccionado no es una imagen válida.');
        }

        $originalWidth = (int) ($imageInfo[0] ?? 0);
        $originalHeight = (int) ($imageInfo[1] ?? 0);
        $originalMime = strtolower((string) ($imageInfo['mime'] ?? ''));

        if ($originalWidth < 1 || $originalHeight < 1) {
            throw new RuntimeException('No fue posible detectar las dimensiones de la imagen.');
        }

        if (($originalWidth * $originalHeight) > (self::MAX_MEGAPIXELS * 1_000_000)) {
            throw new RuntimeException('La imagen supera el límite seguro de '.self::MAX_MEGAPIXELS.' megapíxeles.');
        }

        $image = $this->pipeline->manager()->read($sourcePath);
        $this->pipeline->autorotate($sourcePath, $image);
        $visualOriginalWidth = $image->width();
        $visualOriginalHeight = $image->height();

        $format = $this->resolveFormat((string) ($options['format'] ?? 'webp'), $originalMime);
        $quality = max(35, min(100, (int) ($options['quality'] ?? 82)));
        $maxWidth = max(320, min(6000, (int) ($options['max_width'] ?? 1920)));
        $maxHeight = max(320, min(6000, (int) ($options['max_height'] ?? 1920)));
        $allowUpscale = (bool) ($options['allow_upscale'] ?? false);
        $preserveTransparency = (bool) ($options['preserve_transparency'] ?? true);
        $targetBytes = filled($options['target_kb'] ?? null)
            ? max(50, min(20_000, (int) $options['target_kb'])) * 1024
            : null;
        $warnings = [];

        if ($allowUpscale) {
            $image->scale(width: $maxWidth, height: $maxHeight);
        } else {
            $image->scaleDown(width: $maxWidth, height: $maxHeight);
        }

        if (! $this->pipeline->supports($format)) {
            $fallback = $this->fallbackFormat($format, $originalMime, $preserveTransparency);
            $warnings[] = strtoupper($format).' no está disponible en el servidor; se utilizó '.strtoupper($fallback).'.';
            $format = $fallback;
        }

        if (! $preserveTransparency || $format === 'jpg') {
            $image = $this->flattenOnWhite($image);
        }

        try {
            [$contents, $finalQuality, $reachedTarget] = $this->encodeWithTarget(
                image: $image,
                format: $format,
                preferredQuality: $quality,
                targetBytes: $targetBytes,
            );
        } catch (RuntimeException $encodingException) {
            $fallback = $this->fallbackFormat($format, $originalMime, $preserveTransparency);

            if ($fallback === $format) {
                throw $encodingException;
            }

            $warnings[] = strtoupper($format).' no pudo codificarse; se utilizó '.strtoupper($fallback).' como respaldo.';
            $format = $fallback;

            if ($format === 'jpg') {
                $image = $this->flattenOnWhite($image);
            }

            [$contents, $finalQuality, $reachedTarget] = $this->encodeWithTarget(
                image: $image,
                format: $format,
                preferredQuality: $quality,
                targetBytes: $targetBytes,
            );
        }

        if ($targetBytes !== null && ! $reachedTarget) {
            $warnings[] = 'No fue posible alcanzar exactamente el peso objetivo sin reducir más la calidad o las dimensiones.';
        }

        $usedOriginal = false;
        $sourceExtension = $this->extensionForMime($originalMime);
        $sameDimensions = $image->width() === $visualOriginalWidth && $image->height() === $visualOriginalHeight;
        $originalContents = file_get_contents($sourcePath);

        if (
            is_string($originalContents)
            && $format === $sourceExtension
            && $sameDimensions
            && $targetBytes === null
            && strlen($contents) >= strlen($originalContents)
        ) {
            $contents = $originalContents;
            $usedOriginal = true;
            $finalQuality = null;
            $warnings[] = 'La imagen original ya estaba optimizada; se conservó para evitar aumentar su peso.';
        }

        return [
            'contents' => $contents,
            'format' => $format,
            'mime' => $this->mimeForFormat($format),
            'width' => $image->width(),
            'height' => $image->height(),
            'quality' => $finalQuality,
            'original_width' => $visualOriginalWidth,
            'original_height' => $visualOriginalHeight,
            'original_mime' => $originalMime,
            'warnings' => $warnings,
            'used_original' => $usedOriginal,
        ];
    }

    /** @return array{0:string,1:?int,2:bool} */
    private function encodeWithTarget(
        ImageInterface $image,
        string $format,
        int $preferredQuality,
        ?int $targetBytes,
    ): array {
        if ($targetBytes === null) {
            return [$this->encode($image, $format, $preferredQuality), $format === 'png' ? null : $preferredQuality, true];
        }

        $lastContents = '';
        $lastQuality = $format === 'png' ? null : 35;

        for ($resizePass = 0; $resizePass < 7; $resizePass++) {
            if ($format === 'png') {
                $lastContents = $this->encode($image, $format, $preferredQuality);
                $lastQuality = null;

                if (strlen($lastContents) <= $targetBytes) {
                    return [$lastContents, null, true];
                }
            } else {
                [$candidate, $candidateQuality, $reached] = $this->findQualityForTarget(
                    image: $image,
                    format: $format,
                    maximumQuality: $preferredQuality,
                    targetBytes: $targetBytes,
                );
                $lastContents = $candidate;
                $lastQuality = $candidateQuality;

                if ($reached) {
                    return [$candidate, $candidateQuality, true];
                }
            }

            if ($image->width() <= 480 && $image->height() <= 480) {
                break;
            }

            $image->scaleDown(
                width: max(480, (int) floor($image->width() * 0.88)),
                height: max(480, (int) floor($image->height() * 0.88)),
            );
        }

        return [$lastContents, $lastQuality, false];
    }

    /** @return array{0:string,1:int,2:bool} */
    private function findQualityForTarget(
        ImageInterface $image,
        string $format,
        int $maximumQuality,
        int $targetBytes,
    ): array {
        $low = 35;
        $high = max($low, $maximumQuality);
        $bestContents = null;
        $bestQuality = $low;
        $smallestContents = null;
        $smallestQuality = $low;

        while ($low <= $high) {
            $quality = intdiv($low + $high, 2);
            $contents = $this->encode($image, $format, $quality);

            if ($smallestContents === null || strlen($contents) < strlen($smallestContents)) {
                $smallestContents = $contents;
                $smallestQuality = $quality;
            }

            if (strlen($contents) <= $targetBytes) {
                $bestContents = $contents;
                $bestQuality = $quality;
                $low = $quality + 1;
            } else {
                $high = $quality - 1;
            }
        }

        if (is_string($bestContents)) {
            return [$bestContents, $bestQuality, true];
        }

        return [(string) $smallestContents, $smallestQuality, false];
    }

    private function encode(ImageInterface $image, string $format, int $quality): string
    {
        try {
            return (string) $image->encode($this->pipeline->encoderFor($format, $quality));
        } catch (\Throwable $exception) {
            throw new RuntimeException('No fue posible generar el formato '.strtoupper($format).': '.$exception->getMessage(), 0, $exception);
        }
    }

    private function flattenOnWhite(ImageInterface $image): ImageInterface
    {
        $canvas = $this->pipeline->manager()
            ->create($image->width(), $image->height())
            ->fill('ffffff');
        $canvas->place($image, 'top-left', 0, 0);

        return $canvas;
    }

    private function resolveFormat(string $requested, string $originalMime): string
    {
        $requested = strtolower($requested);

        if ($requested === 'original') {
            return $this->extensionForMime($originalMime);
        }

        if (! in_array($requested, ['jpg', 'png', 'webp', 'avif'], true)) {
            throw new RuntimeException('El formato de salida seleccionado no es válido.');
        }

        return $requested;
    }

    private function fallbackFormat(string $requested, string $originalMime, bool $preserveTransparency): string
    {
        if ($requested !== 'png' && $preserveTransparency && $originalMime === 'image/png' && $this->pipeline->supports('png')) {
            return 'png';
        }

        if ($requested !== 'webp' && $this->pipeline->supports('webp')) {
            return 'webp';
        }

        if ($requested !== 'jpg' && $this->pipeline->supports('jpg')) {
            return 'jpg';
        }

        throw new RuntimeException('El servidor no puede generar JPG, PNG, WebP ni AVIF.');
    }

    private function extensionForMime(string $mime): string
    {
        return match ($mime) {
            'image/jpeg' => 'jpg',
            'image/png' => 'png',
            'image/webp' => 'webp',
            'image/avif' => 'avif',
            default => throw new RuntimeException('El formato original no es compatible.'),
        };
    }

    private function mimeForFormat(string $format): string
    {
        return match ($format) {
            'jpg' => 'image/jpeg',
            'png' => 'image/png',
            'webp' => 'image/webp',
            'avif' => 'image/avif',
            default => 'application/octet-stream',
        };
    }
}
