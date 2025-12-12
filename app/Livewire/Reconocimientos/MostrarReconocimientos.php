<?php

namespace App\Livewire\Reconocimientos;

use App\Models\Reconocimiento;
use Livewire\Attributes\On;
use Livewire\Component;
use Livewire\WithPagination;

class MostrarReconocimientos extends Component
{
    use WithPagination;

    public string $search   = '';
    public int    $perPage  = 10;

    // Mantén search y page en la URL (opcional)
    protected $queryString = [
        'search' => ['except' => ''],
        'page'   => ['except' => 1],
    ];

    // Cuando cambia el search, vuelve a la página 1
    public function updatingSearch()
    {
        $this->resetPage();
    }

    public function clearSearch()
    {
        $this->search = '';
        $this->resetPage();
    }

    // ELIMINAR RECONOCIMIENTO
    public function eliminarReconocimiento($id)
    {
        $reconocimiento = Reconocimiento::find($id);

        if ($reconocimiento) {
            $reconocimiento->delete();

            $this->dispatch('swal', [
                'title' => '¡Reconocimiento eliminado correctamente!',
                'icon' => 'success',
                'position' => 'top-end',
            ]);
        }
    }

    #[On('reconocimientoCreado')]
    public function render()
    {
        $s = trim($this->search);

        $query = Reconocimiento::query()
            ->with(['directivos' => fn($q) => $q->orderBy('id')]);

        if ($s !== '') {
            $query->where(function ($q) use ($s) {
                $like = '%' . $s . '%';
                $q->where('reconocimiento_a', 'like', $like)
                    ->orWhere('lugar_obtenido', 'like', $like)
                    ->orWhere('descripcion', 'like', $like)
                    ->orWhere('fecha', 'like', $like) // simple: 06/11/2025 o 2025-11-06
                    ->orWhereHas('directivos', function ($d) use ($like) {
                        $d->where('titulo', 'like', $like)
                            ->orWhere('nombre', 'like', $like)
                            ->orWhere('cargo', 'like', $like);
                    });
            });
        }

        $reconocimientos = $query->latest('id')->paginate($this->perPage);

        return view('livewire.reconocimientos.mostrar-reconocimientos', compact('reconocimientos'));
    }
}
