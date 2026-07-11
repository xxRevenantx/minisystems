<?php

namespace App\Livewire\Reconocimientos;

use App\Models\Directivo;
use App\Models\ReconocimientoEvento;
use App\Models\ReconocimientoImagen;
use App\Models\ReconocimientoPermiso;
use App\Models\ReconocimientoTipo;
use App\Models\User;
use App\Support\ReconocimientoHtml;
use Illuminate\Support\Facades\Storage;
use Livewire\Component;
use Livewire\WithFileUploads;

class CatalogosReconocimientos extends Component
{
    use WithFileUploads;

    public string $modo = 'eventos';

    public ?int $eventoId = null;
    public string $eventoNombre = '';
    public string $eventoCategoria = '';
    public ?string $eventoFecha = null;
    public string $eventoLugar = '';
    public string $eventoNivel = '';
    public string $eventoCiclo = '';
    public ?int $eventoTipo = null;
    public ?int $eventoImagen = null;
    public string $eventoEstado = 'activo';
    public string $eventoObservaciones = '';

    public ?int $tipoId = null;
    public string $tipoNombre = '';
    public string $tipoTitulo = '';
    public string $tipoDescripcion = '';
    public string $tipoDestinatario = 'alumno';
    public bool $tipoUsaLugar = false;
    public string $tipoNiveles = '';
    public ?int $tipoImagen = null;
    public bool $tipoActivo = true;

    public ?int $directivoId = null;
    public string $directivoTitulo = '';
    public string $directivoNombre = '';
    public string $directivoCargo = '';
    public string $directivoNiveles = '';
    public int $directivoOrden = 0;
    public bool $directivoActivo = true;
    public ?string $directivoVigenciaInicio = null;
    public ?string $directivoVigenciaFin = null;
    public $firmaArchivo = null;
    public $selloArchivo = null;

    public ?int $plantillaId = null;
    public string $plantillaNombre = '';
    public string $plantillaOrientacion = 'horizontal';
    public bool $plantillaActiva = true;
    public int $nombreTop = 250;
    public int $nombreTamano = 34;
    public int $descripcionTop = 330;
    public int $descripcionTamano = 16;
    public int $fechaTop = 470;
    public int $firmasTop = 540;

    public array $permisos = [];

    public function mount(string $modo = 'eventos'): void
    {
        abort_unless(auth()->user()?->puedeReconocimientos('administrar'), 403);
        $this->modo = in_array($modo, ['eventos','configuracion','plantillas'], true) ? $modo : 'eventos';
        $this->cargarPermisos();
    }

    public function guardarEvento(): void
    {
        $data = $this->validate([
            'eventoNombre'=>'required|string|max:255','eventoCategoria'=>'nullable|string|max:255',
            'eventoFecha'=>'nullable|date','eventoLugar'=>'nullable|string|max:255','eventoNivel'=>'nullable|string|max:255',
            'eventoCiclo'=>'nullable|string|max:255','eventoTipo'=>'nullable|exists:reconocimiento_tipos,id',
            'eventoImagen'=>'nullable|exists:reconocimiento_imagenes,id','eventoEstado'=>'required|in:activo,cerrado,archivado',
            'eventoObservaciones'=>'nullable|string|max:2000',
        ]);
        ReconocimientoEvento::updateOrCreate(['id'=>$this->eventoId], [
            'nombre'=>trim($data['eventoNombre']),'categoria'=>$data['eventoCategoria'] ?: null,'fecha'=>$data['eventoFecha'],
            'lugar'=>$data['eventoLugar'] ?: null,'nivel'=>$data['eventoNivel'] ?: null,'ciclo_escolar'=>$data['eventoCiclo'] ?: null,
            'reconocimiento_tipo_id'=>$data['eventoTipo'],'reconocimiento_imagen_id'=>$data['eventoImagen'],
            'estado'=>$data['eventoEstado'],'observaciones'=>$data['eventoObservaciones'] ?: null,'created_by'=>auth()->id(),
        ]);
        $this->resetEvento();
        $this->toast('Evento guardado correctamente');
    }

    public function editarEvento(int $id): void
    {
        $e = ReconocimientoEvento::findOrFail($id);
        $this->eventoId=$e->id; $this->eventoNombre=$e->nombre; $this->eventoCategoria=$e->categoria ?? '';
        $this->eventoFecha=$e->fecha?->toDateString(); $this->eventoLugar=$e->lugar ?? ''; $this->eventoNivel=$e->nivel ?? '';
        $this->eventoCiclo=$e->ciclo_escolar ?? ''; $this->eventoTipo=$e->reconocimiento_tipo_id; $this->eventoImagen=$e->reconocimiento_imagen_id;
        $this->eventoEstado=$e->estado; $this->eventoObservaciones=$e->observaciones ?? '';
    }

