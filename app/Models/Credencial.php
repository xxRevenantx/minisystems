<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Credencial extends Model
{
    /** @use HasFactory<\Database\Factories\CredencialFactory> */
    use HasFactory;

    protected $table = 'credenciales';

    protected $fillable = [
        'nombre',
        'matricula',
        'curp',
        'nivel',
        'grado',
        'grupo',
        'licenciatura',
        'ciclo_escolar',
        'vigencia',
        'telefono',
        'domicilio',
    ];
}
