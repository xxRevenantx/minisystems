<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class PdfBatch extends Model
{
    protected $fillable = [
        'uuid',
        'user_id',
        'operation',
        'status',
        'settings',
        'secret',
        'total_files',
        'uploaded_files',
        'processed_files',
        'completed_files',
        'failed_files',
        'original_bytes',
        'output_bytes',
        'output_name',
        'output_path',
        'error',
        'started_at',
        'completed_at',
        'expires_at',
    ];

    protected function casts(): array
    {
        return [
            'settings' => 'array',
            'secret' => 'encrypted:array',
            'started_at' => 'datetime',
            'completed_at' => 'datetime',
            'expires_at' => 'datetime',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function items(): HasMany
    {
        return $this->hasMany(PdfItem::class)->orderBy('position');
    }

    public function getRouteKeyName(): string
    {
        return 'uuid';
    }

    public function basePath(): string
    {
        return 'system-pdf/'.$this->user_id.'/'.$this->uuid;
    }

    public function isTerminal(): bool
    {
        return in_array($this->status, ['completed', 'partial', 'failed'], true);
    }
}