    public function eliminarEvento(int $id): void
    {
        $e=ReconocimientoEvento::findOrFail($id);
        if ($e->reconocimientos()->exists()) { $e->update(['estado'=>'archivado']); $this->toast('El evento tiene reconocimientos y fue archivado','warning'); return; }
        $e->delete(); $this->toast('Evento enviado a la papelera');
    }

    public function resetEvento(): void
    {
        $this->reset(['eventoId','eventoNombre','eventoCategoria','eventoFecha','eventoLugar','eventoNivel','eventoCiclo','eventoTipo','eventoImagen','eventoObservaciones']);
        $this->eventoEstado='activo'; $this->resetValidation();
    }

    public function guardarTipo(): void
    {
        $data=$this->validate([
            'tipoNombre'=>'required|string|max:255','tipoTitulo'=>'nullable|string|max:255','tipoDescripcion'=>'required|string|max:5000',
            'tipoDestinatario'=>'required|in:alumno,docente,externo,institucion','tipoUsaLugar'=>'boolean','tipoNiveles'=>'nullable|string|max:1000',
            'tipoImagen'=>'nullable|exists:reconocimiento_imagenes,id','tipoActivo'=>'boolean',
        ]);
        ReconocimientoTipo::updateOrCreate(['id'=>$this->tipoId], [
            'nombre'=>trim($data['tipoNombre']),'titulo'=>$data['tipoTitulo'] ?: null,
            'descripcion'=>ReconocimientoHtml::limpiar($data['tipoDescripcion']),'destinatario_tipo'=>$data['tipoDestinatario'],
            'usa_lugar'=>$data['tipoUsaLugar'],'niveles'=>$this->lista($data['tipoNiveles']),
            'reconocimiento_imagen_id'=>$data['tipoImagen'],'activo'=>$data['tipoActivo'],
        ]);
        $this->resetTipo(); $this->toast('Tipo de reconocimiento guardado');
    }

    public function editarTipo(int $id): void
    {
        $t=ReconocimientoTipo::findOrFail($id);
        $this->tipoId=$t->id; $this->tipoNombre=$t->nombre; $this->tipoTitulo=$t->titulo ?? ''; $this->tipoDescripcion=$t->descripcion;
        $this->tipoDestinatario=$t->destinatario_tipo; $this->tipoUsaLugar=$t->usa_lugar; $this->tipoNiveles=implode(', ', $t->niveles ?? []);
        $this->tipoImagen=$t->reconocimiento_imagen_id; $this->tipoActivo=$t->activo;
    }

    public function eliminarTipo(int $id): void
    {
        $t=ReconocimientoTipo::findOrFail($id);
        if ($t->reconocimientos()->exists()) { $t->update(['activo'=>false]); $this->toast('El tipo está en uso y fue desactivado','warning'); return; }
        $t->delete(); $this->toast('Tipo eliminado');
    }

    public function resetTipo(): void
    {
        $this->reset(['tipoId','tipoNombre','tipoTitulo','tipoDescripcion','tipoNiveles','tipoImagen']);
        $this->tipoDestinatario='alumno'; $this->tipoUsaLugar=false; $this->tipoActivo=true; $this->resetValidation();
    }

    public function guardarDirectivo(): void
    {
        $data=$this->validate([
            'directivoTitulo'=>'required|string|max:50','directivoNombre'=>'required|string|max:255','directivoCargo'=>'required|string|max:255',
            'directivoNiveles'=>'nullable|string|max:1000','directivoOrden'=>'integer|min:0|max:999','directivoActivo'=>'boolean',
            'directivoVigenciaInicio'=>'nullable|date','directivoVigenciaFin'=>'nullable|date|after_or_equal:directivoVigenciaInicio',
            'firmaArchivo'=>'nullable|image|mimes:png,jpg,jpeg|max:2048','selloArchivo'=>'nullable|image|mimes:png,jpg,jpeg|max:2048',
        ]);
        $d=$this->directivoId ? Directivo::findOrFail($this->directivoId) : new Directivo();
        $d->fill(['titulo'=>trim($data['directivoTitulo']),'nombre'=>trim($data['directivoNombre']),'cargo'=>trim($data['directivoCargo']),
            'niveles'=>$this->lista($data['directivoNiveles']),'orden'=>$data['directivoOrden'],'activo'=>$data['directivoActivo'],
            'vigencia_inicio'=>$data['directivoVigenciaInicio'],'vigencia_fin'=>$data['directivoVigenciaFin']]);
        if ($this->firmaArchivo) { if($d->firma) Storage::delete('firmasDirectivos/'.$d->firma); $d->firma=basename($this->firmaArchivo->store('firmasDirectivos')); }
        if ($this->selloArchivo) { if($d->sello) Storage::delete('sellosDirectivos/'.$d->sello); $d->sello=basename($this->selloArchivo->store('sellosDirectivos')); }
        $d->save(); $this->resetDirectivo(); $this->toast('Firmante guardado');
    }

