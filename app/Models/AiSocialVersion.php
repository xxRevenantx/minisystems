<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AiSocialVersion extends Model
{
    use HasFactory;

    protected $fillable = [
        'ai_social_generation_id','plataforma','variante','version','titulo','copy_html','copy_texto',
        'hashtags','cta','texto_alt_general','textos_alt_imagenes','caracteres','favorita','aprobada','editada_por',
    ];

    protected function casts(): array
    {
        return ['hashtags' => 'array', 'textos_alt_imagenes' => 'array', 'favorita' => 'boolean', 'aprobada' => 'boolean'];
    }

    public function generacion() { return $this->belongsTo(AiSocialGeneration::class, 'ai_social_generation_id'); }
    public function editor() { return $this->belongsTo(User::class, 'editada_por'); }
}
