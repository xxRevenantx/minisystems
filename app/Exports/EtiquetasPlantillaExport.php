<?php

namespace App\Exports;

use App\Exports\Sheets\EtiquetasCatalogosSheet;
use App\Exports\Sheets\EtiquetasPlantillaAlumnosSheet;
use Maatwebsite\Excel\Concerns\WithMultipleSheets;

class EtiquetasPlantillaExport implements WithMultipleSheets
{
    /**
     * @param  array<int, string>  $niveles
     */
    public function __construct(private readonly array $niveles)
    {
    }

    public function sheets(): array
    {
        return [
            new EtiquetasPlantillaAlumnosSheet($this->niveles),
            new EtiquetasCatalogosSheet($this->niveles),
        ];
    }
}
