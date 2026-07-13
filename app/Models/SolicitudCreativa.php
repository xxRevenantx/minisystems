<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class SolicitudCreativa extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'solicitudes_creativas';

    protected $fillable = [
        'user_id','marca_id','proyecto_creativo_id','titulo','tipo','estado','prioridad',
        'solicitante','contacto','fecha_entrega','descripcion','notas',
    ];

    protected function casts(): array { return ['fecha_entrega' => 'datetime']; }

    public function marca() { return $this->belongsTo(Marca::class); }
    public function proyecto() { return $this->belongsTo(ProyectoCreativo::class, 'proyecto_creativo_id'); }
}
