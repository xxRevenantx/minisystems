<?php

namespace App\Imports;

use Maatwebsite\Excel\Concerns\WithMultipleSheets;

class EtiquetasAlumnosImport implements WithMultipleSheets
{
    private readonly EtiquetasAlumnosSheetImport $alumnosSheet;

    /** @param array<int, string> $niveles */
    public function __construct(int $userId, array $niveles)
    {
        $this->alumnosSheet = new EtiquetasAlumnosSheetImport($userId, $niveles);
    }

    public function sheets(): array
    {
        return [
            0 => $this->alumnosSheet,
        ];
    }

    /** @return array{importados:int,actualizados:int,omitidos:int,errores:array<int,string>} */
    public function reporte(): array
    {
        return $this->alumnosSheet->reporte();
    }
}
