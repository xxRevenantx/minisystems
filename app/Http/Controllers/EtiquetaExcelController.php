<?php

namespace App\Http\Controllers;

use App\Exports\EtiquetasAlumnosExport;
use App\Exports\EtiquetasPlantillaExport;
use App\Models\EtiquetaAlumno;
use App\Models\HistorialExportacion;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Maatwebsite\Excel\Facades\Excel;

class EtiquetaExcelController extends Controller
{
    private const NIVELES = [
        'Preescolar',
        'Primaria',
        'Secundaria',
        'Bachillerato',
        'Licenciatura',
        'Personal',
        'Curso',
        'Taller',
        'Otro',
    ];

    public function plantilla(Request $request)
    {
        abort_unless($request->user()?->puedeEtiquetas('importar'), 403);

        return Excel::download(
            new EtiquetasPlantillaExport(self::NIVELES),
            'plantilla-etiquetas.xlsx'
        );
    }

    public function exportar(Request $request)
    {
        abort_unless($request->user()?->puedeEtiquetas('descargar'), 403);

        $data = $request->validate([
            'alcance' => ['nullable', Rule::in(['filtrados', 'todos', 'seleccionados', 'individual'])],
            'tipo' => ['nullable', Rule::in(['reporte', 'edicion'])],
            'id' => ['nullable', 'integer', 'exists:etiqueta_alumnos,id'],
            'alumnos' => ['nullable'],
            'ordenar_por' => ['nullable', Rule::in(['academico', 'id', 'fecha_creacion', 'nombre', 'apellidos'])],
            'direccion_orden' => ['nullable', Rule::in(['asc', 'desc'])],
        ]);

        $alcance = $data['alcance'] ?? 'filtrados';
        $tipo = $data['tipo'] ?? 'reporte';
        $editable = $tipo === 'edicion';

        $query = EtiquetaAlumno::query()->with('persona:id,nombre');
        $ids = collect();

        if ($alcance === 'individual') {
            abort_unless(filled($data['id'] ?? null), 422, 'No se indicó el registro a exportar.');
            $query->whereKey((int) $data['id']);
        } elseif ($alcance === 'seleccionados') {
            $ids = $this->normalizarIds($request->input('alumnos'));
            abort_if($ids->isEmpty(), 422, 'Selecciona al menos un alumno para exportar.');
            $query->whereIn('id', $ids);
        } elseif ($alcance === 'filtrados') {
            $this->aplicarFiltros($query, $request);
        }

        $this->aplicarOrden(
            $query,
            $data['ordenar_por'] ?? 'academico',
            $data['direccion_orden'] ?? 'asc',
        );

        $cantidad = (clone $query)->count();
        abort_if($cantidad === 0, 422, 'No hay registros para exportar.');

        HistorialExportacion::create([
            'user_id' => $request->user()->id,
            'tipo' => 'etiquetas',
            'formato' => 'xlsx',
            'cantidad' => $cantidad,
            'configuracion' => [
                'operacion' => 'exportacion_excel',
                'alcance' => $alcance,
                'tipo_excel' => $tipo,
                'alumnos' => $ids->all(),
                'filtros' => $alcance === 'filtrados' ? $request->only([
                    'buscar',
                    'nivel',
                    'generacion',
                    'grado',
                    'grupo',
                    'estado',
                    'ordenar_por',
                    'direccion_orden',
                ]) : [],
            ],
            'notas' => $editable
                ? 'Excel editable para actualización masiva'
                : 'Reporte de alumnos en Excel',
        ]);

        $sufijo = match ($alcance) {
            'individual' => 'individual',
            'seleccionados' => 'seleccionados',
            'todos' => 'todos',
            default => 'filtrados',
        };

        $nombre = Str::slug('etiquetas-' . $tipo . '-' . $sufijo) . '-' . now()->format('Ymd-His') . '.xlsx';

        return Excel::download(
            new EtiquetasAlumnosExport($query, $editable, self::NIVELES),
            $nombre
        );
    }

    private function aplicarFiltros(Builder $query, Request $request): void
    {
        if ($buscar = trim((string) $request->input('buscar'))) {
            $query->where(function (Builder $subquery) use ($buscar): void {
                $subquery->where('nombre', 'like', "%{$buscar}%")
                    ->orWhere('apellido_paterno', 'like', "%{$buscar}%")
                    ->orWhere('apellido_materno', 'like', "%{$buscar}%")
                    ->orWhere('nivel', 'like', "%{$buscar}%")
                    ->orWhere('generacion', 'like', "%{$buscar}%")
                    ->orWhere('grado', 'like', "%{$buscar}%")
                    ->orWhere('grupo', 'like', "%{$buscar}%");
            });
        }

        foreach (['nivel', 'generacion', 'grado', 'grupo'] as $campo) {
            $valor = trim((string) $request->input($campo));
            if ($valor !== '') {
                $query->where($campo, $valor);
            }
        }

        $estado = (string) $request->input('estado', 'activos');
        if ($estado === 'activos') {
            $query->where('activo', true);
        } elseif ($estado === 'inactivos') {
            $query->where('activo', false);
        }
    }

    private function aplicarOrden(Builder $query, string $ordenarPor, string $direccionOrden): void
    {
        $direccion = $direccionOrden === 'desc' ? 'desc' : 'asc';

        match ($ordenarPor) {
            'id' => $query
                ->orderBy('id', $direccion),
            'fecha_creacion' => $query
                ->orderByRaw('created_at IS NULL')
                ->orderBy('created_at', $direccion)
                ->orderBy('id', $direccion),
            'nombre' => $query
                ->orderBy('nombre', $direccion)
                ->orderByRaw('apellido_paterno IS NULL')
                ->orderBy('apellido_paterno', $direccion)
                ->orderByRaw('apellido_materno IS NULL')
                ->orderBy('apellido_materno', $direccion)
                ->orderBy('id', $direccion),
            'apellidos' => $query
                ->orderByRaw('apellido_paterno IS NULL')
                ->orderBy('apellido_paterno', $direccion)
                ->orderByRaw('apellido_materno IS NULL')
                ->orderBy('apellido_materno', $direccion)
                ->orderBy('nombre', $direccion)
                ->orderBy('id', $direccion),
            default => $query
                ->orderBy('nivel', $direccion)
                ->orderBy('generacion', $direccion)
                ->orderByRaw('grado IS NULL')
                ->orderBy('grado', $direccion)
                ->orderByRaw('grupo IS NULL')
                ->orderBy('grupo', $direccion)
                ->orderBy('nombre', $direccion)
                ->orderByRaw('apellido_paterno IS NULL')
                ->orderBy('apellido_paterno', $direccion)
                ->orderByRaw('apellido_materno IS NULL')
                ->orderBy('apellido_materno', $direccion)
                ->orderBy('id', $direccion),
        };
    }

    private function normalizarIds(mixed $valor)
    {
        if (is_string($valor)) {
            $decodificado = json_decode($valor, true);
            $valor = is_array($decodificado) ? $decodificado : explode(',', $valor);
        }

        return collect(is_array($valor) ? $valor : [])
            ->filter(fn($id) => is_numeric($id))
            ->map(fn($id) => (int) $id)
            ->unique()
            ->values();
    }
}
