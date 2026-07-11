<?php

namespace App\Http\Controllers;

use App\Models\Reconocimiento;
use Illuminate\Http\Request;

class ReconocimientoController extends Controller
{
    public function index(Request $request)
    {
        abort_unless($request->user()?->puedeReconocimientos('ver'), 403);
        $tab = in_array($request->string('tab')->toString(), ['panel','eventos','reconocimientos','plantillas','configuracion'], true)
            ? $request->string('tab')->toString() : 'panel';
        return view('reconocimientos.index', compact('tab'));
    }

    public function imagenes(Request $request)
    {
        abort_unless($request->user()?->puedeReconocimientos('administrar'), 403);
        return view('reconocimientos.imagenes');
    }

    public function editar(Request $request, int $id)
    {
        $reconocimiento = Reconocimiento::findOrFail($id);
        abort_unless($request->user()?->puedeReconocimientos('editar'), 403);
        return view('reconocimientos.editar', compact('reconocimiento'));
    }
}
