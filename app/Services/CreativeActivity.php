<?php

namespace App\Services;

use App\Models\ActividadCreativa;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Schema;

class CreativeActivity
{
    public static function log(string $modulo, string $accion, ?Model $entidad = null, ?string $descripcion = null, array $datos = []): void
    {
        try {
            if (! Schema::hasTable('actividad_creativa')) {
                return;
            }

            ActividadCreativa::create([
                'user_id' => auth()->id(),
                'modulo' => $modulo,
                'accion' => $accion,
                'entidad_tipo' => $entidad ? $entidad::class : null,
                'entidad_id' => $entidad?->getKey(),
                'descripcion' => $descripcion,
                'datos' => $datos ?: null,
            ]);
        } catch (\Throwable) {
            // El registro de actividad no debe interrumpir el flujo principal.
        }
    }
}
