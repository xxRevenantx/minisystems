<?php

namespace App\Http\Controllers;

use App\Jobs\InspectPdfItem;
use App\Jobs\ProcessPdfCombine;
use App\Jobs\ProcessPdfItem;
use App\Models\PdfBatch;
use App\Models\PdfItem;
use App\Services\Pdf\PdfBatchService;
use App\Services\ZipPartPlanner;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\File;
use Symfony\Component\HttpFoundation\Response;

class SystemPdfBatchController extends Controller
{
    public function active(Request $request): JsonResponse
    {
        $this->authorizeAction($request, 'ver');

        $batch = PdfBatch::query()
            ->where('user_id', $request->user()->id)
            ->where('expires_at', '>', now())
            ->whereNotIn('status', ['completed', 'partial', 'failed'])
            ->latest('id')
            ->first();

        return response()->json(['batch' => $batch ? $this->payload($batch) : null]);
    }

    public function history(Request $request): JsonResponse
    {
        $this->authorizeAction($request, 'ver');

        $query = PdfBatch::query()->with('items')->latest('id');
        if (! $request->user()->puedePdf('administrar')) {
            $query->where('user_id', $request->user()->id);
        }

        $batches = $query->limit(30)->get()->map(fn (PdfBatch $batch): array => $this->payload($batch, true));

        return response()->json(['batches' => $batches]);
    }

    public function store(Request $request): JsonResponse
    {
        $this->authorizeAction($request, 'procesar');
        $maxFiles = (int) config('system_pdf.max_files', 100);
        $maxBytes = (int) config('system_pdf.max_file_kb', 256000) * 1024;

        $data = $request->validate([
            'operation' => ['required', Rule::in(['compress', 'combine', 'split', 'reorder', 'security'])],
            'files' => ['required', 'array', 'min:1', 'max:'.$maxFiles],
            'files.*.name' => ['required', 'string', 'max:255'],
            'files.*.size' => ['required', 'integer', 'min:1', 'max:'.$maxBytes],
            'files.*.type' => ['nullable', 'string', 'max:120'],
            'files.*.fingerprint' => ['nullable', 'string', 'max:1000'],
        ]);

        if (in_array($data['operation'], ['reorder', 'security'], true) && count($data['files']) !== 1) {
            return response()->json([
                'message' => 'Esta operación requiere exactamente un PDF.',
            ], Response::HTTP_UNPROCESSABLE_ENTITY);
        }

        foreach ($data['files'] as $file) {
            $extension = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
            $allowed = $data['operation'] === 'combine'
                ? ['pdf', 'jpg', 'jpeg', 'png', 'webp']
                : ['pdf'];
            if (! in_array($extension, $allowed, true)) {
                return response()->json([
                    'message' => "El archivo {$file['name']} no es compatible con la operación seleccionada.",
                ], Response::HTTP_UNPROCESSABLE_ENTITY);
            }
        }

        $batch = PdfBatch::query()->create([
            'uuid' => (string) Str::uuid(),
            'user_id' => $request->user()->id,
            'operation' => $data['operation'],
            'status' => 'uploading',
            'settings' => [],
            'total_files' => count($data['files']),
            'expires_at' => now()->addHours((int) config('system_pdf.retention_hours', 24)),
        ]);

        foreach ($data['files'] as $index => $file) {
            $batch->items()->create([
                'uuid' => (string) Str::uuid(),
                'position' => $index + 1,
                'client_fingerprint' => $file['fingerprint'] ?? null,
                'original_name' => basename(str_replace('\\', '/', $file['name'])),
                'mime' => $file['type'] ?: null,
                'extension' => strtolower(pathinfo($file['name'], PATHINFO_EXTENSION)),
                'original_size' => (int) $file['size'],
                'status' => 'pending_upload',
            ]);
        }

        return response()->json([
            'message' => 'Lote preparado. Los archivos se subirán de forma progresiva.',
            'batch' => $this->payload($batch),
        ], Response::HTTP_CREATED);
    }

