<?php

namespace App\Livewire\Images;

use App\Models\Marco;
use App\Services\ImagePipeline;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\File;
use Livewire\Component;
use Livewire\WithFileUploads;
use ZipArchive;

class CreacionImagenes extends Component
{
    use WithFileUploads;

    /** @var array<\Livewire\Features\FileUploads\TemporaryUploadedFile> */
    public array $images = [];

    /** Configuración escalar por imagen, indexada por sha1 del temporal. */
    public array $imageSettings = [];

    public int $paso = 1;
    public ?int $marco = null;
    public string $orientationMode = 'auto'; // auto|desktop|mobile
    public string $squareMode = 'desktop';
    public string $missingFrameBehavior = 'skip'; // skip|alternate
    public string $fitMode = 'cover'; // cover|contain|blur
    public string $format = 'jpg'; // jpg|png|webp|original
    public int $quality = 88;
    public string $renamePattern = '{orig}_{index}';
    public bool $organizeFolders = true;

    public int $desktopWidth = 2058;
    public int $desktopHeight = 1365;
    public int $mobileWidth = 1365;
    public int $mobileHeight = 2058;

    public ?string $selectedPreviewKey = null;

    protected function rules(): array
    {
        return [
            'images' => ['required', 'array', 'min:1', 'max:100'],
            'images.*' => [
                'required',
                File::image()->types(['jpg', 'jpeg', 'png', 'webp'])->max(20 * 1024),
            ],
            'marco' => ['nullable', Rule::exists('marcos', 'id')->where(fn ($query) => $query->where('activo', true)->whereNull('deleted_at'))],
            'orientationMode' => ['required', 'in:auto,desktop,mobile'],
            'squareMode' => ['required', 'in:desktop,mobile'],
            'missingFrameBehavior' => ['required', 'in:skip,alternate'],
            'fitMode' => ['required', 'in:cover,contain,blur'],
            'format' => ['required', 'in:jpg,png,webp,original'],
            'quality' => ['required', 'integer', 'between:60,100'],
            'renamePattern' => ['required', 'string', 'max:120'],
            'organizeFolders' => ['boolean'],
            'desktopWidth' => ['required', 'integer', 'between:320,6000'],
            'desktopHeight' => ['required', 'integer', 'between:320,6000'],
            'mobileWidth' => ['required', 'integer', 'between:320,6000'],
            'mobileHeight' => ['required', 'integer', 'between:320,6000'],
        ];
    }

    protected function messages(): array
    {
        return [
            'images.required' => 'Selecciona al menos una imagen.',
            'images.max' => 'Puedes procesar hasta 100 imágenes por lote.',
            'images.*.max' => 'Cada imagen puede pesar hasta 20 MB.',
            'renamePattern.required' => 'Define el patrón de nombre de salida.',
        ];
    }

    public function updatedImages(): void
    {
        $this->sincronizarConfiguracionImagenes();
        $this->resetValidation('images');
    }

    public function updatedMarco(): void
    {
        $this->resetValidation('marco');
    }

    public function updatedImageSettings($value, string $path): void
    {
        if (! str_ends_with($path, '.focus')) {
            return;
        }

        $key = Str::before($path, '.focus');
        $positions = [
            'center' => [50, 50],
            'top' => [50, 0],
            'bottom' => [50, 100],
            'left' => [0, 50],
            'right' => [100, 50],
            'top-left' => [0, 0],
            'top-right' => [100, 0],
            'bottom-left' => [0, 100],
            'bottom-right' => [100, 100],
        ];

        if (isset($positions[$value], $this->imageSettings[$key])) {
            [$x, $y] = $positions[$value];
            $this->imageSettings[$key]['focus_x'] = $x;
            $this->imageSettings[$key]['focus_y'] = $y;
        }
    }

    public function siguientePaso(): void
    {
        if ($this->paso === 1) {
            $this->validate([
                'images' => ['required', 'array', 'min:1', 'max:100'],
                'images.*' => [
                    'required',
                    File::image()->types(['jpg', 'jpeg', 'png', 'webp'])->max(20 * 1024),
                ],
            ]);
            $this->sincronizarConfiguracionImagenes();
            $this->paso = 2;
            return;
        }

        if ($this->paso === 2) {
            $this->validarConfiguracion();
            $this->paso = 3;
            $this->selectedPreviewKey ??= array_key_first($this->imageSettings);
        }
    }

    public function pasoAnterior(): void
    {
        $this->paso = max(1, $this->paso - 1);
    }

