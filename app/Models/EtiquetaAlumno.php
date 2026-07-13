<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class EtiquetaAlumno extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'etiqueta_alumnos';

    protected $fillable = [
        'user_id', 'persona_id', 'nombre', 'nivel', 'generacion', 'grado', 'grupo', 'activo', 'datos_extra',
    ];

    protected function casts(): array
    {
        return ['activo' => 'boolean', 'datos_extra' => 'array'];
    }

    public function persona() { return $this->belongsTo(Persona::class); }
    public function usuario() { return $this->belongsTo(User::class, 'user_id'); }

    public function getDetalleAcademicoAttribute(): string
    {
        return collect([$this->nivel, $this->grado, $this->grupo ? 'Grupo '.$this->grupo : null])
            ->filter()->implode(' · ');
    }
}
