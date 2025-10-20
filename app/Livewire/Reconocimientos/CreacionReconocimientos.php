<?php

namespace App\Livewire\Reconocimientos;

use Livewire\Component;

class CreacionReconocimientos extends Component
{

    public $reconocimiento;

    use \Livewire\WithFileUploads;

    public function render()
    {
        return view('livewire.reconocimientos.creacion-reconocimientos');
    }
}