    public function irAlPaso(int $paso): void
    {
        if ($paso < $this->paso) {
            $this->paso = max(1, $paso);
        }
    }

    public function seleccionarPreview(string $key): void
    {
        if (isset($this->imageSettings[$key])) {
            $this->selectedPreviewKey = $key;
        }
    }

    public function reorder(array $orderedIds): void
    {
        $byId = [];
        foreach ($this->images as $file) {
            $byId[(string) $file->getFilename()] = $file;
        }

        $reordered = [];
        foreach ($orderedIds as $id) {
            if (isset($byId[$id])) {
                $reordered[] = $byId[$id];
                unset($byId[$id]);
            }
        }

        foreach ($byId as $file) {
            $reordered[] = $file;
        }

        $this->images = array_values($reordered);
    }

    public function removeByTemp(string $tempName): void
    {
        $key = sha1($tempName);
        $this->images = array_values(array_filter(
            $this->images,
            fn ($file) => (string) $file->getFilename() !== $tempName
        ));
        unset($this->imageSettings[$key]);

        if ($this->selectedPreviewKey === $key) {
            $this->selectedPreviewKey = array_key_first($this->imageSettings);
        }

        if ($this->images === []) {
            $this->paso = 1;
        }
    }

    public function limpiarLote(): void
    {
        $this->images = [];
        $this->imageSettings = [];
        $this->selectedPreviewKey = null;
        $this->paso = 1;
        $this->resetValidation();
    }

    public function aplicarAImagenes(string $campo): void
    {
        $valor = match ($campo) {
            'fit' => $this->fitMode,
            'orientation' => $this->orientationMode === 'auto' ? 'inherit' : $this->orientationMode,
            'frame' => $this->marco ? (string) $this->marco : 'none',
            default => null,
        };

        if ($valor === null) {
            return;
        }

        foreach ($this->imageSettings as $key => $settings) {
            $this->imageSettings[$key][$campo] = $valor;
        }
    }

    private function sincronizarConfiguracionImagenes(): void
    {
        $vigentes = [];

        foreach ($this->images as $file) {
            $tempName = (string) $file->getFilename();
            $key = sha1($tempName);
            $vigentes[] = $key;
            [$width, $height] = @getimagesize($file->getRealPath()) ?: [0, 0];
            if (function_exists('exif_read_data') && in_array(strtolower($file->getClientOriginalExtension()), ['jpg', 'jpeg'], true)) {
                try {
                    $exif = @exif_read_data($file->getRealPath());
                    if (in_array((int) ($exif['Orientation'] ?? 1), [5, 6, 7, 8], true)) {
                        [$width, $height] = [$height, $width];
                    }
                } catch (\Throwable) {
                    // La detección visual sigue funcionando sin EXIF.
                }
            }
            $detected = $width === $height ? 'square' : ($width > $height ? 'desktop' : 'mobile');

            $this->imageSettings[$key] = array_merge([
                'temp_name' => $tempName,
                'name' => $file->getClientOriginalName(),
                'width' => $width,
                'height' => $height,
                'size' => (int) $file->getSize(),
                'mime' => $file->getMimeType(),
                'extension' => strtolower($file->getClientOriginalExtension() ?: 'jpg'),
                'detected' => $detected,
                'orientation' => 'inherit',
                'fit' => 'inherit',
                'frame' => 'global',
                'focus' => 'center',
                'focus_x' => 50,
                'focus_y' => 50,
                'zoom' => 100,
            ], $this->imageSettings[$key] ?? []);
        }

        $this->imageSettings = array_intersect_key($this->imageSettings, array_flip($vigentes));
        $this->selectedPreviewKey ??= array_key_first($this->imageSettings);
    }

