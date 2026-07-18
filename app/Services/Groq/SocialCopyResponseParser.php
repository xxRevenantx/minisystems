<?php

namespace App\Services\Groq;

use InvalidArgumentException;

class SocialCopyResponseParser
{
    /** @return array<string,mixed> */
    public function parse(string $content): array
    {
        $content = trim($content);
        $content = preg_replace('/^```(?:json)?\s*|\s*```$/i', '', $content) ?? $content;
        $decoded = json_decode($content, true);

        if (! is_array($decoded)) {
            throw new InvalidArgumentException('La respuesta de Groq no contiene JSON válido.');
        }

        $decoded['plataformas'] = is_array($decoded['plataformas'] ?? null) ? $decoded['plataformas'] : [];
        $decoded['imagenes'] = is_array($decoded['imagenes'] ?? null) ? $decoded['imagenes'] : [];
        $decoded['datos_faltantes'] = array_values(array_filter((array) ($decoded['datos_faltantes'] ?? []), 'is_string'));
        $decoded['advertencias'] = array_values(array_filter((array) ($decoded['advertencias'] ?? []), 'is_string'));

        return $decoded;
    }

    public function plainText(string $html): string
    {
        return trim(preg_replace('/\s+/u', ' ', html_entity_decode(strip_tags($html))) ?? '');
    }

    public function safeHtml(string $text): string
    {
        $text = trim($text);
        return '<p>'.nl2br(e($text), false).'</p>';
    }
}
