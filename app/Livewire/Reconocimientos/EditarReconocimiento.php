<?php

namespace App\Livewire\Reconocimientos;

use App\Models\Directivo;
use App\Models\Reconocimiento;
use App\Models\ReconocimientoEvento;
use App\Models\ReconocimientoImagen;
use App\Models\ReconocimientoTipo;
use App\Support\ReconocimientoHtml;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;
use Livewire\Component;

class EditarReconocimiento extends Component
{
    public Reconocimiento $reconocimiento;
    public ?int $reconocimiento_imagen_id=null;
    public ?int $reconocimiento_evento_id=null;
    public ?int $reconocimiento_tipo_id=null;
    public string $reconocimiento_a='';
    public ?string $lugar_obtenido=null;
    public string $descripcion='';
    public ?string $fecha=null;
    public string $estado='borrador';
    public array $directivos=[];
    public string $delivery_method='';
    public string $delivery_to='';
    public string $delivery_notes='';
    public string $cancel_reason='';
    public string $correo_asunto='Reconocimiento institucional';
    public string $correo_mensaje='Se adjunta su reconocimiento en formato PDF.';

    private array $original=[];

    public function mount(Reconocimiento $reconocimiento): void
    {
        abort_unless(auth()->user()?->puedeReconocimientos('editar'),403);
        $this->reconocimiento=$reconocimiento->load('directivos');
        $this->reconocimiento_imagen_id=$reconocimiento->reconocimiento_imagen_id;
        $this->reconocimiento_evento_id=$reconocimiento->reconocimiento_evento_id;
        $this->reconocimiento_tipo_id=$reconocimiento->reconocimiento_tipo_id;
        $this->reconocimiento_a=$reconocimiento->reconocimiento_a;
        $this->lugar_obtenido=$reconocimiento->lugar_obtenido;
        $this->descripcion=$reconocimiento->descripcion ?? '';
        $this->fecha=$reconocimiento->fecha?->toDateString();
        $this->estado=$reconocimiento->estado;
        $this->directivos=$reconocimiento->directivos->pluck('id')->all();
        $this->delivery_method=$reconocimiento->delivery_method ?? '';
        $this->delivery_to=$reconocimiento->delivery_to ?? '';
        $this->delivery_notes=$reconocimiento->delivery_notes ?? '';
        $this->cancel_reason=$reconocimiento->cancel_reason ?? '';
        $this->original=$this->datosActuales();
    }

    public function updatedReconocimientoTipoId($value): void
    {
        if($value && ($tipo=ReconocimientoTipo::find($value))){
            $this->descripcion=$tipo->descripcion;
            $this->reconocimiento_imagen_id=$tipo->reconocimiento_imagen_id ?: $this->reconocimiento_imagen_id;
            $this->dispatch('reconocimiento-descripcion-actualizada', html: $this->descripcion);
        }
    }

    public function actualizarReconocimiento(): void
    {
        $data=$this->validate([
            'reconocimiento_imagen_id'=>'required|exists:reconocimiento_imagenes,id','reconocimiento_evento_id'=>'nullable|exists:reconocimiento_eventos,id',
            'reconocimiento_tipo_id'=>'nullable|exists:reconocimiento_tipos,id','reconocimiento_a'=>'required|string|min:3|max:255',
            'lugar_obtenido'=>'nullable|string|max:255','descripcion'=>'required|string|max:5000','fecha'=>'required|date',
            'estado'=>'required|in:borrador,revision,aprobado,generado,entregado,cancelado','directivos'=>'required|array|min:1|max:5',
            'directivos.*'=>'exists:directivos,id','delivery_method'=>'nullable|in:impreso,correo,whatsapp,digital,ceremonia',
            'delivery_to'=>'nullable|string|max:255','delivery_notes'=>'nullable|string|max:2000',
            'cancel_reason'=>($this->estado==='cancelado'?'required':'nullable').'|string|max:1000',
        ]);

        if($this->estado==='aprobado') abort_unless(auth()->user()?->puedeReconocimientos('aprobar'),403);
        if($this->estado==='cancelado') abort_unless(auth()->user()?->puedeReconocimientos('cancelar'),403);

        $antes=$this->reconocimiento->only(array_keys($this->datosActuales()));
        $emitido=in_array($this->reconocimiento->estado,['aprobado','generado','entregado'],true);
        $sustantivo=$emitido && collect(['reconocimiento_a','descripcion','fecha','reconocimiento_imagen_id'])->contains(fn($k)=>(string)($antes[$k]??'') !== (string)($data[$k]??''));

        $update=[
            'reconocimiento_imagen_id'=>$data['reconocimiento_imagen_id'],'reconocimiento_evento_id'=>$data['reconocimiento_evento_id'],
            'reconocimiento_tipo_id'=>$data['reconocimiento_tipo_id'],'reconocimiento_a'=>trim($data['reconocimiento_a']),
            'lugar_obtenido'=>$data['lugar_obtenido']?trim($data['lugar_obtenido']):null,'descripcion'=>ReconocimientoHtml::limpiar($data['descripcion']),
            'fecha'=>$data['fecha'],'estado'=>$sustantivo?'revision':$data['estado'],'version'=>$sustantivo?$this->reconocimiento->version+1:$this->reconocimiento->version,
            'delivery_method'=>$data['delivery_method']?:null,'delivery_to'=>$data['delivery_to']?:null,'delivery_notes'=>$data['delivery_notes']?:null,
            'cancel_reason'=>$data['estado']==='cancelado'?$data['cancel_reason']:null,
        ];
        if($data['estado']==='aprobado'){ $update['approved_by']=auth()->id(); $update['approved_at']=now(); }
        if($data['estado']==='entregado' && !$this->reconocimiento->delivered_at) $update['delivered_at']=now();

        $this->reconocimiento->update($update);
        $this->reconocimiento->directivos()->sync($this->directivos);
        $cambios=array_filter($update,fn($v,$k)=>(string)($antes[$k]??'') !== (string)$v,ARRAY_FILTER_USE_BOTH);
        $this->reconocimiento->registrarHistorial($sustantivo?'nueva_version':'actualizado',$sustantivo?'Se generó una nueva versión y volvió a revisión.':'Datos actualizados.',$cambios);
        $this->estado=$this->reconocimiento->fresh()->estado;
        $this->dispatch('swal',['title'=>$sustantivo?'Cambios guardados; requiere nueva aprobación':'Reconocimiento actualizado','icon'=>'success','position'=>'top-end']);
    }

