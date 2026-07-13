<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class RegistroValidacion extends Model
{
    use HasFactory;

    protected $table = 'registros_validacion';

    protected $fillable = [
        'user_id','persona_id','proyecto_creativo_id','codigo','tipo','titulo','estado',
        'emitido_at','vence_at','datos_publicos','notas',
    ];

    protected function casts(): array
    {
        return ['emitido_at' => 'date', 'vence_at' => 'date', 'datos_publicos' => 'array'];
    }

    public function persona() { return $this->belongsTo(Persona::class); }
    public function proyecto() { return $this->belongsTo(ProyectoCreativo::class, 'proyecto_creativo_id'); }
}
