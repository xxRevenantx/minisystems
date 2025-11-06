<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Reconocimiento extends Model
{
    /** @use HasFactory<\Database\Factories\ReconocimientoFactory> */
    use HasFactory;



    protected $fillable = [
        'reconocimiento_imagen_id',
        'reconocimiento_a',
        'lugar_obtenido',
        'descripcion',
        'fecha',
    ];

    public function reconocimientoImagen()
    {
        return $this->belongsTo(ReconocimientoImagen::class, 'reconocimiento_imagen_id');
    }

     public function directivos()
    {
        return $this->belongsToMany(Directivo::class , 'directivo_reconocimiento');
    }


}
