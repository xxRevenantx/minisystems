<?php

namespace App\Http\Controllers;

use App\Models\PdfBatch;
use App\Models\PdfItem;
use App\Services\Pdf\PdfToolchain;
use App\Services\Pdf\PdfZipService;
use App\Services\ZipPartPlanner;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Symfony\Component\HttpFoundation\ResponseHeaderBag;

class SystemPdfController extends Controller
{
    public function index(Request $request, PdfToolchain $tools)
    {
        abort_unless($request->user()?->puedePdf('ver'), 403);

        return view('system-pdf.index', [
            'toolStatus' => $tools->status(),
            'maxFiles' => (int) config('system_pdf.max_files', 100),
            'maxFileMb' => round((int) config('system_pdf.max_file_kb', 256000) / 1024),
            'uploadConcurrency' => (int) config('system_pdf.upload_concurrency', 2),
            'zipPartMaxFiles' => (int) config('system_pdf.zip_part_max_files', 1000),
            'zipPartMaxMb' => (int) config('system_pdf.zip_part_max_mb', 1000),
        ]);
    }

    public function thumbnail(Request $request, string $batch, string $item, int $page): BinaryFileResponse
    {
        abort_unless($request->user()?->puedePdf('ver'), 403);
        $itemModel = $this->ownedItem($request, $batch, $item);
        $thumbs = $itemModel->thumbnails ?? [];
        abort_unless(isset($thumbs[$page - 1]), 404);
        return response()->file(Storage::disk('local')->path($thumbs[$page - 1]), [
            'Content-Type' => 'image/jpeg',
            'Cache-Control' => 'private, max-age=3600',
        ]);
    }

    public function sourcePreview(Request $request, string $batch, string $item): BinaryFileResponse
    {
        abort_unless($request->user()?->puedePdf('ver'), 403);
        $itemModel = $this->ownedItem($request, $batch, $item);
        abort_unless($itemModel->source_path && Storage::disk('local')->exists($itemModel->source_path), 404);
        $fallback = $this->safeBase($itemModel->original_name).'.'.($itemModel->extension ?: 'pdf');

        return response()->file(Storage::disk('local')->path($itemModel->source_path), [
            'Content-Type' => $itemModel->mime ?: 'application/pdf',
            'Content-Disposition' => ResponseHeaderBag::makeDisposition(
                ResponseHeaderBag::DISPOSITION_INLINE,
                $itemModel->original_name,
                $fallback,
            ),
            'Cache-Control' => 'private, max-age=900',
        ]);
    }

    public function downloadItem(Request $request, string $batch, string $item): BinaryFileResponse
    {
        abort_unless($request->user()?->puedePdf('descargar'), 403);
        $itemModel = $this->ownedItem($request, $batch, $item);
        abort_unless($itemModel->output_path && Storage::disk('local')->exists($itemModel->output_path), 404);
        return response()->download(
            Storage::disk('local')->path($itemModel->output_path),
            $itemModel->output_name ?: basename($itemModel->output_path)
        );
    }

    public function downloadResult(Request $request, string $batch, string $item, int $result): BinaryFileResponse
    {
        abort_unless($request->user()?->puedePdf('descargar'), 403);
        $itemModel = $this->ownedItem($request, $batch, $item);
        $files = $itemModel->result_files ?? [];
        abort_unless(isset($files[$result]) && Storage::disk('local')->exists($files[$result]['path']), 404);
        return response()->download(Storage::disk('local')->path($files[$result]['path']), $files[$result]['name']);
    }

    public function downloadBatchOutput(Request $request, string $batch): BinaryFileResponse
    {
        abort_unless($request->user()?->puedePdf('descargar'), 403);
        $batchModel = $this->ownedBatch($request, $batch);
        abort_unless($batchModel->output_path && Storage::disk('local')->exists($batchModel->output_path), 404);
        return response()->download(
            Storage::disk('local')->path($batchModel->output_path),
            $batchModel->output_name ?: basename($batchModel->output_path)
        );
    }

