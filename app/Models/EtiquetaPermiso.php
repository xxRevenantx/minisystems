<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class EtiquetaPermiso extends Model
{
    protected $table = 'etiqueta_permisos';

    protected $fillable = ['user_id','ver','crear','editar','eliminar','importar','descargar','administrar'];

    protected function casts(): array
    {
        return [
            'ver'=>'boolean','crear'=>'boolean','editar'=>'boolean','eliminar'=>'boolean',
            'importar'=>'boolean','descargar'=>'boolean','administrar'=>'boolean',
        ];
    }

    public function usuario() { return $this->belongsTo(User::class, 'user_id'); }
}
