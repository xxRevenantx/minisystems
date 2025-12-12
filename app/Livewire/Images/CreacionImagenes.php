<?php

namespace App\Livewire\Images;

use App\Models\Marco;
use Livewire\Component;
use Livewire\WithFileUploads;
use Illuminate\Validation\Rules\File;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use ZipArchive;
use App\Services\ImagePipeline;
use Illuminate\Support\Str;

class CreacionImagenes extends Component
{
    use WithFileUploads;

    /** @var array<\Livewire\Features\FileUploads\TemporaryUploadedFile> */
    public array $images = [];

    // Opciones de salida (fijas)
    public string $format = 'jpg';      // jpg|webp|avif
    public int $quality = 85;           // 60-100
    public string $preset = '1600x1067';
    public string $renamePattern = 'foto_{index}';

    // Dispositivo / orientación
    public string $device = 'desktop';  // desktop | mobile

    // Marco seleccionado
    public ?int $marco = null;          // ID del marco
    public ?string $frameName = null;   // ej. "imagenesMarcos/G9kxjC9....png"

    // Watermark (por ahora desactivado)
    public bool $addWatermark = false;
    public ?string $watermarkPath = null;
    public int $watermarkMargin = 24;

    protected function rules(): array
    {
        return [
            'images'   => ['required', 'array', 'max:100'],
            'images.*' => [
                'required',
                File::image()->types(['jpg', 'jpeg', 'png', 'webp'])->max(20 * 1024),
            ],
            'format'        => ['required', 'in:jpg,webp,avif'],
            'quality'       => ['required', 'integer', 'between:60,90'],
            'preset'        => ['required', 'regex:/^\d+x\d+$/'],
            'renamePattern' => ['required', 'string', 'max:120'],
            'marco'         => ['required', 'exists:marcos,id'],
            'device'        => ['required', 'in:desktop,mobile'],
        ];
    }

