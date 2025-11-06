<?php

namespace App\Http\Controllers;

use App\Models\Reconocimiento;


class ReconocimientoController extends Controller
{

    public function index()
    {
        return view('reconocimientos.index');
    }

    public function imagenes()
    {
        return view('reconocimientos.imagenes');
    }

    public function editar($id)
    {
        $reconocimiento = Reconocimiento::findOrFail($id);
        return view('reconocimientos.editar', compact('reconocimiento'));
    }


}
