<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class EtiquetaController extends Controller
{
    public function index(Request $request)
    {
        abort_unless($request->user()?->puedeEtiquetas('ver'), 403);
        return view('etiquetas.index');
    }
}
