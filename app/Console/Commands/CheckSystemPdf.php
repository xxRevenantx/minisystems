<?php

namespace App\Console\Commands;

use App\Services\Pdf\PdfToolchain;
use Illuminate\Console\Command;

class CheckSystemPdf extends Command
{
    protected $signature = 'system-pdf:check';
    protected $description = 'Verifica Ghostscript, qpdf y Poppler para el módulo System PDF';

    public function handle(PdfToolchain $tools): int
    {
        $status = $tools->status();
        $labels = [
            'ghostscript' => 'Ghostscript',
            'qpdf' => 'qpdf',
            'pdfinfo' => 'Poppler pdfinfo',
            'pdftoppm' => 'Poppler pdftoppm',
        ];

        $rows = [];
        foreach ($labels as $key => $label) {
            $rows[] = [
                $label,
                $status[$key]['available'] ? 'OK' : 'FALTA',
                $status[$key]['path'] ?: 'Configura la variable correspondiente en .env',
            ];
        }
        $this->table(['Herramienta', 'Estado', 'Ruta'], $rows);

        if (collect($status)->contains(fn (array $tool): bool => ! $tool['available'])) {
            $this->newLine();
            $this->warn('Configura estas variables en .env con rutas absolutas de Windows:');
            $this->line('SYSTEM_PDF_GHOSTSCRIPT_BINARY="C:\\Program Files\\gs\\gs10.xx.x\\bin\\gswin64c.exe"');
            $this->line('SYSTEM_PDF_QPDF_BINARY="C:\\Program Files\\qpdf\\bin\\qpdf.exe"');
            $this->line('SYSTEM_PDF_PDFINFO_BINARY="C:\\poppler\\Library\\bin\\pdfinfo.exe"');
            $this->line('SYSTEM_PDF_PDFTOPPM_BINARY="C:\\poppler\\Library\\bin\\pdftoppm.exe"');
            $this->newLine();
            $this->line('Después ejecuta: php artisan config:clear');
            return self::FAILURE;
        }

        $this->info('System PDF está listo para procesar archivos.');
        return self::SUCCESS;
    }
}
