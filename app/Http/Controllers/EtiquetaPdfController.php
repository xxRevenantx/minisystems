<?php

namespace App\Http\Controllers;

use App\Models\EtiquetaAlumno;
use App\Models\EtiquetaPlantilla;
use App\Models\HistorialExportacion;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class EtiquetaPdfController extends Controller
{
    public function generar(Request $request)
    {
        abort_unless($request->user()?->puedeEtiquetas('descargar'), 403);

        $data = $request->validate([
            'alumnos' => ['required', 'string'],
            'plantilla_id' => ['required', 'integer', 'exists:etiqueta_plantillas,id'],
            'modo' => ['required', Rule::in(['diferentes', 'repetir'])],
            'orden' => ['required', Rule::in(['academico', 'nombre', 'seleccion'])],
            'salida' => ['required', Rule::in(['vista', 'descarga'])],
        ]);

        $ids = collect(json_decode($data['alumnos'], true))
            ->filter(fn ($id) => is_numeric($id))->map(fn ($id) => (int) $id)->unique()->values();
        abort_if($ids->isEmpty(), 422, 'Selecciona al menos un alumno.');

        $plantilla = EtiquetaPlantilla::findOrFail($data['plantilla_id']);
        abort_unless($plantilla->activo, 422, 'La plantilla seleccionada está inactiva.');

        $query = EtiquetaAlumno::query()->whereIn('id', $ids)->where('activo', true);
        if ($data['orden'] === 'academico') {
            $query->orderBy('nivel')->orderBy('generacion')->orderBy('grado')->orderBy('grupo')->orderBy('nombre');
        } elseif ($data['orden'] === 'nombre') {
            $query->orderBy('nombre');
        }
        $alumnos = $query->get();
        if ($data['orden'] === 'seleccion') {
            $order = $ids->flip();
            $alumnos = $alumnos->sortBy(fn ($a) => $order[$a->id] ?? PHP_INT_MAX)->values();
        }
        abort_if($alumnos->isEmpty(), 422, 'No hay alumnos activos para generar las etiquetas.');

        $paginas = $data['modo'] === 'repetir'
            ? $alumnos->map(fn ($alumno) => collect([$alumno, $alumno]))
            : $alumnos->chunk(2)->map(fn ($pagina) => $pagina->values())->values();

        $disk = $plantilla->disk ?: 'public';
        abort_unless(Storage::disk($disk)->exists($plantilla->fondo), 422, 'No se encontró la imagen de fondo de la plantilla.');
        $contenido = Storage::disk($disk)->get($plantilla->fondo);
        $mime = Storage::disk($disk)->mimeType($plantilla->fondo) ?: 'image/jpeg';
        $fondoBase64 = 'data:'.$mime.';base64,'.base64_encode($contenido);
        $configuracion = $plantilla->configuracionConValores();

        HistorialExportacion::create([
            'user_id' => $request->user()->id,
            'tipo' => 'etiquetas',
            'formato' => 'pdf',
            'cantidad' => $data['modo'] === 'repetir' ? $alumnos->count() * 2 : $alumnos->count(),
            'configuracion' => [
                'plantilla_id' => $plantilla->id,
                'plantilla' => $plantilla->nombre,
                'modo' => $data['modo'],
                'orden' => $data['orden'],
                'alumnos' => $alumnos->pluck('id')->all(),
            ],
            'notas' => $data['salida'] === 'vista' ? 'Vista previa' : 'Descarga de etiquetas',
        ]);

        $pdf = Pdf::loadView('pdf.etiquetas.hoja', compact('paginas', 'plantilla', 'configuracion', 'fondoBase64'))
            ->setPaper('letter', 'portrait')
            ->setOption('isRemoteEnabled', true)
            ->setOption('dpi', 150);

        $nombre = 'etiquetas-'.Str::slug($plantilla->nombre).'-'.now()->format('Ymd-His').'.pdf';
        return $data['salida'] === 'descarga' ? $pdf->download($nombre) : $pdf->stream($nombre);
    }
}
