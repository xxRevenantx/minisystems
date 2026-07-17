<?php

namespace App\Services;

use Intervention\Image\Drivers\Gd\Driver as GdDriver;
use Intervention\Image\Drivers\Imagick\Driver as ImagickDriver;
use Intervention\Image\Encoders\AvifEncoder;
use Intervention\Image\Encoders\JpegEncoder;
use Intervention\Image\Encoders\PngEncoder;
use Intervention\Image\Encoders\WebpEncoder;
use Intervention\Image\ImageManager;
use Intervention\Image\Interfaces\ImageInterface;
use RuntimeException;

class ImagePipeline
{
    protected ImageManager $manager;

    protected string $driverName;

    public function __construct(?string $driver = null)
    {
        if ($driver === 'imagick') {
            if (! extension_loaded('imagick')) {
                throw new RuntimeException('La extensión Imagick no está disponible en este servidor.');
            }

            $this->manager = new ImageManager(new ImagickDriver());
            $this->driverName = 'Imagick';

            return;
        }

        if ($driver === 'gd') {
            if (! extension_loaded('gd')) {
                throw new RuntimeException('La extensión GD no está disponible en este servidor.');
            }

            $this->manager = new ImageManager(new GdDriver());
            $this->driverName = 'GD';

            return;
        }

        if (extension_loaded('imagick')) {
            $this->manager = new ImageManager(new ImagickDriver());
            $this->driverName = 'Imagick';

            return;
        }

        if (extension_loaded('gd')) {
            $this->manager = new ImageManager(new GdDriver());
            $this->driverName = 'GD';

            return;
        }

        throw new RuntimeException('El servidor necesita la extensión PHP Imagick o GD para procesar imágenes.');
    }

    public function manager(): ImageManager
    {
        return $this->manager;
    }

    public function driverName(): string
    {
        return $this->driverName;
    }

    public function supports(string $format): bool
    {
        $format = strtolower($format);

        if ($this->driverName === 'Imagick' && class_exists(\Imagick::class)) {
            $imagickFormat = match ($format) {
                'jpg', 'jpeg' => 'JPEG',
                default => strtoupper($format),
            };

            try {
                return \Imagick::queryFormats($imagickFormat) !== [];
            } catch (\Throwable) {
                return false;
            }
        }

        if ($this->driverName === 'GD' && function_exists('gd_info')) {
            $information = gd_info();

            return match ($format) {
                'jpg', 'jpeg' => (bool) ($information['JPEG Support'] ?? false),
                'png' => (bool) ($information['PNG Support'] ?? false),
                'webp' => (bool) ($information['WebP Support'] ?? false),
                'avif' => (bool) ($information['AVIF Support'] ?? false),
                default => false,
            };
        }

        return false;
    }

    /** Autorrota la imagen según EXIF Orientation, cuando está disponible. */
    public function autorotate(string $path, ImageInterface $img): void
    {
        if (! function_exists('exif_read_data')) {
            return;
        }

        try {
            $exif = @exif_read_data($path);
            $orientation = $exif['Orientation'] ?? 1;
            $angles = [3 => 180, 6 => 90, 8 => -90];

            if (isset($angles[$orientation])) {
                $img->rotate($angles[$orientation]);
            }
        } catch (\Throwable) {
            // Una imagen sin EXIF no debe detener el lote.
        }
    }

    public function encoderFor(string $format, int $quality)
    {
        return match (strtolower($format)) {
            'png' => new PngEncoder(),
            'webp' => new WebpEncoder(quality: $quality),
            'avif' => new AvifEncoder(quality: $quality),
            default => new JpegEncoder(quality: $quality),
        };
    }
}
