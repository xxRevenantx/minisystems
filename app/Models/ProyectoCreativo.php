<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class ProyectoCreativo extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'proyectos_creativos';

    protected $fillable = [
        'user_id','marca_id','nombre','tipo','estado','prioridad','fecha_inicio',
        'fecha_entrega','descripcion','tags','configuracion',
    ];

    protected function casts(): array
    {
        return ['fecha_inicio' => 'date', 'fecha_entrega' => 'date', 'tags' => 'array', 'configuracion' => 'array'];
    }

    public function marca() { return $this->belongsTo(Marca::class); }
    public function personas() { return $this->belongsToMany(Persona::class, 'persona_proyecto_creativo')->withPivot('rol')->withTimestamps(); }
    public function archivos() { return $this->hasMany(ArchivoMultimedia::class); }
    public function solicitudes() { return $this->hasMany(SolicitudCreativa::class); }
    public function publicaciones() { return $this->hasMany(PublicacionSocial::class); }
}
