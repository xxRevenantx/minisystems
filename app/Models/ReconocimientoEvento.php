<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class ReconocimientoEvento extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = ['nombre', 'categoria', 'fecha', 'lugar', 'nivel', 'ciclo_escolar', 'reconocimiento_tipo_id', 'reconocimiento_imagen_id', 'estado', 'observaciones', 'created_by'];

    protected function casts(): array { return ['fecha' => 'date']; }

    public function tipo() { return $this->belongsTo(ReconocimientoTipo::class, 'reconocimiento_tipo_id'); }
    public function imagen() { return $this->belongsTo(ReconocimientoImagen::class, 'reconocimiento_imagen_id'); }
    public function reconocimientos() { return $this->hasMany(Reconocimiento::class); }
    public function creador() { return $this->belongsTo(User::class, 'created_by'); }
}
