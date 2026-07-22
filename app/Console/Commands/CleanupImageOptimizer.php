<?php

namespace App\Console\Commands;

use App\Models\ImageOptimizerBatch;
use App\Models\SystemImageBatch;
use Illuminate\Console\Command;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Storage;

class CleanupImageOptimizer extends Command
{
    protected $signature = 'images:cleanup-optimizer';

    protected $description = 'Elimina lotes, originales y ZIP temporales vencidos de imágenes';

    public function handle(): int
    {
        $disk = Storage::disk('local');
        $deletedBatches = 0;
        $deletedZips = 0;

        if (Schema::hasTable('image_optimizer_batches')) {
            ImageOptimizerBatch::query()
                ->where('expires_at', '<=', now())
                ->orderBy('id')
                ->chunkById(100, function ($batches) use ($disk, &$deletedBatches): void {
                    foreach ($batches as $batch) {
                        $disk->deleteDirectory($batch->basePath());
                        $batch->delete();
                        $deletedBatches++;
                    }
                });
        }


        if (Schema::hasTable('system_image_batches')) {
            SystemImageBatch::query()
                ->where('expires_at', '<=', now())
                ->orderBy('id')
                ->chunkById(100, function ($batches) use ($disk, &$deletedBatches): void {
                    foreach ($batches as $batch) {
                        $disk->deleteDirectory($batch->basePath());
                        $batch->delete();
                        $deletedBatches++;
                    }
                });
        }

        // Compatibilidad con carpetas antiguas creadas antes del registro en BD.
        foreach ($disk->directories('image-optimizer') as $userDirectory) {
            foreach ($disk->directories($userDirectory) as $batchDirectory) {
                if ($this->isExpiredLegacyDirectory($disk, $batchDirectory)) {
                    $disk->deleteDirectory($batchDirectory);
                    $deletedBatches++;
                }
            }

            if ($disk->directories($userDirectory) === [] && $disk->files($userDirectory) === []) {
                $disk->deleteDirectory($userDirectory);
            }
        }

        foreach ($disk->files('image-optimizer-zips') as $zipPath) {
            try {
                if ($disk->lastModified($zipPath) < now()->subDay()->timestamp) {
                    $disk->delete($zipPath);
                    $deletedZips++;
                }
            } catch (\Throwable) {
                // Un ZIP dañado no debe detener la limpieza.
            }
        }


        foreach ($disk->files('system-images-zips') as $zipPath) {
            try {
                if ($disk->lastModified($zipPath) < now()->subDay()->timestamp) {
                    $disk->delete($zipPath);
                    $deletedZips++;
                }
            } catch (\Throwable) {
                // Un ZIP dañado no debe detener la limpieza.
            }
        }

        $this->info("Limpieza finalizada: {$deletedBatches} lote(s) y {$deletedZips} ZIP(s) eliminados.");

        return self::SUCCESS;
    }

    private function isExpiredLegacyDirectory($disk, string $batchDirectory): bool
    {
        $uuid = basename($batchDirectory);

        if (Schema::hasTable('image_optimizer_batches')
            && ImageOptimizerBatch::query()->where('uuid', $uuid)->exists()) {
            return false;
        }

        $metadataPath = $batchDirectory.'/meta.json';

        if ($disk->exists($metadataPath)) {
            try {
                $metadata = json_decode($disk->get($metadataPath), true, flags: JSON_THROW_ON_ERROR);

                if (isset($metadata['expires_at'])) {
                    return Carbon::parse($metadata['expires_at'])->isPast();
                }
            } catch (\Throwable) {
                // Se intentará determinar la antigüedad mediante los archivos.
            }
        }

        $files = $disk->allFiles($batchDirectory);

        if ($files === []) {
            return true;
        }

        try {
            $latestModification = max(array_map(
                fn (string $path): int => $disk->lastModified($path),
                $files
            ));

            return $latestModification < now()->subDay()->timestamp;
        } catch (\Throwable) {
            return false;
        }
    }
}
