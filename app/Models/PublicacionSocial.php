<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class PublicacionSocial extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'publicaciones_sociales';

    protected $fillable = [
        'user_id','marca_id','proyecto_creativo_id','archivo_multimedia_id','ai_social_generation_id',
        'grupo_publicacion_uuid','titulo','red_social','variante_ia','generada_por_ia','estado','programada_at',
        'publicada_at','copy','copy_html','hashtags','url_publicacion','notas',
    ];

    protected function casts(): array
    {
        return [
            'programada_at' => 'datetime',
            'publicada_at' => 'datetime',
            'generada_por_ia' => 'boolean',
        ];
    }

    public function marca() { return $this->belongsTo(Marca::class); }
    public function proyecto() { return $this->belongsTo(ProyectoCreativo::class, 'proyecto_creativo_id'); }
    public function archivo() { return $this->belongsTo(ArchivoMultimedia::class, 'archivo_multimedia_id'); }
    public function generacionIa() { return $this->belongsTo(AiSocialGeneration::class, 'ai_social_generation_id'); }
    public function archivos()
    {
        return $this->belongsToMany(ArchivoMultimedia::class, 'publicacion_social_archivos')
            ->withPivot(['orden', 'portada', 'texto_alternativo'])
            ->withTimestamps()
            ->orderByPivot('orden');
    }
}
