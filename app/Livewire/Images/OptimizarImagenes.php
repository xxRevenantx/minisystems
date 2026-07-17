<?php

namespace App\Livewire\Images;

use App\Models\HistorialExportacion;
use App\Services\CreativeActivity;
use App\Services\ImageOptimizerService;
use App\Services\ImagePipeline;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Validation\Rules\File;
use Livewire\Component;
use Livewire\WithFileUploads;

class OptimizarImagenes extends Component
{
    use WithFileUploads;

    /** @var array<\Livewire\Features\FileUploads\TemporaryUploadedFile> */
    public array $images = [];

    /** @var array<int,array<string,mixed>> */
    public array $results = [];

    public string $profile = 'balanced';
    public string $format = 'webp';
    public int $quality = 82;
    public int $maxWidth = 1920;
    public int $maxHeight = 1920;
    public $targetKb = null;
    public bool $allowUpscale = false;
    public bool $preserveTransparency = true;
    public string $renamePattern = '{name}-optimizada-{index}';
    public ?string $batchUuid = null;
    public ?string $driverName = null;
    public ?string $systemError = null;

    protected function rules(): array
    {
        return [
            'images' => ['required', 'array', 'min:1', 'max:50'],
            'images.*' => [
                'required',
                File::image()->types(['jpg', 'jpeg', 'png', 'webp'])->max(20 * 1024),
            ],
            'profile' => ['required', 'in:balanced,web,social,documents,max,transparent,custom'],
            'format' => ['required', 'in:original,jpg,png,webp,avif'],
            'quality' => ['required', 'integer', 'between:35,100'],
            'maxWidth' => ['required', 'integer', 'between:320,6000'],
            'maxHeight' => ['required', 'integer', 'between:320,6000'],
            'targetKb' => ['nullable', 'integer', 'between:50,20000'],
            'allowUpscale' => ['boolean'],
            'preserveTransparency' => ['boolean'],
            'renamePattern' => ['required', 'string', 'max:100'],
        ];
    }

    protected function messages(): array
    {
        return [
            'images.required' => 'Selecciona al menos una imagen.',
            'images.max' => 'Puedes optimizar hasta 50 imágenes por lote.',
            'images.*.image' => 'Uno de los archivos no es una imagen compatible.',
            'images.*.max' => 'Cada imagen puede pesar hasta 20 MB.',
            'targetKb.between' => 'El peso objetivo debe estar entre 50 KB y 20 MB.',
            'renamePattern.required' => 'Define el patrón para nombrar los archivos optimizados.',
        ];
    }

    public function mount(): void
    {
        $this->applyProfile('balanced');

        try {
            $pipeline = new ImagePipeline();
            $this->driverName = $pipeline->driverName();
        } catch (\Throwable $exception) {
            $this->systemError = $exception->getMessage();
        }

        $this->cleanupExpiredBatches();
    }

    public function updatedProfile(string $value): void
    {
        $this->applyProfile($value);
    }

    public function updatedImages(): void
    {
        $this->resetValidation('images');
    }

    public function updated(string $property): void
    {
        if (in_array($property, [
            'format',
            'quality',
            'maxWidth',
            'maxHeight',
            'targetKb',
            'allowUpscale',
            'preserveTransparency',
            'renamePattern',
        ], true)) {
            $this->profile = 'custom';
        }
    }

    public function removeImage(string $temporaryName): void
    {
        $this->images = array_values(array_filter(
            $this->images,
            fn ($image) => (string) $image->getFilename() !== $temporaryName
        ));
    }

    public function clearImages(): void
    {
        $this->images = [];
        $this->resetValidation('images');
    }

