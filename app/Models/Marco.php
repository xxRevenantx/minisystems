<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Marco extends Model
{
    /** @use HasFactory<\Database\Factories\MarcoFactory> */
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'nombre',
        'marco', // compatibilidad con instalaciones anteriores
        'descripcion',
        'categoria',
        'activo',
        'marco_desktop',
        'marco_mobile',
        'ancho_desktop',
        'alto_desktop',
        'ancho_mobile',
        'alto_mobile',
        'formato_desktop',
        'formato_mobile',
        'transparencia_desktop',
        'transparencia_mobile',
        'tags',
        'notas',
        'orden',
        'usos',
        'ultimo_uso_at',
    ];

    protected function casts(): array
    {
        return [
            'activo' => 'boolean',
            'transparencia_desktop' => 'boolean',
            'transparencia_mobile' => 'boolean',
            'tags' => 'array',
            'ultimo_uso_at' => 'datetime',
        ];
    }

    public function archivoPara(string $orientacion): ?string
    {
        return $orientacion === 'mobile'
            ? $this->marco_mobile
            : ($this->marco_desktop ?: $this->marco);
    }

    public function archivoAlterno(string $orientacion): ?string
    {
        return $orientacion === 'mobile'
            ? ($this->marco_desktop ?: $this->marco)
            : $this->marco_mobile;
    }

    public function rutaPublicaPara(string $orientacion): ?string
    {
        $archivo = $this->archivoPara($orientacion);

        return $archivo ? 'imagenesMarcos/'.$archivo : null;
    }

    public function getCompletoAttribute(): bool
    {
        return filled($this->marco_desktop ?: $this->marco) && filled($this->marco_mobile);
    }
}