    private function validarConfiguracion(): void
    {
        $this->validate([
            'marco' => ['nullable', Rule::exists('marcos', 'id')->where(fn ($query) => $query->where('activo', true)->whereNull('deleted_at'))],
            'orientationMode' => ['required', 'in:auto,desktop,mobile'],
            'squareMode' => ['required', 'in:desktop,mobile'],
            'missingFrameBehavior' => ['required', 'in:skip,alternate'],
            'fitMode' => ['required', 'in:cover,contain,blur'],
            'format' => ['required', 'in:jpg,png,webp,original'],
            'quality' => ['required', 'integer', 'between:60,100'],
            'renamePattern' => ['required', 'string', 'max:120'],
            'desktopWidth' => ['required', 'integer', 'between:320,6000'],
            'desktopHeight' => ['required', 'integer', 'between:320,6000'],
            'mobileWidth' => ['required', 'integer', 'between:320,6000'],
            'mobileHeight' => ['required', 'integer', 'between:320,6000'],
        ]);

        foreach ($this->imageSettings as $key => $settings) {
            if (! in_array($settings['orientation'] ?? 'inherit', ['inherit', 'desktop', 'mobile'], true)) {
                $this->addError("imageSettings.{$key}.orientation", 'Orientación no válida.');
            }
            if (! in_array($settings['fit'] ?? 'inherit', ['inherit', 'cover', 'contain', 'blur'], true)) {
                $this->addError("imageSettings.{$key}.fit", 'Ajuste no válido.');
            }
            $frameSelection = $settings['frame'] ?? 'global';
            if (! in_array($frameSelection, ['global', 'none'], true)) {
                $validFrame = is_numeric($frameSelection)
                    && Marco::query()->whereKey((int) $frameSelection)->where('activo', true)->exists();
                if (! $validFrame) {
                    $this->addError("imageSettings.{$key}.frame", 'El marco individual ya no está disponible.');
                }
            }
            if (! in_array($settings['focus'] ?? 'center', ['center', 'top', 'bottom', 'left', 'right', 'top-left', 'top-right', 'bottom-left', 'bottom-right'], true)) {
                $this->addError("imageSettings.{$key}.focus", 'Enfoque no válido.');
            }
            $focusX = (int) ($settings['focus_x'] ?? 50);
            $focusY = (int) ($settings['focus_y'] ?? 50);
            if ($focusX < 0 || $focusX > 100 || $focusY < 0 || $focusY > 100) {
                $this->addError("imageSettings.{$key}.focus", 'El punto focal no es válido.');
            }

            $zoom = (int) ($settings['zoom'] ?? 100);
            if ($zoom < 100 || $zoom > 180) {
                $this->addError("imageSettings.{$key}.zoom", 'El zoom debe estar entre 100% y 180%.');
            }
        }

        if ($this->getErrorBag()->isNotEmpty()) {
            throw \Illuminate\Validation\ValidationException::withMessages($this->getErrorBag()->toArray());
        }
    }

