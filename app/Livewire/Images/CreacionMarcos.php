<?php

namespace App\Livewire\Images;

use App\Models\Marco;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Validation\Rules\File;
use Livewire\Component;
use Livewire\WithFileUploads;

class CreacionMarcos extends Component
{
    use WithFileUploads;

    public $marcoDesktop;
    public $marcoMobile;

    public ?int $editandoId = null;
    public string $nombre = '';
    public string $descripcion = '';
    public string $categoria = 'General';
    public string $tagsTexto = '';
    public string $notas = '';
    public bool $activo = true;
    public bool $quitarDesktop = false;
    public bool $quitarMobile = false;

    public string $buscar = '';
    public string $filtroCategoria = '';
    public string $filtroEstado = 'todos';

    protected function rules(): array
    {
        return [
            'nombre' => ['required', 'string', 'max:120'],
            'descripcion' => ['nullable', 'string', 'max:500'],
            'categoria' => ['required', 'string', 'max:80'],
            'tagsTexto' => ['nullable', 'string', 'max:500'],
            'notas' => ['nullable', 'string', 'max:2000'],
            'activo' => ['boolean'],
            'marcoDesktop' => ['nullable', File::image()->types(['jpg', 'jpeg', 'png', 'webp'])->max(20 * 1024)],
            'marcoMobile' => ['nullable', File::image()->types(['jpg', 'jpeg', 'png', 'webp'])->max(20 * 1024)],
            'quitarDesktop' => ['boolean'],
            'quitarMobile' => ['boolean'],
        ];
    }

    public function updatedMarcoDesktop(): void
    {
        $this->resetValidation('marcoDesktop');
        $this->quitarDesktop = false;
    }

    public function updatedMarcoMobile(): void
    {
        $this->resetValidation('marcoMobile');
        $this->quitarMobile = false;
    }

    protected function messages(): array
    {
        return [
            'nombre.required' => 'Escribe el nombre del marco.',
            'categoria.required' => 'Selecciona o escribe una categoría.',
            'marcoDesktop.image' => 'El archivo desktop debe ser una imagen válida.',
            'marcoMobile.image' => 'El archivo móvil debe ser una imagen válida.',
        ];
    }

    public function guardarMarco(): void
    {
        $this->validate();

        if ($this->marcoDesktop && ! $this->orientacionValida($this->marcoDesktop->getRealPath(), 'desktop')) {
            $this->addError('marcoDesktop', 'La versión desktop debe ser horizontal (ancho mayor que alto).');
        }
        if ($this->marcoMobile && ! $this->orientacionValida($this->marcoMobile->getRealPath(), 'mobile')) {
            $this->addError('marcoMobile', 'La versión móvil debe ser vertical (alto mayor que ancho).');
        }
        if ($this->getErrorBag()->isNotEmpty()) {
            return;
        }

        $marco = $this->editandoId ? Marco::findOrFail($this->editandoId) : new Marco();

        $desktopOriginal = $marco->marco_desktop ?: $marco->marco;
        $mobileOriginal = $marco->marco_mobile;
        $desktopActual = $this->quitarDesktop ? null : $desktopOriginal;
        $mobileActual = $this->quitarMobile ? null : $mobileOriginal;

        if (! $this->marcoDesktop && ! $this->marcoMobile && ! $desktopActual && ! $mobileActual) {
            $this->addError('marcoDesktop', 'Sube al menos una versión del marco: desktop o móvil.');
            return;
        }

        $desktop = $this->marcoDesktop
            ? $this->guardarArchivo($this->marcoDesktop, 'desktop')
            : $this->metadatosExistentes($desktopActual, $marco, 'desktop');

        $mobile = $this->marcoMobile
            ? $this->guardarArchivo($this->marcoMobile, 'mobile')
            : $this->metadatosExistentes($mobileActual, $marco, 'mobile');

        $archivosParaEliminar = [];
        if (($this->marcoDesktop || $this->quitarDesktop) && $desktopOriginal && $desktopOriginal !== $desktop['archivo']) {
            $archivosParaEliminar[] = $desktopOriginal;
        }
        if (($this->marcoMobile || $this->quitarMobile) && $mobileOriginal && $mobileOriginal !== $mobile['archivo']) {
            $archivosParaEliminar[] = $mobileOriginal;
        }

        $esNuevo = ! $marco->exists;
        $legacy = $desktop['archivo'] ?: $mobile['archivo'];

        $marco->fill([
            'nombre' => trim($this->nombre),
            'descripcion' => trim($this->descripcion),
            'categoria' => trim($this->categoria) ?: 'General',
            'activo' => $this->activo,
            'marco' => $legacy,
            'marco_desktop' => $desktop['archivo'],
            'marco_mobile' => $mobile['archivo'],
            'ancho_desktop' => $desktop['ancho'],
            'alto_desktop' => $desktop['alto'],
            'ancho_mobile' => $mobile['ancho'],
            'alto_mobile' => $mobile['alto'],
            'formato_desktop' => $desktop['formato'],
            'formato_mobile' => $mobile['formato'],
            'transparencia_desktop' => $desktop['transparencia'],
            'transparencia_mobile' => $mobile['transparencia'],
            'tags' => $this->normalizarTags($this->tagsTexto),
            'notas' => trim($this->notas) ?: null,
        ]);

        if ($esNuevo) {
            $marco->orden = (int) Marco::max('orden') + 1;
        }

        $marco->save();

        foreach (array_unique($archivosParaEliminar) as $archivoAnterior) {
            $this->eliminarArchivoSiNoSeUsa($archivoAnterior, $marco->id);
        }

        $this->limpiarFormulario();

        $this->dispatch('swal', [
            'title' => $esNuevo ? 'Marco creado correctamente' : 'Marco actualizado correctamente',
            'icon' => 'success',
            'position' => 'top-end',
        ]);
    }

