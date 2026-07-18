<?php

namespace App\Jobs;

use App\Models\AiSocialGeneration;
use App\Models\AiSocialVersion;
use App\Models\AiUsageLog;
use App\Services\Groq\GroqClient;
use App\Services\Groq\GroqRateLimitException;
use App\Services\Groq\SocialCopyResponseParser;
use App\Services\Groq\SocialPromptBuilder;
use App\Services\Images\AiImagePreviewService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Throwable;

class GenerateSocialCopyWithGroq implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries = 4;
    public int $timeout = 300;
    public int $maxExceptions = 3;

    public function __construct(public readonly int $generationId)
    {
        $this->onQueue((string) config('groq.queue', 'ai-social'));
    }

    public function backoff(): array
    {
        return [15, 60, 180, 600];
    }

    public function handle(
        GroqClient $client,
        SocialPromptBuilder $prompts,
        SocialCopyResponseParser $parser,
        AiImagePreviewService $previews,
    ): void {
        $generation = AiSocialGeneration::with(['imagenes', 'marca.perfilSocial'])->findOrFail($this->generationId);

        if (in_array($generation->estado, ['completada', 'aprobada', 'enviada_publicaciones'], true)) {
            return;
        }

        $generation->update([
            'estado' => 'analizando',
            'modelo' => config('groq.vision_model'),
            'iniciada_at' => now(),
            'mensaje_error' => null,
        ]);

        $visualSummaries = [];
        $inputTokens = 0;
        $outputTokens = 0;
        $duration = 0;
        $requestId = null;
        $batchSize = (int) config('groq.vision_batch_size', 5);
        $selected = $generation->imagenes->where('seleccionada', true)->values();

        foreach ($selected->chunk($batchSize) as $batchIndex => $batch) {
            $content = [[
                'type' => 'text',
                'text' => $prompts->vision($batchIndex * $batchSize, $batch->count()),
            ]];

            foreach ($batch as $image) {
                $content[] = [
                    'type' => 'image_url',
                    'image_url' => [
                        'url' => $previews->dataUri((string) $image->ruta_privada, (string) ($image->mime_type ?: 'image/jpeg')),
                        'detail' => 'low',
                    ],
                ];
            }

            $response = $client->chat([
                ['role' => 'system', 'content' => $prompts->system()],
                ['role' => 'user', 'content' => $content],
            ], (string) config('groq.vision_model'));

            $decoded = $parser->parse($response['content']);
            $visualSummaries[] = (string) ($decoded['resumen'] ?? $decoded['resumen_visual'] ?? 'Actividad registrada en las fotografías.');
            $inputTokens += (int) ($response['usage']['prompt_tokens'] ?? 0);
            $outputTokens += (int) ($response['usage']['completion_tokens'] ?? 0);
            $duration += (int) ($response['headers']['duration_ms'] ?? 0);
            $requestId ??= $response['request_id'];

            foreach ((array) ($decoded['imagenes'] ?? []) as $visualImage) {
                $absoluteIndex = max(1, (int) ($visualImage['indice'] ?? 1));
                $modelImage = $selected->get($absoluteIndex - 1);
                $modelImage?->update([
                    'descripcion_ia' => $visualImage['descripcion'] ?? null,
                    'texto_alternativo' => $visualImage['texto_alternativo'] ?? null,
                ]);
            }
        }

        $systemPrompt = $prompts->system();
        $userPrompt = $prompts->final($generation, $visualSummaries);
        $final = $client->chat([
            ['role' => 'system', 'content' => $systemPrompt],
            ['role' => 'user', 'content' => $userPrompt],
        ], (string) config('groq.text_model'));
        $structured = $parser->parse($final['content']);

        $inputTokens += (int) ($final['usage']['prompt_tokens'] ?? 0);
        $outputTokens += (int) ($final['usage']['completion_tokens'] ?? 0);
        $duration += (int) ($final['headers']['duration_ms'] ?? 0);
        $requestId = $final['request_id'] ?: $requestId;

        foreach ((array) ($structured['imagenes'] ?? []) as $imageData) {
            $index = max(1, (int) ($imageData['indice'] ?? 1));
            $selected->get($index - 1)?->update([
                'descripcion_ia' => $imageData['descripcion'] ?? null,
                'texto_alternativo' => $imageData['texto_alternativo'] ?? null,
            ]);
        }

        AiSocialVersion::where('ai_social_generation_id', $generation->id)->delete();
        foreach ((array) ($structured['plataformas'] ?? []) as $platform => $variants) {
            foreach ((array) $variants as $variant => $copy) {
                if (! is_array($copy)) {
                    continue;
                }
                $plain = trim((string) ($copy['copy'] ?? ''));
                AiSocialVersion::create([
                    'ai_social_generation_id' => $generation->id,
                    'plataforma' => strtolower((string) $platform),
                    'variante' => strtolower((string) $variant),
                    'version' => 1,
                    'titulo' => $copy['titulo'] ?? null,
                    'copy_html' => $parser->safeHtml($plain),
                    'copy_texto' => $plain,
                    'hashtags' => array_values(array_unique(array_filter((array) ($copy['hashtags'] ?? []), 'is_string'))),
                    'cta' => $copy['cta'] ?? null,
                    'caracteres' => mb_strlen($plain),
                ]);
            }
        }

        $generation->update([
            'estado' => 'completada',
            'prompt_sistema' => $systemPrompt,
            'prompt_usuario' => $userPrompt,
            'respuesta_estructurada' => $structured,
            'respuesta_original' => config('groq.store_raw_response') ? $final['content'] : null,
            'tokens_entrada' => $inputTokens,
            'tokens_salida' => $outputTokens,
            'tokens_totales' => $inputTokens + $outputTokens,
            'duracion_ms' => $duration,
            'groq_request_id' => $requestId,
            'completada_at' => now(),
        ]);

        AiUsageLog::create([
            'user_id' => $generation->user_id,
            'ai_social_generation_id' => $generation->id,
            'proveedor' => 'groq',
            'modelo' => config('groq.text_model'),
            'endpoint' => 'chat/completions',
            'estado' => 'completada',
            'tokens_entrada' => $inputTokens,
            'tokens_salida' => $outputTokens,
            'tokens_totales' => $inputTokens + $outputTokens,
            'duracion_ms' => $duration,
            'http_status' => 200,
            'request_id' => $requestId,
        ]);
    }

    public function failed(Throwable $exception): void
    {
        $generation = AiSocialGeneration::find($this->generationId);
        $generation?->update([
            'estado' => 'con_errores',
            'mensaje_error' => $exception->getMessage(),
        ]);

        if ($generation) {
            AiUsageLog::create([
                'user_id' => $generation->user_id,
                'ai_social_generation_id' => $generation->id,
                'proveedor' => 'groq',
                'modelo' => config('groq.vision_model'),
                'endpoint' => 'chat/completions',
                'estado' => 'error',
                'http_status' => $exception->getCode() ?: null,
                'error' => $exception->getMessage(),
            ]);
        }
    }

    public function retryUntil(): \DateTimeInterface
    {
        return now()->addMinutes(30);
    }

    public function middleware(): array
    {
        return [];
    }

    public function releaseForRateLimit(GroqRateLimitException $exception): void
    {
        $this->release($exception->retryAfter);
    }
}
