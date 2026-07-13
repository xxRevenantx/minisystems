<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Storage;

class EtiquetaPlantilla extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'etiqueta_plantillas';

    protected $fillable = [
        'user_id', 'nombre', 'nivel', 'descripcion', 'fondo', 'disk', 'es_predeterminada', 'activo', 'configuracion',
    ];

    protected function casts(): array
    {
        return ['es_predeterminada' => 'boolean', 'activo' => 'boolean', 'configuracion' => 'array'];
    }

    public function usuario() { return $this->belongsTo(User::class, 'user_id'); }

    public function getUrlAttribute(): ?string
    {
        return $this->fondo ? Storage::disk($this->disk ?: 'public')->url($this->fondo) : null;
    }

    public function configuracionConValores(): array
    {
        return array_merge([
            'superior_top' => 3.15,
            'inferior_top' => 17.10,
            'ancho_bloque' => 90,
            'nombre_tamano' => 60,
            'nombre_tamano_medio' => 52,
            'nombre_tamano_largo' => 44,
            'datos_tamano' => 21,
            'nombre_color' => '#111827',
            'datos_color' => '#334155',
            'alineacion' => 'center',
            'mayusculas' => true,
            'mostrar_grado' => true,
            'mostrar_grupo' => true,
            'mostrar_generacion' => true,
        ], $this->configuracion ?? []);
    }
}