    public function editarMarco(int $id): void
    {
        $marco = Marco::findOrFail($id);

        $this->editandoId = $marco->id;
        $this->nombre = $marco->nombre ?: $marco->descripcion;
        $this->descripcion = $marco->descripcion ?? '';
        $this->categoria = $marco->categoria ?: 'General';
        $this->tagsTexto = implode(', ', $marco->tags ?? []);
        $this->notas = $marco->notas ?? '';
        $this->activo = (bool) $marco->activo;
        $this->marcoDesktop = null;
        $this->marcoMobile = null;
        $this->quitarDesktop = false;
        $this->quitarMobile = false;
        $this->resetValidation();

        $this->dispatch('marco-form-focus');
    }

    public function cancelarEdicion(): void
    {
        $this->limpiarFormulario();
    }

    public function alternarEstado(int $id): void
    {
        $marco = Marco::findOrFail($id);
        $marco->update(['activo' => ! $marco->activo]);

        $this->dispatch('swal', [
            'title' => $marco->activo ? 'Marco activado' : 'Marco desactivado',
            'icon' => 'success',
            'position' => 'top-end',
        ]);
    }

    public function duplicarMarco(int $id): void
    {
        $origen = Marco::findOrFail($id);
        $desktop = $this->copiarArchivo($origen->marco_desktop ?: $origen->marco, 'desktop');
        $mobile = $this->copiarArchivo($origen->marco_mobile, 'mobile');

        if (! $desktop && ! $mobile) {
            $this->dispatch('swal', [
                'title' => 'No se pudo duplicar: no se encontraron los archivos del marco.',
                'icon' => 'error',
                'position' => 'top-end',
            ]);
            return;
        }

        $copia = $origen->replicate([
            'usos',
            'ultimo_uso_at',
            'created_at',
            'updated_at',
            'deleted_at',
        ]);

        $copia->nombre = ($origen->nombre ?: $origen->descripcion).' (copia)';
        $copia->marco_desktop = $desktop;
        $copia->marco_mobile = $mobile;
        $copia->marco = $desktop ?: $mobile;
        $copia->activo = false;
        $copia->orden = (int) Marco::max('orden') + 1;
        $copia->usos = 0;
        $copia->ultimo_uso_at = null;
        $copia->save();

        $this->dispatch('swal', [
            'title' => 'Marco duplicado. Se creó como inactivo.',
            'icon' => 'success',
            'position' => 'top-end',
        ]);
    }

