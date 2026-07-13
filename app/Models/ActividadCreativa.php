<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ActividadCreativa extends Model
{
    use HasFactory;

    protected $table = 'actividad_creativa';

    protected $fillable = ['user_id','modulo','accion','entidad_tipo','entidad_id','descripcion','datos'];

    protected function casts(): array { return ['datos' => 'array']; }
}
