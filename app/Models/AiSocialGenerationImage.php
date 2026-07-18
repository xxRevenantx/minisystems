<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AiSocialGenerationImage extends Model
{
    use HasFactory;

    protected $fillable = [
        'ai_social_generation_id','archivo_multimedia_id','nombre_original','ruta_privada','ruta_preview',
        'mime_type','ancho','alto','peso','orientacion','checksum','orden','seleccionada','portada',
        'calidad_score','descripcion_ia','texto_alternativo','metadatos',
    ];

    protected function casts(): array
    {
        return ['seleccionada' => 'boolean', 'portada' => 'boolean', 'metadatos' => 'array', 'calidad_score' => 'decimal:2'];
    }

    public function generacion() { return $this->belongsTo(AiSocialGeneration::class, 'ai_social_generation_id'); }
    public function archivo() { return $this->belongsTo(ArchivoMultimedia::class, 'archivo_multimedia_id'); }
}
