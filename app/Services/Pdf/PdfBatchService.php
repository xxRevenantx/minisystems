<?php

namespace App\Services\Pdf;

use App\Models\PdfBatch;

class PdfBatchService
{
    public function recalculate(PdfBatch $batch): PdfBatch
    {
        $batch->loadMissing('items');
        $items = $batch->items;

        $uploaded = $items->whereNotIn('status', ['pending_upload', 'upload_failed'])->count();
        $completed = $items->where('status', 'completed')->count();
        $failed = $items->whereIn('status', ['failed', 'inspection_failed', 'upload_failed'])->count();
        $processed = $completed + $items->where('status', 'failed')->count();
        $originalBytes = (int) $items->sum('original_size');
        $outputBytes = (int) $items->sum(fn ($item): int => (int) ($item->output_size ?? 0)
            + collect($item->result_files ?? [])->sum(fn (array $file): int => (int) ($file['size'] ?? 0)));

        $status = $batch->status;

        if (! $batch->started_at) {
            if ($uploaded < (int) $batch->total_files) {
                $status = 'uploading';
            } elseif ($items->contains(fn ($item): bool => $item->status === 'inspecting')) {
                $status = 'preparing';
            } elseif ($items->contains(fn ($item): bool => in_array($item->status, ['inspection_failed', 'upload_failed'], true))) {
                $status = 'needs_attention';
            } else {
                $status = 'ready';
            }
        } elseif (! $batch->isTerminal()) {
            if ($batch->operation === 'combine') {
                $status = in_array($batch->status, ['queued', 'processing'], true)
                    ? $batch->status
                    : 'processing';
            } else {
                $pending = $items->whereIn('status', ['queued', 'processing', 'ready'])->count();
                if ($pending > 0) {
                    $status = 'processing';
                } elseif ($completed === 0 && $failed > 0) {
                    $status = 'failed';
                } elseif ($failed > 0) {
                    $status = 'partial';
                } else {
                    $status = 'completed';
                }
            }
        }

        $completedAt = in_array($status, ['completed', 'partial', 'failed'], true)
            ? ($batch->completed_at ?? now())
            : null;

        $batch->forceFill([
            'status' => $status,
            'uploaded_files' => $uploaded,
            'processed_files' => $processed,
            'completed_files' => $completed,
            'failed_files' => $failed,
            'original_bytes' => $originalBytes,
            'output_bytes' => max($outputBytes, (int) ($batch->output_bytes ?? 0)),
            'completed_at' => $completedAt,
        ])->save();

        return $batch->fresh('items');
    }
}