    public function downloadZip(
        Request $request,
        string $batch,
        PdfZipService $zipService,
        ZipPartPlanner $planner,
    ): BinaryFileResponse {
        abort_unless($request->user()?->puedePdf('descargar'), 403);
        $batchModel = $this->ownedBatch($request, $batch)->load('items');
        $selected = array_filter(explode(',', (string) $request->query('items', '')));
        $resultKeys = array_filter(explode(',', (string) $request->query('results', '')));
        $entries = $this->entries($batchModel, $selected, $resultKeys);
        abort_if($entries === [], 404, 'No hay resultados para descargar.');

        $parts = $planner->plan(
            $entries,
            (int) config('system_pdf.zip_part_max_files', 1000),
            (int) config('system_pdf.zip_part_max_mb', 1000) * 1024 * 1024,
        );
        $partNumber = max(1, (int) $request->query('part', 1));
        abort_unless(isset($parts[$partNumber - 1]), 404);
        $part = $parts[$partNumber - 1];
        $suffix = count($parts) > 1 ? '-parte-'.$partNumber : '';
        $zipName = 'system-pdf-'.$batchModel->uuid.$suffix.'.zip';
        $relative = 'system-pdf-zips/'.Str::uuid().'-'.$zipName;
        $absolute = Storage::disk('local')->path($relative);
        $zipService->create($absolute, Storage::disk('local'), $part['entries']);

        return response()->download($absolute, $zipName)->deleteFileAfterSend(true);
    }

    private function ownedBatch(Request $request, string $uuid): PdfBatch
    {
        $query = PdfBatch::query()->where('uuid', $uuid);
        if (! $request->user()?->puedePdf('administrar')) {
            $query->where('user_id', $request->user()->id);
        }
        return $query->firstOrFail();
    }

    private function ownedItem(Request $request, string $batch, string $item): PdfItem
    {
        $batchModel = $this->ownedBatch($request, $batch);
        return PdfItem::query()
            ->where('pdf_batch_id', $batchModel->id)
            ->where('uuid', $item)
            ->firstOrFail();
    }

    /** @return array<int,array{name:string,path:string,size:int}> */
    private function entries(PdfBatch $batch, array $selected, array $resultKeys = []): array
    {
        $entries = [];
        $filterResults = $resultKeys !== [];
        if ($batch->output_path && Storage::disk('local')->exists($batch->output_path)
            && (! $filterResults || in_array('batch', $resultKeys, true))) {
            $entries[] = [
                'name' => $batch->output_name ?: basename($batch->output_path),
                'path' => $batch->output_path,
                'size' => (int) $batch->output_bytes,
            ];
        }

        foreach ($batch->items as $item) {
            if ($selected !== [] && ! in_array($item->uuid, $selected, true)) {
                continue;
            }
            $prefix = (int) $batch->total_files > 1
                ? str_pad((string) $item->position, 3, '0', STR_PAD_LEFT).'-'
                : '';
            if ($item->output_path && Storage::disk('local')->exists($item->output_path)
                && (! $filterResults || in_array('item:'.$item->uuid, $resultKeys, true))) {
                $entries[] = [
                    'name' => $prefix.($item->output_name ?: basename($item->output_path)),
                    'path' => $item->output_path,
                    'size' => (int) $item->output_size,
                ];
            }
            foreach ($item->result_files ?? [] as $index => $file) {
                $key = 'result:'.$item->uuid.':'.$index;
                if (isset($file['path'], $file['name']) && Storage::disk('local')->exists($file['path'])
                    && (! $filterResults || in_array($key, $resultKeys, true))) {
                    $entries[] = [
                        'name' => $prefix.$this->safeBase($item->original_name).'/'.$file['name'],
                        'path' => $file['path'],
                        'size' => (int) ($file['size'] ?? 0),
                    ];
                }
            }
        }

        return $entries;
    }

    private function safeBase(string $name): string
    {
        $base = Str::ascii(pathinfo($name, PATHINFO_FILENAME));
        $base = preg_replace('/[^A-Za-z0-9_-]+/', '_', $base) ?: 'documento';
        return trim(substr($base, 0, 100), '_-') ?: 'documento';
    }
}