    public function show(Request $request, string $batch, PdfBatchService $service): JsonResponse
    {
        $this->authorizeAction($request, 'ver');
        $model = $this->ownedBatch($request, $batch);
        $model = $service->recalculate($model);
        return response()->json(['batch' => $this->payload($model)]);
    }

    public function upload(
        Request $request,
        string $batch,
        string $item,
        PdfBatchService $service,
    ): JsonResponse {
        $this->authorizeAction($request, 'procesar');
        $batchModel = $this->ownedBatch($request, $batch);
        $itemModel = $this->ownedItem($batchModel, $item);
        abort_unless(
            in_array($itemModel->status, ['pending_upload', 'upload_failed'], true),
            Response::HTTP_CONFLICT,
            'Este archivo ya fue cargado.'
        );

        $maxKb = (int) config('system_pdf.max_file_kb', 256000);
        $allowed = $batchModel->operation === 'combine'
            ? ['pdf', 'jpg', 'jpeg', 'png', 'webp']
            : ['pdf'];
        $data = $request->validate([
            'file' => ['required', File::types($allowed)->max($maxKb)],
        ], [
            'file.required' => 'No se recibió el archivo.',
            'file.max' => 'El archivo supera el límite de '.round($maxKb / 1024).' MB.',
        ]);

        $file = $data['file'];
        if ((int) $file->getSize() !== (int) $itemModel->original_size) {
            return response()->json([
                'message' => 'El archivo no coincide con el elemento pendiente del lote.',
            ], Response::HTTP_UNPROCESSABLE_ENTITY);
        }

        $extension = strtolower($file->getClientOriginalExtension() ?: $itemModel->extension ?: 'pdf');
        $safe = $this->safeBase($itemModel->original_name);
        $storedName = str_pad((string) $itemModel->position, 3, '0', STR_PAD_LEFT).'-'.$safe.'.'.$extension;
        $path = $file->storeAs($batchModel->basePath().'/originals', $storedName, 'local');
        if (! is_string($path)) {
            return response()->json(['message' => 'No se pudo guardar el archivo temporal.'], 500);
        }

        $mime = strtolower((string) ($file->getMimeType() ?: $itemModel->mime ?: 'application/octet-stream'));
        $isImage = str_starts_with($mime, 'image/') || in_array($extension, ['jpg', 'jpeg', 'png', 'webp'], true);
        $itemModel->forceFill([
            'stored_name' => $storedName,
            'source_path' => $path,
            'mime' => $mime,
            'extension' => $extension,
            'original_size' => (int) $file->getSize(),
            'page_count' => $isImage ? 1 : null,
            'encrypted' => false,
            'status' => $isImage ? 'ready' : 'inspecting',
            'error' => null,
            'uploaded_at' => now(),
        ])->save();

        if (! $isImage) {
            InspectPdfItem::dispatch($itemModel->id)
                ->onQueue((string) config('system_pdf.queue', 'system-pdf'));
        }

        $batchModel = $service->recalculate($batchModel);

        return response()->json([
            'message' => $isImage ? 'Imagen cargada.' : 'PDF cargado y enviado a análisis.',
            'batch' => $this->payload($batchModel),
        ]);
    }

    public function password(
        Request $request,
        string $batch,
        string $item,
        PdfBatchService $service,
    ): JsonResponse {
        $this->authorizeAction($request, 'procesar');
        $data = $request->validate([
            'password' => ['required', 'string', 'max:200'],
        ]);
        $batchModel = $this->ownedBatch($request, $batch);
        $itemModel = $this->ownedItem($batchModel, $item);
        abort_unless($itemModel->source_path, 409, 'El PDF aún no se ha cargado.');

        $itemModel->forceFill([
            'secret' => $data['password'],
            'status' => 'inspecting',
            'error' => null,
        ])->save();
        InspectPdfItem::dispatch($itemModel->id)
            ->onQueue((string) config('system_pdf.queue', 'system-pdf'));

        return response()->json([
            'message' => 'Contraseña guardada de forma cifrada. Se volvió a analizar el PDF.',
            'batch' => $this->payload($service->recalculate($batchModel)),
        ]);
    }

