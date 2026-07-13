<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Persona extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'personas';

    protected $fillable = [
        'user_id','marca_id','tipo','nombre','foto','cargo','organizacion','email',
        'telefono','identificador','tags','datos_extra','notas','activo',
    ];

    protected function casts(): array
    {
        return ['tags' => 'array', 'datos_extra' => 'array', 'activo' => 'boolean'];
    }

    public function marca() { return $this->belongsTo(Marca::class); }
    public function proyectos() { return $this->belongsToMany(ProyectoCreativo::class, 'persona_proyecto_creativo')->withPivot('rol')->withTimestamps(); }
    public function validaciones() { return $this->hasMany(RegistroValidacion::class); }
}
