<?php

namespace App\Http\Controllers;

use App\Models\Credencial;
use App\Models\HistorialExportacion;
use App\Services\CreativeActivity;
use Illuminate\Support\Facades\Schema;
use App\Models\Reconocimiento;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use ZipArchive;

class PDFController extends Controller
{
    public function reconocimiento(Request $request, int $id)
    {
        abort_unless($request->user()?->puedeReconocimientos('descargar'), 403);
        $reconocimiento = Reconocimiento::with(['reconocimientoImagen','directivos','evento','tipo','marca','proyectoCreativo','registroValidacion'])->findOrFail($id);
        abort_if($reconocimiento->estado === 'cancelado', 422, 'El reconocimiento está cancelado.');

        $this->registrarGeneracion(collect([$reconocimiento]));
        $orientacion = $reconocimiento->reconocimientoImagen?->orientacion === 'vertical' ? 'portrait' : 'landscape';
        $pdf = Pdf::loadView('livewire.reconocimientos.pdf.documentosPDF', ['reconocimientos' => collect([$reconocimiento])])
            ->setPaper('letter', $orientacion)
            ->setOption(['fontDir'=>public_path('/fonts'),'fontCache'=>public_path('/fonts'),'defaultFont'=>'DejaVu Sans','isRemoteEnabled'=>true]);

        $this->registrarExportacion('reconocimiento_individual', 'pdf', 1, $reconocimiento->marca_id, $reconocimiento->proyecto_creativo_id);
        $nombre = Str::slug($reconocimiento->reconocimiento_a, '_');
        return $pdf->stream("Reconocimiento_{$nombre}.pdf");
    }

    public function descargar_reconocimientos(Request $request)
    {
        abort_unless($request->user()?->puedeReconocimientos('descargar'), 403);
        $reconocimientos = $this->consultaReconocimientos($request)->orderBy('reconocimiento_evento_id')->orderBy('reconocimiento_a')->get();
        abort_if($reconocimientos->isEmpty(), 404, 'No hay reconocimientos para descargar.');
        $this->registrarGeneracion($reconocimientos);

        $orientacion = $reconocimientos->first()->reconocimientoImagen?->orientacion === 'vertical' ? 'portrait' : 'landscape';
        $pdf = Pdf::loadView('livewire.reconocimientos.pdf.documentosPDF', compact('reconocimientos'))
            ->setPaper('letter', $orientacion)
            ->setOption(['fontDir'=>public_path('/fonts'),'fontCache'=>public_path('/fonts'),'defaultFont'=>'DejaVu Sans','isRemoteEnabled'=>true]);
        $this->registrarExportacion('reconocimientos_lote', 'pdf', $reconocimientos->count());
        return $pdf->stream('Reconocimientos_'.now()->format('Ymd_His').'.pdf');
    }

    public function descargar_reconocimientos_zip(Request $request)
    {
        abort_unless($request->user()?->puedeReconocimientos('descargar'), 403);
        abort_unless(class_exists(ZipArchive::class), 501, 'La extensión ZIP de PHP no está habilitada.');
        $reconocimientos = $this->consultaReconocimientos($request)->orderBy('reconocimiento_a')->limit(150)->get();
        abort_if($reconocimientos->isEmpty(), 404, 'No hay reconocimientos para descargar.');

        $tmp = tempnam(sys_get_temp_dir(), 'reconocimientos_');
        $zipPath = $tmp.'.zip';
        @unlink($tmp);
        $zip = new ZipArchive();
        abort_unless($zip->open($zipPath, ZipArchive::CREATE | ZipArchive::OVERWRITE) === true, 500, 'No se pudo crear el ZIP.');

        foreach ($reconocimientos as $r) {
            $orientacion = $r->reconocimientoImagen?->orientacion === 'vertical' ? 'portrait' : 'landscape';
            $contenido = Pdf::loadView('livewire.reconocimientos.pdf.documentosPDF', ['reconocimientos'=>collect([$r])])
                ->setPaper('letter', $orientacion)
                ->setOption(['fontDir'=>public_path('/fonts'),'fontCache'=>public_path('/fonts'),'defaultFont'=>'DejaVu Sans','isRemoteEnabled'=>true])
                ->output();
            $zip->addFromString(sprintf('%04d_%s.pdf', $r->id, Str::slug($r->reconocimiento_a, '_')), $contenido);
        }
        $zip->close();
        $this->registrarGeneracion($reconocimientos);
        $this->registrarExportacion('reconocimientos_lote', 'zip/pdf', $reconocimientos->count());
        return response()->download($zipPath, 'Reconocimientos_'.now()->format('Ymd_His').'.zip')->deleteFileAfterSend(true);
    }

