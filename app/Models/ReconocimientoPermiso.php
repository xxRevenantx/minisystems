<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ReconocimientoPermiso extends Model
{
    protected $fillable = ['user_id', 'ver', 'crear', 'editar', 'aprobar', 'descargar', 'cancelar', 'administrar'];
    protected function casts(): array
    {
        return ['ver'=>'boolean','crear'=>'boolean','editar'=>'boolean','aprobar'=>'boolean','descargar'=>'boolean','cancelar'=>'boolean','administrar'=>'boolean'];
    }
    public function usuario() { return $this->belongsTo(User::class, 'user_id'); }
}