    public function start(Request $request, string $batch, PdfBatchService $service): JsonResponse
    {
        $this->authorizeAction($request, 'procesar');
        $batchModel = $this->ownedBatch($request, $batch)->load('items');
        abort_unless(in_array($batchModel->status, ['ready', 'needs_attention'], true), 409, 'El lote aún no está listo.');
        abort_if($batchModel->items->contains(fn (PdfItem $item): bool => $item->status !== 'ready'), 409, 'Corrige los archivos con error antes de procesar.');

        $settings = $this->validatedSettings($request, $batchModel);
        $secret = [];
        if ($batchModel->operation === 'security' && ($settings['security_mode'] ?? 'protect') === 'protect') {
            $secret = [
                'user_password' => (string) $request->input('new_password', ''),
                'owner_password' => (string) $request->input('owner_password', ''),
            ];
            if ($secret['user_password'] === '') {
                return response()->json(['message' => 'Escribe la nueva contraseña de apertura.'], 422);
            }
        }

        $order = array_values(array_filter((array) $request->input('item_order', []), 'is_string'));
        if ($order !== []) {
            $expected = $batchModel->items->pluck('uuid')->sort()->values()->all();
            $received = collect($order)->unique()->sort()->values()->all();
            if (count($order) !== count($received) || $received !== $expected) {
                return response()->json(['message' => 'El orden recibido no coincide con los archivos del lote.'], 422);
            }

            foreach ($order as $index => $uuid) {
                $batchModel->items()->where('uuid', $uuid)->update(['position' => $index + 1]);
            }
        }

        $batchModel->forceFill([
            'settings' => $settings,
            'secret' => $secret !== [] ? $secret : null,
            'status' => 'queued',
            'started_at' => now(),
            'completed_at' => null,
            'error' => null,
        ])->save();

        if ($batchModel->operation === 'combine') {
            ProcessPdfCombine::dispatch($batchModel->id)
                ->onQueue((string) config('system_pdf.queue', 'system-pdf'));
        } else {
            foreach ($batchModel->items()->orderBy('position')->get() as $itemModel) {
                $itemModel->forceFill(['status' => 'queued', 'error' => null])->save();
                ProcessPdfItem::dispatch($itemModel->id)
                    ->onQueue((string) config('system_pdf.queue', 'system-pdf'));
            }
        }

        return response()->json([
            'message' => 'El lote se envió a la cola System PDF.',
            'batch' => $this->payload($service->recalculate($batchModel)),
        ]);
    }

    public function retry(
        Request $request,
        string $batch,
        string $item,
        PdfBatchService $service,
    ): JsonResponse {
        $this->authorizeAction($request, 'procesar');
        $batchModel = $this->ownedBatch($request, $batch);
        $itemModel = $this->ownedItem($batchModel, $item);
        abort_unless($itemModel->status === 'failed', 409, 'Este archivo no se puede reintentar.');

        if ($batchModel->operation === 'combine') {
            $batchModel->items()->update(['status' => 'queued', 'error' => null, 'processed_at' => null]);
            $batchModel->forceFill([
                'status' => 'queued',
                'error' => null,
                'completed_at' => null,
                'output_name' => null,
                'output_path' => null,
                'output_bytes' => 0,
            ])->save();
            ProcessPdfCombine::dispatch($batchModel->id)
                ->onQueue((string) config('system_pdf.queue', 'system-pdf'));

            return response()->json([
                'message' => 'La combinación completa volvió a la cola.',
                'batch' => $this->payload($service->recalculate($batchModel)),
            ]);
        }

        $itemModel->forceFill(['status' => 'queued', 'error' => null, 'processed_at' => null])->save();
        $batchModel->forceFill(['status' => 'processing', 'completed_at' => null])->save();
        ProcessPdfItem::dispatch($itemModel->id)
            ->onQueue((string) config('system_pdf.queue', 'system-pdf'));

        return response()->json([
            'message' => 'El archivo volvió a la cola.',
            'batch' => $this->payload($service->recalculate($batchModel)),
        ]);
    }

    public function destroy(Request $request, string $batch): JsonResponse
    {
        $this->authorizeAction($request, 'eliminar');
        $model = $this->ownedBatch($request, $batch);
        Storage::disk('local')->deleteDirectory($model->basePath());
        $model->delete();
        return response()->json(['message' => 'El lote y sus archivos temporales fueron eliminados.']);
    }

