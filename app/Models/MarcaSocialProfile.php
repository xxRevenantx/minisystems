<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MarcaSocialProfile extends Model
{
    use HasFactory;

    protected $fillable = [
        'marca_id','tono_predeterminado','nivel_emojis','idioma','hashtags_fijos','hashtags_bloqueados',
        'palabras_preferidas','palabras_prohibidas','descripcion_voz','firma_predeterminada','instrucciones_ia','activo',
    ];

    protected function casts(): array
    {
        return [
            'hashtags_fijos' => 'array', 'hashtags_bloqueados' => 'array', 'palabras_preferidas' => 'array',
            'palabras_prohibidas' => 'array', 'activo' => 'boolean',
        ];
    }

    public function marca() { return $this->belongsTo(Marca::class); }
}