    public function optimizar(): void
    {
        if ($this->systemError) {
            $this->addError('images', $this->systemError);

            return;
        }

        @set_time_limit(0);
        $this->validate();
        $this->deleteCurrentBatch();

        try {
            $pipeline = new ImagePipeline();
            $optimizer = new ImageOptimizerService($pipeline);
        } catch (\Throwable $exception) {
            $this->systemError = $exception->getMessage();
            $this->addError('images', $this->systemError);

            return;
        }

        $disk = Storage::disk('local');
        $this->batchUuid = (string) Str::uuid();
        $basePath = $this->batchBasePath($this->batchUuid);
        $this->results = [];
        $usedNames = [];
        $completed = 0;
        $failed = 0;

        foreach ($this->images as $position => $file) {
            $index = $position + 1;
            $originalName = $file->getClientOriginalName();
            $originalSize = (int) $file->getSize();
            $originalPath = null;
            $outputPath = null;

            try {
                $optimized = $optimizer->optimize($file->getRealPath(), [
                    'format' => $this->format,
                    'quality' => $this->quality,
                    'max_width' => $this->maxWidth,
                    'max_height' => $this->maxHeight,
                    'target_kb' => filled($this->targetKb) ? (int) $this->targetKb : null,
                    'allow_upscale' => $this->allowUpscale,
                    'preserve_transparency' => $this->preserveTransparency,
                ]);

                $originalExtension = $this->extensionFromMime($optimized['original_mime']);
                $storedOriginalName = str_pad((string) $index, 3, '0', STR_PAD_LEFT)
                    .'-'.$this->safeBaseName($originalName).'.'.$originalExtension;
                $originalPath = $disk->putFileAs($basePath.'/originals', $file, $storedOriginalName);

                if (! is_string($originalPath)) {
                    throw new \RuntimeException('No fue posible guardar la copia temporal de la imagen original.');
                }

                $outputName = $this->buildOutputName(
                    index: $index,
                    originalName: $originalName,
                    extension: $optimized['format'],
                    usedNames: $usedNames,
                );
                $outputPath = $basePath.'/outputs/'.$outputName;

                if (! $disk->put($outputPath, $optimized['contents'])) {
                    throw new \RuntimeException('No fue posible guardar la imagen optimizada.');
                }

                $optimizedSize = strlen($optimized['contents']);
                $savedBytes = max(0, $originalSize - $optimizedSize);
                $reduction = $originalSize > 0
                    ? round((1 - ($optimizedSize / $originalSize)) * 100, 1)
                    : 0.0;

                $this->results[] = [
                    'status' => 'completed',
                    'original_name' => $originalName,
                    'original_file' => $storedOriginalName,
                    'optimized_file' => $outputName,
                    'original_size' => $originalSize,
                    'optimized_size' => $optimizedSize,
                    'saved_bytes' => $savedBytes,
                    'reduction' => $reduction,
                    'original_width' => $optimized['original_width'],
                    'original_height' => $optimized['original_height'],
                    'width' => $optimized['width'],
                    'height' => $optimized['height'],
                    'format' => strtoupper($optimized['format']),
                    'mime' => $optimized['mime'],
                    'quality' => $optimized['quality'],
                    'warnings' => $optimized['warnings'],
                    'used_original' => $optimized['used_original'],
                ];
                $completed++;
            } catch (\Throwable $exception) {
                foreach ([$originalPath, $outputPath] as $partialPath) {
                    if (is_string($partialPath) && $disk->exists($partialPath)) {
                        $disk->delete($partialPath);
                    }
                }

                $this->results[] = [
                    'status' => 'failed',
                    'original_name' => $originalName,
                    'original_size' => $originalSize,
                    'error' => $exception->getMessage(),
                ];
                $failed++;
            }
        }

        $disk->put($basePath.'/meta.json', json_encode([
            'user_id' => auth()->id(),
            'batch_uuid' => $this->batchUuid,
            'created_at' => now()->toIso8601String(),
            'expires_at' => now()->addDay()->toIso8601String(),
            'completed' => $completed,
            'failed' => $failed,
        ], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));

        if ($completed === 0) {
            $disk->deleteDirectory($basePath);
            $this->batchUuid = null;
            $this->addError('images', 'No se pudo optimizar ninguna imagen. Revisa los archivos y la configuración seleccionada.');

            return;
        }

        $this->registerExport($completed);
        $this->images = [];

        $this->dispatch('swal', [
            'title' => $failed > 0
                ? "{$completed} imágenes optimizadas y {$failed} con error"
                : "{$completed} imágenes optimizadas correctamente",
            'icon' => $failed > 0 ? 'warning' : 'success',
            'position' => 'top-end',
        ]);
    }

    public function limpiarResultados(): void
    {
        $this->deleteCurrentBatch();
        $this->results = [];
        $this->batchUuid = null;
    }

    public function totalOriginalBytes(): int
    {
        return (int) collect($this->results)
            ->where('status', 'completed')
            ->sum('original_size');
    }

    public function totalOptimizedBytes(): int
    {
        return (int) collect($this->results)
            ->where('status', 'completed')
            ->sum('optimized_size');
    }

    public function totalDifferenceBytes(): int
    {
        return $this->totalOriginalBytes() - $this->totalOptimizedBytes();
    }

    public function totalSavedBytes(): int
    {
        return max(0, $this->totalDifferenceBytes());
    }

    public function totalReduction(): float
    {
        $original = $this->totalOriginalBytes();

        return $original > 0
            ? round((1 - ($this->totalOptimizedBytes() / $original)) * 100, 1)
            : 0.0;
    }

    private function applyProfile(string $profile): void
    {
        $settings = $this->profiles()[$profile] ?? null;

        if (! $settings || $profile === 'custom') {
            return;
        }

        $this->format = $settings['format'];
        $this->quality = $settings['quality'];
        $this->maxWidth = $settings['max_width'];
        $this->maxHeight = $settings['max_height'];
        $this->targetKb = $settings['target_kb'];
        $this->preserveTransparency = $settings['preserve_transparency'];
        $this->allowUpscale = false;
    }

