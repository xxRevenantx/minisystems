<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;

class EtiquetaAlumno extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'etiqueta_alumnos';

    protected $fillable = [
        'user_id',
        'persona_id',
        'nombre',
        'apellido_paterno',
        'apellido_materno',
        'nivel',
        'generacion',
        'grado',
        'grupo',
        'activo',
        'datos_extra',
    ];

    protected $appends = ['nombre_completo'];

    protected function casts(): array
    {
        return [
            'activo' => 'boolean',
            'datos_extra' => 'array',
        ];
    }

    public function persona()
    {
        return $this->belongsTo(Persona::class);
    }

    public function usuario()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    /** Nombre(s), apellido paterno y apellido materno. */
    public function getNombreCompletoAttribute(): string
    {
        return collect([
            $this->nombre,
            $this->apellido_paterno,
            $this->apellido_materno,
        ])
            ->filter(fn ($parte) => filled($parte))
            ->map(fn ($parte) => Str::squish((string) $parte))
            ->implode(' ');
    }

    public function getInicialesAttribute(): string
    {
        return Str::of($this->nombre_completo)
            ->explode(' ')
            ->filter()
            ->take(2)
            ->map(fn ($parte) => mb_substr((string) $parte, 0, 1, 'UTF-8'))
            ->implode('');
    }

    public function getDetalleAcademicoAttribute(): string
    {
        return collect([
            $this->nivel,
            $this->grado,
            $this->grupo ? 'Grupo '.$this->grupo : null,
        ])->filter()->implode(' · ');
    }
}