    public function eliminarMarco(int $id): void
    {
        $marco = Marco::findOrFail($id);
        $marco->delete();

        $this->dispatch('swal', [
            'title' => 'Marco enviado a la papelera',
            'icon' => 'success',
            'position' => 'top-end',
        ]);
    }

    public function eliminarDefinitivamente(int $id): void
    {
        $marco = Marco::withTrashed()->findOrFail($id);
        $archivos = array_filter([$marco->marco_desktop ?: $marco->marco, $marco->marco_mobile]);

        foreach (array_unique($archivos) as $archivo) {
            $this->eliminarArchivoSiNoSeUsa($archivo, $marco->id, true);
        }

        $marco->forceDelete();

        $this->dispatch('swal', [
            'title' => 'Marco eliminado definitivamente',
            'icon' => 'success',
            'position' => 'top-end',
        ]);
    }

    public function restaurarMarco(int $id): void
    {
        $marco = Marco::onlyTrashed()->findOrFail($id);
        $marco->restore();

        $this->dispatch('swal', [
            'title' => 'Marco restaurado correctamente',
            'icon' => 'success',
            'position' => 'top-end',
        ]);
    }

    public function reordenarMarcos(array $ids): void
    {
        foreach ($ids as $orden => $id) {
            Marco::withTrashed()->whereKey((int) $id)->update(['orden' => $orden + 1]);
        }
    }

    private function orientacionValida(string $ruta, string $tipo): bool
    {
        [$ancho, $alto] = @getimagesize($ruta) ?: [0, 0];
        if ($ancho <= 0 || $alto <= 0) {
            return false;
        }

        return $tipo === 'desktop' ? $ancho > $alto : $alto > $ancho;
    }

    private function guardarArchivo($archivo, string $tipo): array
    {
        $extension = strtolower($archivo->getClientOriginalExtension() ?: 'png');
        $nombre = Str::uuid().'_'.$tipo.'.'.$extension;
        $archivo->storeAs('imagenesMarcos', $nombre, 'public');

        [$ancho, $alto] = @getimagesize($archivo->getRealPath()) ?: [null, null];

        return [
            'archivo' => $nombre,
            'ancho' => $ancho,
            'alto' => $alto,
            'formato' => $extension,
            'transparencia' => $this->tieneTransparencia($archivo->getRealPath(), $extension),
        ];
    }

    private function metadatosExistentes(?string $archivo, Marco $marco, string $tipo): array
    {
        if (! $archivo) {
            return ['archivo' => null, 'ancho' => null, 'alto' => null, 'formato' => null, 'transparencia' => null];
        }

        return [
            'archivo' => $archivo,
            'ancho' => $tipo === 'desktop' ? $marco->ancho_desktop : $marco->ancho_mobile,
            'alto' => $tipo === 'desktop' ? $marco->alto_desktop : $marco->alto_mobile,
            'formato' => $tipo === 'desktop' ? $marco->formato_desktop : $marco->formato_mobile,
            'transparencia' => $tipo === 'desktop' ? $marco->transparencia_desktop : $marco->transparencia_mobile,
        ];
    }

    private function copiarArchivo(?string $archivo, string $tipo): ?string
    {
        if (! $archivo || ! Storage::disk('public')->exists('imagenesMarcos/'.$archivo)) {
            return null;
        }

        $extension = pathinfo($archivo, PATHINFO_EXTENSION) ?: 'png';
        $nuevo = Str::uuid().'_'.$tipo.'.'.$extension;
        Storage::disk('public')->copy('imagenesMarcos/'.$archivo, 'imagenesMarcos/'.$nuevo);

        return $nuevo;
    }

    private function eliminarArchivoSiNoSeUsa(?string $archivo, ?int $exceptoId = null, bool $incluirEliminados = false): void
    {
        if (! $archivo) {
            return;
        }

        $query = Marco::withTrashed();
        $enUso = $query
            ->when($exceptoId, fn ($q) => $q->where('id', '!=', $exceptoId))
            ->where(function ($q) use ($archivo): void {
                $q->where('marco_desktop', $archivo)
                    ->orWhere('marco_mobile', $archivo)
                    ->orWhere('marco', $archivo);
            })
            ->exists();

        if (! $enUso) {
            Storage::disk('public')->delete('imagenesMarcos/'.$archivo);
        }
    }

