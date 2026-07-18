<?php

namespace App\Services\Images;

use App\Services\ImagePipeline;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class AiImagePreviewService
{
    /** @return array<string,mixed> */
    public function prepare(UploadedFile $file, string $generationUuid, int $order): array
    {
        $pipeline = new ImagePipeline();
        $manager = $pipeline->manager();
        $image = $manager->read($file->getRealPath());
        $pipeline->autorotate($file->getRealPath(), $image);

        $maxSide = 1600;
        if ($image->width() > $maxSide || $image->height() > $maxSide) {
            $image->scaleDown($maxSide, $maxSide);
        }

        $quality = 78;
        $encoded = $image->encode($pipeline->encoderFor('jpg', $quality));
        $limit = max(150000, (int) config('groq.preview_max_bytes', 550000));

        while (strlen((string) $encoded) > $limit && $quality > 48) {
            $quality -= 6;
            $encoded = $image->encode($pipeline->encoderFor('jpg', $quality));
        }

        $directory = 'ai-social/'.$generationUuid;
        $filename = str_pad((string) $order, 2, '0', STR_PAD_LEFT).'-'.Str::random(10).'.jpg';
        $path = $directory.'/'.$filename;
        Storage::disk('local')->put($path, (string) $encoded);

        return [
            'ruta_privada' => $path,
            'ruta_preview' => $path,
            'mime_type' => 'image/jpeg',
            'ancho' => $image->width(),
            'alto' => $image->height(),
            'peso' => strlen((string) $encoded),
            'orientacion' => $image->width() === $image->height() ? 'cuadrada' : ($image->width() > $image->height() ? 'horizontal' : 'vertical'),
            'checksum' => hash('sha256', (string) $encoded),
            'metadatos' => ['quality' => $quality, 'source_size' => $file->getSize()],
        ];
    }

    public function dataUri(string $path, string $mime = 'image/jpeg'): string
    {
        $bytes = Storage::disk('local')->get($path);
        return 'data:'.$mime.';base64,'.base64_encode($bytes);
    }
}
