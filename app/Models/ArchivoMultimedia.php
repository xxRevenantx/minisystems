<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class ArchivoMultimedia extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'archivos_multimedia';

    protected $fillable = [
        'user_id','marca_id','proyecto_creativo_id','nombre','categoria','archivo','disk',
        'mime','extension','ancho','alto','peso','orientacion','transparencia','tags',
        'descripcion','activo',
    ];

    protected function casts(): array
    {
        return ['transparencia' => 'boolean', 'tags' => 'array', 'activo' => 'boolean'];
    }

    public function marca() { return $this->belongsTo(Marca::class); }
    public function proyecto() { return $this->belongsTo(ProyectoCreativo::class, 'proyecto_creativo_id'); }
    public function getUrlAttribute(): string { return asset('storage/'.$this->archivo); }
}
