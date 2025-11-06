<?php

namespace App\Livewire\Reconocimientos;

use App\Models\Reconocimiento;
use Illuminate\Validation\Rule;
use Livewire\Attributes\On;
use Livewire\Component;

class EditarReconocimiento extends Component
{
    /** Campos del formulario */
    public ?int $reconocimientoId = null;
    public ?string $reconocimiento_a = null;
    public ?string $lugar_obtenido = null;   // <- lo usas en el form
    public ?string $descripcion = null;      // HTML desde TinyMCE
    public ?string $fecha = null;

    /** Control modal (si lo necesitas en otros lados) */
    public bool $open = false;

    /** Reglas (puedes moverlas a rules() si prefieres) */
    protected array $rules = [
        'reconocimiento_a' => ['required','string','min:3','max:255'],
        'lugar_obtenido'   => ['nullable','string','max:255'],
        'descripcion'      => ['nullable','string'], // TinyMCE HTML
        'fecha'            => ['required','date'],
    ];

    /**
     * Abrir modal con datos
     */
    #[On('editarModal')]
    public function editarModal(int $id): void
    {
        $rec = Reconocimiento::findOrFail($id);

        $this->reconocimientoId = $rec->id;
        $this->reconocimiento_a = $rec->reconocimiento_a;
        $this->lugar_obtenido   = $rec->lugar_obtenido;
        $this->descripcion      = $rec->descripcion; // HTML (NO strip_tags)
        $this->fecha            = optional($rec->fecha)->format('Y-m-d') ?? $rec->fecha;

        // Si manejas show con Alpine, avisa que ya cargó y entonces inicializas TinyMCE
        $this->dispatch('editar-cargado');
    }

    /**
     * Guardar cambios
     */
    public function actualizarReconocimiento(): void
    {
        $this->validate();

        $rec = Reconocimiento::findOrFail($this->reconocimientoId);

        // Si quieres sanear HTML antes de guardar, aplica aquí (ej. mews/purifier).
        $rec->update([
            'reconocimiento_a' => $this->reconocimiento_a,
            'lugar_obtenido'   => $this->lugar_obtenido,
            'descripcion'      => $this->descripcion,   // TinyMCE HTML
            'fecha'            => $this->fecha,
        ]);

        // Opcional: notificación/toast
        $this->dispatch('toast', type: 'success', message: 'Reconocimiento actualizado');

        // Cerrar modal en Alpine (y destruir TinyMCE en el listener del front)
        $this->dispatch('cerrar-modal-editar');

        // Limpiar estado del componente
        $this->cerrarModal();
        $this->dispatch('$refresh'); // refrescar listados si lo necesitas
    }

    /**
     * Cerrar / limpiar modal
     */
    public function cerrarModal(): void
    {
        $this->reset([
            'open',
            'reconocimientoId',
            'reconocimiento_a',
            'lugar_obtenido',
            'descripcion',
            'fecha',
        ]);

        $this->resetValidation();
    }

    public function render()
    {
        return view('livewire.reconocimientos.editar-reconocimiento');
    }
}
