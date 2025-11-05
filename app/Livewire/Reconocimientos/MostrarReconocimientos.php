<?php

namespace App\Livewire\Reconocimientos;

use App\Models\Reconocimiento;
use Livewire\Attributes\On;
use Livewire\Component;

class MostrarReconocimientos extends Component
{

    #[On("reconocimientoCreado")]
    public function render()
    {
        $reconocimientos = Reconocimiento::all();
        return view('livewire.reconocimientos.mostrar-reconocimientos', compact('reconocimientos'));
    }
}
