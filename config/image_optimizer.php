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

    // 0 = sin límite fijo de lote. El navegador sube progresivamente y los ZIP se descargan por partes.
    'max_files' => (int) env('IMAGE_OPTIMIZER_MAX_FILES', 0),
    'max_file_kb' => (int) env('IMAGE_OPTIMIZER_MAX_FILE_KB', 20 * 1024),
    'upload_concurrency' => max(1, min(6, (int) env('IMAGE_OPTIMIZER_UPLOAD_CONCURRENCY', 2))),
    'retention_hours' => (int) env('IMAGE_OPTIMIZER_RETENTION_HOURS', 24),
    'connection' => env('IMAGE_OPTIMIZER_QUEUE_CONNECTION', 'database'),
    'queue' => env('IMAGE_OPTIMIZER_QUEUE', 'image-optimizer'),
    'stale_processing_minutes' => (int) env('IMAGE_OPTIMIZER_STALE_MINUTES', 30),
    'zip_part_max_files' => max(1, (int) env('IMAGE_OPTIMIZER_ZIP_PART_MAX_FILES', 100)),
    'zip_part_max_mb' => max(1, (int) env('IMAGE_OPTIMIZER_ZIP_PART_MAX_MB', 500)),
];
