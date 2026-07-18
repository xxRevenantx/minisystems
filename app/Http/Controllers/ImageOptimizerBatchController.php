<?php

namespace App\Http\Controllers;

use App\Jobs\ProcessImageOptimization;
use App\Models\ImageOptimizerBatch;
use App\Models\ImageOptimizerItem;
use App\Services\ImageOptimizerBatchService;
use App\Services\ZipPartPlanner;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\File;
use Symfony\Component\HttpFoundation\Response;

class ImageOptimizerBatchController extends Controller
{
    public function active(Request $request, ImageOptimizerBatchService $batchService): JsonResponse
    {
        $this->deleteExpiredForUser((int) $request->user()->id);

        $batch = ImageOptimizerBatch::query()
            ->where('user_id', $request->user()->id)
            ->where('expires_at', '>', now())
            ->latest('id')
            ->first();

        if (! $batch) {
            return response()->json(['batch' => null]);
        }

        $this->markStaleItems($batch);
        $batch = $batchService->recalculate($batch);

        return response()->json(['batch' => $this->payload($batch)]);
    }

    public function store(Request $request): JsonResponse
    {
        $maxFiles = max(0, (int) config('image_optimizer.max_files', 0));
        $maxFileKb = (int) config('image_optimizer.max_file_kb', 20 * 1024);
        $filesRule = ['required', 'array', 'min:1'];

        if ($maxFiles > 0) {
            $filesRule[] = 'max:'.$maxFiles;
        }

        $data = $request->validate([
            'settings' => ['required', 'array'],
            'settings.profile' => ['required', Rule::in(['balanced', 'web', 'social', 'documents', 'max', 'transparent', 'custom'])],
            'settings.format' => ['required', Rule::in(['original', 'jpg', 'png', 'webp', 'avif'])],
            'settings.quality' => ['required', 'integer', 'between:35,100'],
            'settings.max_width' => ['required', 'integer', 'between:320,6000'],
            'settings.max_height' => ['required', 'integer', 'between:320,6000'],
            'settings.target_kb' => ['nullable', 'integer', 'between:50,20000'],
            'settings.allow_upscale' => ['required', 'boolean'],
            'settings.preserve_transparency' => ['required', 'boolean'],
            'settings.rename_pattern' => ['required', 'string', 'max:100'],
            'files' => $filesRule,
            'files.*.fingerprint' => ['required', 'string', 'max:1000'],
            'files.*.name' => ['required', 'string', 'max:255'],
            'files.*.relative_path' => ['nullable', 'string', 'max:1000'],
            'files.*.size' => ['required', 'integer', 'min:1', 'max:'.($maxFileKb * 1024)],
            'files.*.mime' => ['nullable', 'string', 'max:100'],
            'files.*.last_modified' => ['nullable', 'integer', 'min:0'],
        ], [
            'files.max' => "El lote admite como máximo {$maxFiles} imágenes.",
            'files.*.size.max' => 'Cada imagen puede pesar hasta '.round($maxFileKb / 1024).' MB.',
        ]);

        $this->deleteExpiredForUser((int) $request->user()->id);

        $existing = ImageOptimizerBatch::query()
            ->where('user_id', $request->user()->id)
            ->where('expires_at', '>', now())
            ->latest('id')
            ->first();

        if ($existing) {
            return response()->json([
                'message' => 'Ya existe un lote activo. Continúalo o pulsa “Nuevo lote”.',
                'batch' => $this->payload($existing->load('items')),
            ], Response::HTTP_CONFLICT);
        }

        $batch = DB::transaction(function () use ($request, $data): ImageOptimizerBatch {
            $retentionHours = (int) config('image_optimizer.retention_hours', 24);
            $files = array_values($data['files']);

            $batch = ImageOptimizerBatch::create([
                'uuid' => (string) Str::uuid(),
                'user_id' => $request->user()->id,
                'status' => 'uploading',
                'settings' => $data['settings'],
                'total_files' => count($files),
                'bytes_total' => (int) collect($files)->sum('size'),
                'started_at' => now(),
                'expires_at' => now()->addHours($retentionHours),
            ]);

            foreach ($files as $position => $file) {
                $batch->items()->create([
                    'uuid' => (string) Str::uuid(),
                    'position' => $position + 1,
                    'client_fingerprint' => $file['fingerprint'],
                    'relative_path' => filled($file['relative_path'] ?? null)
                        ? $file['relative_path']
                        : null,
                    'original_name' => basename(str_replace('\\', '/', $file['name'])),
                    'mime' => filled($file['mime'] ?? null) ? $file['mime'] : null,
                    'original_size' => (int) $file['size'],
                    'status' => 'pending_upload',
                ]);
            }

            return $batch->load('items');
        });

        return response()->json([
            'message' => 'Lote preparado. Las imágenes se subirán una por una.',
            'batch' => $this->payload($batch),
        ], Response::HTTP_CREATED);
    }