    public function submit()
    {
        @set_time_limit(0);
        $this->validate();
        $this->sincronizarConfiguracionImagenes();
        $this->validarConfiguracion();

        $pipeline = new ImagePipeline();
        $manager = $pipeline->manager();
        $marcos = Marco::query()->where('activo', true)->get()->keyBy('id');

        $tmpZipPath = tempnam(sys_get_temp_dir(), 'minisystems_images_');
        abort_if($tmpZipPath === false, 500, 'No se pudo crear el archivo temporal.');

        $zip = new ZipArchive();
        if ($zip->open($tmpZipPath, ZipArchive::OVERWRITE) !== true) {
            @unlink($tmpZipPath);
            abort(500, 'No se pudo abrir el ZIP temporal.');
        }

        $manifest = [
            'generated_at' => now()->toIso8601String(),
            'settings' => [
                'orientation_mode' => $this->orientationMode,
                'square_mode' => $this->squareMode,
                'fit_mode' => $this->fitMode,
                'format' => $this->format,
                'quality' => $this->quality,
                'desktop' => "{$this->desktopWidth}x{$this->desktopHeight}",
                'mobile' => "{$this->mobileWidth}x{$this->mobileHeight}",
            ],
            'files' => [],
        ];

        $added = 0;
        $usage = [];
        $usedPaths = [];

        foreach ($this->images as $position => $file) {
            $index = $position + 1;
            $key = sha1((string) $file->getFilename());
            $settings = $this->imageSettings[$key] ?? [];
            $original = $file->getClientOriginalName();

            try {
                if (! @getimagesize($file->getRealPath())) {
                    throw new \RuntimeException('El archivo no es una imagen válida.');
                }

                $source = $manager->read($file->getRealPath());
                $pipeline->autorotate($file->getRealPath(), $source);
                $sourceWidth = $source->width();
                $sourceHeight = $source->height();
                unset($source);

                $orientation = $this->resolverOrientacion($settings, $sourceWidth, $sourceHeight);
                [$targetW, $targetH] = $orientation === 'mobile'
                    ? [$this->mobileWidth, $this->mobileHeight]
                    : [$this->desktopWidth, $this->desktopHeight];

                $fit = ($settings['fit'] ?? 'inherit') === 'inherit' ? $this->fitMode : $settings['fit'];
                $focus = $settings['focus'] ?? 'center';
                $focusX = (int) ($settings['focus_x'] ?? 50);
                $focusY = (int) ($settings['focus_y'] ?? 50);
                $zoom = (int) ($settings['zoom'] ?? 100);

                $image = $this->ajustarImagen(
                    pipeline: $pipeline,
                    path: $file->getRealPath(),
                    width: $targetW,
                    height: $targetH,
                    fit: $fit,
                    focus: $focus,
                    focusX: $focusX,
                    focusY: $focusY,
                    zoom: $zoom,
                );

                $frameInfo = $this->resolverMarco($settings, $orientation, $marcos);
                $warnings = [];

                if ($frameInfo['path']) {
                    $frame = $manager->read($frameInfo['path'])->resize($targetW, $targetH);
                    $image->place($frame, 'top-left', 0, 0);
                    $usage[$frameInfo['id']] = ($usage[$frameInfo['id']] ?? 0) + 1;
                    if ($frameInfo['alternate']) {
                        $warnings[] = 'Se utilizó la orientación alterna del marco.';
                    }
                } elseif ($frameInfo['requested']) {
                    $warnings[] = 'El marco no tenía una versión disponible para esta orientación; se procesó sin marco.';
                }

                $extension = $this->resolverFormatoSalida($settings['extension'] ?? $file->getClientOriginalExtension());
                try {
                    $encoder = $pipeline->encoderFor($extension, $this->quality);
                    $encoded = $image->encode($encoder);
                } catch (\Throwable $encodingError) {
                    if ($extension === 'jpg') {
                        throw $encodingError;
                    }
                    $warnings[] = "El servidor no pudo generar {$extension}; se utilizó JPG como respaldo.";
                    $extension = 'jpg';
                    $encoded = $image->encode($pipeline->encoderFor('jpg', min(92, $this->quality)));
                }

                $baseName = $this->buildOutName($index, $original, $orientation);
                $folder = $this->organizeFolders ? $orientation.'/' : '';
                $outPath = $folder.$baseName.'.'.$extension;
                if (isset($usedPaths[$outPath])) {
                    $outPath = $folder.$baseName.'_'.str_pad((string) $index, 3, '0', STR_PAD_LEFT).'.'.$extension;
                }
                $usedPaths[$outPath] = true;
                if (! $zip->addFromString($outPath, (string) $encoded)) {
                    throw new \RuntimeException('No se pudo agregar la imagen al ZIP.');
                }
                $added++;

                $manifest['files'][] = [
                    'index' => $index,
                    'original' => $original,
                    'source_dimensions' => "{$sourceWidth}x{$sourceHeight}",
                    'detected_orientation' => $sourceWidth === $sourceHeight ? 'square' : ($sourceWidth > $sourceHeight ? 'desktop' : 'mobile'),
                    'output_orientation' => $orientation,
                    'output_dimensions' => "{$targetW}x{$targetH}",
                    'fit' => $fit,
                    'focus' => $focus,
                    'focus_x' => $focusX,
                    'focus_y' => $focusY,
                    'zoom' => $zoom,
                    'frame_id' => $frameInfo['id'],
                    'frame_file' => $frameInfo['file'],
                    'output' => $outPath,
                    'format' => $extension,
                    'quality' => $this->quality,
                    'warnings' => $warnings,
                ];
            } catch (\Throwable $e) {
                $manifest['files'][] = [
                    'index' => $index,
                    'original' => $original,
                    'error' => $e->getMessage(),
                ];
            }
        }

        if ($added === 0) {
            $zip->close();
            @unlink($tmpZipPath);
            $this->addError('images', 'No se pudo procesar ninguna imagen. Revisa los formatos y vuelve a intentarlo.');
            $this->paso = 1;
            return null;
        }

        $zip->addFromString('manifest.json', json_encode($manifest, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES));
        $zip->close();

        foreach ($usage as $marcoId => $cantidad) {
            Marco::whereKey($marcoId)->increment('usos', $cantidad, ['ultimo_uso_at' => now()]);
        }

        $zipFilename = 'imagenes_procesadas_'.now()->format('Ymd_His').'.zip';

        return response()->download($tmpZipPath, $zipFilename)->deleteFileAfterSend(true);
    }

    private function resolverOrientacion(array $settings, int $width, int $height): string
    {
        $individual = $settings['orientation'] ?? 'inherit';
        if (in_array($individual, ['desktop', 'mobile'], true)) {
            return $individual;
        }

        if (in_array($this->orientationMode, ['desktop', 'mobile'], true)) {
            return $this->orientationMode;
        }

        if ($width === $height) {
            return $this->squareMode;
        }

        return $width > $height ? 'desktop' : 'mobile';
    }

