<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ReconocimientoTipo extends Model
{
    use HasFactory;

    protected $fillable = ['nombre', 'titulo', 'descripcion', 'destinatario_tipo', 'usa_lugar', 'niveles', 'reconocimiento_imagen_id', 'activo'];

    protected function casts(): array
    {
        return ['usa_lugar' => 'boolean', 'niveles' => 'array', 'activo' => 'boolean'];
    }

    public function imagen() { return $this->belongsTo(ReconocimientoImagen::class, 'reconocimiento_imagen_id'); }
    public function reconocimientos() { return $this->hasMany(Reconocimiento::class); }
}
