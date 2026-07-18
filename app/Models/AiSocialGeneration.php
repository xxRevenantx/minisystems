<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class AiSocialGeneration extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'uuid','user_id','marca_id','proyecto_creativo_id','estado','plataformas','idioma','tono','intensidad',
        'extension','nivel_emojis','nombre_evento','fecha_evento','lugar_evento','tipo_evento','nivel_educativo',
        'objetivo','resultados_logros','personas_autorizadas','contexto_adicional','cta_tipo','cta_personalizado',
        'autorizacion_publicacion','contiene_menores','modelo','prompt_version','prompt_sistema','prompt_usuario',
        'respuesta_estructurada','respuesta_original','tokens_entrada','tokens_salida','tokens_totales','duracion_ms',
        'groq_request_id','mensaje_error','iniciada_at','completada_at','expira_at',
    ];

    protected function casts(): array
    {
        return [
            'plataformas' => 'array', 'respuesta_estructurada' => 'array', 'fecha_evento' => 'date',
            'autorizacion_publicacion' => 'boolean', 'contiene_menores' => 'boolean',
            'iniciada_at' => 'datetime', 'completada_at' => 'datetime', 'expira_at' => 'datetime',
        ];
    }

    public function user() { return $this->belongsTo(User::class); }
    public function marca() { return $this->belongsTo(Marca::class); }
    public function proyecto() { return $this->belongsTo(ProyectoCreativo::class, 'proyecto_creativo_id'); }
    public function imagenes() { return $this->hasMany(AiSocialGenerationImage::class)->orderBy('orden'); }
    public function versiones() { return $this->hasMany(AiSocialVersion::class); }
    public function publicaciones() { return $this->hasMany(PublicacionSocial::class); }
}
