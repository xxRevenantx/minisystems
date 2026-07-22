<?php

return [
    // 0 = sin límite fijo de lote. Las imágenes se suben progresivamente y el ZIP sale por partes.
    'max_files' => (int) env('SYSTEM_IMAGES_MAX_FILES', 0),
    'max_file_kb' => (int) env('SYSTEM_IMAGES_MAX_FILE_KB', 20 * 1024),
    'upload_concurrency' => max(1, min(6, (int) env('SYSTEM_IMAGES_UPLOAD_CONCURRENCY', 3))),
    'retention_hours' => (int) env('SYSTEM_IMAGES_RETENTION_HOURS', 24),
    'connection' => env('SYSTEM_IMAGES_QUEUE_CONNECTION', 'database'),
    'queue' => env('SYSTEM_IMAGES_QUEUE', 'system-images'),
    'zip_queue' => env('SYSTEM_IMAGES_ZIP_QUEUE', 'system-images-zip'),
    'stale_processing_minutes' => (int) env('SYSTEM_IMAGES_STALE_MINUTES', 30),
    'zip_part_max_files' => max(1, (int) env('SYSTEM_IMAGES_ZIP_PART_MAX_FILES', 100)),
    'zip_part_max_mb' => max(1, (int) env('SYSTEM_IMAGES_ZIP_PART_MAX_MB', 500)),
];
