<?php

namespace App\Http\Controllers;

use App\Exports\EtiquetasAlumnosExport;
use App\Exports\EtiquetasPlantillaExport;
use App\Models\EtiquetaAlumno;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
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

        $query = EtiquetaAlumno::query();
        $this->aplicarFiltros($query, $request);

        $query->orderBy('nivel')
            ->orderBy('generacion')
            ->orderBy('grado')
            ->orderBy('grupo')
            ->orderBy('nombre');

        return Excel::download(
            new EtiquetasAlumnosExport($query),
            'etiquetas-'.now()->format('Ymd-His').'.xlsx'
        );
    }

    private function aplicarFiltros(Builder $query, Request $request): void
    {
        if ($buscar = trim((string) $request->query('buscar'))) {
            $query->where(function (Builder $subquery) use ($buscar): void {
                $subquery->where('nombre', 'like', "%{$buscar}%")
                    ->orWhere('nivel', 'like', "%{$buscar}%")
                    ->orWhere('generacion', 'like', "%{$buscar}%")
                    ->orWhere('grado', 'like', "%{$buscar}%")
                    ->orWhere('grupo', 'like', "%{$buscar}%");
            });
        }

        foreach (['nivel', 'generacion', 'grado', 'grupo'] as $campo) {
            $valor = trim((string) $request->query($campo));

            if ($valor !== '') {
                $query->where($campo, $valor);
            }
        }

        $estado = (string) $request->query('estado', 'activos');

        if ($estado === 'activos') {
            $query->where('activo', true);
        } elseif ($estado === 'inactivos') {
            $query->where('activo', false);
        }
    }
}
