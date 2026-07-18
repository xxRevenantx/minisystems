<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AiUsageLog extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id','ai_social_generation_id','proveedor','modelo','endpoint','estado','tokens_entrada',
        'tokens_salida','tokens_totales','duracion_ms','http_status','request_id','rate_limit_remaining',
        'rate_limit_reset_at','error','metadatos',
    ];

    protected function casts(): array
    {
        return ['rate_limit_reset_at' => 'datetime', 'metadatos' => 'array'];
    }

    public function user() { return $this->belongsTo(User::class); }
    public function generacion() { return $this->belongsTo(AiSocialGeneration::class, 'ai_social_generation_id'); }
}
