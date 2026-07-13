<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Reconocimiento extends Model
{
    use HasFactory, SoftDeletes;



    public const ESTADOS = ['borrador', 'revision', 'aprobado', 'generado', 'entregado', 'cancelado'];

    protected $fillable = [
        'marca_id',
        'proyecto_creativo_id',
        'persona_id',
        'registro_validacion_id',
        'reconocimiento_evento_id',
        'reconocimiento_tipo_id',
        'credencial_id',
        'destinatario_tipo',
        'reconocimiento_imagen_id',
        'reconocimiento_a',
        'lugar_obtenido',
        'descripcion',
        'fecha',
        'estado',
        'version',
        'duplicado_de_id',
        'created_by',
        'approved_by',
        'approved_at',
        'generated_at',
        'delivered_at',
        'delivery_method',
        'delivery_to',
        'delivery_notes',
        'cancel_reason',
    ];

    protected function casts(): array
    {
        return [
            'fecha' => 'date',
            'approved_at' => 'datetime',
            'generated_at' => 'datetime',
            'delivered_at' => 'datetime',
        ];
    }

    public function marca()
    {
        return $this->belongsTo(Marca::class);
    }
    public function proyectoCreativo()
    {
        return $this->belongsTo(ProyectoCreativo::class, 'proyecto_creativo_id');
    }
    public function persona()
    {
        return $this->belongsTo(Persona::class);
    }
    public function registroValidacion()
    {
        return $this->belongsTo(RegistroValidacion::class);
    }
    public function reconocimientoImagen()
    {
        return $this->belongsTo(ReconocimientoImagen::class, 'reconocimiento_imagen_id');
    }
    public function directivos()
    {
        return $this->belongsToMany(Directivo::class, 'directivo_reconocimiento')->withTimestamps();
    }
    public function evento()
    {
        return $this->belongsTo(ReconocimientoEvento::class, 'reconocimiento_evento_id');
    }
    public function tipo()
    {
        return $this->belongsTo(ReconocimientoTipo::class, 'reconocimiento_tipo_id');
    }
    public function credencial()
    {
        return $this->belongsTo(Credencial::class);
    }
    public function creador()
    {
        return $this->belongsTo(User::class, 'created_by');
    }
    public function aprobador()
    {
        return $this->belongsTo(User::class, 'approved_by');
    }
    public function historial()
    {
        return $this->hasMany(ReconocimientoHistorial::class)->latest();
    }
    public function original()
    {
        return $this->belongsTo(self::class, 'duplicado_de_id');
    }

    public function registrarHistorial(string $accion, ?string $descripcion = null, array $cambios = []): void
    {
        $this->historial()->create([
            'user_id' => auth()->id(),
            'accion' => $accion,
            'descripcion' => $descripcion,
            'cambios' => $cambios ?: null,
        ]);
    }
}
