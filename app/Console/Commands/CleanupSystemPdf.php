<?php

namespace App\Console\Commands;

use App\Models\PdfBatch;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Storage;

class CleanupSystemPdf extends Command
{
    protected $signature = 'system-pdf:cleanup';
    protected $description = 'Elimina lotes y archivos temporales vencidos de System PDF';

    public function handle(): int
    {
        if (! Schema::hasTable('pdf_batches')) {
            return self::SUCCESS;
        }

        $disk = Storage::disk('local');
        $deleted = 0;

        PdfBatch::query()
            ->whereNotNull('expires_at')
            ->where('expires_at', '<=', now())
            ->orderBy('id')
            ->chunkById(50, function ($batches) use ($disk, &$deleted): void {
                foreach ($batches as $batch) {
                    $disk->deleteDirectory($batch->basePath());
                    $batch->delete();
                    $deleted++;
                }
            });

        foreach ($disk->files('system-pdf-zips') as $zip) {
            try {
                if ($disk->lastModified($zip) < now()->subDay()->timestamp) {
                    $disk->delete($zip);
                }
            } catch (\Throwable) {
                // Un ZIP dañado no detiene la limpieza.
            }
        }

        $this->info("System PDF: {$deleted} lote(s) vencido(s) eliminado(s).");
        return self::SUCCESS;
    }
}
