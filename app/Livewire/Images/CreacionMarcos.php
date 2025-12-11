<?php

namespace App\Livewire\Images;

use App\Models\Marco;
use Livewire\Component;

class CreacionMarcos extends Component
{

    public $marco;
    public $descripcion;

    use \Livewire\WithFileUploads;


     public function guardarMarco()
    {

        $this->validate([
            'marco' => 'required|image|mimes:jpeg,jpg,png',
            'descripcion' => 'required|string|max:255',
        ],[

        ]);

        if ($this->marco) {
            $imagen = $this->marco->store('imagenesMarcos');
            $datos["marco"] = str_replace('imagenesMarcos/', '', $imagen);
        } else {
            $datos["marco"] = null;
        }

        Marco::create([
            'marco' => $datos["marco"],
            'descripcion' => trim($this->descripcion),
        ]);

        $this->reset([]);


        $this->dispatch('swal', [
            'title' => '¡Imagen creada correctamente!',
            'icon' => 'success',
            'position' => 'top-end',
        ]);


    }

    public function eliminarMarco($id)
    {
        $imagen = Marco::find($id);

        if ($imagen) {
            // Eliminar el archivo de imagen del almacenamiento
            \Storage::delete('imagenesMarcos/' . $imagen->marco);

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
        $marcos = Marco::orderBy('created_at', 'desc')->get();
        return view('livewire.images.creacion-marcos', [
            'marcos' => $marcos,
        ]);

    }
}
