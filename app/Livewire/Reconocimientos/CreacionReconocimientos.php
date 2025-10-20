<?php

namespace App\Livewire\Reconocimientos;

use Livewire\Component;

class CreacionReconocimientos extends Component
{

    public $reconocimiento;

    use \Livewire\WithFileUploads;

    public function render()
    {
        $reconocimientosImagenes = \App\Models\ReconocimientoImagen::all();
        return view('livewire.reconocimientos.creacion-reconocimientos', compact('reconocimientosImagenes'));
    }
}
