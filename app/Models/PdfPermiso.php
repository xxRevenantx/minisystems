<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PdfPermiso extends Model
{
    protected $table = 'pdf_permisos';

    protected $fillable = [
        'user_id',
        'ver',
        'procesar',
        'descargar',
        'eliminar',
        'administrar',
    ];

    protected function casts(): array
    {
        return [
            'ver' => 'boolean',
            'procesar' => 'boolean',
            'descargar' => 'boolean',
            'eliminar' => 'boolean',
            'administrar' => 'boolean',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
