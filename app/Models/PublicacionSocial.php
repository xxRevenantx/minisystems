<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class PublicacionSocial extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'publicaciones_sociales';

    protected $fillable = [
        'user_id','marca_id','proyecto_creativo_id','archivo_multimedia_id','titulo','red_social',
        'estado','programada_at','publicada_at','copy','hashtags','url_publicacion','notas',
    ];

    protected function casts(): array
    {
        return ['programada_at' => 'datetime', 'publicada_at' => 'datetime'];
    }

    public function marca() { return $this->belongsTo(Marca::class); }
    public function proyecto() { return $this->belongsTo(ProyectoCreativo::class, 'proyecto_creativo_id'); }
    public function archivo() { return $this->belongsTo(ArchivoMultimedia::class, 'archivo_multimedia_id'); }
}
