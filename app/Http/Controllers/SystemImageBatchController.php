<?php

namespace App\Http\Controllers;

use App\Jobs\ProcessSystemImage;
use App\Models\SystemImageBatch;
use App\Models\SystemImageItem;
use App\Services\SystemImageBatchService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\File;
use Symfony\Component\HttpFoundation\Response;

class SystemImageBatchController extends Controller
{
    public function active(Request $request, SystemImageBatchService $batchService): JsonResponse
    {
        $this->deleteExpiredForUser((int) $request->user()->id);

        $batch = SystemImageBatch::query()
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
        $maxFiles = (int) config('system_images.max_files', 500);
        $maxFileKb = (int) config('system_images.max_file_kb', 20 * 1024);

        $data = $request->validate([
            'settings' => ['required', 'array'],
            'settings.marco_id' => [
                'nullable',
                Rule::exists('marcos', 'id')->where(fn ($query) => $query->where('activo', true)->whereNull('deleted_at')),
            ],
            'settings.orientation_mode' => ['required', Rule::in(['auto', 'desktop', 'mobile'])],
            'settings.square_mode' => ['required', Rule::in(['desktop', 'mobile'])],
            'settings.missing_frame_behavior' => ['required', Rule::in(['skip', 'alternate'])],
            'settings.fit_mode' => ['required', Rule::in(['cover', 'contain', 'blur'])],
            'settings.format' => ['required', Rule::in(['jpg', 'png', 'webp', 'original'])],
            'settings.quality' => ['required', 'integer', 'between:60,100'],
            'settings.rename_pattern' => ['required', 'string', 'max:120'],
            'settings.organize_folders' => ['required', 'boolean'],
            'settings.preset_social_id' => ['nullable', 'integer', 'exists:presets_sociales,id'],
            'settings.desktop_width' => ['required', 'integer', 'between:320,6000'],
            'settings.desktop_height' => ['required', 'integer', 'between:320,6000'],
            'settings.mobile_width' => ['required', 'integer', 'between:320,6000'],
            'settings.mobile_height' => ['required', 'integer', 'between:320,6000'],
            'files' => ['required', 'array', 'min:1', 'max:'.$maxFiles],
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

        $existing = SystemImageBatch::query()
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

        $batch = DB::transaction(function () use ($request, $data): SystemImageBatch {
            $retentionHours = (int) config('system_images.retention_hours', 24);
            $files = array_values($data['files']);

            $batch = SystemImageBatch::create([
                'uuid' => (string) Str::uuid(),
                'user_id' => $request->user()->id,
                'status' => 'uploading',
                'settings' => $this->normalizedSettings($data['settings']),
                'total_files' => count($files),
                'bytes_total' => (int) collect($files)->sum('size'),
                'zip_status' => 'pending',
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
                    'settings' => [
                        'orientation' => 'inherit',
                        'fit' => 'inherit',
                        'frame' => 'global',
                        'focus' => 'center',
                        'focus_x' => 50,
                        'focus_y' => 50,
                        'zoom' => 100,
                    ],
                    'status' => 'pending_upload',
                ]);
            }

            return $batch->load('items');
        });

        return response()->json([
            'message' => 'Lote preparado. Las imágenes se subirán una por una y entrarán a cola automáticamente.',
            'batch' => $this->payload($batch),
        ], Response::HTTP_CREATED);
    }

    public function show(Request $request, string $batch, SystemImageBatchService $batchService): JsonResponse
    {
        $model = $this->ownedBatch($request, $batch);
        $this->markStaleItems($model);
        $model = $batchService->recalculate($model);

        return response()->json(['batch' => $this->payload($model)]);
    }

    public function upload(
        Request $request,
        string $batch,
        string $item,
        SystemImageBatchService $batchService,
    ): JsonResponse {
        $maxFileKb = (int) config('system_images.max_file_kb', 20 * 1024);
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
                'errors' => ['file' => ['Verifica el nombre y el tamaño de la imagen.']],
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
                'message' => 'No fue posible guardar temporalmente la imagen.',
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }

        [$width, $height] = @getimagesize($file->getRealPath()) ?: [null, null];

        if ($width && $height && function_exists('exif_read_data') && in_array($extension, ['jpg', 'jpeg'], true)) {
            try {
                $exif = @exif_read_data($file->getRealPath());
                if (in_array((int) ($exif['Orientation'] ?? 1), [5, 6, 7, 8], true)) {
                    [$width, $height] = [$height, $width];
                }
            } catch (\Throwable) {
                // La cola autorrota nuevamente; esto solo mejora la vista previa de metadatos.
            }
        }

        $detected = $width && $height
            ? ($width === $height ? 'square' : ($width > $height ? 'desktop' : 'mobile'))
            : null;

        $itemModel->forceFill([
            'stored_name' => $storedName,
            'source_path' => $storedPath,
            'output_name' => null,
            'output_path' => null,
            'mime' => $mime,
            'extension' => $extension,
            'original_size' => $actualSize,
            'processed_size' => null,
            'original_width' => $width,
            'original_height' => $height,
            'width' => null,
            'height' => null,
            'orientation' => $detected,
            'status' => 'queued',
            'warnings' => null,
            'error' => null,
            'uploaded_at' => now(),
            'processed_at' => null,
        ])->save();

        ProcessSystemImage::dispatch($itemModel->id)
            ->onQueue((string) config('system_images.queue', 'system-images'));

        $batchModel = $batchService->recalculate($batchModel);

        return response()->json([
            'message' => 'Imagen subida. El procesamiento en cola comenzó automáticamente.',
            'batch' => $this->payload($batchModel),
        ]);
    }

