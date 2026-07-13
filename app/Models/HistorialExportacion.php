<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class HistorialExportacion extends Model
{
    use HasFactory;

    protected $table = 'historial_exportaciones';

    protected $fillable = [
        'user_id','marca_id','proyecto_creativo_id','plantilla_creativa_id','tipo','formato',
        'archivo','cantidad','configuracion','notas',
    ];

    protected function casts(): array { return ['configuracion' => 'array']; }

    public function marca() { return $this->belongsTo(Marca::class); }
    public function proyecto() { return $this->belongsTo(ProyectoCreativo::class, 'proyecto_creativo_id'); }
    public function plantilla() { return $this->belongsTo(PlantillaCreativa::class, 'plantilla_creativa_id'); }
}
