<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class SystemImageBatch extends Model
{
    protected $fillable = [
        'uuid',
        'user_id',
        'status',
        'settings',
        'total_files',
        'uploaded_files',
        'processed_files',
        'completed_files',
        'failed_files',
        'bytes_total',
        'bytes_uploaded',
        'zip_status',
        'zip_path',
        'zip_size',
        'zip_error',
        'started_at',
        'processing_started_at',
        'completed_at',
        'export_registered_at',
        'expires_at',
    ];

    protected function casts(): array
    {
        return [
            'settings' => 'array',
            'started_at' => 'datetime',
            'processing_started_at' => 'datetime',
            'completed_at' => 'datetime',
            'export_registered_at' => 'datetime',
            'expires_at' => 'datetime',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function items(): HasMany
    {
        return $this->hasMany(SystemImageItem::class)->orderBy('position');
    }

    public function getRouteKeyName(): string
    {
        return 'uuid';
    }

    public function basePath(): string
    {
        return 'system-images/'.$this->user_id.'/'.$this->uuid;
    }

    public function isTerminal(): bool
    {
        return in_array($this->status, ['completed', 'partial', 'failed'], true);
    }
}