    public function exportar_reconocimientos_csv(Request $request)
    {
        abort_unless($request->user()?->puedeReconocimientos('descargar'), 403);
        $items = $this->consultaReconocimientos($request)->orderBy('reconocimiento_a')->get();
        return response()->streamDownload(function () use ($items) {
            $out=fopen('php://output','w'); fwrite($out, "\xEF\xBB\xBF");
            fputcsv($out,['Destinatario','Evento','Tipo','Lugar obtenido','Fecha','Estado','Firmantes','Método de entrega','Entregado a']);
            foreach($items as $r) fputcsv($out,[$r->reconocimiento_a,$r->evento?->nombre,$r->tipo?->nombre,$r->lugar_obtenido,$r->fecha?->format('Y-m-d'),$r->estado,$r->directivos->pluck('nombre_completo')->implode(' | '),$r->delivery_method,$r->delivery_to]);
            fclose($out);
        }, 'Listado_reconocimientos_'.now()->format('Ymd_His').'.csv', ['Content-Type'=>'text/csv; charset=UTF-8']);
    }

    public function plantilla_importacion_csv(Request $request)
    {
        abort_unless($request->user()?->puedeReconocimientos('crear'), 403);
        return response()->streamDownload(function(){
            $out=fopen('php://output','w'); fwrite($out, "\xEF\xBB\xBF");
            fputcsv($out,['matricula']); fputcsv($out,['EJEMPLO123']); fclose($out);
        }, 'Plantilla_destinatarios_reconocimientos.csv', ['Content-Type'=>'text/csv; charset=UTF-8']);
    }

    private function consultaReconocimientos(Request $request)
    {
        $query=Reconocimiento::query()->with(['reconocimientoImagen','directivos','evento','tipo','marca','proyectoCreativo','registroValidacion'])->where('estado','!=','cancelado');
        if($request->filled('ids')){
            $ids=collect(explode(',',(string)$request->string('ids')))->filter(fn($id)=>ctype_digit((string)$id))->map(fn($id)=>(int)$id)->take(300);
            return $query->whereIn('id',$ids);
        }
        return $query->when($request->filled('search'),function($q) use($request){
            $s='%'.trim((string)$request->string('search')).'%';
            $q->where(fn($w)=>$w->where('reconocimiento_a','like',$s)->orWhere('descripcion','like',$s)
                ->orWhereHas('evento',fn($e)=>$e->where('nombre','like',$s))
                ->orWhereHas('directivos',fn($d)=>$d->where('nombre','like',$s)));
        })->when($request->filled('estado'),fn($q)=>$q->where('estado',(string)$request->string('estado')))
          ->when($request->filled('evento'),fn($q)=>$q->where('reconocimiento_evento_id',$request->integer('evento')))
          ->when($request->filled('tipo'),fn($q)=>$q->where('reconocimiento_tipo_id',$request->integer('tipo')))
          ->when($request->filled('plantilla'),fn($q)=>$q->where('reconocimiento_imagen_id',$request->integer('plantilla')))
          ->when($request->filled('desde'),fn($q)=>$q->whereDate('fecha','>=',(string)$request->string('desde')))
          ->when($request->filled('hasta'),fn($q)=>$q->whereDate('fecha','<=',(string)$request->string('hasta')));
    }

    private function registrarGeneracion($reconocimientos): void
    {
        foreach ($reconocimientos as $r) {
            if (!in_array($r->estado, ['entregado','cancelado'], true)) {
                $r->update(['estado'=>'generado','generated_at'=>now()]);
            } elseif (!$r->generated_at) {
                $r->update(['generated_at'=>now()]);
            }
            $r->registrarHistorial('pdf_generado', 'PDF generado o descargado.');
        }
    }

    public function credencialPdf(Credencial $credencial)
    {
        $credencial->load(['marca','proyectoCreativo','registroValidacion']);
        $pdf = Pdf::loadView('pdf.credencialIndividualPDF', ['credenciales'=>collect([$credencial])])
            ->setPaper('letter','portrait');
        $this->registrarExportacion('credencial_individual', 'pdf', 1, $credencial->marca_id, $credencial->proyecto_creativo_id);
        $name = Str::slug($credencial->folio ?: $credencial->matricula ?: $credencial->nombre, '_');
        return $pdf->stream('credencial_'.$name.'.pdf');
    }

    public function credencialesPdfTodas()
    {
        $credenciales=Credencial::with(['marca','proyectoCreativo','registroValidacion'])
            ->orderBy('tipo')->orderBy('organizacion')->orderBy('nivel')->orderBy('nombre')->get();
        abort_if($credenciales->isEmpty(),404,'No hay credenciales registradas para descargar.');
        $this->registrarExportacion('credenciales_lote', 'pdf', $credenciales->count());
        return Pdf::loadView('pdf.credencialTodasPDF',compact('credenciales'))
            ->setPaper('letter','portrait')->stream('todas_las_credenciales.pdf');
    }

    private function registrarExportacion(string $tipo, string $formato, int $cantidad, ?int $marcaId = null, ?int $proyectoId = null): void
    {
        try {
            if (! Schema::hasTable('historial_exportaciones')) {
                return;
            }

            $record = HistorialExportacion::create([
                'user_id' => auth()->id(),
                'marca_id' => $marcaId,
                'proyecto_creativo_id' => $proyectoId,
                'tipo' => $tipo,
                'formato' => $formato,
                'cantidad' => max(1, $cantidad),
            ]);
            CreativeActivity::log('exportaciones', 'generar', $record, $tipo, ['cantidad' => $cantidad, 'formato' => $formato]);
        } catch (\Throwable) {
            // No interrumpe la descarga si el historial todavía no está migrado.
        }
    }
}
