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
    /** @var array{importados:int,actualizados:int,omitidos:int,errores:array<int,string>} */
    private array $reporte = [
        'importados' => 0,
        'actualizados' => 0,
        'omitidos' => 0,
        'errores' => [],
    ];

    /** @param array<int, string> $niveles */
    public function __construct(
        private readonly int $userId,
        private readonly array $niveles,
    ) {
    }

    public function collection(Collection $rows): void
    {
        if ($rows->isEmpty()) {
            throw new RuntimeException('El archivo no contiene alumnos para importar o actualizar.');
        }

        $encabezados = $rows->first()->keys()->map(fn ($key) => (string) $key)->all();
        foreach (['nombre', 'nivel', 'generacion'] as $obligatorio) {
            if (! in_array($obligatorio, $encabezados, true)) {
                throw new RuntimeException('La hoja debe contener los encabezados nombre, nivel y generacion.');
            }
        }

        DB::transaction(function () use ($rows): void {
            foreach ($rows as $index => $row) {
                $numeroFila = $index + 2;

                try {
                    $id = is_numeric($row['id'] ?? null) ? (int) $row['id'] : null;
                    $nombre = trim((string) ($row['nombre'] ?? ''));
                    $apellidoPaterno = trim((string) ($row['apellido_paterno'] ?? ''));
                    $apellidoMaterno = trim((string) ($row['apellido_materno'] ?? ''));
                    $nivel = trim((string) ($row['nivel'] ?? ''));
                    $generacion = trim((string) ($row['generacion'] ?? ''));
                    $grado = trim((string) ($row['grado'] ?? ''));
                    $grupo = trim((string) ($row['grupo'] ?? ''));
                    $estado = trim((string) ($row['estado'] ?? 'activo'));

                    if ($nombre === '' && $nivel === '' && $generacion === '' && $grado === '' && $grupo === '') {
                        continue;
                    }

                    if ($nombre === '' || $nivel === '') {
                        $this->reporte['errores'][] = "Fila {$numeroFila}: nombre y nivel son obligatorios.";
                        continue;
                    }

                    $nivelValido = collect($this->niveles)->first(
                        fn (string $item) => Str::lower(Str::ascii($item)) === Str::lower(Str::ascii($nivel))
                    );

                    if (! $nivelValido) {
                        $this->reporte['errores'][] = "Fila {$numeroFila}: el nivel «{$nivel}» no es válido.";
                        continue;
                    }

                    $sinDatosAcademicos = in_array($nivelValido, ['Personal', 'Otro'], true);

                    if (! $sinDatosAcademicos && $generacion === '') {
                        $this->reporte['errores'][] = "Fila {$numeroFila}: la generación es obligatoria para el nivel {$nivelValido}.";
                        continue;
                    }

                    $payload = [
                        'nombre' => $this->normalizarNombre($nombre),
                        'apellido_paterno' => $apellidoPaterno !== '' ? $this->normalizarNombre($apellidoPaterno) : null,
                        'apellido_materno' => $apellidoMaterno !== '' ? $this->normalizarNombre($apellidoMaterno) : null,
                        'nivel' => $nivelValido,
                        'generacion' => ! $sinDatosAcademicos && $generacion !== '' ? Str::squish($generacion) : null,
                        'grado' => ! $sinDatosAcademicos && $grado !== '' ? Str::squish($grado) : null,
                        'grupo' => ! $sinDatosAcademicos && $grupo !== '' ? Str::upper(Str::squish($grupo)) : null,
                        'activo' => ! in_array(Str::lower(Str::ascii($estado)), ['inactivo', '0', 'no', 'false'], true),
                    ];

                    $alumno = null;
                    if ($id !== null) {
                        $alumno = EtiquetaAlumno::query()->find($id);
                        if (! $alumno) {
                            $this->reporte['errores'][] = "Fila {$numeroFila}: no existe el registro con ID {$id}.";
                            continue;
                        }
                    }

                    if ($this->existeDuplicado($payload, $alumno?->id)) {
                        $this->reporte['omitidos']++;
                        continue;
                    }

                    if ($alumno) {
                        $alumno->update($payload);
                        $this->reporte['actualizados']++;
                    } else {
                        EtiquetaAlumno::create($payload + ['user_id' => $this->userId]);
                        $this->reporte['importados']++;
                    }
                } catch (\Throwable $exception) {
                    $this->reporte['errores'][] = "Fila {$numeroFila}: {$exception->getMessage()}";
                }
            }
        });
    }

    /** @param array<string, mixed> $payload */
    private function existeDuplicado(array $payload, ?int $exceptoId): bool
    {
        return EtiquetaAlumno::query()
            ->when($exceptoId, fn (Builder $query) => $query->where('id', '!=', $exceptoId))
            ->where('nombre', $payload['nombre'])
            ->where(function (Builder $query) use ($payload): void {
                $payload['apellido_paterno'] === null
                    ? $query->whereNull('apellido_paterno')
                    : $query->where('apellido_paterno', $payload['apellido_paterno']);
            })
            ->where(function (Builder $query) use ($payload): void {
                $payload['apellido_materno'] === null
                    ? $query->whereNull('apellido_materno')
                    : $query->where('apellido_materno', $payload['apellido_materno']);
            })
            ->where('nivel', $payload['nivel'])
            ->where('generacion', $payload['generacion'])
            ->where(function (Builder $query) use ($payload): void {
                $payload['grado'] === null
                    ? $query->whereNull('grado')
                    : $query->where('grado', $payload['grado']);
            })
            ->where(function (Builder $query) use ($payload): void {
                $payload['grupo'] === null
                    ? $query->whereNull('grupo')
                    : $query->where('grupo', $payload['grupo']);
            })
            ->exists();
    }

    private function normalizarNombre(string $valor): string
    {
        return Str::upper(Str::squish($valor));
    }

    /** @return array{importados:int,actualizados:int,omitidos:int,errores:array<int,string>} */
    public function reporte(): array
    {
        return $this->reporte;
    }
}
