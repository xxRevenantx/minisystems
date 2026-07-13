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
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class EtiquetasAlumnosExport implements FromQuery, ShouldAutoSize, WithEvents, WithHeadings, WithMapping, WithStyles, WithTitle
{
    public function __construct(private readonly Builder $builder)
    {
    }

    public function query(): Builder
    {
        return clone $this->builder;
    }

    public function headings(): array
    {
        return ['nombre', 'nivel', 'generacion', 'grado', 'grupo', 'estado'];
    }

    /**
     * @param  EtiquetaAlumno  $alumno
     */
    public function map($alumno): array
    {
        return [
            $alumno->nombre,
            $alumno->nivel,
            $alumno->generacion,
            $alumno->grado ?? '',
            $alumno->grupo ?? '',
            $alumno->activo ? 'activo' : 'inactivo',
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
        ];
    }

    public function registerEvents(): array
    {
        return [
            AfterSheet::class => function (AfterSheet $event): void {
                $sheet = $event->sheet->getDelegate();
                $highestRow = max(1, $sheet->getHighestRow());
                $sheet->freezePane('A2');
                $sheet->setAutoFilter("A1:F{$highestRow}");
                $sheet->getRowDimension(1)->setRowHeight(24);
                $sheet->getColumnDimension('A')->setWidth(38);
                $sheet->getColumnDimension('B')->setWidth(20);
                $sheet->getColumnDimension('C')->setWidth(20);
                $sheet->getColumnDimension('D')->setWidth(15);
                $sheet->getColumnDimension('E')->setWidth(15);
                $sheet->getColumnDimension('F')->setWidth(15);
                $sheet->getStyle("A1:F{$highestRow}")->getAlignment()->setVertical(Alignment::VERTICAL_CENTER);

                if ($highestRow >= 2) {
                    $sheet->getStyle("A2:F{$highestRow}")->getBorders()->getAllBorders()
                        ->setBorderStyle(Border::BORDER_HAIR)
                        ->getColor()->setRGB('E2E8F0');
                }
            },
        ];
    }
}
