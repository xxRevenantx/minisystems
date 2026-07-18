<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SystemImageItem extends Model
{
    protected $fillable = [
        'system_image_batch_id',
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
        'processed_size',
        'original_width',
        'original_height',
        'width',
        'height',
        'orientation',
        'settings',
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
            'settings' => 'array',
            'warnings' => 'array',
            'uploaded_at' => 'datetime',
            'processed_at' => 'datetime',
        ];
    }

    public function batch(): BelongsTo
    {
        return $this->belongsTo(SystemImageBatch::class, 'system_image_batch_id');
    }

    public function getRouteKeyName(): string
    {
        return 'uuid';
    }
}
