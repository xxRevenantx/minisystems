<?php

namespace App\Exports\Sheets;

use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Events\AfterSheet;
use PhpOffice\PhpSpreadsheet\Cell\DataValidation;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class EtiquetasPlantillaAlumnosSheet implements FromArray, WithEvents, WithStyles, WithTitle
{
    /** @param array<int, string> $niveles */
    public function __construct(private readonly array $niveles)
    {
    }

    public function array(): array
    {
        return [
            ['id', 'nombre', 'apellido_paterno', 'apellido_materno', 'nivel', 'generacion', 'grado', 'grupo', 'estado'],
            ['', 'MARÍA FERNANDA', 'PÉREZ', 'LÓPEZ', 'Primaria', '2023-2029', '3°', 'A', 'activo'],
            ['', 'JUAN CARLOS', 'HERNÁNDEZ', 'CRUZ', 'Curso', '2026', '', '', 'activo'],
        ];
    }

    public function title(): string
    {
        return 'Alumnos';
    }

    public function styles(Worksheet $sheet): array
    {
        return [
            1 => [
                'font' => ['bold' => true, 'color' => ['rgb' => 'FFFFFF']],
                'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => '006492']],
                'alignment' => [
                    'horizontal' => Alignment::HORIZONTAL_CENTER,
                    'vertical' => Alignment::VERTICAL_CENTER,
                ],
                'borders' => [
                    'allBorders' => ['borderStyle' => Border::BORDER_THIN, 'color' => ['rgb' => 'D7E3EA']],
                ],
            ],
            'A2:I1000' => [
                'alignment' => ['vertical' => Alignment::VERTICAL_CENTER],
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
                $sheet->setAutoFilter('A1:I1000');
                $sheet->getRowDimension(1)->setRowHeight(24);
                $sheet->getColumnDimension('A')->setWidth(12);
                $sheet->getColumnDimension('B')->setWidth(28);
                $sheet->getColumnDimension('C')->setWidth(24);
                $sheet->getColumnDimension('D')->setWidth(24);
                $sheet->getColumnDimension('E')->setWidth(20);
                $sheet->getColumnDimension('F')->setWidth(20);
                $sheet->getColumnDimension('G')->setWidth(15);
                $sheet->getColumnDimension('H')->setWidth(15);
                $sheet->getColumnDimension('I')->setWidth(15);
                $sheet->getStyle('A1:I1000')->getAlignment()->setWrapText(true);
                $sheet->getStyle('A2:I3')->getFill()
                    ->setFillType(Fill::FILL_SOLID)
                    ->getStartColor()->setRGB('F8FAFC');

                $sheet->setCellValue('K1', 'niveles_validacion');
                foreach ($this->niveles as $index => $nivel) {
                    $sheet->setCellValue('K'.($index + 2), $nivel);
                }
                $sheet->setCellValue('L1', 'estados_validacion');
                $sheet->setCellValue('L2', 'activo');
                $sheet->setCellValue('L3', 'inactivo');
                $sheet->getColumnDimension('K')->setVisible(false);
                $sheet->getColumnDimension('L')->setVisible(false);

                $nivelValidation = new DataValidation();
                $nivelValidation->setType(DataValidation::TYPE_LIST)
                    ->setErrorStyle(DataValidation::STYLE_STOP)
                    ->setAllowBlank(false)
                    ->setShowInputMessage(true)
                    ->setShowErrorMessage(true)
                    ->setShowDropDown(true)
                    ->setErrorTitle('Nivel no válido')
                    ->setError('Selecciona un nivel de la lista.')
                    ->setPromptTitle('Nivel')
                    ->setPrompt('Selecciona el nivel correspondiente.')
                    ->setFormula1('$K$2:$K$'.(count($this->niveles) + 1));

                $estadoValidation = new DataValidation();
                $estadoValidation->setType(DataValidation::TYPE_LIST)
                    ->setErrorStyle(DataValidation::STYLE_STOP)
                    ->setAllowBlank(true)
                    ->setShowInputMessage(true)
                    ->setShowErrorMessage(true)
                    ->setShowDropDown(true)
                    ->setErrorTitle('Estado no válido')
                    ->setError('Utiliza activo o inactivo.')
                    ->setPromptTitle('Estado')
                    ->setPrompt('Vacío se interpreta como activo.')
                    ->setFormula1('$L$2:$L$3');

                for ($row = 2; $row <= 1000; $row++) {
                    $sheet->getCell("E{$row}")->setDataValidation(clone $nivelValidation);
                    $sheet->getCell("I{$row}")->setDataValidation(clone $estadoValidation);
                }
            },
        ];
    }
}
