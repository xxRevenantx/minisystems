<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Credencial extends Model
{
    /** @use HasFactory<\Database\Factories\CredencialFactory> */
    use HasFactory;

    protected $table = 'credenciales';

    protected $fillable = [
        'marca_id',
        'proyecto_creativo_id',
        'persona_id',
        'registro_validacion_id',
        'nombre',
        'datos_extra',
        'tiene_reverso',
        'reverso_texto',
        'reverso_imagen',
        'estado',
        'foto',
        'correo',
        'organizacion',
        'cargo',
        'folio',
        'tipo',
        'matricula',
        'curp',
        'nivel',
        'grado',
        'grupo',
        'licenciatura',
        'ciclo_escolar',
        'vigencia',
        'telefono',
        'domicilio',
    ];

    protected function casts(): array
    {
        return ['datos_extra' => 'array', 'tiene_reverso' => 'boolean'];
    }

    public function marca() { return $this->belongsTo(Marca::class); }
    public function proyectoCreativo() { return $this->belongsTo(ProyectoCreativo::class, 'proyecto_creativo_id'); }
    public function persona() { return $this->belongsTo(Persona::class); }
    public function registroValidacion() { return $this->belongsTo(RegistroValidacion::class); }
}