    public function enviarCorreo(): void
    {
        abort_unless(auth()->user()?->puedeReconocimientos('descargar'), 403);
        $this->validate([
            'delivery_to' => 'required|email|max:255',
            'correo_asunto' => 'required|string|max:255',
            'correo_mensaje' => 'required|string|max:2000',
        ]);

        $reconocimiento = $this->reconocimiento->fresh()->load(['reconocimientoImagen','directivos','evento','tipo']);
        abort_if($reconocimiento->estado === 'cancelado', 422, 'No se puede enviar un reconocimiento cancelado.');
        $orientacion = $reconocimiento->reconocimientoImagen?->orientacion === 'vertical' ? 'portrait' : 'landscape';
        $contenido = Pdf::loadView('livewire.reconocimientos.pdf.documentosPDF', ['reconocimientos'=>collect([$reconocimiento])])
            ->setPaper('letter', $orientacion)
            ->setOption(['fontDir'=>public_path('/fonts'),'fontCache'=>public_path('/fonts'),'defaultFont'=>'DejaVu Sans','isRemoteEnabled'=>true])
            ->output();
        $archivo = 'Reconocimiento_'.Str::slug($reconocimiento->reconocimiento_a, '_').'.pdf';

        try {
            Mail::raw($this->correo_mensaje, function ($mensaje) use ($contenido, $archivo) {
                $mensaje->to($this->delivery_to)
                    ->subject($this->correo_asunto)
                    ->attachData($contenido, $archivo, ['mime'=>'application/pdf']);
            });
        } catch (\Throwable $e) {
            report($e);
            $this->dispatch('swal',['title'=>'No se pudo enviar. Revisa la configuración de correo del servidor.','icon'=>'error','position'=>'top-end']);
            return;
        }

        $reconocimiento->update([
            'estado'=>'entregado','generated_at'=>$reconocimiento->generated_at ?: now(),'delivered_at'=>now(),
            'delivery_method'=>'correo','delivery_to'=>$this->delivery_to,
        ]);
        $reconocimiento->registrarHistorial('enviado_correo', 'Reconocimiento enviado a '.$this->delivery_to.'.');
        $this->estado='entregado';
        $this->delivery_method='correo';
        $this->dispatch('swal',['title'=>'Reconocimiento enviado por correo','icon'=>'success','position'=>'top-end']);
    }

    private function datosActuales(): array
    {
        return ['reconocimiento_imagen_id'=>$this->reconocimiento_imagen_id,'reconocimiento_evento_id'=>$this->reconocimiento_evento_id,
            'reconocimiento_tipo_id'=>$this->reconocimiento_tipo_id,'reconocimiento_a'=>$this->reconocimiento_a,'lugar_obtenido'=>$this->lugar_obtenido,
            'descripcion'=>$this->descripcion,'fecha'=>$this->fecha,'estado'=>$this->estado];
    }

    public function render()
    {
        return view('livewire.reconocimientos.editar-reconocimiento',[
            'reconocimientosImagenes'=>ReconocimientoImagen::where('activo',true)->get(),
            'directivosLista'=>Directivo::where('activo',true)->orderBy('orden')->get(),
            'eventos'=>ReconocimientoEvento::where('estado','activo')->orderByDesc('fecha')->get(),
            'tipos'=>ReconocimientoTipo::where('activo',true)->orderBy('nombre')->get(),
            'historial'=>$this->reconocimiento->historial()->with('usuario')->limit(20)->get(),
        ]);
    }
}
