<?php

namespace App\Http\Controllers;

use App\Models\SystemImageBatch;
use App\Models\SystemImageItem;
use App\Services\SimpleZipService;
use App\Services\ZipPartPlanner;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Symfony\Component\HttpFoundation\StreamedResponse;

class SystemImageController extends Controller
{
    public function preview(Request $request, string $batch, string $item, string $type): StreamedResponse
    {
        abort_unless(in_array($type, ['original', 'output'], true), 404);

        $itemModel = $this->ownedItem($request, $batch, $item);
        $path = $type === 'original' ? $itemModel->source_path : $itemModel->output_path;
        abort_unless($path, 404, 'La imagen temporal ya no está disponible.');

        $disk = Storage::disk('local');
        abort_unless($disk->exists($path), 404, 'La imagen temporal ya no está disponible.');

        return $disk->response($path, basename($path), [
            'Content-Type' => $disk->mimeType($path) ?: 'application/octet-stream',
            'Cache-Control' => 'private, max-age=300',
            'X-Content-Type-Options' => 'nosniff',
        ], 'inline');
    }

    public function download(Request $request, string $batch, string $item): StreamedResponse
    {
        $itemModel = $this->ownedItem($request, $batch, $item);
        abort_unless($itemModel->status === 'completed' && $itemModel->output_path, 404);

        $disk = Storage::disk('local');
        abort_unless($disk->exists($itemModel->output_path), 404, 'La imagen procesada ya no está disponible.');

        return $disk->download($itemModel->output_path, basename($itemModel->output_name ?: $itemModel->output_path), [
            'Content-Type' => $disk->mimeType($itemModel->output_path) ?: 'application/octet-stream',
            'X-Content-Type-Options' => 'nosniff',
        ]);
    }

    public function downloadBatch(Request $request, string $batch, SimpleZipService $zipService): BinaryFileResponse
    {
        $model = $this->ownedBatch($request, $batch);
        $disk = Storage::disk('local');
        $entries = $this->downloadEntries($model, $disk);

        abort_if($entries === [], 404, 'No hay imágenes procesadas disponibles en este lote.');

        $parts = app(ZipPartPlanner::class)->plan(
            $entries,
            (int) config('system_images.zip_part_max_files', 100),
            (int) config('system_images.zip_part_max_mb', 500) * 1024 * 1024,
        );

        $partNumber = max(1, (int) $request->query('part', 1));
        $part = collect($parts)->firstWhere('number', $partNumber);

        abort_unless($part, 404, 'La parte solicitada no existe para este lote.');

        $temporaryPath = storage_path('app/private/system-images-zips/'.Str::uuid().'.zip');
        $zipService->createFromStorage($temporaryPath, $disk, $part['entries']);

        $suffix = count($parts) > 1
            ? '-parte-'.str_pad((string) $part['number'], 3, '0', STR_PAD_LEFT)
            : '';

        return response()
            ->download($temporaryPath, 'system-images'.$suffix.'-'.now()->format('Ymd-His').'.zip', [
                'Content-Type' => 'application/zip',
                'X-Content-Type-Options' => 'nosniff',
            ])
            ->deleteFileAfterSend(true);
    }

    /** @return array<int,array{name:string,path:string,size:int}> */
    private function downloadEntries(SystemImageBatch $model, $disk): array
    {
        return $model->items()
            ->where('status', 'completed')
            ->whereNotNull('output_path')
            ->orderBy('position')
            ->get()
            ->filter(fn (SystemImageItem $item): bool => $item->output_path && $disk->exists($item->output_path))
            ->map(fn (SystemImageItem $item): array => [
                'name' => $item->output_name ?: basename($item->output_path),
                'path' => $item->output_path,
                'size' => (int) ($item->processed_size ?? $disk->size($item->output_path)),
            ])
            ->values()
            ->all();
    }

    private function ownedItem(Request $request, string $batch, string $item): SystemImageItem
    {
        $model = $this->ownedBatch($request, $batch);
        abort_unless(Str::isUuid($item), 404);

        return $model->items()->where('uuid', $item)->firstOrFail();
    }

    private function ownedBatch(Request $request, string $batch): SystemImageBatch
    {
        abort_unless(Str::isUuid($batch), 404);

        $model = SystemImageBatch::query()
            ->where('uuid', $batch)
            ->where('user_id', $request->user()->id)
            ->firstOrFail();

        abort_if($model->expires_at?->isPast(), 410, 'El lote temporal ya venció.');

        return $model;
    }
}
