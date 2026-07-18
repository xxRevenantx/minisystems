<?php

return [
    'api_key' => env('GROQ_API_KEY'),

    'base_url' => rtrim((string) env('GROQ_BASE_URL', 'https://api.groq.com/openai/v1'), '/'),

    'vision_model' => env('GROQ_VISION_MODEL', 'meta-llama/llama-4-scout-17b-16e-instruct'),
    'text_model' => env('GROQ_TEXT_MODEL', 'openai/gpt-oss-20b'),

    'timeout' => (int) env('GROQ_TIMEOUT', 120),
    'connect_timeout' => (int) env('GROQ_CONNECT_TIMEOUT', 15),

    // La interfaz admite hasta 10 fotografías. El job las analiza en bloques
    // de cinco para respetar los límites de los modelos visuales de Groq.
    'max_images' => (int) env('GROQ_SOCIAL_MAX_IMAGES', 10),
    'vision_batch_size' => min(5, max(1, (int) env('GROQ_VISION_BATCH_SIZE', 5))),
    'preview_max_bytes' => (int) env('GROQ_PREVIEW_MAX_BYTES', 550000),
    'max_output_tokens' => (int) env('GROQ_SOCIAL_MAX_OUTPUT_TOKENS', 5000),

    'daily_limit_per_user' => (int) env('GROQ_DAILY_LIMIT_PER_USER', 30),
    'queue' => env('GROQ_QUEUE', 'ai-social'),
    'temporary_retention_hours' => (int) env('GROQ_TEMPORARY_RETENTION_HOURS', 24),
    'store_raw_response' => filter_var(env('GROQ_STORE_RAW_RESPONSE', true), FILTER_VALIDATE_BOOL),
];