    private function validatedSettings(Request $request, PdfBatch $batch): array
    {
        return match ($batch->operation) {
            'compress' => $request->validate([
                'compression_profile' => ['required', Rule::in(['auto', 'low', 'medium', 'high', 'custom'])],
                'custom_quality' => ['nullable', 'integer', 'min:35', 'max:100'],
            ]),
            'combine' => $request->validate([
                'output_name' => ['nullable', 'string', 'max:120'],
            ]),
            'split' => $request->validate([
                'split_mode' => ['required', Rule::in(['each', 'ranges', 'every', 'selected'])],
                'split_ranges' => ['nullable', 'string', 'max:2000'],
                'split_every' => ['nullable', 'integer', 'min:1', 'max:10000'],
                'selected_pages' => ['nullable', 'array', 'max:10000'],
                'selected_pages.*' => ['integer', 'min:1'],
            ]),
            'reorder' => $request->validate([
                'page_plan' => ['required', 'array', 'min:1', 'max:10000'],
                'page_plan.*.source' => ['required', 'integer', 'min:1'],
                'page_plan.*.rotation' => ['nullable', 'integer', Rule::in([0, 90, 180, 270])],
            ]),
            'security' => $request->validate([
                'security_mode' => ['required', Rule::in(['protect', 'unlock'])],
                'allow_print' => ['nullable', Rule::in(['none', 'low', 'full'])],
                'allow_modify' => ['nullable', Rule::in(['none', 'assembly', 'form', 'annotate', 'all'])],
                'allow_extract' => ['nullable', 'boolean'],
            ]),
            default => [],
        };
    }

