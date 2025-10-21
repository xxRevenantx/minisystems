<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Directivo extends Model
{
    /** @use HasFactory<\Database\Factories\DirectivoFactory> */
    use HasFactory;

    protected $fillable = [
        'titulo',
        'nombre',
        'cargo',
    ];

      public function reconocimientos()
    {
        return $this->belongsToMany(Reconocimiento::class, 'directivo_reconocimiento');
    }

}