    /**
     * Cuando se selecciona un marco en el select.
     * Se guarda la ruta relativa para el preview.
     */
    public function updatedMarco($value): void
    {
        $this->frameName = null;

        if (!$value) {
            return;
        }

        $marco = Marco::find($value);

        if ($marco && $marco->marco) {
            // El archivo del marco está en: asset('storage/imagenesMarcos/'.$marco->marco)
            // Guardamos la parte relativa a /storage
            $this->frameName = 'imagenesMarcos/' . $marco->marco;
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
        foreach ($byId as $left) $reordered[] = $left;
        $this->images = array_values($reordered);
    }

    public function removeByTemp(string $tempName): void
    {
        $this->images = array_values(array_filter(
            $this->images,
            fn($f) => (string) $f->getFilename() !== $tempName
        ));
    }

    public function remove(int $index): void
    {
        if (!isset($this->images[$index])) return;
        unset($this->images[$index]);
        $this->images = array_values($this->images);
    }

    private function parsePreset(string $preset): array
    {
        [$w, $h] = array_map('intval', explode('x', $preset));
        return [$w, $h];
    }

    private function safeBaseName(string $name): string
    {
        $base = pathinfo($name, PATHINFO_FILENAME);
        return Str::of($base)->slug('_')->toString();
    }

    private function buildOutName(int $index, string $originalBase): string
    {
        $date = now()->format('Ymd');
        $orig = $this->safeBaseName($originalBase);
        $name = str_replace(['{index}', '{date}', '{orig}'], [$index, $date, $orig], $this->renamePattern);
        $name = trim(preg_replace('/[^a-zA-Z0-9_\-]+/', '_', $name), '_-');
        return $name !== '' ? $name : 'foto_' . $index;
    }

    public function submit(): BinaryFileResponse
    {
        $this->validate();

        // Ajustar preset según el tipo de dispositivo
        // Desktop: 1600x1067 (horizontal)
        // Mobile:  1067x1600 (vertical)
        if ($this->device === 'mobile') {
            $this->preset = '1067x1600';   // vertical
        } else {
            $this->preset = '1600x1067';   // horizontal
        }

        $pipeline = new ImagePipeline();
        $manager  = $pipeline->manager();

        // ===== Resolver marco seleccionado (para procesamiento) =====
        $framePath = null;
        $frameName = null;

        if ($this->marco) {
            $marco = Marco::find($this->marco);

            if ($marco && $marco->marco) {
                // La URL pública es: asset('storage/imagenesMarcos/'.$marco->marco)
                // Físicamente está en: storage/app/public/imagenesMarcos/...
                $relativePath = 'imagenesMarcos/' . $marco->marco;
                $candidate    = storage_path('app/public/' . $relativePath);

                if (is_file($candidate)) {
                    $framePath       = $candidate;     // ruta absoluta para Intervention
                    $frameName       = $relativePath;  // para manifest
                    $this->frameName = $relativePath;  // por si venimos directo de submit
                }
            }
        }

        $hasFrame = $framePath && is_file($framePath);

        // Watermark opcional (por ahora sin usar, pero se deja la lógica)
        $wmPath = ($this->addWatermark && $this->watermarkPath)
            ? $this->watermarkPath
            : null;

        $hasWm  = $wmPath && is_file($wmPath);

        [$targetW, $targetH] = $this->parsePreset($this->preset);

        $zipFilename = 'imagenes_' . now()->format('Ymd_His') . '.zip';
        $tmpZipPath  = tempnam(sys_get_temp_dir(), 'zip_');
        if ($tmpZipPath === false) abort(500, 'No se pudo crear temporal.');

        $zip = new ZipArchive();
        if ($zip->open($tmpZipPath, ZipArchive::OVERWRITE) !== true) {
            @unlink($tmpZipPath);
            abort(500, 'No se pudo abrir el ZIP temporal.');
        }

        $manifest = [];
        $added = 0;
        $i = 1;

        foreach ($this->images as $file) {
            $origName = $file->getClientOriginalName();
            $baseName = $this->buildOutName($i, $origName);
            $ext      = $this->format;

            try {
                if (!@getimagesize($file->getRealPath())) {
                    throw new \RuntimeException('Archivo no es una imagen válida.');
                }

                // Leemos la imagen tal cual, SIN rotarla
                $img = $manager->read($file->getRealPath());

                // Recortamos al tamaño objetivo (desktop o móvil) sin cambiar orientación
                $img = $img->cover($targetW, $targetH);

                // ===== APLICAR MARCO SELECCIONADO =====
                if ($hasFrame) {
                    $frame = $manager->read($framePath)->resize($targetW, $targetH);
                    $img->place($frame, 'top-left', 0, 0);
                }

                // ===== Watermark opcional =====
                if ($hasWm) {
                    $watermark = $manager->read($wmPath);
                    $wmTargetW = max(64, (int) round($targetW * 0.15));
                    $watermark->scaleDown(width: $wmTargetW);
                    $img->place($watermark, 'bottom-right', $this->watermarkMargin, $this->watermarkMargin);
                }

                // Solo encoder de la imagen completa (sin thumb)
                try {
                    $encoder     = $pipeline->encoderFor($this->format, $this->quality);
                    $encodedFull = $img->encode($encoder);
                } catch (\Throwable $e) {
                    $encoder     = $pipeline->encoderFor('jpg', min(90, $this->quality));
                    $ext         = 'jpg';
                    $encodedFull = $img->encode($encoder);
                }

                // Agregamos solo la imagen full al ZIP
                $zip->addFromString("full/{$baseName}.{$ext}", (string) $encodedFull);
                $added++;

                // Manifest sin out_thumb
                $manifest[] = [
                    'index'     => $i,
                    'original'  => $origName,
                    'out_full'  => "full/{$baseName}.{$ext}",
                    'preset'    => $this->preset,
                    'frame'     => $hasFrame ? $frameName : null,
                    'watermark' => $hasWm ? basename($wmPath) : null,
                    'format'    => $ext,
                    'quality'   => $this->quality,
                ];
            } catch (\Throwable $e) {
                $manifest[] = [
                    'index'    => $i,
                    'original' => $origName,
                    'error'    => $e->getMessage(),
                ];
            }

            $i++;
        }

        if ($added === 0) {
            $zip->close();
            @unlink($tmpZipPath);
            $this->addError('images', 'No se pudo procesar ninguna imagen.');
            return back();
        }

        $zip->addFromString('manifest.json', json_encode($manifest, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
        $zip->close();

        try {
            foreach ($this->images as $f) {
                @unlink($f->getRealPath());
            }
        } catch (\Throwable $e) {
        }

        $this->images = [];

        return response()->download($tmpZipPath, $zipFilename)->deleteFileAfterSend(true);
    }

    public function render()
    {
        $marcos = Marco::all();

        return view('livewire.images.creacion-imagenes', [
            'marcos' => $marcos,
        ]);
    }
}