    private function payload(PdfBatch $batch, bool $compact = false): array
    {
        $batch->loadMissing('items');
        $planner = app(ZipPartPlanner::class);
        $entries = [];
        $items = $batch->items->map(function (PdfItem $item) use ($batch, &$entries, $compact): array {
            $prefix = (int) $batch->total_files > 1
                ? str_pad((string) $item->position, 3, '0', STR_PAD_LEFT).'-'
                : '';
            if ($item->output_path) {
                $name = $item->output_name ?: basename($item->output_path);
                $entries[] = ['key' => 'item:'.$item->uuid, 'name' => $prefix.$name, 'path' => $item->output_path, 'size' => (int) $item->output_size];
            }
            foreach ($item->result_files ?? [] as $resultIndex => $file) {
                $entries[] = ['key' => 'result:'.$item->uuid.':'.$resultIndex, 'name' => $prefix.$this->safeBase($item->original_name).'/'.$file['name'], 'path' => $file['path'], 'size' => (int) ($file['size'] ?? 0)];
            }

            $pages = [];
            $includePageEditor = ! $compact
                && (int) $item->position === 1
                && in_array($batch->operation, ['reorder', 'split'], true);
            if ($includePageEditor) {
                for ($page = 1; $page <= (int) ($item->page_count ?? 0); $page++) {
                    $pages[] = [
                        'number' => $page,
                        'thumbnail_url' => isset(($item->thumbnails ?? [])[$page - 1])
                            ? route('system-pdf.thumbnail', [$batch->uuid, $item->uuid, $page])
                            : null,
                    ];
                }
            }

            return [
                'uuid' => $item->uuid,
                'position' => (int) $item->position,
                'original_name' => $item->original_name,
                'mime' => $item->mime,
                'extension' => $item->extension,
                'original_size' => (int) $item->original_size,
                'page_count' => $item->page_count !== null ? (int) $item->page_count : null,
                'encrypted' => (bool) $item->encrypted,
                'status' => $item->status,
                'error' => $item->error,
                'warnings' => $item->warnings ?? [],
                'output_name' => $item->output_name,
                'output_size' => $item->output_size !== null ? (int) $item->output_size : null,
                'source_preview_url' => $item->source_path ? route('system-pdf.source-preview', [$batch->uuid, $item->uuid]) : null,
                'first_thumbnail_url' => isset(($item->thumbnails ?? [])[0])
                    ? route('system-pdf.thumbnail', [$batch->uuid, $item->uuid, 1])
                    : null,
                'download_url' => $item->output_path ? route('system-pdf.download-item', [$batch->uuid, $item->uuid]) : null,
                'selection_key' => $item->output_path ? 'item:'.$item->uuid : null,
                'results' => collect($item->result_files ?? [])->values()->map(fn (array $file, int $index): array => [
                    'name' => $file['name'],
                    'size' => (int) ($file['size'] ?? 0),
                    'selection_key' => 'result:'.$item->uuid.':'.$index,
                    'download_url' => route('system-pdf.download-result', [$batch->uuid, $item->uuid, $index]),
                ])->all(),
                'pages' => $pages,
            ];
        })->values()->all();

        if ($batch->output_path) {
            $entries[] = ['key' => 'batch', 'name' => $batch->output_name ?: basename($batch->output_path), 'path' => $batch->output_path, 'size' => (int) $batch->output_bytes];
        }
        $parts = $planner->plan($entries, (int) config('system_pdf.zip_part_max_files', 1000), (int) config('system_pdf.zip_part_max_mb', 1000) * 1024 * 1024);

        return [
            'uuid' => $batch->uuid,
            'operation' => $batch->operation,
            'operation_label' => $this->operationLabel($batch->operation),
            'status' => $batch->status,
            'settings' => $batch->settings ?? [],
            'total_files' => (int) $batch->total_files,
            'uploaded_files' => (int) $batch->uploaded_files,
            'processed_files' => (int) $batch->processed_files,
            'completed_files' => (int) $batch->completed_files,
            'failed_files' => (int) $batch->failed_files,
            'original_bytes' => (int) $batch->original_bytes,
            'output_bytes' => (int) $batch->output_bytes,
            'output_name' => $batch->output_name,
            'error' => $batch->error,
            'created_at' => $batch->created_at?->toIso8601String(),
            'completed_at' => $batch->completed_at?->toIso8601String(),
            'expires_at' => $batch->expires_at?->toIso8601String(),
            'download_output_url' => $batch->output_path ? route('system-pdf.download-output', $batch->uuid) : null,
            'download_zip_parts' => collect($parts)->map(fn (array $part): array => [
                'number' => $part['number'],
                'file_count' => $part['file_count'],
                'bytes' => $part['bytes'],
                'url' => route('system-pdf.download-zip', ['batch' => $batch->uuid, 'part' => $part['number']]),
            ])->all(),
            'zip_entries' => collect($entries)->map(fn (array $entry): array => [
                'key' => (string) ($entry['key'] ?? ''),
                'name' => $entry['name'],
                'size' => (int) ($entry['size'] ?? 0),
            ])->values()->all(),
            'items' => $items,
        ];
    }

    private function operationLabel(string $operation): string
    {
        return match ($operation) {
            'compress' => 'Reducir peso',
            'combine' => 'Combinar PDF',
            'split' => 'Descombinar PDF',
            'reorder' => 'Ordenar PDF',
            'security' => 'Proteger o desbloquear',
            default => 'System PDF',
        };
    }

    private function ownedBatch(Request $request, string $uuid): PdfBatch
    {
        $query = PdfBatch::query()->where('uuid', $uuid);
        if (! $request->user()->puedePdf('administrar')) {
            $query->where('user_id', $request->user()->id);
        }
        return $query->firstOrFail();
    }

    private function ownedItem(PdfBatch $batch, string $uuid): PdfItem
    {
        return PdfItem::query()->where('pdf_batch_id', $batch->id)->where('uuid', $uuid)->firstOrFail();
    }

    private function authorizeAction(Request $request, string $action): void
    {
        abort_unless($request->user()?->puedePdf($action), 403);
    }

    private function safeBase(string $name): string
    {
        $base = Str::ascii(pathinfo($name, PATHINFO_FILENAME));
        $base = preg_replace('/[^A-Za-z0-9_-]+/', '_', $base) ?: 'documento';
        return trim(substr($base, 0, 100), '_-') ?: 'documento';
    }
}
