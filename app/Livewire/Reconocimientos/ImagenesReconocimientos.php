<?php

namespace App\Livewire\Reconocimientos;

use Livewire\Component;
use App\Models\ReconocimientoImagen;

class ImagenesReconocimientos extends Component
{

    public $reconocimiento;
    public $descripcion;

    use \Livewire\WithFileUploads;


    // EDITAR IMAGEN RECONOCIMIENTO
    public $isModalOpen = false;

    public $imagenEditId = null;
    public $nuevaImagen = null;
    public $descripcionEdit = '';


     public function guardarImagenReconocimiento()
    {

        $this->validate([
            'reconocimiento' => 'required|image|mimes:jpeg,jpg,png',
            'descripcion' => 'required|string|max:255',
        ],[

        ]);

        if ($this->reconocimiento) {
            $imagen = $this->reconocimiento->store('imagenesReconocimientos');
            $datos["reconocimiento"] = str_replace('imagenesReconocimientos/', '', $imagen);
        } else {
            $datos["reconocimiento"] = null;
        }

        ReconocimientoImagen::create([
            'imagen' => $datos["reconocimiento"],
            'descripcion' => trim($this->descripcion),
        ]);

        $this->reset([]);


        $this->dispatch('swal', [
            'title' => '¡Imagen creada correctamente!',
            'icon' => 'success',
            'position' => 'top-end',
        ]);


    }

    public function eliminarImagenReconocimiento($id)
    {
        $imagen = ReconocimientoImagen::find($id);

        if ($imagen) {
            // Eliminar el archivo de imagen del almacenamiento
            \Storage::delete('imagenesReconocimientos/' . $imagen->imagen);

            // Eliminar el registro de la base de datos
            $imagen->delete();

            $this->dispatch('swal', [
                'title' => '¡Imagen eliminada correctamente!',
                'icon' => 'success',
                'position' => 'top-end',
            ]);
        }
    }


    public function editarImagen($id, $descripcion)
    {
        $this->imagenEditId = $id;
        $this->descripcionEdit = $descripcion ?? '';
        $this->nuevaImagen = null;
        $this->isModalOpen = true;
    }

    public function actualizarImagenReconocimiento()
    {
        $this->validate([
            'imagenEditId'   => 'required|exists:reconocimiento_imagenes,id',
            'nuevaImagen'    => 'nullable|image|mimes:jpeg,jpg,png|max:5120', // 5MB
            'descripcionEdit'=> 'required|string|max:255',
        ]);

        $img = ReconocimientoImagen::findOrFail($this->imagenEditId);

        // si viene nueva imagen, reemplaza
        if ($this->nuevaImagen) {
            if ($img->imagen) {
                \Storage::delete('imagenesReconocimientos/' . $img->imagen);
            }

            $path = $this->nuevaImagen->store('imagenesReconocimientos');
            $img->imagen = str_replace('imagenesReconocimientos/', '', $path);
        }

        $img->descripcion = trim($this->descripcionEdit);
        $img->save();

        $this->dispatch('swal', [
            'title' => '¡Imagen actualizada!',
            'icon' => 'success',
            'position' => 'top-end',
        ]);

        $this->closeModal();
    }

    public function closeModal()
    {
        $this->isModalOpen = false;
        $this->reset(['imagenEditId','nuevaImagen','descripcionEdit']);
    }



    public function render()
    {
        $imagenes = ReconocimientoImagen::all();
        return view('livewire.reconocimientos.imagenes-reconocimientos', compact('imagenes'));
    }
}
