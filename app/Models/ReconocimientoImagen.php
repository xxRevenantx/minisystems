<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ReconocimientoImagen extends Model
{
    use HasFactory;

    protected $table = 'reconocimiento_imagenes';
    protected $fillable = ['imagen', 'nombre', 'descripcion', 'orientacion', 'configuracion', 'activo'];

    protected function casts(): array { return ['configuracion' => 'array', 'activo' => 'boolean']; }

    public function reconocimientos() { return $this->hasMany(Reconocimiento::class, 'reconocimiento_imagen_id'); }

    public function config(string $key, mixed $default = null): mixed
    {
        return data_get($this->configuracion ?? [], $key, $default);
    }
}
