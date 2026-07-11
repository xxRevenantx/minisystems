<?php

namespace App\Livewire\Reconocimientos;

use App\Models\Reconocimiento;
use App\Models\ReconocimientoEvento;
use Livewire\Component;

class ResumenReconocimientos extends Component
{
    public function mount(): void { abort_unless(auth()->user()?->puedeReconocimientos('ver'), 403); }

    public function render()
    {
        return view('livewire.reconocimientos.resumen-reconocimientos', [
            'stats'=>[
                'total'=>Reconocimiento::count(),'mes'=>Reconocimiento::whereBetween('created_at',[now()->startOfMonth(),now()->endOfMonth()])->count(),
                'revision'=>Reconocimiento::where('estado','revision')->count(),'aprobados'=>Reconocimiento::where('estado','aprobado')->count(),
                'entregados'=>Reconocimiento::where('estado','entregado')->count(),'cancelados'=>Reconocimiento::where('estado','cancelado')->count(),
            ],
            'eventos'=>ReconocimientoEvento::withCount('reconocimientos')->orderByDesc('fecha')->limit(6)->get(),
            'recientes'=>Reconocimiento::with(['evento','tipo'])->latest()->limit(8)->get(),
            'porTipo'=>Reconocimiento::query()->selectRaw("COALESCE(reconocimiento_tipos.nombre, 'Sin tipo') as nombre, COUNT(*) as total")
                ->leftJoin('reconocimiento_tipos','reconocimiento_tipos.id','=','reconocimientos.reconocimiento_tipo_id')
                ->groupBy('reconocimiento_tipos.nombre')->orderByDesc('total')->limit(8)->get(),
        ]);
    }
}
