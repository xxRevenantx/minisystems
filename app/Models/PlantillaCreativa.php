<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class PlantillaCreativa extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'plantillas_creativas';

    protected $fillable = [
        'user_id','marca_id','fondo_archivo_id','nombre','tipo','ancho','alto',
        'orientacion','estructura','configuracion_impresion','descripcion','estado','version','activo',
    ];

    protected function casts(): array
    {
        return ['estructura' => 'array', 'configuracion_impresion' => 'array', 'activo' => 'boolean'];
    }

    public function marca() { return $this->belongsTo(Marca::class); }
    public function fondo() { return $this->belongsTo(ArchivoMultimedia::class, 'fondo_archivo_id'); }
    public function versiones() { return $this->hasMany(PlantillaVersion::class)->latest('version'); }
}