    /** @return array<string,array<string,mixed>> */
    private function profiles(): array
    {
        return [
            'balanced' => [
                'label' => 'Equilibrado',
                'description' => 'Buena calidad con reducción notable para uso general.',
                'format' => 'webp',
                'quality' => 82,
                'max_width' => 1920,
                'max_height' => 1920,
                'target_kb' => null,
                'preserve_transparency' => true,
            ],
            'web' => [
                'label' => 'Sitio web',
                'description' => 'Archivos ligeros para galerías, biblioteca y publicaciones.',
                'format' => 'webp',
                'quality' => 78,
                'max_width' => 1600,
                'max_height' => 1600,
                'target_kb' => null,
                'preserve_transparency' => true,
            ],
            'social' => [
                'label' => 'Redes sociales',
                'description' => 'Calidad alta y dimensiones adecuadas para publicar.',
                'format' => 'jpg',
                'quality' => 85,
                'max_width' => 2048,
                'max_height' => 2048,
                'target_kb' => null,
                'preserve_transparency' => false,
            ],
            'documents' => [
                'label' => 'Documentos e impresión',
                'description' => 'Mayor detalle para fondos, reconocimientos y documentos.',
                'format' => 'jpg',
                'quality' => 90,
                'max_width' => 3000,
                'max_height' => 3000,
                'target_kb' => null,
                'preserve_transparency' => false,
            ],
            'max' => [
                'label' => 'Máxima reducción',
                'description' => 'Reduce agresivamente para compartir o almacenar.',
                'format' => 'webp',
                'quality' => 68,
                'max_width' => 1280,
                'max_height' => 1280,
                'target_kb' => 400,
                'preserve_transparency' => true,
            ],
            'transparent' => [
                'label' => 'Logos y transparencia',
                'description' => 'Protege fondos transparentes y bordes definidos.',
                'format' => 'webp',
                'quality' => 88,
                'max_width' => 2400,
                'max_height' => 2400,
                'target_kb' => null,
                'preserve_transparency' => true,
            ],
            'custom' => [
                'label' => 'Personalizado',
                'description' => 'Control manual de formato, calidad, dimensiones y peso.',
            ],
        ];
    }

    private function buildOutputName(int $index, string $originalName, string $extension, array &$usedNames): string
    {
        $replacements = [
            '{name}' => $this->safeBaseName($originalName),
            '{index}' => str_pad((string) $index, 3, '0', STR_PAD_LEFT),
            '{date}' => now()->format('Ymd'),
            '{format}' => $extension,
        ];
        $base = strtr($this->renamePattern, $replacements);
        $base = trim((string) preg_replace('/[^a-zA-Z0-9_-]+/', '-', $base), '-_');
        $base = mb_substr($base !== '' ? $base : 'imagen-optimizada-'.$index, 0, 100);
        $candidate = $base.'.'.$extension;
        $suffix = 2;

        while (isset($usedNames[strtolower($candidate)])) {
            $candidate = $base.'-'.$suffix.'.'.$extension;
            $suffix++;
        }

        $usedNames[strtolower($candidate)] = true;

        return $candidate;
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
            'image/avif' => 'avif',
            default => 'jpg',
        };
    }

    private function batchBasePath(string $batchUuid): string
    {
        return 'image-optimizer/'.auth()->id().'/'.$batchUuid;
    }

    private function deleteCurrentBatch(): void
    {
        if ($this->batchUuid && auth()->check()) {
            Storage::disk('local')->deleteDirectory($this->batchBasePath($this->batchUuid));
        }
    }

    private function cleanupExpiredBatches(): void
    {
        if (! auth()->check()) {
            return;
        }

        $disk = Storage::disk('local');
        $userPath = 'image-optimizer/'.auth()->id();

        foreach ($disk->directories($userPath) as $directory) {
            $metadataPath = $directory.'/meta.json';

            if (! $disk->exists($metadataPath)) {
                continue;
            }

            try {
                $metadata = json_decode($disk->get($metadataPath), true, flags: JSON_THROW_ON_ERROR);
                $expiresAt = isset($metadata['expires_at']) ? Carbon::parse($metadata['expires_at']) : null;

                if ($expiresAt && $expiresAt->isPast()) {
                    $disk->deleteDirectory($directory);
                }
            } catch (\Throwable) {
                // Un metadato dañado no debe impedir el uso del optimizador.
            }
        }
    }

    private function registerExport(int $completed): void
    {
        try {
            if (! Schema::hasTable('historial_exportaciones')) {
                return;
            }

            $record = HistorialExportacion::create([
                'user_id' => auth()->id(),
                'tipo' => 'optimizador_imagenes',
                'formato' => $this->format,
                'cantidad' => $completed,
                'configuracion' => [
                    'profile' => $this->profile,
                    'quality' => $this->quality,
                    'max_width' => $this->maxWidth,
                    'max_height' => $this->maxHeight,
                    'target_kb' => filled($this->targetKb) ? (int) $this->targetKb : null,
                    'preserve_transparency' => $this->preserveTransparency,
                    'driver' => $this->driverName,
                    'batch_uuid' => $this->batchUuid,
                ],
            ]);
            CreativeActivity::log('exportaciones', 'optimizar_imagenes', $record, $completed.' imágenes optimizadas');
        } catch (\Throwable) {
            // La optimización no debe fallar si el historial todavía no está migrado.
        }
    }

    public function render()
    {
        $profiles = $this->profiles();

        return view('livewire.images.optimizar-imagenes', [
            'profiles' => $profiles,
            'selectedProfile' => $profiles[$this->profile] ?? $profiles['custom'],
        ]);
    }
}
