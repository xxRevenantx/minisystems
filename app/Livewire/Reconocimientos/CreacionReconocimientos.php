<?php

namespace App\Livewire\Reconocimientos;

use App\Models\Reconocimiento;
use App\Models\Directivo;
use App\Models\ReconocimientoImagen;
use Livewire\Component;
use Livewire\WithFileUploads;

class CreacionReconocimientos extends Component
{
    use WithFileUploads;

    public $reconocimiento_id;

    public $reconocimiento_imagen_id = null;   // plantilla seleccionada (radio)
    public $reconocimiento = '';        // "Reconocimiento a"
    public $descripcion = '';           // HTML o texto desde TinyMCE
    public $lugar_obtenido = null;      // opcional
    public $fecha = null;               // date
    public $directivos = [];            // checkboxes

    protected $rules = [
        'reconocimiento_imagen_id' => 'required|integer|exists:reconocimiento_imagenes,id', // <- usa la tabla correcta de plantillas
        'reconocimiento'    => 'required|string|max:255',
        'descripcion'       => 'required|string',
        'lugar_obtenido'    => 'nullable|string|max:255',
        'fecha'             => 'required|date',
        'directivos'        => 'required|array|min:1',
        'directivos.*'      => 'integer|exists:directivos,id',
    ];

    protected $messages = [
        'reconocimiento_imagen_id.required' => 'Debes seleccionar una plantilla de reconocimiento.',
        'reconocimiento.required'    => 'El campo "Reconocimiento a" es obligatorio.',
        'descripcion.required'       => 'La descripción es obligatoria.',
        'fecha.required'             => 'La fecha es obligatoria.',
        'directivos.required'        => 'Selecciona al menos un directivo.',
        'directivos.*.exists'        => 'Algún directivo seleccionado no existe.',
    ];

    protected $validationAttributes = [
        'reconocimiento_imagen_id' => 'plantilla de reconocimiento',
        'reconocimiento'    => 'Reconocimiento a',
        'descripcion'       => 'descripción',
        'lugar_obtenido'    => 'lugar obtenido',
        'fecha'             => 'fecha',
        'directivos'        => 'directivos',
    ];

    public function guardarReconocimiento()
    {
        $this->validate();

        // dd( $this->reconocimiento_id );

        $rec = Reconocimiento::create([
            'reconocimiento_imagen_id' =>        $this->reconocimiento_imagen_id, // si tu relación guarda la plantilla
            'reconocimiento_a'         => trim($this->reconocimiento),
            'descripcion'              => $this->descripcion,
            'lugar_obtenido'           => $this->lugar_obtenido,
            'fecha'                    => $this->fecha,
        ]);

        $rec->directivos()->sync($this->directivos);

        // Notificación (opcional)
        $this->dispatch('swal', [
            'title' => 'Reconocimiento creado correctamente!',
            'icon' => 'success',
            'position' => 'top-end',
        ]);

        $this->dispatch('reconocimientoCreado');

        // Reset completo
        $this->reset([
            'reconocimiento',
            'lugar_obtenido',
        ]);

        $this->descripcion = "";
    }

    public function render()
    {
        // Si tus tablas se llaman distinto, ajusta aquí:
        $reconocimientosImagenes = ReconocimientoImagen::orderBy('id', 'desc')->get();
        $directivosLista = Directivo::orderBy('id')->get();

        return view(
            'livewire.reconocimientos.creacion-reconocimientos',
            compact('reconocimientosImagenes', 'directivosLista')
        );
    }
}
