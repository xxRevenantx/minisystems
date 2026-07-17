<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Optimizador de imágenes por lotes
    |--------------------------------------------------------------------------
    |
    | Cada archivo se carga en una petición independiente. Esto evita enviar
    | cientos de megabytes en un único POST y permite continuar el lote aunque
    | una fotografía falle durante la subida o el procesamiento.
    |
    */

    'max_files' => (int) env('IMAGE_OPTIMIZER_MAX_FILES', 100),
    'max_file_kb' => (int) env('IMAGE_OPTIMIZER_MAX_FILE_KB', 20 * 1024),
    'retention_hours' => (int) env('IMAGE_OPTIMIZER_RETENTION_HOURS', 24),
    'connection' => env('IMAGE_OPTIMIZER_QUEUE_CONNECTION', 'database'),
    'queue' => env('IMAGE_OPTIMIZER_QUEUE', 'image-optimizer'),
    'stale_processing_minutes' => (int) env('IMAGE_OPTIMIZER_STALE_MINUTES', 30),
];
