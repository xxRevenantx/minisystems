<?php

namespace App\Http\Controllers;

use App\Models\EtiquetaAlumno;
use App\Services\EtiquetaXlsxService;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class EtiquetaExcelController extends Controller
{
    public function plantilla(Request $request, EtiquetaXlsxService $xlsx)
    {
        abort_unless($request->user()?->puedeEtiquetas('importar'), 403);
        $ruta = sys_get_temp_dir().'/etiquetas_'.Str::uuid().'.xlsx';
        $xlsx->crearPlantilla($ruta);
        return response()->download($ruta, 'plantilla-etiquetas.xlsx')->deleteFileAfterSend(true);
    }

    public function exportar(Request $request, EtiquetaXlsxService $xlsx)
    {
        abort_unless($request->user()?->puedeEtiquetas('descargar'), 403);

        $query = EtiquetaAlumno::query();
        $this->aplicarFiltros($query, $request);
        $alumnos = $query->orderBy('nivel')->orderBy('generacion')->orderBy('grado')->orderBy('grupo')->orderBy('nombre')->get();

        $ruta = sys_get_temp_dir().'/etiquetas_export_'.Str::uuid().'.xlsx';
        $xlsx->crearExportacion($ruta, $alumnos);
        $nombre = 'etiquetas-'.now()->format('Ymd-His').'.xlsx';

        return response()->download($ruta, $nombre)->deleteFileAfterSend(true);
    }

    private function aplicarFiltros(Builder $query, Request $request): void
    {
        if ($buscar = trim((string) $request->query('buscar'))) {
            $query->where(function (Builder $q) use ($buscar) {
                $q->where('nombre', 'like', "%{$buscar}%")
                    ->orWhere('nivel', 'like', "%{$buscar}%")
                    ->orWhere('generacion', 'like', "%{$buscar}%")
                    ->orWhere('grado', 'like', "%{$buscar}%")
                    ->orWhere('grupo', 'like', "%{$buscar}%");
            });
        }
        foreach (['nivel', 'generacion', 'grado', 'grupo'] as $campo) {
            $valor = trim((string) $request->query($campo));
            if ($valor !== '') $query->where($campo, $valor);
        }
        $estado = (string) $request->query('estado', 'activos');
        if ($estado === 'activos') $query->where('activo', true);
        if ($estado === 'inactivos') $query->where('activo', false);
    }
}
