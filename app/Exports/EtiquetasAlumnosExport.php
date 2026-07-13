<?php

namespace App\Exports;

use App\Models\EtiquetaAlumno;
use Illuminate\Database\Eloquent\Builder;
use Maatwebsite\Excel\Concerns\FromQuery;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Events\AfterSheet;
use PhpOffice\PhpSpreadsheet\Cell\DataValidation;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class EtiquetasAlumnosExport implements FromQuery, ShouldAutoSize, WithEvents, WithHeadings, WithMapping, WithStyles, WithTitle
{
    /** @param array<int, string> $niveles */
    public function __construct(
        private readonly Builder $builder,
        private readonly bool $editable = false,
        private readonly array $niveles = [],
    ) {
    }

    public function query(): Builder
    {
        return clone $this->builder;
    }

    public function headings(): array
    {
        if ($this->editable) {
            return [
                'id',
                'nombre',
                'apellido_paterno',
                'apellido_materno',
                'nivel',
                'generacion',
                'grado',
                'grupo',
                'estado',
            ];
        }

        return [
            'id',
            'nombre',
            'apellido_paterno',
            'apellido_materno',
            'nombre_completo',
            'nivel',
            'generacion',
            'grado',
            'grupo',
            'estado',
            'persona_vinculada',
            'fecha_registro',
            'ultima_modificacion',
        ];
    }

    /** @param EtiquetaAlumno $alumno */
    public function map($alumno): array
    {
        if ($this->editable) {
            return [
                $alumno->id,
                $alumno->nombre,
                $alumno->apellido_paterno ?? '',
                $alumno->apellido_materno ?? '',
                $alumno->nivel,
                $alumno->generacion,
                $alumno->grado ?? '',
                $alumno->grupo ?? '',
                $alumno->activo ? 'activo' : 'inactivo',
            ];
        }

        return [
            $alumno->id,
            $alumno->nombre,
            $alumno->apellido_paterno ?? '',
            $alumno->apellido_materno ?? '',
            $alumno->nombre_completo,
            $alumno->nivel,
            $alumno->generacion,
            $alumno->grado ?? '',
            $alumno->grupo ?? '',
            $alumno->activo ? 'activo' : 'inactivo',
            $alumno->persona?->nombre ?? '',
            optional($alumno->created_at)->format('d/m/Y H:i'),
            optional($alumno->updated_at)->format('d/m/Y H:i'),
        ];
    }

    public function title(): string
    {
        return $this->editable ? 'Edición masiva' : 'Alumnos';
    }

    public function styles(Worksheet $sheet): array
    {
        return [
            1 => [
                'font' => ['bold' => true, 'color' => ['rgb' => 'FFFFFF']],
                'fill' => [
                    'fillType' => Fill::FILL_SOLID,
                    'startColor' => ['rgb' => $this->editable ? '66851D' : '006492'],
                ],
                'alignment' => [
                    'horizontal' => Alignment::HORIZONTAL_CENTER,
                    'vertical' => Alignment::VERTICAL_CENTER,
                ],
                'borders' => [
                    'allBorders' => [
                        'borderStyle' => Border::BORDER_THIN,
                        'color' => ['rgb' => 'D7E3EA'],
                    ],
                ],
            ],
        ];
    }

    public function registerEvents(): array
    {
        return [
            AfterSheet::class => function (AfterSheet $event): void {
                $sheet = $event->sheet->getDelegate();
                $highestRow = max(1, $sheet->getHighestRow());
                $lastColumn = $this->editable ? 'I' : 'M';

                $sheet->freezePane('A2');
                $sheet->setAutoFilter("A1:{$lastColumn}{$highestRow}");
                $sheet->getRowDimension(1)->setRowHeight(25);
                $sheet->getStyle("A1:{$lastColumn}{$highestRow}")
                    ->getAlignment()
                    ->setVertical(Alignment::VERTICAL_CENTER);

                if ($highestRow >= 2) {
                    $sheet->getStyle("A2:{$lastColumn}{$highestRow}")
                        ->getBorders()
                        ->getAllBorders()
                        ->setBorderStyle(Border::BORDER_HAIR)
                        ->getColor()
                        ->setRGB('E2E8F0');
                }

                foreach (['B', 'C', 'D', 'E'] as $column) {
                    $sheet->getColumnDimension($column)->setWidth($column === 'E' && ! $this->editable ? 42 : 24);
                }

                $sheet->getColumnDimension('F')->setWidth(20);
                $sheet->getColumnDimension('G')->setWidth(18);
                $sheet->getColumnDimension('H')->setWidth(14);
                $sheet->getColumnDimension('I')->setWidth(14);

                if (! $this->editable) {
                    $sheet->getColumnDimension('J')->setWidth(14);
                    $sheet->getColumnDimension('K')->setWidth(32);
                    $sheet->getColumnDimension('L')->setWidth(20);
                    $sheet->getColumnDimension('M')->setWidth(20);
                    return;
                }

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
                    ->setShowErrorMessage(true)
                    ->setShowDropDown(true)
                    ->setErrorTitle('Nivel no válido')
                    ->setError('Selecciona un nivel de la lista.')
                    ->setFormula1('$K$2:$K$'.(count($this->niveles) + 1));

                $estadoValidation = new DataValidation();
                $estadoValidation->setType(DataValidation::TYPE_LIST)
                    ->setErrorStyle(DataValidation::STYLE_STOP)
                    ->setAllowBlank(false)
                    ->setShowErrorMessage(true)
                    ->setShowDropDown(true)
                    ->setErrorTitle('Estado no válido')
                    ->setError('Utiliza activo o inactivo.')
                    ->setFormula1('$L$2:$L$3');

                for ($row = 2; $row <= max(1000, $highestRow); $row++) {
                    $sheet->getCell("E{$row}")->setDataValidation(clone $nivelValidation);
                    $sheet->getCell("I{$row}")->setDataValidation(clone $estadoValidation);
                }
            },
        ];
    }
}
