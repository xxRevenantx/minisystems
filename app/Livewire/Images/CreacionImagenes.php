<?php

namespace App\Livewire\Images;

use Livewire\Component;
use Livewire\WithFileUploads;
use Illuminate\Validation\Rules\File;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use ZipArchive;

// Intervention Image v3
use Intervention\Image\ImageManager;
use Intervention\Image\Drivers\Gd\Driver;     // Usa Imagick si lo prefieres
use Intervention\Image\Encoders\JpegEncoder;  // Salida JPG

class CreacionImagenes extends Component
{
    use WithFileUploads;

    /** @var array<\Livewire\Features\FileUploads\TemporaryUploadedFile> */
    public array $images = [];

    protected function rules(): array
    {
        return [
            'images'   => ['required', 'array', 'max:100'],
            'images.*' => [
                'required',
                File::image()->types(['jpg','jpeg','png','webp'])->max(20 * 1024),
            ],
        ];
    }

    /* ===========================
     * Drag & drop (reordenar)
     * =========================== */

    /** Reordena $images según una lista de IDs (filename temporal de cada archivo). */
    public function reorder(array $orderedIds): void
    {
        // Mapa por filename temporal
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

        // Adjunta los que quedaron (por si hay diferencias)
        foreach ($byId as $left) {
            $reordered[] = $left;
        }

        $this->images = array_values($reordered);
    }

    /** Eliminar por filename temporal (seguro aunque cambie el orden). */
    public function removeByTemp(string $tempName): void
    {
        $this->images = array_values(array_filter(
            $this->images,
            fn ($f) => (string) $f->getFilename() !== $tempName
        ));
    }

    /** Eliminar por índice (opcional, si lo usas). */
    public function remove(int $index): void
    {
        if (!isset($this->images[$index])) return;
        unset($this->images[$index]);
        $this->images = array_values($this->images);
    }

    /* ===========================
     * Generación del ZIP
     * =========================== */
    public function submit(): BinaryFileResponse
    {
        $this->validate();

        // Archivo ZIP temporal
        $zipFilename = 'imagenes_' . now()->format('Ymd_His') . '.zip';
        $tmpZipPath  = tempnam(sys_get_temp_dir(), 'zip_');
        if ($tmpZipPath === false) abort(500, 'No se pudo crear un archivo temporal.');

        $zip = new ZipArchive();
        if ($zip->open($tmpZipPath, ZipArchive::OVERWRITE) !== true) {
            @unlink($tmpZipPath);
            abort(500, 'No se pudo abrir el ZIP temporal.');
        }

        // Manager de imágenes
        $manager = new ImageManager(new Driver());

        // Marco PNG con transparencia (colócalo en public/frames/marco.png)
        $framePath = public_path('frames/marco.png');
        if (!is_file($framePath)) {
            $zip->close();
            @unlink($tmpZipPath);
            abort(500, 'No se encontró el marco en public/frames/marco.png');
        }

        $targetW = 2048;
        $targetH = 1365;
        $i = 1;

        foreach ($this->images as $file) {
            // 1) Leer imagen
            $img = $manager->read($file->getRealPath());

            // 2) Ajustar a 2048x1365 (recorta si hace falta)
            $img = $img->cover($targetW, $targetH);

            // 3) Leer marco y redimensionar (no usamos clone())
            $frame = $manager->read($framePath)->resize($targetW, $targetH);

            // 4) Superponer marco
            $img->place($frame, 'top-left', 0, 0);

            // 5) Codificar a JPG (calidad 85)
            $encoded = $img->encode(new JpegEncoder(quality: 85));

            // 6) Añadir al ZIP
            $zipName = sprintf('foto_%d.jpg', $i++);
            $zip->addFromString($zipName, (string) $encoded);
        }

        $zip->close();

        // Limpieza de temporales de Livewire
        try {
            foreach ($this->images as $f) {
                @unlink($f->getRealPath());
            }
        } catch (\Throwable $e) {
            // silencioso
        }

        // Limpiar selección (opcional)
        $this->images = [];

        // Descargar y eliminar ZIP
        return response()->download($tmpZipPath, $zipFilename)->deleteFileAfterSend(true);
    }

    public function render()
    {
        return view('livewire.images.creacion-imagenes');
    }
}
