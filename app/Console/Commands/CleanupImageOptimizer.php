<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Storage;

class CleanupImageOptimizer extends Command
{
    protected $signature = 'images:cleanup-optimizer';

    protected $description = 'Elimina lotes y ZIP temporales vencidos del optimizador de imágenes';

    public function handle(): int
    {
        $disk = Storage::disk('local');
        $deletedBatches = 0;
        $deletedZips = 0;

        foreach ($disk->directories('image-optimizer') as $userDirectory) {
            foreach ($disk->directories($userDirectory) as $batchDirectory) {
                if ($this->isExpired($disk, $batchDirectory)) {
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
                // Un archivo temporal dañado no debe detener la limpieza.
            }
        }

        $this->info("Limpieza finalizada: {$deletedBatches} lote(s) y {$deletedZips} ZIP(s) eliminados.");

        return self::SUCCESS;
    }

    private function isExpired($disk, string $batchDirectory): bool
    {
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