    public function editarDirectivo(int $id): void
    {
        $d=Directivo::findOrFail($id); $this->directivoId=$d->id; $this->directivoTitulo=$d->titulo; $this->directivoNombre=$d->nombre;
        $this->directivoCargo=$d->cargo ?? ''; $this->directivoNiveles=implode(', ', $d->niveles ?? []); $this->directivoOrden=$d->orden;
        $this->directivoActivo=$d->activo; $this->directivoVigenciaInicio=$d->vigencia_inicio?->toDateString(); $this->directivoVigenciaFin=$d->vigencia_fin?->toDateString();
    }

    public function resetDirectivo(): void
    {
        $this->reset(['directivoId','directivoTitulo','directivoNombre','directivoCargo','directivoNiveles','directivoVigenciaInicio','directivoVigenciaFin','firmaArchivo','selloArchivo']);
        $this->directivoOrden=0; $this->directivoActivo=true; $this->resetValidation();
    }

    public function editarPlantilla(int $id): void
    {
        $p=ReconocimientoImagen::findOrFail($id); $c=$p->configuracion ?? [];
        $this->plantillaId=$p->id; $this->plantillaNombre=$p->nombre ?: ($p->descripcion ?? ''); $this->plantillaOrientacion=$p->orientacion;
        $this->plantillaActiva=$p->activo; $this->nombreTop=(int)data_get($c,'nombre.top',250); $this->nombreTamano=(int)data_get($c,'nombre.tamano',34);
        $this->descripcionTop=(int)data_get($c,'descripcion.top',330); $this->descripcionTamano=(int)data_get($c,'descripcion.tamano',16);
        $this->fechaTop=(int)data_get($c,'fecha.top',470); $this->firmasTop=(int)data_get($c,'firmas.top',540);
    }

    public function guardarPlantilla(): void
    {
        $data=$this->validate(['plantillaId'=>'required|exists:reconocimiento_imagenes,id','plantillaNombre'=>'required|string|max:255',
            'plantillaOrientacion'=>'required|in:horizontal,vertical','plantillaActiva'=>'boolean','nombreTop'=>'integer|min:0|max:1000',
            'nombreTamano'=>'integer|min:12|max:80','descripcionTop'=>'integer|min:0|max:1000','descripcionTamano'=>'integer|min:8|max:40',
            'fechaTop'=>'integer|min:0|max:1000','firmasTop'=>'integer|min:0|max:1000']);
        ReconocimientoImagen::findOrFail($data['plantillaId'])->update([
            'nombre'=>trim($data['plantillaNombre']),'orientacion'=>$data['plantillaOrientacion'],'activo'=>$data['plantillaActiva'],
            'configuracion'=>['nombre'=>['top'=>$data['nombreTop'],'tamano'=>$data['nombreTamano']],
                'descripcion'=>['top'=>$data['descripcionTop'],'tamano'=>$data['descripcionTamano']],
                'fecha'=>['top'=>$data['fechaTop']],'firmas'=>['top'=>$data['firmasTop']]],
        ]);
        $this->plantillaId=null; $this->toast('Configuración de plantilla guardada');
    }

    public function cargarPermisos(): void
    {
        foreach (User::with('permisoReconocimientos')->get() as $u) {
            $p=$u->permisoReconocimientos;
            $this->permisos[$u->id]=[
                'ver'=>$p?->ver ?? true,'crear'=>$p?->crear ?? true,'editar'=>$p?->editar ?? true,
                'aprobar'=>$p?->aprobar ?? true,'descargar'=>$p?->descargar ?? true,'cancelar'=>$p?->cancelar ?? true,'administrar'=>$p?->administrar ?? true,
            ];
        }
    }

    public function guardarPermisos(int $userId): void
    {
        $data=$this->permisos[$userId] ?? [];
        if ($userId === (int) auth()->id()) {
            $data['administrar'] = true;
            $this->permisos[$userId]['administrar'] = true;
        }
        ReconocimientoPermiso::updateOrCreate(['user_id'=>$userId], array_map(fn($v)=>(bool)$v, $data));
        $this->toast('Permisos actualizados');
    }

    private function lista(?string $texto): ?array
    {
        $items=array_values(array_filter(array_map('trim', explode(',', (string)$texto))));
        return $items ?: null;
    }

    private function toast(string $titulo, string $icon='success'): void
    {
        $this->dispatch('swal',['title'=>$titulo,'icon'=>$icon,'position'=>'top-end']);
    }

    public function render()
    {
        return view('livewire.reconocimientos.catalogos-reconocimientos',[
            'eventos'=>ReconocimientoEvento::withCount('reconocimientos')->with(['tipo','imagen'])->latest('id')->get(),
            'tipos'=>ReconocimientoTipo::with('imagen')->orderBy('nombre')->get(),
            'directivos'=>Directivo::orderBy('orden')->orderBy('id')->get(),
            'plantillas'=>ReconocimientoImagen::withCount('reconocimientos')->latest()->get(),
            'usuarios'=>User::orderBy('name')->get(),
        ]);
    }
}
