<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PresetSocial extends Model
{
    use HasFactory;

    protected $table = 'presets_sociales';

    protected $fillable = ['nombre','red_social','ancho','alto','orientacion','descripcion','activo'];

    protected function casts(): array { return ['activo' => 'boolean']; }

    public function getMedidasAttribute(): string { return $this->ancho.' × '.$this->alto; }
}
