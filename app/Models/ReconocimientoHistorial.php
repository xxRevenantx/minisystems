<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ReconocimientoHistorial extends Model
{
    protected $fillable = ['reconocimiento_id', 'user_id', 'accion', 'descripcion', 'cambios'];
    protected function casts(): array { return ['cambios' => 'array']; }
    public function reconocimiento() { return $this->belongsTo(Reconocimiento::class); }
    public function usuario() { return $this->belongsTo(User::class, 'user_id'); }
}
