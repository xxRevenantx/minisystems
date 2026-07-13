<?php

namespace App\Services;

use BaconQrCode\Renderer\Image\SvgImageBackEnd;
use BaconQrCode\Renderer\ImageRenderer;
use BaconQrCode\Renderer\RendererStyle\RendererStyle;
use BaconQrCode\Writer;

class QrCodeService
{
    public function svg(string $content, int $size = 180, int $margin = 1): ?string
    {
        $content = trim($content);
        if ($content === '') {
            return null;
        }

        try {
            $renderer = new ImageRenderer(
                new RendererStyle(max(80, min(1000, $size)), max(0, min(10, $margin))),
                new SvgImageBackEnd(),
            );

            return (new Writer($renderer))->writeString($content);
        } catch (\Throwable) {
            return null;
        }
    }

    public function dataUri(string $content, int $size = 180, int $margin = 1): ?string
    {
        $svg = $this->svg($content, $size, $margin);

        return $svg ? 'data:image/svg+xml;base64,'.base64_encode($svg) : null;
    }
}
