<?php

namespace App\Livewire\Reconocimientos;

use App\Models\ReconocimientoEvento;
use App\Models\ReconocimientoImagen;
use App\Models\ReconocimientoTipo;
use Illuminate\Support\Facades\Storage;
use Livewire\Component;
use Livewire\WithFileUploads;

class ImagenesReconocimientos extends Component
{
    use WithFileUploads;

    public $reconocimiento = null;
    public string $descripcion = '';

    public bool $isModalOpen = false;
    public ?int $imagenEditId = null;
    public $nuevaImagen = null;
    public string $descripcionEdit = '';
    public string $imagenActual = '';
    public string $orientacionEdit = 'horizontal';

    public function mount(): void
    {
        abort_unless(auth()->user()?->puedeReconocimientos('administrar'), 403);
    }

    public function guardarImagenReconocimiento(): void
    {
        $data = $this->validate([
            'reconocimiento' => ['required', 'image', 'mimes:jpeg,jpg,png', 'max:5120'],
            'descripcion' => ['required', 'string', 'max:255'],
        ], [
            'reconocimiento.required' => 'Selecciona una imagen para el diseño.',
            'reconocimiento.image' => 'El archivo seleccionado debe ser una imagen válida.',
            'reconocimiento.mimes' => 'Solo se permiten imágenes JPG, JPEG o PNG.',
            'reconocimiento.max' => 'La imagen no debe superar los 5 MB.',
            'descripcion.required' => 'Escribe un nombre para identificar el diseño.',
        ]);

        $path = $this->reconocimiento->store('imagenesReconocimientos');

        ReconocimientoImagen::create([
            'imagen' => basename($path),
            'nombre' => trim($data['descripcion']),
            'descripcion' => trim($data['descripcion']),
            'orientacion' => $this->detectarOrientacion($this->reconocimiento),
            'configuracion' => [
                'nombre' => ['top' => 250, 'tamano' => 34],
                'descripcion' => ['top' => 330, 'tamano' => 16],
                'fecha' => ['top' => 470],
                'firmas' => ['top' => 540],
            ],
            'activo' => true,
        ]);

        $this->reset(['reconocimiento', 'descripcion']);
        $this->resetValidation();
        $this->dispatch('limpiar-dropzone');
        $this->dispatch(
            'swal',
            title: 'Diseño guardado correctamente',
            text: 'Ya está disponible para crear reconocimientos.',
            icon: 'success',
            position: 'top-end'
        );
    }

    public function editarImagen(int $id): void
    {
        $imagen = ReconocimientoImagen::findOrFail($id);

        $this->imagenEditId = $imagen->id;
        $this->descripcionEdit = $imagen->nombre ?: ($imagen->descripcion ?? '');
        $this->imagenActual = $imagen->imagen ?? '';
        $this->orientacionEdit = $imagen->orientacion ?: 'horizontal';
        $this->nuevaImagen = null;
        $this->resetValidation();
        $this->isModalOpen = true;
    }

    public function actualizarImagenReconocimiento(): void
    {
        $data = $this->validate([
            'imagenEditId' => ['required', 'exists:reconocimiento_imagenes,id'],
            'nuevaImagen' => ['nullable', 'image', 'mimes:jpeg,jpg,png', 'max:5120'],
            'descripcionEdit' => ['required', 'string', 'max:255'],
        ], [
            'nuevaImagen.image' => 'El archivo seleccionado debe ser una imagen válida.',
            'nuevaImagen.mimes' => 'Solo se permiten imágenes JPG, JPEG o PNG.',
            'nuevaImagen.max' => 'La imagen no debe superar los 5 MB.',
            'descripcionEdit.required' => 'El nombre del diseño es obligatorio.',
        ]);

        $imagen = ReconocimientoImagen::findOrFail((int) $data['imagenEditId']);

        if ($this->nuevaImagen) {
            $imagenAnterior = $imagen->imagen;
            $nuevoPath = $this->nuevaImagen->store('imagenesReconocimientos');

            $imagen->imagen = basename($nuevoPath);
            $imagen->orientacion = $this->detectarOrientacion($this->nuevaImagen);

            if ($imagenAnterior) {
                Storage::delete('imagenesReconocimientos/' . $imagenAnterior);
            }
        }

        $imagen->nombre = trim($data['descripcionEdit']);
        $imagen->descripcion = trim($data['descripcionEdit']);
        $imagen->save();

        $this->closeModal();
        $this->dispatch(
            'swal',
            title: 'Diseño actualizado',
            text: 'Los cambios se guardaron correctamente.',
            icon: 'success',
            position: 'top-end'
        );
    }

    public function eliminarImagenReconocimiento(int $id): void
    {
        $imagen = ReconocimientoImagen::findOrFail($id);

        $enUso = $imagen->reconocimientos()->withTrashed()->exists()
            || ReconocimientoEvento::where('reconocimiento_imagen_id', $id)->exists()
            || ReconocimientoTipo::where('reconocimiento_imagen_id', $id)->exists();

        if ($enUso) {
            $imagen->update(['activo' => false]);

            $this->dispatch(
                'swal',
                title: 'Diseño desactivado',
                text: 'Está vinculado a reconocimientos existentes, por eso se conservó en el historial.',
                icon: 'warning',
                position: 'top-end'
            );

            return;
        }

        if ($imagen->imagen) {
            Storage::delete('imagenesReconocimientos/' . $imagen->imagen);
        }

        $imagen->delete();

        $this->dispatch(
            'swal',
            title: 'Diseño eliminado',
            text: 'La imagen se eliminó correctamente.',
            icon: 'success',
            position: 'top-end'
        );
    }

    public function closeModal(): void
    {
        $this->isModalOpen = false;
        $this->reset([
            'imagenEditId',
            'nuevaImagen',
            'descripcionEdit',
            'imagenActual',
            'orientacionEdit',
        ]);
        $this->resetValidation();
    }

    private function detectarOrientacion($archivo): string
    {
        try {
            $dimensiones = @getimagesize($archivo->getRealPath());

            if (is_array($dimensiones) && isset($dimensiones[0], $dimensiones[1])) {
                return $dimensiones[1] > $dimensiones[0] ? 'vertical' : 'horizontal';
            }
        } catch (\Throwable) {
            // Si no es posible leer las dimensiones, se conserva la orientación predeterminada.
        }

        return 'horizontal';
    }

    public function render()
    {
        $imagenes = ReconocimientoImagen::latest('id')->get();

        return view('livewire.reconocimientos.imagenes-reconocimientos', [
            'imagenes' => $imagenes,
            'totalDisenos' => $imagenes->count(),
            'totalActivos' => $imagenes->where('activo', true)->count(),
            'totalInactivos' => $imagenes->where('activo', false)->count(),
        ]);
    }
}
