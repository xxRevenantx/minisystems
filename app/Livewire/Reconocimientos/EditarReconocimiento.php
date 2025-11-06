<?php

namespace App\Livewire\Reconocimientos;

use App\Models\Directivo;
use App\Models\Reconocimiento;
use App\Models\ReconocimientoImagen;
use Illuminate\Validation\Rule;
use Livewire\Attributes\On;
use Livewire\Component;

class EditarReconocimiento extends Component
{
    /** Campos del formulario */
    public  $reconocimiento;
    public ?int $reconocimientoId = null;
    public ?int $reconocimiento_imagen_id;
    public ?string $reconocimiento_a;
    public ?string $lugar_obtenido = null;   // <- lo usas en el form
    public ?string $descripcion = null;      // HTML desde TinyMCE
    public ?string $fecha = null;
    public $directivosLista;
    public array $directivos = [];



    public function mount(){
        $this->reconocimiento_imagen_id = $this->reconocimiento->reconocimiento_imagen_id;
        $this->reconocimiento_a = $this->reconocimiento->reconocimiento_a;
        $this->lugar_obtenido = $this->reconocimiento->lugar_obtenido;
        $this->descripcion = $this->reconocimiento->descripcion;
        $this->fecha = $this->reconocimiento->fecha;
        $this->reconocimientoId = $this->reconocimiento->id;

              // catálogo completo para los checkboxes
        $this->directivosLista = Directivo::orderBy('id')->get();

        // dd($this->directivosLista);

        // preseleccionar lo que ya está en el pivote
        $this->directivos = $this->reconocimiento
            ->directivos()
            ->pluck('directivos.id')
            ->toArray();

    }

    protected $rules = [
        'reconocimiento_a' => ['required','string','min:3','max:255'],
        'lugar_obtenido'   => ['nullable','string','max:255'],
        'descripcion'      => ['nullable','string'],
        'fecha'            => ['required','date'],

        'directivos'       => ['array'],
        'directivos.*'     => ['integer','exists:directivos,id'],
    ];


      public function actualizarReconocimiento()
    {
        $this->validate();

        // actualizar datos base
        $this->reconocimiento->update([
            'reconocimiento_imagen_id' => $this->reconocimiento_imagen_id,
            'reconocimiento_a' => $this->reconocimiento_a,
            'lugar_obtenido'   => $this->lugar_obtenido,
            'descripcion'      => $this->descripcion,
            'fecha'            => $this->fecha,
        ]);

        // sincronizar pivote (clave 🔥)
        $this->reconocimiento->directivos()->sync($this->directivos);

         $this->dispatch('swal', [
            'title' => '¡Reconocimiento actualizado correctamente!',
            'icon' => 'success',
            'position' => 'top-end',
        ]);
    }



    public function render()
    {
        $reconocimientosImagenes = ReconocimientoImagen::all();
        return view('livewire.reconocimientos.editar-reconocimiento', compact('reconocimientosImagenes'));
    }
}
