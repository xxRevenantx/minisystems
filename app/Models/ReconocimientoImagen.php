<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class ReconocimientoImagen extends Model
{
  use HasFactory;

    protected $table = 'reconocimiento_imagenes';

    protected $fillable = [
        'imagen',
        'descripcion',
    ];
}
