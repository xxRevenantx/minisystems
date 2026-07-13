<?php

namespace App\Http\Controllers;

use App\Models\RegistroValidacion;

class ValidationController extends Controller
{
    public function show(string $codigo)
    {
        $registro = RegistroValidacion::with(['persona', 'proyecto'])
            ->where('codigo', $codigo)
            ->first();

        return view('creative.validation-public', compact('registro', 'codigo'));
    }
}