    public function show(
        Request $request,
        string $batch,
        ImageOptimizerBatchService $batchService,
    ): JsonResponse {
        $model = $this->ownedBatch($request, $batch);
        $this->markStaleItems($model);
        $model = $batchService->recalculate($model);

        return response()->json(['batch' => $this->payload($model)]);
    }

    public function upload(
        Request $request,
        string $batch,
        string $item,
        ImageOptimizerBatchService $batchService,
    ): JsonResponse {
        $maxFileKb = (int) config('image_optimizer.max_file_kb', 20 * 1024);
        $batchModel = $this->ownedBatch($request, $batch);
        $itemModel = $this->ownedItem($batchModel, $item);

        abort_unless(
            in_array($itemModel->status, ['pending_upload', 'upload_failed', 'failed'], true),
            Response::HTTP_CONFLICT,
            'Esta imagen ya se está procesando o fue completada.'
        );

        $data = $request->validate([
            'file' => [
                'required',
                File::image()
                    ->types(['jpg', 'jpeg', 'png', 'webp'])
                    ->max($maxFileKb),
            ],
        ], [
            'file.required' => 'No se recibió la imagen.',
            'file.image' => 'El archivo no es una imagen compatible.',
            'file.max' => 'La imagen supera el límite de '.round($maxFileKb / 1024).' MB.',
        ]);

        $file = $data['file'];
        $actualSize = (int) $file->getSize();

        if ($itemModel->original_size > 0 && $actualSize !== (int) $itemModel->original_size) {
            return response()->json([
                'message' => 'El archivo seleccionado no coincide con el elemento pendiente del lote.',
                'errors' => ['file' => ['Verifica el nombre y el tamaño de la fotografía.']],
            ], Response::HTTP_UNPROCESSABLE_ENTITY);
        }

        $mime = strtolower((string) ($file->getMimeType() ?: ''));
        $extension = $this->extensionFromMime($mime);
        $safeName = $this->safeBaseName($itemModel->original_name);
        $storedName = str_pad((string) $itemModel->position, 3, '0', STR_PAD_LEFT)
            .'-'.$safeName.'.'.$extension;
        $basePath = $batchModel->basePath();
        $disk = Storage::disk('local');

        foreach ([$itemModel->source_path, $itemModel->output_path] as $oldPath) {
            if ($oldPath && $disk->exists($oldPath)) {
                $disk->delete($oldPath);
            }
        }

        $storedPath = $file->storeAs($basePath.'/originals', $storedName, 'local');

        if (! is_string($storedPath)) {
            return response()->json([
                'message' => 'No fue posible guardar temporalmente la fotografía.',
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }

        $itemModel->forceFill([
            'stored_name' => $storedName,
            'source_path' => $storedPath,
            'output_name' => null,
            'output_path' => null,
            'mime' => $mime,
            'extension' => $extension,
            'original_size' => $actualSize,
            'optimized_size' => null,
            'saved_bytes' => 0,
            'status' => 'queued',
            'warnings' => null,
            'error' => null,
            'uploaded_at' => now(),
            'processed_at' => null,
        ])->save();

        ProcessImageOptimization::dispatch($itemModel->id)
            ->onQueue((string) config('image_optimizer.queue', 'image-optimizer'));

        $batchModel = $batchService->recalculate($batchModel);

        return response()->json([
            'message' => 'Imagen subida. La optimización comenzó automáticamente.',
            'batch' => $this->payload($batchModel),
        ]);
    }

    public function markUploadFailed(
        Request $request,
        string $batch,
        string $item,
        ImageOptimizerBatchService $batchService,
    ): JsonResponse {
        $data = $request->validate([
            'error' => ['nullable', 'string', 'max:1000'],
        ]);

        $batchModel = $this->ownedBatch($request, $batch);
        $itemModel = $this->ownedItem($batchModel, $item);

        if (! in_array($itemModel->status, ['pending_upload', 'upload_failed'], true)) {
            return response()->json(['batch' => $this->payload($batchModel->load('items'))]);
        }

        $itemModel->forceFill([
            'status' => 'upload_failed',
            'error' => $data['error'] ?? 'La carga de esta imagen no pudo completarse.',
        ])->save();

        $batchModel = $batchService->recalculate($batchModel);

        return response()->json(['batch' => $this->payload($batchModel)]);
    }

    public function retry(
        Request $request,
        string $batch,
        string $item,
        ImageOptimizerBatchService $batchService,
    ): JsonResponse {
        $batchModel = $this->ownedBatch($request, $batch);
        $itemModel = $this->ownedItem($batchModel, $item);

        abort_unless(
            $itemModel->status === 'failed' && $itemModel->source_path,
            Response::HTTP_CONFLICT,
            'Esta imagen no está disponible para reintentar el procesamiento.'
        );

        $itemModel->forceFill([
            'status' => 'queued',
            'error' => null,
            'processed_at' => null,
        ])->save();

        ProcessImageOptimization::dispatch($itemModel->id)
            ->onQueue((string) config('image_optimizer.queue', 'image-optimizer'));

        $batchModel = $batchService->recalculate($batchModel);

        return response()->json([
            'message' => 'La imagen volvió a la cola de procesamiento.',
            'batch' => $this->payload($batchModel),
        ]);
    }

    public function destroy(Request $request, string $batch): JsonResponse
    {
        $model = $this->ownedBatch($request, $batch);
        Storage::disk('local')->deleteDirectory($model->basePath());
        $model->delete();

        return response()->json([
            'message' => 'El lote y sus archivos temporales fueron eliminados.',
        ]);
    }

    private function payload(ImageOptimizerBatch $batch): array
    {
        $batch->loadMissing('items');

        $items = $batch->items->map(function (ImageOptimizerItem $item) use ($batch): array {
            $completed = $item->status === 'completed' && $item->output_name;

            return [
                'uuid' => $item->uuid,
                'position' => $item->position,
                'fingerprint' => $item->client_fingerprint,
                'relative_path' => $item->relative_path,
                'original_name' => $item->original_name,
                'original_size' => (int) $item->original_size,
                'optimized_size' => $item->optimized_size !== null ? (int) $item->optimized_size : null,
                'saved_bytes' => (int) $item->saved_bytes,
                'status' => $item->status,
                'error' => $item->error,
                'warnings' => $item->warnings ?? [],
                'attempts' => (int) $item->attempts,
                'original_width' => $item->original_width,
                'original_height' => $item->original_height,
                'width' => $item->width,
                'height' => $item->height,
                'format' => $item->format,
                'quality' => $item->quality,
                'reduction' => $item->reduction !== null ? (float) $item->reduction : null,
                'preview_original_url' => $item->stored_name
                    ? route('images.optimizer.preview', [$batch->uuid, 'originals', $item->stored_name])
                    : null,
                'preview_output_url' => $completed
                    ? route('images.optimizer.preview', [$batch->uuid, 'outputs', $item->output_name])
                    : null,
                'download_url' => $completed
                    ? route('images.optimizer.download', [$batch->uuid, $item->output_name])
                    : null,
            ];
        })->values()->all();

        $originalBytes = (int) $batch->items->where('status', 'completed')->sum('original_size');
        $optimizedBytes = (int) $batch->items->where('status', 'completed')->sum('optimized_size');
        $difference = $originalBytes - $optimizedBytes;
        $reduction = $originalBytes > 0
            ? round((1 - ($optimizedBytes / $originalBytes)) * 100, 2)
            : 0.0;
        $downloadParts = $this->downloadParts($batch);

        return [
            'uuid' => $batch->uuid,
            'status' => $batch->status,
            'settings' => $batch->settings,
            'total_files' => (int) $batch->total_files,
            'uploaded_files' => (int) $batch->uploaded_files,
            'processed_files' => (int) $batch->processed_files,
            'completed_files' => (int) $batch->completed_files,
            'failed_files' => (int) $batch->failed_files,
            'pending_upload_files' => collect($items)->where('status', 'pending_upload')->count(),
            'queued_files' => collect($items)->where('status', 'queued')->count(),
            'processing_files' => collect($items)->where('status', 'processing')->count(),
            'bytes_total' => (int) $batch->bytes_total,
            'bytes_uploaded' => (int) $batch->bytes_uploaded,
            'total_original_bytes' => $originalBytes,
            'total_optimized_bytes' => $optimizedBytes,
            'total_difference_bytes' => $difference,
            'total_reduction' => $reduction,
            'created_at' => $batch->created_at?->toIso8601String(),
            'completed_at' => $batch->completed_at?->toIso8601String(),
            'expires_at' => $batch->expires_at?->toIso8601String(),
            'download_batch_url' => $downloadParts !== [] ? $downloadParts[0]['url'] : null,
            'download_part_count' => count($downloadParts),
            'download_parts' => $downloadParts,
            'items' => $items,
        ];
    }

    /** @return array<int,array{number:int,label:string,file_count:int,bytes:int,url:string}> */
    private function downloadParts(ImageOptimizerBatch $batch): array
    {
        $parts = app(ZipPartPlanner::class)->plan(
            $this->downloadEntries($batch),
            (int) config('image_optimizer.zip_part_max_files', 100),
            (int) config('image_optimizer.zip_part_max_mb', 500) * 1024 * 1024,
        );

        return collect($parts)->map(function (array $part) use ($batch): array {
            return [
                'number' => $part['number'],
                'label' => 'Parte '.str_pad((string) $part['number'], 2, '0', STR_PAD_LEFT),
                'file_count' => $part['file_count'],
                'bytes' => $part['bytes'],
                'url' => route('images.optimizer.download-batch', [
                    'batch' => $batch->uuid,
                    'part' => $part['number'],
                ]),
            ];
        })->values()->all();
    }

    /** @return array<int,array{name:string,path:string,size:int}> */
    private function downloadEntries(ImageOptimizerBatch $batch): array
    {
        return $batch->items
            ->where('status', 'completed')
            ->filter(fn (ImageOptimizerItem $item): bool => filled($item->output_path))
            ->map(fn (ImageOptimizerItem $item): array => [
                'name' => $item->output_name ?: basename((string) $item->output_path),
                'path' => (string) $item->output_path,
                'size' => (int) ($item->optimized_size ?? 0),
            ])
            ->values()
            ->all();
    }

    private function ownedBatch(Request $request, string $uuid): ImageOptimizerBatch
    {
        abort_unless(Str::isUuid($uuid), 404);

        $batch = ImageOptimizerBatch::query()
            ->where('uuid', $uuid)
            ->where('user_id', $request->user()->id)
            ->firstOrFail();

        if ($batch->expires_at?->isPast()) {
            Storage::disk('local')->deleteDirectory($batch->basePath());
            $batch->delete();
            abort(410, 'El lote temporal venció. Crea uno nuevo.');
        }

        return $batch;
    }

    private function ownedItem(ImageOptimizerBatch $batch, string $uuid): ImageOptimizerItem
    {
        abort_unless(Str::isUuid($uuid), 404);

        return $batch->items()->where('uuid', $uuid)->firstOrFail();
    }

    private function markStaleItems(ImageOptimizerBatch $batch): void
    {
        $minutes = (int) config('image_optimizer.stale_processing_minutes', 30);

        $batch->items()
            ->whereIn('status', ['queued', 'processing'])
            ->where('updated_at', '<', now()->subMinutes($minutes))
            ->update([
                'status' => 'failed',
                'error' => 'El proceso en cola dejó de responder. Verifica el worker y pulsa Reintentar.',
                'processed_at' => now(),
                'updated_at' => now(),
            ]);
    }

    private function deleteExpiredForUser(int $userId): void
    {
        ImageOptimizerBatch::query()
            ->where('user_id', $userId)
            ->where('expires_at', '<=', now())
            ->get()
            ->each(function (ImageOptimizerBatch $batch): void {
                Storage::disk('local')->deleteDirectory($batch->basePath());
                $batch->delete();
            });
    }

    private function safeBaseName(string $name): string
    {
        $slug = Str::slug(pathinfo($name, PATHINFO_FILENAME), '-');

        return mb_substr($slug !== '' ? $slug : 'imagen', 0, 80);
    }

    private function extensionFromMime(string $mime): string
    {
        return match ($mime) {
            'image/jpeg' => 'jpg',
            'image/png' => 'png',
            'image/webp' => 'webp',
            default => throw \Illuminate\Validation\ValidationException::withMessages([
                'file' => ['El formato real de la imagen no es compatible.'],
            ]),
        };
    }
}