    private function normalizarTags(string $texto): array
    {
        return collect(preg_split('/[,;]+/', $texto) ?: [])
            ->map(fn ($tag) => trim($tag))
            ->filter()
            ->unique(fn ($tag) => Str::lower($tag))
            ->take(12)
            ->values()
            ->all();
    }

    private function tieneTransparencia(string $ruta, string $extension): bool
    {
        if (! in_array($extension, ['png', 'webp'], true)) {
            return false;
        }

        if (extension_loaded('imagick')) {
            try {
                $imagen = new \Imagick($ruta);
                return $imagen->getImageAlphaChannel();
            } catch (\Throwable) {
                // Continúa con GD.
            }
        }

        if (! function_exists('imagecreatefrompng')) {
            return false;
        }

        try {
            $imagen = $extension === 'png'
                ? @imagecreatefrompng($ruta)
                : (function_exists('imagecreatefromwebp') ? @imagecreatefromwebp($ruta) : false);

            if (! $imagen) {
                return false;
            }

            $ancho = imagesx($imagen);
            $alto = imagesy($imagen);
            $pasoX = max(1, (int) floor($ancho / 50));
            $pasoY = max(1, (int) floor($alto / 50));

            for ($y = 0; $y < $alto; $y += $pasoY) {
                for ($x = 0; $x < $ancho; $x += $pasoX) {
                    $rgba = imagecolorat($imagen, $x, $y);
                    if ((($rgba & 0x7F000000) >> 24) > 0) {
                        imagedestroy($imagen);
                        return true;
                    }
                }
            }

            imagedestroy($imagen);
        } catch (\Throwable) {
            return false;
        }

        return false;
    }

    private function limpiarFormulario(): void
    {
        $this->reset([
            'marcoDesktop',
            'marcoMobile',
            'editandoId',
            'nombre',
            'descripcion',
            'tagsTexto',
            'notas',
            'quitarDesktop',
            'quitarMobile',
        ]);
        $this->categoria = 'General';
        $this->activo = true;
        $this->resetValidation();
    }

    public function render()
    {
        $query = $this->filtroEstado === 'papelera'
            ? Marco::onlyTrashed()
            : Marco::query();

        if (filled($this->buscar)) {
            $buscar = trim($this->buscar);
            $query->where(function ($q) use ($buscar): void {
                $q->where('nombre', 'like', "%{$buscar}%")
                    ->orWhere('descripcion', 'like', "%{$buscar}%")
                    ->orWhere('categoria', 'like', "%{$buscar}%");
            });
        }

        if (filled($this->filtroCategoria)) {
            $query->where('categoria', $this->filtroCategoria);
        }

        if ($this->filtroEstado === 'activos') {
            $query->where('activo', true);
        } elseif ($this->filtroEstado === 'inactivos') {
            $query->where('activo', false);
        } elseif ($this->filtroEstado === 'incompletos') {
            $query->where(function ($q): void {
                $q->whereNull('marco_desktop')->orWhereNull('marco_mobile');
            });
        }

        return view('livewire.images.creacion-marcos', [
            'marcos' => $query->orderBy('orden')->orderByDesc('created_at')->get(),
            'marcoEditando' => $this->editandoId ? Marco::find($this->editandoId) : null,
            'categorias' => Marco::withTrashed()->select('categoria')->distinct()->orderBy('categoria')->pluck('categoria'),
            'estadisticas' => [
                'total' => Marco::count(),
                'activos' => Marco::where('activo', true)->count(),
                'completos' => Marco::whereNotNull('marco_desktop')->whereNotNull('marco_mobile')->count(),
                'incompletos' => Marco::where(fn ($q) => $q->whereNull('marco_desktop')->orWhereNull('marco_mobile'))->count(),
                'papelera' => Marco::onlyTrashed()->count(),
            ],
        ]);
    }
}
