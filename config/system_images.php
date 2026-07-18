<?php

return [
    'max_files' => (int) env('SYSTEM_IMAGES_MAX_FILES', 500),
    'max_file_kb' => (int) env('SYSTEM_IMAGES_MAX_FILE_KB', 20 * 1024),
    'upload_concurrency' => max(1, min(6, (int) env('SYSTEM_IMAGES_UPLOAD_CONCURRENCY', 3))),
    'retention_hours' => (int) env('SYSTEM_IMAGES_RETENTION_HOURS', 24),
    'connection' => env('SYSTEM_IMAGES_QUEUE_CONNECTION', 'database'),
    'queue' => env('SYSTEM_IMAGES_QUEUE', 'system-images'),
    'zip_queue' => env('SYSTEM_IMAGES_ZIP_QUEUE', 'system-images-zip'),
    'stale_processing_minutes' => (int) env('SYSTEM_IMAGES_STALE_MINUTES', 30),
];
