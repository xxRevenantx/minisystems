<?php

namespace App\Services\Groq;

use App\Models\AiSocialGeneration;

class SocialPromptBuilder
{
    public function system(): string
    {
        return <<<'PROMPT'
Eres especialista en comunicación institucional y redacción profesional para redes sociales.
Analiza únicamente lo visible y la información confirmada por el usuario. No inventes nombres, fechas, lugares, cargos, premios, instituciones ni resultados. No identifiques personas por sus rostros.
Cuando aparezcan menores, describe la actividad de forma general, sin inferir edades exactas ni datos personales.
Devuelve exclusivamente JSON válido, sin Markdown.
PROMPT;
    }

    /** @param array<int,string> $visualSummaries */
    public function final(AiSocialGeneration $generation, array $visualSummaries): string
    {
        $profile = $generation->marca?->perfilSocial;
        $hashtags = implode(', ', $profile?->hashtags_fijos ?? []);
        $preferred = implode(', ', $profile?->palabras_preferidas ?? []);
        $forbidden = implode(', ', $profile?->palabras_prohibidas ?? []);
        $platforms = implode(', ', $generation->plataformas ?? []);
        $visual = implode("\n", array_map(fn ($item, $i) => ($i + 1).'. '.$item, $visualSummaries, array_keys($visualSummaries)));

        return <<<PROMPT
Genera contenido para estas plataformas: {$platforms}.
Idioma: {$generation->idioma}.
Tono: {$generation->tono}, intensidad {$generation->intensidad}/5.
Extensión: {$generation->extension}. Emojis: {$generation->nivel_emojis}.
Evento: {$generation->nombre_evento}.
Fecha: {$generation->fecha_evento?->format('Y-m-d')}.
Lugar: {$generation->lugar_evento}.
Tipo de actividad: {$generation->tipo_evento}.
Nivel o público: {$generation->nivel_educativo}.
Objetivo confirmado: {$generation->objetivo}.
Resultados confirmados: {$generation->resultados_logros}.
Personas autorizadas para mencionar: {$generation->personas_autorizadas}.
Contexto adicional: {$generation->contexto_adicional}.
CTA: {$generation->cta_tipo}; texto personalizado: {$generation->cta_personalizado}.
Marca: {$generation->marca?->nombre}.
Hashtags fijos: {$hashtags}.
Palabras preferidas: {$preferred}.
Palabras prohibidas: {$forbidden}.

Resumen visual de las fotografías:
{$visual}

Entrega este esquema JSON exacto:
{
  "resumen_visual": "",
  "datos_faltantes": [],
  "advertencias": [],
  "imagenes": [{"indice": 1, "descripcion": "", "texto_alternativo": ""}],
  "plataformas": {
    "facebook": {
      "breve": {"titulo":"","copy":"","hashtags":[],"cta":""},
      "equilibrada": {"titulo":"","copy":"","hashtags":[],"cta":""},
      "emotiva": {"titulo":"","copy":"","hashtags":[],"cta":""}
    }
  }
}
Incluye solo las plataformas solicitadas. Cada variante debe ser realmente distinta. Los hashtags no deben repetirse.
PROMPT;
    }

    public function vision(int $offset, int $count): string
    {
        return "Analiza estas {$count} fotografías como conjunto para preparar una publicación institucional. Describe actividades, ambiente, objetos, escenario y tema común. No identifiques rostros ni inventes nombres. Devuelve JSON con: resumen, tipo_evento_probable, advertencias e imagenes; cada imagen debe incluir indice absoluto comenzando en ".($offset + 1).", descripcion y texto_alternativo.";
    }
}
