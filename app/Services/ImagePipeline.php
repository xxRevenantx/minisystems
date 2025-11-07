<?php

namespace App\Services;

use Intervention\Image\ImageManager;
use Intervention\Image\Drivers\Gd\Driver as GdDriver;
use Intervention\Image\Drivers\Imagick\Driver as ImagickDriver;
use Intervention\Image\Encoders\JpegEncoder;
use Intervention\Image\Encoders\WebpEncoder;
use Intervention\Image\Encoders\AvifEncoder;
use Intervention\Image\Interfaces\ImageInterface;

class ImagePipeline
{
    protected ImageManager $manager;

    public function __construct(?string $driver = null)
    {
        // Prefiere Imagick si está disponible
        if ($driver === 'imagick') {
            $this->manager = new ImageManager(new ImagickDriver());
        } elseif ($driver === 'gd') {
            $this->manager = new ImageManager(new GdDriver());
        } else {
            $this->manager = extension_loaded('imagick')
                ? new ImageManager(new ImagickDriver())
                : new ImageManager(new GdDriver());
        }
    }

    public function manager(): ImageManager
    {
        return $this->manager;
    }

    /** Autorrota la imagen según EXIF Orientation (si existe) */
    public function autorotate(string $path, ImageInterface $img): void
    {
        if (!function_exists('exif_read_data')) return;

        try {
            $exif = @exif_read_data($path);
            $orientation = $exif['Orientation'] ?? 1;
            $angles = [3 => 180, 6 => 90, 8 => -90]; // mapa común
            if (isset($angles[$orientation])) {
                $img->rotate($angles[$orientation]);
            }
        } catch (\Throwable $e) {
            // no romper flujo
        }
    }

    /** Devuelve encoder según formato solicitado */
    public function encoderFor(string $format, int $quality)
    {
        return match (strtolower($format)) {
            'webp' => new WebpEncoder(quality: $quality),
            'avif' => new AvifEncoder(quality: $quality),
            default => new JpegEncoder(quality: $quality),
        };
    }
}
