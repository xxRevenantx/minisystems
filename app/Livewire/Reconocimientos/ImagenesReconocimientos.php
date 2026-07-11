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

    public $reconocimiento;
    public $descripcion;
    public bool $isModalOpen=false;
    public ?int $imagenEditId=null;
    public $nuevaImagen=null;
    public string $descripcionEdit='';

    public function mount(): void { abort_unless(auth()->user()?->puedeReconocimientos('administrar'),403); }

    public function guardarImagenReconocimiento(): void
    {
        $this->validate(['reconocimiento'=>'required|image|mimes:jpeg,jpg,png|max:5120','descripcion'=>'required|string|max:255']);
        $path=$this->reconocimiento->store('imagenesReconocimientos');
        ReconocimientoImagen::create([
            'imagen'=>basename($path),'nombre'=>trim($this->descripcion),'descripcion'=>trim($this->descripcion),
            'orientacion'=>'horizontal','configuracion'=>['nombre'=>['top'=>250,'tamano'=>34],'descripcion'=>['top'=>330,'tamano'=>16],'fecha'=>['top'=>470],'firmas'=>['top'=>540]],'activo'=>true,
        ]);
        $this->reset(['reconocimiento','descripcion']);
        $this->dispatch('swal',['title'=>'Diseño creado correctamente','icon'=>'success','position'=>'top-end']);
    }

    public function eliminarImagenReconocimiento(int $id): void
    {
        $imagen=ReconocimientoImagen::findOrFail($id);
        $enUso=$imagen->reconocimientos()->withTrashed()->exists()
            || ReconocimientoEvento::where('reconocimiento_imagen_id',$id)->exists()
            || ReconocimientoTipo::where('reconocimiento_imagen_id',$id)->exists();
        if($enUso){
            $imagen->update(['activo'=>false]);
            $this->dispatch('swal',['title'=>'El diseño está en uso y fue desactivado; no se eliminó','icon'=>'warning','position'=>'top-end']);
            return;
        }
        if($imagen->imagen) Storage::delete('imagenesReconocimientos/'.$imagen->imagen);
        $imagen->delete();
        $this->dispatch('swal',['title'=>'Diseño eliminado correctamente','icon'=>'success','position'=>'top-end']);
    }

    public function editarImagen(int $id, $descripcion): void
    {
        $this->imagenEditId=$id; $this->descripcionEdit=$descripcion ?? ''; $this->nuevaImagen=null; $this->isModalOpen=true;
    }

    public function actualizarImagenReconocimiento(): void
    {
        $this->validate(['imagenEditId'=>'required|exists:reconocimiento_imagenes,id','nuevaImagen'=>'nullable|image|mimes:jpeg,jpg,png|max:5120','descripcionEdit'=>'required|string|max:255']);
        $img=ReconocimientoImagen::findOrFail($this->imagenEditId);
        if($this->nuevaImagen){
            if($img->imagen) Storage::delete('imagenesReconocimientos/'.$img->imagen);
            $img->imagen=basename($this->nuevaImagen->store('imagenesReconocimientos'));
        }
        $img->nombre=trim($this->descripcionEdit); $img->descripcion=trim($this->descripcionEdit); $img->save();
        $this->dispatch('swal',['title'=>'Diseño actualizado','icon'=>'success','position'=>'top-end']); $this->closeModal();
    }

    public function closeModal(): void
    {
        $this->isModalOpen=false; $this->reset(['imagenEditId','nuevaImagen','descripcionEdit']);
    }

    public function render()
    {
        return view('livewire.reconocimientos.imagenes-reconocimientos',['imagenes'=>ReconocimientoImagen::latest()->get()]);
    }
}
