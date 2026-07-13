<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Marca extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'marcas';

    protected $fillable = [
        'user_id','nombre','slug','tipo','contacto','email','telefono','sitio_web',
        'logo','logo_secundario','color_primario','color_secundario','tipografias','notas','activo',
    ];

    protected function casts(): array
    {
        return ['tipografias' => 'array', 'activo' => 'boolean'];
    }

    public function personas() { return $this->hasMany(Persona::class); }
    public function proyectos() { return $this->hasMany(ProyectoCreativo::class); }
    public function archivos() { return $this->hasMany(ArchivoMultimedia::class); }
    public function plantillas() { return $this->hasMany(PlantillaCreativa::class); }
}
