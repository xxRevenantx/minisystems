<?php

return [
    'max_files' => max(1, min(100, (int) env('SYSTEM_PDF_MAX_FILES', 100))),
    'max_file_kb' => max(1024, (int) env('SYSTEM_PDF_MAX_FILE_KB', 250 * 1024)),
    'upload_concurrency' => max(1, min(4, (int) env('SYSTEM_PDF_UPLOAD_CONCURRENCY', 2))),
    'retention_hours' => max(1, (int) env('SYSTEM_PDF_RETENTION_HOURS', 24)),
    'connection' => env('SYSTEM_PDF_QUEUE_CONNECTION', 'database'),
    'queue' => env('SYSTEM_PDF_QUEUE', 'system-pdf'),
    'job_timeout' => max(300, (int) env('SYSTEM_PDF_JOB_TIMEOUT', 3600)),
    'max_preview_pages' => max(1, (int) env('SYSTEM_PDF_MAX_PREVIEW_PAGES', 600)),
    'thumbnail_dpi' => max(18, min(72, (int) env('SYSTEM_PDF_THUMBNAIL_DPI', 30))),
    'zip_part_max_files' => max(1, (int) env('SYSTEM_PDF_ZIP_PART_MAX_FILES', 1000)),
    'zip_part_max_mb' => max(50, (int) env('SYSTEM_PDF_ZIP_PART_MAX_MB', 1000)),

    'binaries' => [
        'ghostscript' => env('SYSTEM_PDF_GHOSTSCRIPT_BINARY'),
        'qpdf' => env('SYSTEM_PDF_QPDF_BINARY'),
        'pdfinfo' => env('SYSTEM_PDF_PDFINFO_BINARY'),
        'pdftoppm' => env('SYSTEM_PDF_PDFTOPPM_BINARY'),
    ],
];
