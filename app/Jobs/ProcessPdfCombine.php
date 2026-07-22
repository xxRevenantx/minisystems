<?php

namespace App\Jobs;

use App\Models\PdfBatch;
use App\Services\Pdf\PdfBatchService;
use App\Services\Pdf\PdfProcessor;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Throwable;

class ProcessPdfCombine implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries = 1;
    public int $timeout;
    public bool $failOnTimeout = true;

    public function __construct(public int $batchId)
    {
        $this->timeout = (int) config('system_pdf.job_timeout', 3600);
        $this->onConnection((string) config('system_pdf.connection', 'database'));
        $this->onQueue((string) config('system_pdf.queue', 'system-pdf'));
    }

    public function handle(PdfProcessor $processor, PdfBatchService $batches): void
    {
        $batch = PdfBatch::query()->with('items')->find($this->batchId);
        if (! $batch) {
            return;
        }

        $batch->forceFill(['status' => 'processing', 'error' => null])->save();
        $batch->items()->update(['status' => 'processing', 'error' => null]);

        try {
            $result = $processor->combine($batch, $batch->settings ?? []);
            $batch->items()->update([
                'status' => 'completed',
                'secret' => null,
                'processed_at' => now(),
            ]);
            $batch->forceFill([
                'status' => 'completed',
                'output_name' => $result['output_name'],
                'output_path' => $result['output_path'],
                'output_bytes' => $result['output_size'],
                'secret' => null,
                'completed_at' => now(),
            ])->save();
        } catch (Throwable $exception) {
            $batch->items()->update([
                'status' => 'failed',
                'error' => mb_substr(trim($exception->getMessage()) ?: 'No fue posible combinar los PDF.', 0, 5000),
                'processed_at' => now(),
            ]);
            $batch->forceFill([
                'status' => 'failed',
                'error' => mb_substr(trim($exception->getMessage()) ?: 'No fue posible combinar los PDF.', 0, 5000),
                'completed_at' => now(),
            ])->save();
        }

        $batches->recalculate($batch);
    }
}