    private function ajustarImagen(
        ImagePipeline $pipeline,
        string $path,
        int $width,
        int $height,
        string $fit,
        string $focus,
        int $focusX,
        int $focusY,
        int $zoom,
    ) {
        $manager = $pipeline->manager();

        if ($fit === 'contain') {
            $image = $manager->read($path);
            $pipeline->autorotate($path, $image);
            return $image->contain($width, $height, 'ffffff', $focus);
        }

        if ($fit === 'blur') {
            $background = $manager->read($path);
            $pipeline->autorotate($path, $background);
            $this->coverConPuntoFocal($background, $width, $height, $focusX, $focusY, 100)->blur(28);

            $foreground = $manager->read($path);
            $pipeline->autorotate($path, $foreground);
            $foreground->scale((int) round($width * 0.94), (int) round($height * 0.94));
            $background->place($foreground, 'center', 0, 0);

            return $background;
        }

        $image = $manager->read($path);
        $pipeline->autorotate($path, $image);

        return $this->coverConPuntoFocal($image, $width, $height, $focusX, $focusY, $zoom);
    }

    private function coverConPuntoFocal($image, int $width, int $height, int $focusX, int $focusY, int $zoom)
    {
        $sourceWidth = max(1, $image->width());
        $sourceHeight = max(1, $image->height());
        $zoom = max(100, min(180, $zoom));
        $scale = max($width / $sourceWidth, $height / $sourceHeight) * ($zoom / 100);
        $resizedWidth = max($width, (int) ceil($sourceWidth * $scale));
        $resizedHeight = max($height, (int) ceil($sourceHeight * $scale));

        $image->resize($resizedWidth, $resizedHeight);

        $maxX = max(0, $resizedWidth - $width);
        $maxY = max(0, $resizedHeight - $height);
        $offsetX = (int) round($maxX * (max(0, min(100, $focusX)) / 100));
        $offsetY = (int) round($maxY * (max(0, min(100, $focusY)) / 100));

        return $image->crop($width, $height, $offsetX, $offsetY);
    }

    private function resolverMarco(array $settings, string $orientation, $marcos): array
    {
        $selection = $settings['frame'] ?? 'global';
        $frameId = $selection === 'global'
            ? $this->marco
            : ($selection === 'none' ? null : (int) $selection);

        $result = [
            'requested' => (bool) $frameId,
            'id' => null,
            'file' => null,
            'path' => null,
            'alternate' => false,
        ];

        if (! $frameId || ! ($marco = $marcos->get($frameId))) {
            return $result;
        }

        $file = $marco->archivoPara($orientation);
        $alternate = false;

        if (! $file && $this->missingFrameBehavior === 'alternate') {
            $file = $marco->archivoAlterno($orientation);
            $alternate = (bool) $file;
        }

        if (! $file) {
            return $result;
        }

        $path = storage_path('app/public/imagenesMarcos/'.$file);
        if (! is_file($path)) {
            return $result;
        }

        return [
            'requested' => true,
            'id' => $marco->id,
            'file' => 'imagenesMarcos/'.$file,
            'path' => $path,
            'alternate' => $alternate,
        ];
    }

    private function resolverFormatoSalida(?string $original): string
    {
        if ($this->format !== 'original') {
            return $this->format;
        }

        $ext = strtolower((string) $original);
        return match ($ext) {
            'png' => 'png',
            'webp' => 'webp',
            default => 'jpg',
        };
    }

    private function safeBaseName(string $name): string
    {
        return Str::of(pathinfo($name, PATHINFO_FILENAME))->slug('_')->toString();
    }

    private function buildOutName(int $index, string $original, string $orientation): string
    {
        $replacement = [
            '{index}' => str_pad((string) $index, 3, '0', STR_PAD_LEFT),
            '{date}' => now()->format('Ymd'),
            '{orig}' => $this->safeBaseName($original),
            '{orientation}' => $orientation,
        ];

        $name = strtr($this->renamePattern, $replacement);
        $name = trim((string) preg_replace('/[^a-zA-Z0-9_\-]+/', '_', $name), '_-');

        return $name !== '' ? $name : 'imagen_'.str_pad((string) $index, 3, '0', STR_PAD_LEFT);
    }

    public function render()
    {
        return view('livewire.images.creacion-imagenes', [
            'marcos' => Marco::query()->where('activo', true)->orderBy('orden')->orderBy('nombre')->get(),
        ]);
    }
}
