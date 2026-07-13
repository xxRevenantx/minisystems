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
    /**
     * @param  array<int, string>  $niveles
     */
    public function __construct(private readonly array $niveles)
    {
    }

    public function array(): array
    {
        return [
            ['nombre', 'nivel', 'generacion', 'grado', 'grupo', 'estado'],
            ['MARÍA PÉREZ LÓPEZ', 'Primaria', '2023-2029', '3°', 'A', 'activo'],
            ['JUAN HERNÁNDEZ CRUZ', 'Curso', '2026', '', '', 'activo'],
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
            'A2:F1000' => [
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
                $sheet->setAutoFilter('A1:F1000');
                $sheet->getRowDimension(1)->setRowHeight(24);
                $sheet->getColumnDimension('A')->setWidth(38);
                $sheet->getColumnDimension('B')->setWidth(20);
                $sheet->getColumnDimension('C')->setWidth(20);
                $sheet->getColumnDimension('D')->setWidth(15);
                $sheet->getColumnDimension('E')->setWidth(15);
                $sheet->getColumnDimension('F')->setWidth(15);
                $sheet->getStyle('A1:F1000')->getAlignment()->setWrapText(true);
                $sheet->getStyle('A2:F3')->getFill()
                    ->setFillType(Fill::FILL_SOLID)
                    ->getStartColor()->setRGB('F8FAFC');

                $sheet->setCellValue('H1', 'niveles_validacion');
                foreach ($this->niveles as $index => $nivel) {
                    $sheet->setCellValue('H'.($index + 2), $nivel);
                }
                $sheet->setCellValue('I1', 'estados_validacion');
                $sheet->setCellValue('I2', 'activo');
                $sheet->setCellValue('I3', 'inactivo');
                $sheet->getColumnDimension('H')->setVisible(false);
                $sheet->getColumnDimension('I')->setVisible(false);

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
                    ->setFormula1('$H$2:$H$'.(count($this->niveles) + 1));

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
                    ->setFormula1('$I$2:$I$3');

                for ($row = 2; $row <= 1000; $row++) {
                    $sheet->getCell("B{$row}")->setDataValidation(clone $nivelValidation);
                    $sheet->getCell("F{$row}")->setDataValidation(clone $estadoValidation);
                }
            },
        ];
    }
}