    public function markUploadFailed(
        Request $request,
        string $batch,
        string $item,
        SystemImageBatchService $batchService,
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
        SystemImageBatchService $batchService,
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

        ProcessSystemImage::dispatch($itemModel->id)
            ->onQueue((string) config('system_images.queue', 'system-images'));

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

    private function payload(SystemImageBatch $batch): array
    {
        $batch->loadMissing('items');

        $items = $batch->items->map(function (SystemImageItem $item) use ($batch): array {
            $completed = $item->status === 'completed' && $item->output_path;

            return [
                'uuid' => $item->uuid,
                'position' => $item->position,
                'fingerprint' => $item->client_fingerprint,
                'relative_path' => $item->relative_path,
                'original_name' => $item->original_name,
                'original_size' => (int) $item->original_size,
                'processed_size' => $item->processed_size !== null ? (int) $item->processed_size : null,
                'status' => $item->status,
                'error' => $item->error,
                'warnings' => $item->warnings ?? [],
                'attempts' => (int) $item->attempts,
                'original_width' => $item->original_width,
                'original_height' => $item->original_height,
                'width' => $item->width,
                'height' => $item->height,
                'orientation' => $item->orientation,
                'extension' => $item->extension,
                'settings' => $item->settings,
                'preview_original_url' => $item->source_path
                    ? route('images.system.preview', [$batch->uuid, $item->uuid, 'original'])
                    : null,
                'preview_output_url' => $completed
                    ? route('images.system.preview', [$batch->uuid, $item->uuid, 'output'])
                    : null,
                'download_url' => $completed
                    ? route('images.system.download', [$batch->uuid, $item->uuid])
                    : null,
            ];
        })->values()->all();

        $originalBytes = (int) $batch->items->where('status', 'completed')->sum('original_size');
        $processedBytes = (int) $batch->items->where('status', 'completed')->sum('processed_size');
        $difference = $originalBytes - $processedBytes;
        $reduction = $originalBytes > 0
            ? round((1 - ($processedBytes / $originalBytes)) * 100, 2)
            : 0.0;

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
            'total_processed_bytes' => $processedBytes,
            'total_difference_bytes' => $difference,
            'total_reduction' => $reduction,
            'created_at' => $batch->created_at?->toIso8601String(),
            'completed_at' => $batch->completed_at?->toIso8601String(),
            'expires_at' => $batch->expires_at?->toIso8601String(),
            'download_batch_url' => $batch->completed_files > 0
                ? route('images.system.download-batch', $batch->uuid)
                : null,
            'items' => $items,
        ];
    }

    private function ownedBatch(Request $request, string $uuid): SystemImageBatch
    {
        abort_unless(Str::isUuid($uuid), 404);

        $batch = SystemImageBatch::query()
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

    private function ownedItem(SystemImageBatch $batch, string $uuid): SystemImageItem
    {
        abort_unless(Str::isUuid($uuid), 404);

        return $batch->items()->where('uuid', $uuid)->firstOrFail();
    }

    private function markStaleItems(SystemImageBatch $batch): void
    {
        $minutes = (int) config('system_images.stale_processing_minutes', 30);

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
        SystemImageBatch::query()
            ->where('user_id', $userId)
            ->where('expires_at', '<=', now())
            ->get()
            ->each(function (SystemImageBatch $batch): void {
                Storage::disk('local')->deleteDirectory($batch->basePath());
                $batch->delete();
            });
    }

    /** @param array<string,mixed> $settings */
    private function normalizedSettings(array $settings): array
    {
        return [
            'marco_id' => filled($settings['marco_id'] ?? null) ? (int) $settings['marco_id'] : null,
            'orientation_mode' => (string) $settings['orientation_mode'],
            'square_mode' => (string) $settings['square_mode'],
            'missing_frame_behavior' => (string) $settings['missing_frame_behavior'],
            'fit_mode' => (string) $settings['fit_mode'],
            'format' => (string) $settings['format'],
            'quality' => (int) $settings['quality'],
            'rename_pattern' => (string) $settings['rename_pattern'],
            'organize_folders' => (bool) $settings['organize_folders'],
            'preset_social_id' => filled($settings['preset_social_id'] ?? null) ? (int) $settings['preset_social_id'] : null,
            'desktop_width' => (int) $settings['desktop_width'],
            'desktop_height' => (int) $settings['desktop_height'],
            'mobile_width' => (int) $settings['mobile_width'],
            'mobile_height' => (int) $settings['mobile_height'],
        ];
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
