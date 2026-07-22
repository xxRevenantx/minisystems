<?php

namespace App\Http\Controllers;

use App\Models\ImageOptimizerBatch;
use App\Services\SimpleZipService;
use App\Services\ZipPartPlanner;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Symfony\Component\HttpFoundation\StreamedResponse;

class ImageOptimizerController extends Controller
{
    public function preview(Request $request, string $batch, string $type, string $filename): StreamedResponse
    {
        abort_unless(in_array($type, ['originals', 'outputs'], true), 404);
        $path = $this->authorizedPath($request, $batch, $type, $filename);
        $disk = Storage::disk('local');
        abort_unless($disk->exists($path), 404, 'La imagen temporal ya no está disponible.');

        $mime = $disk->mimeType($path) ?: 'application/octet-stream';

        return $disk->response($path, basename($filename), [
            'Content-Type' => $mime,
            'Cache-Control' => 'private, max-age=300',
            'X-Content-Type-Options' => 'nosniff',
        ], 'inline');
    }

    public function download(Request $request, string $batch, string $filename): StreamedResponse
    {
        $path = $this->authorizedPath($request, $batch, 'outputs', $filename);
        $disk = Storage::disk('local');
        abort_unless($disk->exists($path), 404, 'La imagen optimizada ya no está disponible.');

        return $disk->download($path, basename($filename), [
            'Content-Type' => $disk->mimeType($path) ?: 'application/octet-stream',
            'X-Content-Type-Options' => 'nosniff',
        ]);
    }

    public function downloadBatch(Request $request, string $batch, SimpleZipService $zipService): BinaryFileResponse
    {
        $model = $this->ownedBatch($request, $batch);
        $disk = Storage::disk('local');
        $entries = $this->downloadEntries($model, $disk);

        abort_if($entries === [], 404, 'No hay imágenes optimizadas disponibles en este lote.');

        $parts = app(ZipPartPlanner::class)->plan(
            $entries,
            (int) config('image_optimizer.zip_part_max_files', 100),
            (int) config('image_optimizer.zip_part_max_mb', 500) * 1024 * 1024,
        );

        $partNumber = max(1, (int) $request->query('part', 1));
        $part = collect($parts)->firstWhere('number', $partNumber);

        abort_unless($part, 404, 'La parte solicitada no existe para este lote.');

        $temporaryPath = storage_path('app/private/image-optimizer-zips/'.Str::uuid().'.zip');
        $zipService->createFromStorage($temporaryPath, $disk, $part['entries']);

        $suffix = count($parts) > 1
            ? '-parte-'.str_pad((string) $part['number'], 3, '0', STR_PAD_LEFT)
            : '';

        return response()
            ->download($temporaryPath, 'imagenes-optimizadas'.$suffix.'-'.now()->format('Ymd-His').'.zip', [
                'Content-Type' => 'application/zip',
                'X-Content-Type-Options' => 'nosniff',
            ])
            ->deleteFileAfterSend(true);
    }

    /** @return array<int,array{name:string,path:string,size:int}> */
    private function downloadEntries(ImageOptimizerBatch $model, $disk): array
    {
        return $model->items()
            ->where('status', 'completed')
            ->whereNotNull('output_path')
            ->orderBy('position')
            ->get()
            ->filter(fn ($item): bool => $item->output_path && $disk->exists($item->output_path))
            ->map(fn ($item): array => [
                'name' => $item->output_name ?: basename($item->output_path),
                'path' => $item->output_path,
                'size' => (int) ($item->optimized_size ?? $disk->size($item->output_path)),
            ])
            ->values()
            ->all();
    }

    private function authorizedPath(Request $request, string $batch, string $type, string $filename): string
    {
        $model = $this->ownedBatch($request, $batch);
        abort_unless($filename === basename($filename), 404);
        abort_if(str_contains($filename, "\0"), 404);

        return $model->basePath().'/'.$type.'/'.$filename;
    }

    private function ownedBatch(Request $request, string $batch): ImageOptimizerBatch
    {
        abort_unless(Str::isUuid($batch), 404);

        $model = ImageOptimizerBatch::query()
            ->where('uuid', $batch)
            ->where('user_id', $request->user()->id)
            ->firstOrFail();

        abort_if($model->expires_at?->isPast(), 410, 'El lote temporal ya venció.');

        return $model;
    }
}
