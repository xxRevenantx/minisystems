<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Directivo extends Model
{
    use HasFactory;

    protected $fillable = ['titulo', 'nombre', 'cargo', 'firma', 'sello', 'vigencia_inicio', 'vigencia_fin', 'niveles', 'orden', 'activo'];

    protected function casts(): array
    {
        return ['vigencia_inicio'=>'date','vigencia_fin'=>'date','niveles'=>'array','activo'=>'boolean'];
    }

    public function reconocimientos() { return $this->belongsToMany(Reconocimiento::class, 'directivo_reconocimiento'); }

    public function getNombreCompletoAttribute(): string
    {
        return trim($this->titulo.' '.$this->nombre);
    }
}
