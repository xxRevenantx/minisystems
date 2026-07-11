<?php

namespace App\Support;

class ReconocimientoHtml
{
    public static function limpiar(?string $html): string
    {
        $html = strip_tags((string) $html, '<p><br><strong><b><em><i><u><ul><ol><li><span>');
        $html = preg_replace('/\s(on\w+|style|class|id)\s*=\s*(["\']).*?\2/iu', '', $html) ?? $html;
        $html = preg_replace('/javascript\s*:/iu', '', $html) ?? $html;
        return trim($html);
    }
}
