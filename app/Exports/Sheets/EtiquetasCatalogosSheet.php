<?php

namespace App\Exports\Sheets;

use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Events\AfterSheet;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class EtiquetasCatalogosSheet implements FromArray, WithEvents, WithStyles, WithTitle
{
    /**
     * @param  array<int, string>  $niveles
     */
    public function __construct(private readonly array $niveles)
    {
    }

    public function array(): array
    {
        $instrucciones = [
            'Nombre(s) y nivel son obligatorios. La generación es obligatoria excepto para Personal y Otro.',
            'Captura por separado nombre, apellido paterno y apellido materno. La etiqueta mostrará primero el nombre.',
            'Para crear un registro nuevo deja la columna id vacía.',
            'Para actualizar registros descarga un Excel editable y conserva el id de cada fila.',
            'Para Personal y Otro, generación, licenciatura o grado y grupo pueden quedar vacíos y no se imprimen en el PDF.',
            'No cambies los encabezados de la hoja Alumnos.',
            'Los duplicados se omiten usando nombre, apellidos, nivel, generación, grado y grupo.',
            'El estado vacío se interpreta como activo.',
            'La importación actualiza únicamente Etiquetas; no modifica el módulo Personas.',
            'Puedes agregar hasta 999 registros por archivo.',
        ];

        $total = max(count($this->niveles), 2, count($instrucciones));
        $filas = [['NIVELES DISPONIBLES', 'ESTADOS', 'INSTRUCCIONES']];

        for ($i = 0; $i < $total; $i++) {
            $filas[] = [
                $this->niveles[$i] ?? '',
                ['activo', 'inactivo'][$i] ?? '',
                $instrucciones[$i] ?? '',
            ];
        }

        return $filas;
    }

    public function title(): string
    {
        return 'Catalogos';
    }

    public function styles(Worksheet $sheet): array
    {
        return [
            1 => [
                'font' => ['bold' => true, 'color' => ['rgb' => 'FFFFFF']],
                'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => '88AC2E']],
                'alignment' => [
                    'horizontal' => Alignment::HORIZONTAL_CENTER,
                    'vertical' => Alignment::VERTICAL_CENTER,
                ],
                'borders' => [
                    'allBorders' => ['borderStyle' => Border::BORDER_THIN, 'color' => ['rgb' => 'D7E3EA']],
                ],
            ],
            'A2:C50' => [
                'alignment' => ['vertical' => Alignment::VERTICAL_TOP, 'wrapText' => true],
                'borders' => [
                    'allBorders' => ['borderStyle' => Border::BORDER_HAIR, 'color' => ['rgb' => 'E2E8F0']],
                ],
            ],
        ];
    }

    public function registerEvents(): array
    {
        return [
            AfterSheet::class => function (AfterSheet $event): void {
                $sheet = $event->sheet->getDelegate();
                $sheet->freezePane('A2');
                $sheet->getRowDimension(1)->setRowHeight(24);
                $sheet->getColumnDimension('A')->setWidth(26);
                $sheet->getColumnDimension('B')->setWidth(18);
                $sheet->getColumnDimension('C')->setWidth(72);
                $sheet->getStyle('C2:C50')->getAlignment()->setWrapText(true);
            },
        ];
    }
}
