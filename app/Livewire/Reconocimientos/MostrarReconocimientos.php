<?php

namespace App\Livewire\Reconocimientos;

use App\Models\Reconocimiento;
use Livewire\Attributes\On;
use Livewire\Component;
use Livewire\WithPagination;

class MostrarReconocimientos extends Component
{

     use WithPagination;

    #[On("reconocimientoCreado")]
    public function render()
    {
        $reconocimientos = Reconocimiento::paginate(10);
        return view('livewire.reconocimientos.mostrar-reconocimientos', compact('reconocimientos'));
    }
}
