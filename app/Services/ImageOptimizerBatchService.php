<?php

namespace App\Services;

use App\Models\HistorialExportacion;
use App\Models\ImageOptimizerBatch;
use Illuminate\Support\Facades\Schema;

class ImageOptimizerBatchService
{
    /**
     * Recalcula el avance del lote a partir del estado real de sus elementos.
     */
    public function recalculate(ImageOptimizerBatch $batch): ImageOptimizerBatch
    {
        $batch->refresh();

        $counts = $batch->items()
            ->reorder()
            ->selectRaw('status, COUNT(*) as total')
            ->groupBy('status')
            ->pluck('total', 'status');

        $pendingUploads = (int) ($counts['pending_upload'] ?? 0);
        $queued = (int) ($counts['queued'] ?? 0);
        $processing = (int) ($counts['processing'] ?? 0);
        $completed = (int) ($counts['completed'] ?? 0);
        $processingFailed = (int) ($counts['failed'] ?? 0);
        $uploadFailed = (int) ($counts['upload_failed'] ?? 0);
        $failed = $processingFailed + $uploadFailed;
        $total = (int) $batch->items()->reorder()->count();
        $uploaded = (int) $batch->items()->reorder()->whereNotNull('source_path')->count();
        $bytesUploaded = (int) $batch->items()->reorder()->whereNotNull('source_path')->sum('original_size');

        $status = match (true) {
            $pendingUploads > 0 => 'uploading',
            ($queued + $processing) > 0 => 'processing',
            $total > 0 && $completed === $total => 'completed',
            $completed > 0 && $failed > 0 => 'partial',
            $failed > 0 => 'failed',
            default => 'uploading',
        };

        $terminal = in_array($status, ['completed', 'partial', 'failed'], true);
        $wasTerminal = $batch->isTerminal();

        $batch->forceFill([
            'status' => $status,
            'total_files' => $total,
            'uploaded_files' => $uploaded,
            'processed_files' => $completed + $failed,
            'completed_files' => $completed,
            'failed_files' => $failed,
            'bytes_uploaded' => $bytesUploaded,
            'started_at' => $batch->started_at ?: now(),
            'completed_at' => $terminal ? ($batch->completed_at ?: now()) : null,
            'expires_at' => $terminal && ! $wasTerminal
                ? now()->addHours((int) config('image_optimizer.retention_hours', 24))
                : $batch->expires_at,
        ])->save();

        if ($terminal && $completed > 0) {
            $this->registerExportOnce($batch);
        }

        return $batch->fresh('items');
    }

    private function registerExportOnce(ImageOptimizerBatch $batch): void
    {
        if ($batch->export_registered_at || ! Schema::hasTable('historial_exportaciones')) {
            return;
        }

        try {
            $settings = $batch->settings ?? [];

            HistorialExportacion::create([
                'user_id' => $batch->user_id,
                'tipo' => 'optimizador_imagenes',
                'formato' => (string) ($settings['format'] ?? 'webp'),
                'cantidad' => $batch->completed_files,
                'configuracion' => array_merge($settings, [
                    'batch_uuid' => $batch->uuid,
                    'failed' => $batch->failed_files,
                    'progressive_upload' => true,
                ]),
            ]);

            $batch->forceFill(['export_registered_at' => now()])->save();
        } catch (\Throwable) {
            // El historial no debe impedir que el lote finalice.
        }
    }
}
