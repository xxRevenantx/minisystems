<?php

namespace App\Jobs;

use App\Models\PdfItem;
use App\Services\Pdf\PdfBatchService;
use App\Services\Pdf\PdfProcessor;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Throwable;

class ProcessPdfItem implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries = 1;
    public int $timeout;
    public bool $failOnTimeout = true;

    public function __construct(public int $itemId)
    {
        $this->timeout = (int) config('system_pdf.job_timeout', 3600);
        $this->onConnection((string) config('system_pdf.connection', 'database'));
        $this->onQueue((string) config('system_pdf.queue', 'system-pdf'));
    }

    public function handle(PdfProcessor $processor, PdfBatchService $batches): void
    {
        $item = PdfItem::query()->with('batch')->find($this->itemId);
        if (! $item || ! $item->batch) {
            return;
        }

        $batch = $item->batch;
        $settings = $batch->settings ?? [];
        $item->forceFill([
            'status' => 'processing',
            'error' => null,
            'output_name' => null,
            'output_path' => null,
            'output_size' => null,
            'result_files' => null,
            'warnings' => null,
            'attempts' => (int) $item->attempts + 1,
        ])->save();

        try {
            $result = match ($batch->operation) {
                'compress' => $processor->compress($item, $settings),
                'split' => $processor->split($item, $settings),
                'reorder' => $processor->reorder($item, $settings),
                'security' => $processor->security($item, $settings, $batch->secret ?? []),
                default => throw new \RuntimeException('Operación PDF no compatible.'),
            };

            $item->forceFill([
                'status' => 'completed',
                'output_name' => $result['output_name'] ?? null,
                'output_path' => $result['output_path'] ?? null,
                'output_size' => $result['output_size'] ?? null,
                'result_files' => $result['result_files'] ?? null,
                'warnings' => $result['warnings'] ?? null,
                'secret' => null,
                'error' => null,
                'processed_at' => now(),
            ])->save();

            if ($batch->operation === 'security') {
                $batch->forceFill(['secret' => null])->save();
            }
        } catch (Throwable $exception) {
            $item->forceFill([
                'status' => 'failed',
                'error' => mb_substr(trim($exception->getMessage()) ?: 'No fue posible procesar el PDF.', 0, 5000),
                'processed_at' => now(),
            ])->save();
        }

        $batches->recalculate($batch);
    }
}
