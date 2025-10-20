<?php

namespace App\Livewire\Reconocimientos;

use Livewire\Component;
use App\Models\ReconocimientoImagen;

class ImagenesReconocimientos extends Component
{

    public $reconocimiento;
    public $descripcion;

    use \Livewire\WithFileUploads;


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



    public function render()
    {
        $imagenes = ReconocimientoImagen::all();
        return view('livewire.reconocimientos.imagenes-reconocimientos', compact('imagenes'));
    }
}
