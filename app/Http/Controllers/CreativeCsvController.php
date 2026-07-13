<?php

namespace App\Http\Controllers;

use App\Models\Persona;
use Illuminate\Http\Response;

class CreativeCsvController extends Controller
{
    public function personasPlantilla(): Response
    {
        $csv = "nombre,tipo,cargo,organizacion,email,telefono,identificador,tags,notas\n";
        $csv .= "\"María López\",contacto,\"Diseñadora\",\"Empresa Ejemplo\",\"maria@ejemplo.com\",\"5550000000\",\"EMP-001\",\"cliente,evento\",\"Contacto principal\"\n";

        return response($csv, 200, [
            'Content-Type' => 'text/csv; charset=UTF-8',
            'Content-Disposition' => 'attachment; filename=\"plantilla_personas.csv\"',
        ]);
    }

    public function personasExportar(): Response
    {
        $stream = fopen('php://temp', 'w+');
        fwrite($stream, "\xEF\xBB\xBF");
        fputcsv($stream, ['nombre','tipo','marca','cargo','organizacion','email','telefono','identificador','tags','notas']);

        Persona::with('marca')->orderBy('nombre')->chunk(250, function ($personas) use ($stream): void {
            foreach ($personas as $persona) {
                fputcsv($stream, [
                    $persona->nombre,
                    $persona->tipo,
                    $persona->marca?->nombre,
                    $persona->cargo,
                    $persona->organizacion,
                    $persona->email,
                    $persona->telefono,
                    $persona->identificador,
                    implode(',', $persona->tags ?? []),
                    $persona->notas,
                ]);
            }
        });

        rewind($stream);
        $csv = stream_get_contents($stream);
        fclose($stream);

        return response($csv, 200, [
            'Content-Type' => 'text/csv; charset=UTF-8',
            'Content-Disposition' => 'attachment; filename=\"personas_'.now()->format('Ymd_His').'.csv\"',
        ]);
    }

    public function generadorPlantilla(): Response
    {
        $csv = "nombre,cargo,organizacion,motivo,fecha,folio\n";
        $csv .= "\"María López\",\"Ponente\",\"Empresa Ejemplo\",\"Por su destacada participación\",\"".now()->format('Y-m-d')."\",\"REC-001\"\n";

        return response($csv, 200, [
            'Content-Type' => 'text/csv; charset=UTF-8',
            'Content-Disposition' => 'attachment; filename=\"plantilla_generador_masivo.csv\"',
        ]);
    }
}
