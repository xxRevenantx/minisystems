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

class InspectPdfItem implements ShouldQueue
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

        $item->forceFill([
            'status' => 'inspecting',
            'error' => null,
            'attempts' => (int) $item->attempts + 1,
        ])->save();

        try {
            $result = $processor->inspect($item);
            $item->forceFill([
                'page_count' => $result['page_count'],
                'encrypted' => $result['encrypted'],
                'thumbnails' => $result['thumbnails'],
                'status' => 'ready',
                'error' => null,
            ])->save();
        } catch (Throwable $exception) {
            $item->forceFill([
                'status' => 'inspection_failed',
                'error' => $this->friendlyMessage($exception),
            ])->save();
        }

        $batches->recalculate($item->batch);
    }

    private function friendlyMessage(Throwable $exception): string
    {
        return mb_substr(trim($exception->getMessage()) ?: 'No fue posible analizar el PDF.', 0, 3000);
    }
}
