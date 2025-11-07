<?php

namespace App\Livewire\Images;

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

    // Opciones de salida
    public string $format = 'jpg'; // jpg|webp|avif
    public int $quality = 85;      // 60-95

    // Presets de tamaño (ancho x alto)
    public string $preset = '2048x1365';

    // Marcos
    public ?string $frameName = 'marco.png'; // null para sin marco

    // Watermark
    public bool $addWatermark = false;
    public ?string $watermarkPath = null; // p.ej. public_path('watermarks/logo.png')
    public int $watermarkMargin = 24;     // px desde borde

    // Renombrado masivo
    public string $renamePattern = 'foto_{index}'; // placeholders: {index}, {date}, {orig}

    protected function rules(): array
    {
        return [
            'images'   => ['required', 'array', 'max:100'],
            'images.*' => [
                'required',
                File::image()->types(['jpg','jpeg','png','webp'])->max(20 * 1024),
            ],
            'format'  => ['required', 'in:jpg,webp,avif'],
            'quality' => ['required','integer','between:60,95'],
            'preset'  => ['required','regex:/^\d+x\d+$/'],
            'renamePattern' => ['required','string','max:120'],
        ];
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
            fn ($f) => (string) $f->getFilename() !== $tempName
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
        $name = str_replace(['{index}','{date}','{orig}'], [$index, $date, $orig], $this->renamePattern);
        $name = trim(preg_replace('/[^a-zA-Z0-9_\-]+/','_', $name), '_-');
        return $name !== '' ? $name : 'foto_'.$index;
    }

    public function submit(): BinaryFileResponse
    {
        $this->validate();

        $pipeline = new ImagePipeline();
        $manager  = $pipeline->manager();

        $framePath = $this->frameName ? public_path('frames/'.$this->frameName) : null;
        $hasFrame  = $framePath && is_file($framePath);

        $wmPath = $this->addWatermark && $this->watermarkPath ? $this->watermarkPath : null;
        $hasWm  = $wmPath && is_file($wmPath);

        [$targetW, $targetH] = $this->parsePreset($this->preset);

        $zipFilename = 'imagenes_'.now()->format('Ymd_His').'.zip';
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

                $img = $manager->read($file->getRealPath());
                $pipeline->autorotate($file->getRealPath(), $img);
                $img = $img->cover($targetW, $targetH);

                if ($hasFrame) {
                    $frame = $manager->read($framePath)->resize($targetW, $targetH);
                    // sin opacidad
                    $img->place($frame, 'top-left', 0, 0);
                }

                if ($hasWm) {
                    $watermark = $manager->read($wmPath);
                    $wmTargetW = max(64, (int) round($targetW * 0.15));
                    $watermark->scaleDown(width: $wmTargetW);
                    $img->place($watermark, 'bottom-right', $this->watermarkMargin, $this->watermarkMargin);
                }

                // Encoder con fallback a JPG si falla
                try {
                    $encoder = $pipeline->encoderFor($this->format, $this->quality);
                    $encodedFull = $img->encode($encoder);
                    $thumb = $img->scaleDown(width: 512);
                    $encodedThumb = $thumb->encode($encoder);
                } catch (\Throwable $e) {
                    $encoder = $pipeline->encoderFor('jpg', min(90, $this->quality));
                    $ext = 'jpg';
                    $encodedFull = $img->encode($encoder);
                    $thumb = $img->scaleDown(width: 512);
                    $encodedThumb = $thumb->encode($encoder);
                }

                $zip->addFromString("full/{$baseName}.{$ext}", (string) $encodedFull);
                $zip->addFromString("thumbs/{$baseName}_512.{$ext}", (string) $encodedThumb);
                $added++;

                $manifest[] = [
                    'index'    => $i,
                    'original' => $origName,
                    'out_full' => "full/{$baseName}.{$ext}",
                    'out_thumb'=> "thumbs/{$baseName}_512.{$ext}",
                    'preset'   => $this->preset,
                    'frame'    => $hasFrame ? basename($framePath) : null,
                    'watermark'=> $hasWm ? basename($wmPath) : null,
                    'format'   => $ext,
                    'quality'  => $this->quality,
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

        $zip->addFromString('manifest.json', json_encode($manifest, JSON_PRETTY_PRINT|JSON_UNESCAPED_UNICODE));
        $zip->close();

        try {
            foreach ($this->images as $f) @unlink($f->getRealPath());
        } catch (\Throwable $e) {}

        $this->images = [];

        return response()->download($tmpZipPath, $zipFilename)->deleteFileAfterSend(true);
    }

    public function render()
    {
        return view('livewire.images.creacion-imagenes');
    }
}
