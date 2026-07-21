<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PdfItem extends Model
{
    protected $fillable = [
        'pdf_batch_id',
        'uuid',
        'position',
        'client_fingerprint',
        'original_name',
        'stored_name',
        'source_path',
        'mime',
        'extension',
        'original_size',
        'page_count',
        'encrypted',
        'status',
        'output_name',
        'output_path',
        'output_size',
        'result_files',
        'thumbnails',
        'warnings',
        'secret',
        'error',
        'attempts',
        'uploaded_at',
        'processed_at',
    ];

    protected function casts(): array
    {
        return [
            'encrypted' => 'boolean',
            'result_files' => 'array',
            'thumbnails' => 'array',
            'warnings' => 'array',
            'secret' => 'encrypted',
            'uploaded_at' => 'datetime',
            'processed_at' => 'datetime',
        ];
    }

    public function batch(): BelongsTo
    {
        return $this->belongsTo(PdfBatch::class, 'pdf_batch_id');
    }

    public function getRouteKeyName(): string
    {
        return 'uuid';
    }
}
