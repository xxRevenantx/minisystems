<?php

namespace App\Imports;

use App\Models\EtiquetaAlumno;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Maatwebsite\Excel\Concerns\SkipsEmptyRows;
use Maatwebsite\Excel\Concerns\ToCollection;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use RuntimeException;

class EtiquetasAlumnosSheetImport implements SkipsEmptyRows, ToCollection, WithHeadingRow
{
    /** @var array{importados:int,omitidos:int,errores:array<int,string>} */
    private array $reporte = [
        'importados' => 0,
        'omitidos' => 0,
        'errores' => [],
    ];

    /**
     * @param  array<int, string>  $niveles
     */
    public function __construct(
        private readonly int $userId,
        private readonly array $niveles,
    ) {
    }

    public function collection(Collection $rows): void
    {
        if ($rows->isEmpty()) {
            throw new RuntimeException('El archivo no contiene alumnos para importar.');
        }

        $encabezados = $rows->first()->keys()->map(fn ($key) => (string) $key)->all();
        foreach (['nombre', 'nivel', 'generacion'] as $obligatorio) {
            if (! in_array($obligatorio, $encabezados, true)) {
                throw new RuntimeException('La hoja Alumnos debe contener los encabezados nombre, nivel y generacion.');
            }
        }

        DB::transaction(function () use ($rows): void {
            foreach ($rows as $index => $row) {
                $numeroFila = $index + 2;

                try {
                    $nombre = trim((string) ($row['nombre'] ?? ''));
                    $nivel = trim((string) ($row['nivel'] ?? ''));
                    $generacion = trim((string) ($row['generacion'] ?? ''));
                    $grado = trim((string) ($row['grado'] ?? ''));
                    $grupo = trim((string) ($row['grupo'] ?? ''));
                    $estado = trim((string) ($row['estado'] ?? 'activo'));

                    if ($nombre === '' && $nivel === '' && $generacion === '' && $grado === '' && $grupo === '') {
                        continue;
                    }

                    if ($nombre === '' || $nivel === '' || $generacion === '') {
                        $this->reporte['errores'][] = "Fila {$numeroFila}: nombre, nivel y generación son obligatorios.";
                        continue;
                    }

                    $nivelValido = collect($this->niveles)->first(
                        fn (string $item) => Str::lower(Str::ascii($item)) === Str::lower(Str::ascii($nivel))
                    );

                    if (! $nivelValido) {
                        $this->reporte['errores'][] = "Fila {$numeroFila}: el nivel «{$nivel}» no es válido.";
                        continue;
                    }

                    $payload = [
                        'nombre' => Str::upper(Str::squish($nombre)),
                        'nivel' => $nivelValido,
                        'generacion' => Str::squish($generacion),
                        'grado' => $grado !== '' ? Str::squish($grado) : null,
                        'grupo' => $grupo !== '' ? Str::upper(Str::squish($grupo)) : null,
                    ];

                    $duplicado = EtiquetaAlumno::query()
                        ->where('nombre', $payload['nombre'])
                        ->where('nivel', $payload['nivel'])
                        ->where('generacion', $payload['generacion'])
                        ->where(fn (Builder $query) => $payload['grado'] === null
                            ? $query->whereNull('grado')
                            : $query->where('grado', $payload['grado']))
                        ->where(fn (Builder $query) => $payload['grupo'] === null
                            ? $query->whereNull('grupo')
                            : $query->where('grupo', $payload['grupo']))
                        ->exists();

                    if ($duplicado) {
                        $this->reporte['omitidos']++;
                        continue;
                    }

                    EtiquetaAlumno::create($payload + [
                        'user_id' => $this->userId,
                        'activo' => ! in_array(Str::lower(Str::ascii($estado)), ['inactivo', '0', 'no', 'false'], true),
                    ]);

                    $this->reporte['importados']++;
                } catch (\Throwable $exception) {
                    $this->reporte['errores'][] = "Fila {$numeroFila}: {$exception->getMessage()}";
                }
            }
        });
    }

    /**
     * @return array{importados:int,omitidos:int,errores:array<int,string>}
     */
    public function reporte(): array
    {
        return $this->reporte;
    }
}
