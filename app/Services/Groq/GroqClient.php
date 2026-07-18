<?php

namespace App\Services\Groq;

use Illuminate\Http\Client\PendingRequest;
use Illuminate\Support\Facades\Http;
use RuntimeException;

class GroqClient
{
    public function configured(): bool
    {
        return filled(config('groq.api_key'));
    }

    /**
     * @param array<int, array{role:string,content:mixed}> $messages
     * @return array{content:string,usage:array<string,mixed>,request_id:?string,status:int,headers:array<string,mixed>,raw:array<string,mixed>}
     */
    public function chat(array $messages, ?string $model = null, bool $json = true): array
    {
        if (! $this->configured()) {
            throw new RuntimeException('Groq no está configurado. Agrega GROQ_API_KEY en el archivo .env.');
        }

        $payload = [
            'model' => $model ?: config('groq.text_model'),
            'messages' => $messages,
            'temperature' => 0.45,
            'max_completion_tokens' => (int) config('groq.max_output_tokens', 5000),
        ];

        if ($json) {
            $payload['response_format'] = ['type' => 'json_object'];
        }

        $started = microtime(true);
        $response = $this->request()->post('/chat/completions', $payload);
        $duration = (int) round((microtime(true) - $started) * 1000);

        if ($response->status() === 429) {
            $retryAfter = max(10, (int) $response->header('retry-after', 30));
            throw new GroqRateLimitException('Groq alcanzó temporalmente su límite de uso.', $retryAfter);
        }

        if ($response->failed()) {
            $message = (string) data_get($response->json(), 'error.message', 'Groq no pudo procesar la solicitud.');
            throw new RuntimeException($message, $response->status());
        }

        $jsonBody = $response->json();

        return [
            'content' => (string) data_get($jsonBody, 'choices.0.message.content', ''),
            'usage' => (array) ($jsonBody['usage'] ?? []),
            'request_id' => $response->header('x-request-id'),
            'status' => $response->status(),
            'headers' => [
                'remaining_requests' => $response->header('x-ratelimit-remaining-requests'),
                'remaining_tokens' => $response->header('x-ratelimit-remaining-tokens'),
                'retry_after' => $response->header('retry-after'),
                'duration_ms' => $duration,
            ],
            'raw' => is_array($jsonBody) ? $jsonBody : [],
        ];
    }

    private function request(): PendingRequest
    {
        return Http::baseUrl((string) config('groq.base_url'))
            ->withToken((string) config('groq.api_key'))
            ->acceptJson()
            ->asJson()
            ->connectTimeout((int) config('groq.connect_timeout', 15))
            ->timeout((int) config('groq.timeout', 120));
    }
}
