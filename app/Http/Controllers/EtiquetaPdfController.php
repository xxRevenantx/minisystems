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
        abort_unless(
            $request->user()?->puedeEtiquetas('descargar'),
            403
        );

        $data = $request->validate([
            'alumnos' => [
                'required',
                'string',
            ],
            'plantilla_id' => [
                'required',
                'integer',
                'exists:etiqueta_plantillas,id',
            ],
            'modo' => [
                'required',
                Rule::in([
                    'diferentes',
                    'repetir',
                ]),
            ],
            'orden' => [
                'required',
                Rule::in([
                    'academico',
                    'nombre',
                    'seleccion',
                ]),
            ],
            'salida' => [
                'required',
                Rule::in([
                    'vista',
                    'descarga',
                ]),
            ],
        ]);

        /*
         * Convertir los identificadores recibidos en una colección
         * de enteros únicos.
         */
        $ids = collect(json_decode($data['alumnos'], true))
            ->filter(fn($id) => is_numeric($id))
            ->map(fn($id) => (int) $id)
            ->unique()
            ->values();

        abort_if(
            $ids->isEmpty(),
            422,
            'Selecciona al menos un alumno.'
        );

        /*
         * Obtener y validar la plantilla.
         */
        $plantilla = EtiquetaPlantilla::query()
            ->findOrFail($data['plantilla_id']);

        abort_unless(
            $plantilla->activo,
            422,
            'La plantilla seleccionada está inactiva.'
        );

        /*
         * Consultar únicamente alumnos activos.
         */
        $query = EtiquetaAlumno::query()
            ->whereIn('id', $ids)
            ->where('activo', true);

        /*
         * Aplicar el orden seleccionado.
         */
        if ($data['orden'] === 'academico') {
            $query
                ->orderBy('nivel')
                ->orderBy('generacion')
                ->orderBy('grado')
                ->orderBy('grupo')
                ->orderBy('nombre')
                ->orderBy('apellido_paterno')
                ->orderBy('apellido_materno');
        } elseif ($data['orden'] === 'nombre') {
            $query->orderBy('nombre')
                ->orderBy('apellido_paterno')
                ->orderBy('apellido_materno');
        }

        $alumnos = $query->get();

        /*
         * Mantener el orden en que el usuario seleccionó
         * los alumnos.
         */
        if ($data['orden'] === 'seleccion') {
            $ordenSeleccion = $ids->flip();

            $alumnos = $alumnos
                ->sortBy(
                    fn(EtiquetaAlumno $alumno) =>
                    $ordenSeleccion[$alumno->id] ?? PHP_INT_MAX
                )
                ->values();
        }

        abort_if(
            $alumnos->isEmpty(),
            422,
            'No hay alumnos activos para generar las etiquetas.'
        );

        /*
         * Nombre del modo que recibirá la vista Blade.
         *
         * repetir:
         * El mismo alumno aparece dos veces en cada hoja.
         * El nombre superior se gira 180 grados.
         *
         * diferentes:
         * Aparecen dos alumnos distintos en cada hoja.
         * Ningún nombre se gira.
         */
        $modoImpresion = $data['modo'];

        /*
         * Construcción de las páginas.
         */
        if ($modoImpresion === 'repetir') {
            $paginas = $alumnos
                ->map(
                    fn(EtiquetaAlumno $alumno) => collect([
                        $alumno,
                        $alumno,
                    ])
                )
                ->values();
        } else {
            $paginas = $alumnos
                ->chunk(2)
                ->map(fn($pagina) => $pagina->values())
                ->values();
        }

        /*
         * Cargar la imagen de fondo y convertirla a Base64
         * para garantizar que Dompdf pueda utilizarla.
         */
        $disk = $plantilla->disk ?: 'public';

        abort_unless(
            Storage::disk($disk)->exists($plantilla->fondo),
            422,
            'No se encontró la imagen de fondo de la plantilla.'
        );

        $contenido = Storage::disk($disk)->get($plantilla->fondo);

        $mime = Storage::disk($disk)->mimeType($plantilla->fondo)
            ?: 'image/jpeg';

        $fondoBase64 = 'data:'
            . $mime
            . ';base64,'
            . base64_encode($contenido);

        /*
         * Configuración de posiciones, tamaños, colores
         * y campos visibles.
         */
        $configuracion = $plantilla->configuracionConValores();

        /*
         * Registrar la operación en el historial.
         */
        HistorialExportacion::create([
            'user_id' => $request->user()->id,
            'tipo' => 'etiquetas',
            'formato' => 'pdf',
            'cantidad' => $modoImpresion === 'repetir'
                ? $alumnos->count() * 2
                : $alumnos->count(),

            'configuracion' => [
                'plantilla_id' => $plantilla->id,
                'plantilla' => $plantilla->nombre,
                'modo' => $modoImpresion,
                'orden' => $data['orden'],
                'alumnos' => $alumnos->pluck('id')->all(),
            ],

            'notas' => $data['salida'] === 'vista'
                ? 'Vista previa'
                : 'Descarga de etiquetas',
        ]);

        /*
         * Generar el PDF.
         *
         * Es indispensable enviar $modoImpresion a la vista
         * para determinar cuándo debe girarse el primer nombre.
         */
        $pdf = Pdf::loadView('pdf.etiquetas.hoja', [
            'paginas' => $paginas,
            'plantilla' => $plantilla,
            'configuracion' => $configuracion,
            'fondoBase64' => $fondoBase64,
            'modoImpresion' => $modoImpresion,
        ])
            ->setPaper('letter', 'portrait')
            ->setOption('isRemoteEnabled', true)
            ->setOption('dpi', 150);

        $nombre = 'etiquetas-'
            . Str::slug($plantilla->nombre)
            . '-'
            . now()->format('Ymd-His')
            . '.pdf';

        return $data['salida'] === 'descarga'
            ? $pdf->download($nombre)
            : $pdf->stream($nombre);
    }
}
