<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ImageOptimizerItem extends Model
{
    protected $fillable = [
        'image_optimizer_batch_id',
        'uuid',
        'position',
        'client_fingerprint',
        'relative_path',
        'original_name',
        'stored_name',
        'source_path',
        'output_name',
        'output_path',
        'mime',
        'extension',
        'original_size',
        'optimized_size',
        'saved_bytes',
        'original_width',
        'original_height',
        'width',
        'height',
        'format',
        'quality',
        'reduction',
        'status',
        'warnings',
        'error',
        'attempts',
        'uploaded_at',
        'processed_at',
    ];

    protected function casts(): array
    {
        return [
            'warnings' => 'array',
            'reduction' => 'float',
            'uploaded_at' => 'datetime',
            'processed_at' => 'datetime',
        ];
    }

    public function batch(): BelongsTo
    {
        return $this->belongsTo(ImageOptimizerBatch::class, 'image_optimizer_batch_id');
    }

    public function getRouteKeyName(): string
    {
        return 'uuid';
    }
}
