<?php

namespace App\Livewire\Creative;

use App\Models\ActividadCreativa;
use App\Models\ArchivoMultimedia;
use App\Models\HistorialExportacion;
use App\Models\Marca;
use App\Models\Persona;
use App\Models\PlantillaCreativa;
use App\Models\PlantillaVersion;
use App\Models\PresetSocial;
use App\Models\ProyectoCreativo;
use App\Models\PublicacionSocial;
use App\Models\RegistroValidacion;
use App\Models\SolicitudCreativa;
use App\Services\CreativeActivity;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\File;
use Livewire\Component;
use Livewire\WithFileUploads;
use ZipArchive;

class CreativeHub extends Component
{
    use WithFileUploads;

    public string $section = 'marcas';
    public string $buscar = '';
    public string $filtroEstado = 'todos';
    public string $filtroTipo = 'todos';

    public const SECTIONS = [
        'marcas','personas','proyectos','biblioteca','plantillas','generador',
        'solicitudes','publicaciones','presets','exportaciones','validaciones','actividad',
    ];

    // Marca
    public ?int $marcaId = null;
    public string $marcaNombre = '';
    public string $marcaTipo = 'cliente';
    public string $marcaContacto = '';
    public string $marcaEmail = '';
    public string $marcaTelefono = '';
    public string $marcaSitio = '';
    public string $marcaColorPrimario = '#006492';
    public string $marcaColorSecundario = '#88AC2E';
    public string $marcaNotas = '';
    public bool $marcaActivo = true;
    public $marcaLogo;
    public $marcaLogoSecundario;

    // Persona
    public ?int $personaId = null;
    public ?int $personaMarcaId = null;
    public string $personaTipo = 'contacto';
    public string $personaNombre = '';
    public string $personaCargo = '';
    public string $personaOrganizacion = '';
    public string $personaEmail = '';
    public string $personaTelefono = '';
    public string $personaIdentificador = '';
    public string $personaTags = '';
    public string $personaNotas = '';
    public bool $personaActivo = true;
    public $personaFoto;
    public $personasCsv;

    // Proyecto
    public ?int $proyectoId = null;
    public ?int $proyectoMarcaId = null;
    public string $proyectoNombre = '';
    public string $proyectoTipo = 'campaña';
    public string $proyectoEstado = 'borrador';
    public string $proyectoPrioridad = 'media';
    public string $proyectoInicio = '';
    public string $proyectoEntrega = '';
    public string $proyectoDescripcion = '';
    public string $proyectoTags = '';
    public array $proyectoPersonas = [];

    // Biblioteca
    public ?int $archivoId = null;
    public ?int $archivoMarcaId = null;
    public ?int $archivoProyectoId = null;
    public string $archivoNombre = '';
    public string $archivoCategoria = 'imagen';
    public string $archivoDescripcion = '';
    public string $archivoTags = '';
    public bool $archivoActivo = true;
    public $archivoUpload;

    // Plantilla
    public ?int $plantillaId = null;
    public ?int $plantillaMarcaId = null;
    public ?int $plantillaFondoId = null;
    public string $plantillaNombre = '';
    public string $plantillaTipo = 'general';
    public int $plantillaAncho = 1920;
    public int $plantillaAlto = 1080;
    public string $plantillaEstado = 'borrador';
    public string $plantillaDescripcion = '';
    public bool $plantillaActivo = true;
    public bool $plantillaParaImpresion = false;
    public float $plantillaSangradoMm = 3;
    public float $plantillaMargenSeguroMm = 5;
    public bool $plantillaMarcasCorte = false;
    public string $plantillaModoColor = 'rgb';
    public array $plantillaBloques = [];

    // Generador masivo
    public ?int $generadorPlantillaId = null;
    public ?int $generadorMarcaId = null;
    public ?int $generadorProyectoId = null;
    public string $generadorNombrePatron = '{nombre}_{index}';
    public $generadorCsv;
    public array $generadorFilas = [];
    public array $generadorCabeceras = [];

    // Solicitud
    public ?int $solicitudId = null;
    public ?int $solicitudMarcaId = null;
    public ?int $solicitudProyectoId = null;
    public string $solicitudTitulo = '';
    public string $solicitudTipo = 'diseño';
    public string $solicitudEstado = 'pendiente';
    public string $solicitudPrioridad = 'media';
    public string $solicitudSolicitante = '';
    public string $solicitudContacto = '';
    public string $solicitudEntrega = '';
    public string $solicitudDescripcion = '';
    public string $solicitudNotas = '';

    // Publicación
    public ?int $publicacionId = null;
    public ?int $publicacionMarcaId = null;
    public ?int $publicacionProyectoId = null;
    public ?int $publicacionArchivoId = null;
    public string $publicacionTitulo = '';
    public string $publicacionRed = 'Instagram';
    public string $publicacionEstado = 'borrador';
    public string $publicacionProgramada = '';
    public string $publicacionCopy = '';
    public string $publicacionHashtags = '';
    public string $publicacionUrl = '';
    public string $publicacionNotas = '';

    // Preset
    public ?int $presetId = null;
    public string $presetNombre = '';
    public string $presetRed = 'General';
    public int $presetAncho = 1080;
    public int $presetAlto = 1080;
    public string $presetDescripcion = '';
    public bool $presetActivo = true;

    // Validación
    public ?int $validacionId = null;
    public ?int $validacionPersonaId = null;
    public ?int $validacionProyectoId = null;
    public string $validacionCodigo = '';
    public string $validacionTipo = 'documento';
    public string $validacionTitulo = '';
    public string $validacionEstado = 'valido';
    public string $validacionEmitido = '';
    public string $validacionVence = '';
    public string $validacionDatos = '';
    public string $validacionNotas = '';

    public function mount(string $section = 'marcas'): void
    {
        abort_unless(in_array($section, self::SECTIONS, true), 404);
        $this->section = $section;
        $this->plantillaBloques = [$this->bloqueBase('texto')];
        $this->validacionEmitido = now()->format('Y-m-d');
    }

    public function updatedBuscar(): void
    {
        $this->resetValidation();
    }

    private function tags(string $texto): array
    {
        return collect(preg_split('/[,;\n]+/', $texto) ?: [])
            ->map(fn ($tag) => trim($tag))
            ->filter()
            ->unique()
            ->values()
            ->all();
    }

    private function alert(string $title, string $icon = 'success'): void
    {
        $this->dispatch('swal', [
            'title' => $title,
            'icon' => $icon,
            'position' => 'top-end',
        ]);
    }

    private function storePublicFile($file, string $folder): string
    {
        return $file->store($folder, 'public');
    }

    private function deletePublicFile(?string $path): void
    {
        if ($path && Storage::disk('public')->exists($path)) {
            Storage::disk('public')->delete($path);
        }
    }

    private function imageMetadata(string $path): array
    {
        $absolute = Storage::disk('public')->path($path);
        [$width, $height] = @getimagesize($absolute) ?: [null, null];
        $orientation = null;
        if ($width && $height) {
            $orientation = $width === $height ? 'cuadrada' : ($width > $height ? 'horizontal' : 'vertical');
        }

        return [
            'ancho' => $width,
            'alto' => $height,
            'orientacion' => $orientation,
            'transparencia' => $this->hasTransparency($absolute),
        ];
    }

    private function hasTransparency(string $path): ?bool
    {
        $extension = strtolower(pathinfo($path, PATHINFO_EXTENSION));
        if (! in_array($extension, ['png', 'webp'], true)) {
            return false;
        }

        try {
            $info = getimagesize($path);
            if (($info['mime'] ?? '') === 'image/png') {
                $image = @imagecreatefrompng($path);
            } elseif (($info['mime'] ?? '') === 'image/webp' && function_exists('imagecreatefromwebp')) {
                $image = @imagecreatefromwebp($path);
            } else {
                return null;
            }
            if (! $image) {
                return null;
            }
            $width = imagesx($image);
            $height = imagesy($image);
            $stepX = max(1, intdiv($width, 30));
            $stepY = max(1, intdiv($height, 30));
            for ($x = 0; $x < $width; $x += $stepX) {
                for ($y = 0; $y < $height; $y += $stepY) {
                    if ((imagecolorat($image, $x, $y) & 0x7F000000) >> 24 > 0) {
                        imagedestroy($image);
                        return true;
                    }
                }
            }
            imagedestroy($image);
            return false;
        } catch (\Throwable) {
            return null;
        }
    }

    // MARCAS
    public function guardarMarca(): void
    {
        $data = $this->validate([
            'marcaNombre' => ['required','string','max:160'],
            'marcaTipo' => ['required','in:cliente,institucion,evento,personal,otro'],
            'marcaContacto' => ['nullable','string','max:160'],
            'marcaEmail' => ['nullable','email','max:160'],
            'marcaTelefono' => ['nullable','string','max:40'],
            'marcaSitio' => ['nullable','url','max:255'],
            'marcaColorPrimario' => ['required','regex:/^#[0-9A-Fa-f]{6}$/'],
            'marcaColorSecundario' => ['required','regex:/^#[0-9A-Fa-f]{6}$/'],
            'marcaNotas' => ['nullable','string','max:3000'],
            'marcaActivo' => ['boolean'],
            'marcaLogo' => ['nullable', File::image()->types(['jpg','jpeg','png','webp'])->max(10 * 1024)],
            'marcaLogoSecundario' => ['nullable', File::image()->types(['jpg','jpeg','png','webp'])->max(10 * 1024)],
        ]);

        $marca = $this->marcaId ? Marca::findOrFail($this->marcaId) : new Marca();
        $isNew = ! $marca->exists;
        $logo = $marca->logo;
        $logoSecondary = $marca->logo_secundario;

        if ($this->marcaLogo) {
            $this->deletePublicFile($logo);
            $logo = $this->storePublicFile($this->marcaLogo, 'creative/brands');
        }
        if ($this->marcaLogoSecundario) {
            $this->deletePublicFile($logoSecondary);
            $logoSecondary = $this->storePublicFile($this->marcaLogoSecundario, 'creative/brands');
        }

        $slugBase = Str::slug($data['marcaNombre']) ?: 'marca';
        $slug = $slugBase;
        $suffix = 2;
        while (Marca::withTrashed()->where('slug', $slug)->when($marca->exists, fn ($q) => $q->where('id', '!=', $marca->id))->exists()) {
            $slug = $slugBase.'-'.$suffix++;
        }

        $marca->fill([
            'user_id' => $marca->user_id ?: auth()->id(),
            'nombre' => trim($data['marcaNombre']),
            'slug' => $slug,
            'tipo' => $data['marcaTipo'],
            'contacto' => trim($data['marcaContacto']) ?: null,
            'email' => trim($data['marcaEmail']) ?: null,
            'telefono' => trim($data['marcaTelefono']) ?: null,
            'sitio_web' => trim($data['marcaSitio']) ?: null,
            'logo' => $logo,
            'logo_secundario' => $logoSecondary,
            'color_primario' => $data['marcaColorPrimario'],
            'color_secundario' => $data['marcaColorSecundario'],
            'notas' => trim($data['marcaNotas']) ?: null,
            'activo' => $data['marcaActivo'],
        ])->save();

        CreativeActivity::log('marcas', $isNew ? 'crear' : 'actualizar', $marca, $marca->nombre);
        $this->limpiarMarca();
        $this->alert($isNew ? 'Marca creada correctamente' : 'Marca actualizada correctamente');
    }

    public function editarMarca(int $id): void
    {
        $m = Marca::findOrFail($id);
        $this->marcaId = $m->id;
        $this->marcaNombre = $m->nombre;
        $this->marcaTipo = $m->tipo;
        $this->marcaContacto = $m->contacto ?? '';
        $this->marcaEmail = $m->email ?? '';
        $this->marcaTelefono = $m->telefono ?? '';
        $this->marcaSitio = $m->sitio_web ?? '';
        $this->marcaColorPrimario = $m->color_primario;
        $this->marcaColorSecundario = $m->color_secundario;
        $this->marcaNotas = $m->notas ?? '';
        $this->marcaActivo = $m->activo;
        $this->marcaLogo = null;
        $this->marcaLogoSecundario = null;
        $this->resetValidation();
        $this->dispatch('scroll-form');
    }

    public function alternarMarca(int $id): void
    {
        $m = Marca::findOrFail($id);
        $m->update(['activo' => ! $m->activo]);
        CreativeActivity::log('marcas', 'cambiar_estado', $m, $m->nombre, ['activo' => $m->activo]);
        $this->alert($m->activo ? 'Marca activada' : 'Marca desactivada');
    }

    public function eliminarMarca(int $id): void
    {
        $m = Marca::findOrFail($id);
        $m->delete();
        CreativeActivity::log('marcas', 'archivar', $m, $m->nombre);
        $this->alert('Marca archivada');
    }

    public function limpiarMarca(): void
    {
        $this->reset([
            'marcaId','marcaNombre','marcaContacto','marcaEmail','marcaTelefono','marcaSitio',
            'marcaNotas','marcaLogo','marcaLogoSecundario',
        ]);
        $this->marcaTipo = 'cliente';
        $this->marcaColorPrimario = '#006492';
        $this->marcaColorSecundario = '#88AC2E';
        $this->marcaActivo = true;
        $this->resetValidation();
    }

    // PERSONAS
    public function guardarPersona(): void
    {
        $data = $this->validate([
            'personaMarcaId' => ['nullable','exists:marcas,id'],
            'personaTipo' => ['required','in:contacto,participante,ponente,empleado,proveedor,cliente,otro'],
            'personaNombre' => ['required','string','max:180'],
            'personaCargo' => ['nullable','string','max:180'],
            'personaOrganizacion' => ['nullable','string','max:180'],
            'personaEmail' => ['nullable','email','max:180'],
            'personaTelefono' => ['nullable','string','max:40'],
            'personaIdentificador' => ['nullable','string','max:100'],
            'personaTags' => ['nullable','string','max:500'],
            'personaNotas' => ['nullable','string','max:3000'],
            'personaActivo' => ['boolean'],
            'personaFoto' => ['nullable', File::image()->types(['jpg','jpeg','png','webp'])->max(10 * 1024)],
        ]);

        $persona = $this->personaId ? Persona::findOrFail($this->personaId) : new Persona();
        $isNew = ! $persona->exists;
        $photo = $persona->foto;
        if ($this->personaFoto) {
            $this->deletePublicFile($photo);
            $photo = $this->storePublicFile($this->personaFoto, 'creative/people');
        }

        $persona->fill([
            'user_id' => $persona->user_id ?: auth()->id(),
            'marca_id' => $data['personaMarcaId'],
            'tipo' => $data['personaTipo'],
            'nombre' => trim($data['personaNombre']),
            'foto' => $photo,
            'cargo' => trim($data['personaCargo']) ?: null,
            'organizacion' => trim($data['personaOrganizacion']) ?: null,
            'email' => trim($data['personaEmail']) ?: null,
            'telefono' => trim($data['personaTelefono']) ?: null,
            'identificador' => trim($data['personaIdentificador']) ?: null,
            'tags' => $this->tags($data['personaTags']),
            'notas' => trim($data['personaNotas']) ?: null,
            'activo' => $data['personaActivo'],
        ])->save();

        CreativeActivity::log('personas', $isNew ? 'crear' : 'actualizar', $persona, $persona->nombre);
        $this->limpiarPersona();
        $this->alert($isNew ? 'Persona creada correctamente' : 'Persona actualizada correctamente');
    }

    public function editarPersona(int $id): void
    {
        $p = Persona::findOrFail($id);
        $this->personaId = $p->id;
        $this->personaMarcaId = $p->marca_id;
        $this->personaTipo = $p->tipo;
        $this->personaNombre = $p->nombre;
        $this->personaCargo = $p->cargo ?? '';
        $this->personaOrganizacion = $p->organizacion ?? '';
        $this->personaEmail = $p->email ?? '';
        $this->personaTelefono = $p->telefono ?? '';
        $this->personaIdentificador = $p->identificador ?? '';
        $this->personaTags = implode(', ', $p->tags ?? []);
        $this->personaNotas = $p->notas ?? '';
        $this->personaActivo = $p->activo;
        $this->personaFoto = null;
        $this->resetValidation();
        $this->dispatch('scroll-form');
    }

    public function alternarPersona(int $id): void
    {
        $p = Persona::findOrFail($id);
        $p->update(['activo' => ! $p->activo]);
        CreativeActivity::log('personas', 'cambiar_estado', $p, $p->nombre);
        $this->alert($p->activo ? 'Persona activada' : 'Persona desactivada');
    }

    public function eliminarPersona(int $id): void
    {
        $p = Persona::findOrFail($id);
        $p->delete();
        CreativeActivity::log('personas', 'archivar', $p, $p->nombre);
        $this->alert('Persona archivada');
    }

    public function importarPersonasCsv(): void
    {
        $this->validate(['personasCsv' => ['required','file','mimes:csv,txt','max:5120']]);
        $handle = fopen($this->personasCsv->getRealPath(), 'r');
        if (! $handle) {
            $this->addError('personasCsv', 'No se pudo leer el archivo.');
            return;
        }

        $headers = fgetcsv($handle);
        if (! $headers) {
            fclose($handle);
            $this->addError('personasCsv', 'El CSV está vacío.');
            return;
        }
        $headers = array_map(fn ($h) => Str::of((string) $h)->lower()->ascii()->replace(' ', '_')->toString(), $headers);
        if (! in_array('nombre', $headers, true)) {
            fclose($handle);
            $this->addError('personasCsv', 'El CSV debe incluir una columna llamada nombre.');
            return;
        }

        $created = 0;
        while (($row = fgetcsv($handle)) !== false) {
            if (count($row) !== count($headers)) {
                continue;
            }
            $data = array_combine($headers, $row);
            if (! trim((string) ($data['nombre'] ?? ''))) {
                continue;
            }
            Persona::create([
                'user_id' => auth()->id(),
                'marca_id' => $this->personaMarcaId,
                'nombre' => trim($data['nombre']),
                'tipo' => trim($data['tipo'] ?? '') ?: 'contacto',
                'cargo' => trim($data['cargo'] ?? '') ?: null,
                'organizacion' => trim($data['organizacion'] ?? '') ?: null,
                'email' => trim($data['email'] ?? '') ?: null,
                'telefono' => trim($data['telefono'] ?? '') ?: null,
                'identificador' => trim($data['identificador'] ?? '') ?: null,
                'tags' => $this->tags($data['tags'] ?? ''),
                'notas' => trim($data['notas'] ?? '') ?: null,
                'activo' => true,
            ]);
            $created++;
        }
        fclose($handle);
        $this->personasCsv = null;
        CreativeActivity::log('personas', 'importar_csv', null, "{$created} personas importadas");
        $this->alert("Se importaron {$created} personas");
    }

    public function limpiarPersona(): void
    {
        $this->reset([
            'personaId','personaMarcaId','personaNombre','personaCargo','personaOrganizacion',
            'personaEmail','personaTelefono','personaIdentificador','personaTags','personaNotas',
            'personaFoto','personasCsv',
        ]);
        $this->personaTipo = 'contacto';
        $this->personaActivo = true;
        $this->resetValidation();
    }

    // PROYECTOS
    public function guardarProyecto(): void
    {
        $data = $this->validate([
            'proyectoMarcaId' => ['nullable','exists:marcas,id'],
            'proyectoNombre' => ['required','string','max:180'],
            'proyectoTipo' => ['required','in:campaña,evento,redes,reconocimientos,credenciales,impresión,otro'],
            'proyectoEstado' => ['required','in:borrador,en_proceso,revision,aprobado,entregado,archivado'],
            'proyectoPrioridad' => ['required','in:baja,media,alta,urgente'],
            'proyectoInicio' => ['nullable','date'],
            'proyectoEntrega' => ['nullable','date','after_or_equal:proyectoInicio'],
            'proyectoDescripcion' => ['nullable','string','max:5000'],
            'proyectoTags' => ['nullable','string','max:500'],
            'proyectoPersonas' => ['array'],
            'proyectoPersonas.*' => ['integer','exists:personas,id'],
        ]);

        $project = $this->proyectoId ? ProyectoCreativo::findOrFail($this->proyectoId) : new ProyectoCreativo();
        $isNew = ! $project->exists;
        $project->fill([
            'user_id' => $project->user_id ?: auth()->id(),
            'marca_id' => $data['proyectoMarcaId'],
            'nombre' => trim($data['proyectoNombre']),
            'tipo' => $data['proyectoTipo'],
            'estado' => $data['proyectoEstado'],
            'prioridad' => $data['proyectoPrioridad'],
            'fecha_inicio' => $data['proyectoInicio'] ?: null,
            'fecha_entrega' => $data['proyectoEntrega'] ?: null,
            'descripcion' => trim($data['proyectoDescripcion']) ?: null,
            'tags' => $this->tags($data['proyectoTags']),
        ])->save();
        $project->personas()->sync($data['proyectoPersonas']);

        CreativeActivity::log('proyectos', $isNew ? 'crear' : 'actualizar', $project, $project->nombre);
        $this->limpiarProyecto();
        $this->alert($isNew ? 'Proyecto creado correctamente' : 'Proyecto actualizado correctamente');
    }

    public function editarProyecto(int $id): void
    {
        $p = ProyectoCreativo::with('personas')->findOrFail($id);
        $this->proyectoId = $p->id;
        $this->proyectoMarcaId = $p->marca_id;
        $this->proyectoNombre = $p->nombre;
        $this->proyectoTipo = $p->tipo;
        $this->proyectoEstado = $p->estado;
        $this->proyectoPrioridad = $p->prioridad;
        $this->proyectoInicio = $p->fecha_inicio?->format('Y-m-d') ?? '';
        $this->proyectoEntrega = $p->fecha_entrega?->format('Y-m-d') ?? '';
        $this->proyectoDescripcion = $p->descripcion ?? '';
        $this->proyectoTags = implode(', ', $p->tags ?? []);
        $this->proyectoPersonas = $p->personas->pluck('id')->map(fn ($id) => (string) $id)->all();
        $this->resetValidation();
        $this->dispatch('scroll-form');
    }

    public function eliminarProyecto(int $id): void
    {
        $p = ProyectoCreativo::findOrFail($id);
        $p->delete();
        CreativeActivity::log('proyectos', 'archivar', $p, $p->nombre);
        $this->alert('Proyecto archivado');
    }

    public function limpiarProyecto(): void
    {
        $this->reset([
            'proyectoId','proyectoMarcaId','proyectoNombre','proyectoInicio','proyectoEntrega',
            'proyectoDescripcion','proyectoTags','proyectoPersonas',
        ]);
        $this->proyectoTipo = 'campaña';
        $this->proyectoEstado = 'borrador';
        $this->proyectoPrioridad = 'media';
        $this->resetValidation();
    }

    // BIBLIOTECA
    public function guardarArchivo(): void
    {
        $data = $this->validate([
            'archivoMarcaId' => ['nullable','exists:marcas,id'],
            'archivoProyectoId' => ['nullable','exists:proyectos_creativos,id'],
            'archivoNombre' => ['required','string','max:180'],
            'archivoCategoria' => ['required','in:imagen,logo,fondo,marco,firma,sello,icono,documento,otro'],
            'archivoDescripcion' => ['nullable','string','max:3000'],
            'archivoTags' => ['nullable','string','max:500'],
            'archivoActivo' => ['boolean'],
            'archivoUpload' => [$this->archivoId ? 'nullable' : 'required','file','max:30720','mimes:jpg,jpeg,png,webp,pdf,svg'],
        ]);

        $asset = $this->archivoId ? ArchivoMultimedia::findOrFail($this->archivoId) : new ArchivoMultimedia();
        $isNew = ! $asset->exists;
        $path = $asset->archivo;
        $meta = [
            'mime' => $asset->mime,
            'extension' => $asset->extension,
            'ancho' => $asset->ancho,
            'alto' => $asset->alto,
            'peso' => $asset->peso,
            'orientacion' => $asset->orientacion,
            'transparencia' => $asset->transparencia,
        ];

        if ($this->archivoUpload) {
            $this->deletePublicFile($path);
            $path = $this->storePublicFile($this->archivoUpload, 'creative/library');
            $meta['mime'] = $this->archivoUpload->getMimeType();
            $meta['extension'] = strtolower($this->archivoUpload->getClientOriginalExtension());
            $meta['peso'] = $this->archivoUpload->getSize();
            if (str_starts_with((string) $meta['mime'], 'image/')) {
                $meta = array_merge($meta, $this->imageMetadata($path));
            } else {
                $meta['ancho'] = $meta['alto'] = null;
                $meta['orientacion'] = null;
                $meta['transparencia'] = null;
            }
        }

        $asset->fill([
            'user_id' => $asset->user_id ?: auth()->id(),
            'marca_id' => $data['archivoMarcaId'],
            'proyecto_creativo_id' => $data['archivoProyectoId'],
            'nombre' => trim($data['archivoNombre']),
            'categoria' => $data['archivoCategoria'],
            'archivo' => $path,
            'disk' => 'public',
            'mime' => $meta['mime'],
            'extension' => $meta['extension'],
            'ancho' => $meta['ancho'],
            'alto' => $meta['alto'],
            'peso' => $meta['peso'],
            'orientacion' => $meta['orientacion'],
            'transparencia' => $meta['transparencia'],
            'tags' => $this->tags($data['archivoTags']),
            'descripcion' => trim($data['archivoDescripcion']) ?: null,
            'activo' => $data['archivoActivo'],
        ])->save();

        CreativeActivity::log('biblioteca', $isNew ? 'subir' : 'actualizar', $asset, $asset->nombre);
        $this->limpiarArchivo();
        $this->alert($isNew ? 'Archivo agregado a la biblioteca' : 'Archivo actualizado');
    }

    public function editarArchivo(int $id): void
    {
        $a = ArchivoMultimedia::findOrFail($id);
        $this->archivoId = $a->id;
        $this->archivoMarcaId = $a->marca_id;
        $this->archivoProyectoId = $a->proyecto_creativo_id;
        $this->archivoNombre = $a->nombre;
        $this->archivoCategoria = $a->categoria;
        $this->archivoDescripcion = $a->descripcion ?? '';
        $this->archivoTags = implode(', ', $a->tags ?? []);
        $this->archivoActivo = $a->activo;
        $this->archivoUpload = null;
        $this->resetValidation();
        $this->dispatch('scroll-form');
    }

    public function alternarArchivo(int $id): void
    {
        $a = ArchivoMultimedia::findOrFail($id);
        $a->update(['activo' => ! $a->activo]);
        CreativeActivity::log('biblioteca', 'cambiar_estado', $a, $a->nombre);
        $this->alert($a->activo ? 'Archivo activado' : 'Archivo desactivado');
    }

    public function eliminarArchivo(int $id): void
    {
        $a = ArchivoMultimedia::findOrFail($id);
        $a->delete();
        CreativeActivity::log('biblioteca', 'archivar', $a, $a->nombre);
        $this->alert('Archivo enviado a la papelera');
    }

    public function limpiarArchivo(): void
    {
        $this->reset([
            'archivoId','archivoMarcaId','archivoProyectoId','archivoNombre','archivoDescripcion',
            'archivoTags','archivoUpload',
        ]);
        $this->archivoCategoria = 'imagen';
        $this->archivoActivo = true;
        $this->resetValidation();
    }

    // PLANTILLAS
    private function bloqueBase(string $tipo): array
    {
        return [
            'uid' => (string) Str::uuid(),
            'tipo' => $tipo,
            'nombre' => $tipo === 'texto' ? 'Texto' : ucfirst($tipo),
            'contenido' => $tipo === 'texto' ? '{{nombre}}' : '',
            'variable' => $tipo === 'texto' ? 'nombre' : '',
            'x' => 10,
            'y' => 35,
            'w' => 80,
            'h' => 15,
            'font_size' => 42,
            'color' => '#111827',
            'align' => 'center',
            'font_weight' => '700',
            'background' => 'transparent',
            'border_radius' => 0,
        ];
    }

    public function agregarBloque(string $tipo = 'texto'): void
    {
        abort_unless(in_array($tipo, ['texto','imagen','qr','linea','caja'], true), 422);
        $block = $this->bloqueBase($tipo);
        $block['y'] = min(85, 10 + count($this->plantillaBloques) * 12);
        $this->plantillaBloques[] = $block;
    }

    public function eliminarBloque(int $index): void
    {
        if (isset($this->plantillaBloques[$index])) {
            unset($this->plantillaBloques[$index]);
            $this->plantillaBloques = array_values($this->plantillaBloques);
        }
    }

    public function moverBloque(int $index, string $direccion): void
    {
        $target = $direccion === 'arriba' ? $index - 1 : $index + 1;
        if (! isset($this->plantillaBloques[$index], $this->plantillaBloques[$target])) {
            return;
        }
        [$this->plantillaBloques[$index], $this->plantillaBloques[$target]] =
            [$this->plantillaBloques[$target], $this->plantillaBloques[$index]];
    }

    public function actualizarPosicionBloque(string $uid, float $x, float $y): void
    {
        foreach ($this->plantillaBloques as $index => $block) {
            if (($block['uid'] ?? null) !== $uid) {
                continue;
            }

            $width = max(1, min(100, (float) ($block['w'] ?? 10)));
            $height = max(1, min(100, (float) ($block['h'] ?? 10)));
            $this->plantillaBloques[$index]['x'] = round(max(0, min(100 - $width, $x)), 2);
            $this->plantillaBloques[$index]['y'] = round(max(0, min(100 - $height, $y)), 2);
            break;
        }
    }

    public function aplicarPresetPlantilla(int $presetId): void
    {
        $preset = PresetSocial::findOrFail($presetId);
        $this->plantillaAncho = $preset->ancho;
        $this->plantillaAlto = $preset->alto;
    }

    public function guardarPlantilla(): void
    {
        $data = $this->validate([
            'plantillaMarcaId' => ['nullable','exists:marcas,id'],
            'plantillaFondoId' => ['nullable','exists:archivos_multimedia,id'],
            'plantillaNombre' => ['required','string','max:180'],
            'plantillaTipo' => ['required','in:general,reconocimiento,credencial,red_social,invitacion,diploma,flyer,portada'],
            'plantillaAncho' => ['required','integer','between:200,8000'],
            'plantillaAlto' => ['required','integer','between:200,8000'],
            'plantillaEstado' => ['required','in:borrador,revision,aprobado,archivado'],
            'plantillaDescripcion' => ['nullable','string','max:4000'],
            'plantillaActivo' => ['boolean'],
            'plantillaParaImpresion' => ['boolean'],
            'plantillaSangradoMm' => ['required','numeric','between:0,20'],
            'plantillaMargenSeguroMm' => ['required','numeric','between:0,50'],
            'plantillaMarcasCorte' => ['boolean'],
            'plantillaModoColor' => ['required','in:rgb,cmyk'],
            'plantillaBloques' => ['required','array','min:1','max:50'],
            'plantillaBloques.*.tipo' => ['required','in:texto,imagen,qr,linea,caja'],
            'plantillaBloques.*.x' => ['required','numeric','between:0,100'],
            'plantillaBloques.*.y' => ['required','numeric','between:0,100'],
            'plantillaBloques.*.w' => ['required','numeric','between:1,100'],
            'plantillaBloques.*.h' => ['required','numeric','between:1,100'],
            'plantillaBloques.*.font_size' => ['nullable','integer','between:6,300'],
            'plantillaBloques.*.color' => ['nullable','regex:/^#[0-9A-Fa-f]{6}$/'],
        ]);

        $template = $this->plantillaId ? PlantillaCreativa::findOrFail($this->plantillaId) : new PlantillaCreativa();
        $isNew = ! $template->exists;

        if ($template->exists) {
            PlantillaVersion::create([
                'plantilla_creativa_id' => $template->id,
                'user_id' => auth()->id(),
                'version' => $template->version,
                'estructura' => $template->estructura,
                'configuracion' => [
                    'ancho' => $template->ancho,
                    'alto' => $template->alto,
                    'estado' => $template->estado,
                    'fondo_archivo_id' => $template->fondo_archivo_id,
                    'configuracion_impresion' => $template->configuracion_impresion,
                ],
                'nota' => 'Versión guardada antes de la actualización.',
            ]);
            $template->version++;
        }

        $template->fill([
            'user_id' => $template->user_id ?: auth()->id(),
            'marca_id' => $data['plantillaMarcaId'],
            'fondo_archivo_id' => $data['plantillaFondoId'],
            'nombre' => trim($data['plantillaNombre']),
            'tipo' => $data['plantillaTipo'],
            'ancho' => $data['plantillaAncho'],
            'alto' => $data['plantillaAlto'],
            'orientacion' => $data['plantillaAncho'] === $data['plantillaAlto'] ? 'cuadrada' : ($data['plantillaAncho'] > $data['plantillaAlto'] ? 'horizontal' : 'vertical'),
            'estructura' => array_values($data['plantillaBloques']),
            'configuracion_impresion' => [
                'habilitada' => (bool) $data['plantillaParaImpresion'],
                'sangrado_mm' => (float) $data['plantillaSangradoMm'],
                'margen_seguro_mm' => (float) $data['plantillaMargenSeguroMm'],
                'marcas_corte' => (bool) $data['plantillaMarcasCorte'],
                'modo_color' => $data['plantillaModoColor'],
            ],
            'descripcion' => trim($data['plantillaDescripcion']) ?: null,
            'estado' => $data['plantillaEstado'],
            'activo' => $data['plantillaActivo'],
        ])->save();

        CreativeActivity::log('plantillas', $isNew ? 'crear' : 'actualizar', $template, $template->nombre, ['version' => $template->version]);
        $this->limpiarPlantilla();
        $this->alert($isNew ? 'Plantilla creada correctamente' : 'Plantilla actualizada y versionada');
    }

    public function editarPlantilla(int $id): void
    {
        $t = PlantillaCreativa::findOrFail($id);
        $this->plantillaId = $t->id;
        $this->plantillaMarcaId = $t->marca_id;
        $this->plantillaFondoId = $t->fondo_archivo_id;
        $this->plantillaNombre = $t->nombre;
        $this->plantillaTipo = $t->tipo;
        $this->plantillaAncho = $t->ancho;
        $this->plantillaAlto = $t->alto;
        $this->plantillaEstado = $t->estado;
        $this->plantillaDescripcion = $t->descripcion ?? '';
        $this->plantillaActivo = $t->activo;
        $print = $t->configuracion_impresion ?? [];
        $this->plantillaParaImpresion = (bool) ($print['habilitada'] ?? false);
        $this->plantillaSangradoMm = (float) ($print['sangrado_mm'] ?? 3);
        $this->plantillaMargenSeguroMm = (float) ($print['margen_seguro_mm'] ?? 5);
        $this->plantillaMarcasCorte = (bool) ($print['marcas_corte'] ?? false);
        $this->plantillaModoColor = (string) ($print['modo_color'] ?? 'rgb');
        $this->plantillaBloques = $t->estructura ?: [$this->bloqueBase('texto')];
        $this->resetValidation();
        $this->dispatch('scroll-form');
    }

    public function duplicarPlantilla(int $id): void
    {
        $source = PlantillaCreativa::findOrFail($id);
        $copy = $source->replicate(['created_at','updated_at','deleted_at']);
        $copy->nombre = $source->nombre.' (copia)';
        $copy->estado = 'borrador';
        $copy->version = 1;
        $copy->user_id = auth()->id();
        $copy->save();
        CreativeActivity::log('plantillas', 'duplicar', $copy, $copy->nombre);
        $this->alert('Plantilla duplicada como borrador');
    }

    public function eliminarPlantilla(int $id): void
    {
        $t = PlantillaCreativa::findOrFail($id);
        $t->delete();
        CreativeActivity::log('plantillas', 'archivar', $t, $t->nombre);
        $this->alert('Plantilla archivada');
    }

    public function restaurarVersion(int $versionId): void
    {
        $version = PlantillaVersion::findOrFail($versionId);
        $template = $version->plantilla;
        PlantillaVersion::create([
            'plantilla_creativa_id' => $template->id,
            'user_id' => auth()->id(),
            'version' => $template->version,
            'estructura' => $template->estructura,
            'configuracion' => ['ancho' => $template->ancho, 'alto' => $template->alto, 'estado' => $template->estado, 'configuracion_impresion' => $template->configuracion_impresion],
            'nota' => 'Respaldo automático previo a restauración.',
        ]);
        $config = $version->configuracion ?? [];
        $template->update([
            'estructura' => $version->estructura,
            'ancho' => $config['ancho'] ?? $template->ancho,
            'alto' => $config['alto'] ?? $template->alto,
            'estado' => 'borrador',
            'configuracion_impresion' => $config['configuracion_impresion'] ?? $template->configuracion_impresion,
            'version' => $template->version + 1,
        ]);
        CreativeActivity::log('plantillas', 'restaurar_version', $template, "Restaurada versión {$version->version}");
        $this->alert("Versión {$version->version} restaurada");
    }

    public function limpiarPlantilla(): void
    {
        $this->reset([
            'plantillaId','plantillaMarcaId','plantillaFondoId','plantillaNombre',
            'plantillaDescripcion',
        ]);
        $this->plantillaTipo = 'general';
        $this->plantillaAncho = 1920;
        $this->plantillaAlto = 1080;
        $this->plantillaEstado = 'borrador';
        $this->plantillaActivo = true;
        $this->plantillaParaImpresion = false;
        $this->plantillaSangradoMm = 3;
        $this->plantillaMargenSeguroMm = 5;
        $this->plantillaMarcasCorte = false;
        $this->plantillaModoColor = 'rgb';
        $this->plantillaBloques = [$this->bloqueBase('texto')];
        $this->resetValidation();
    }

    // GENERADOR MASIVO
    public function cargarCsvGenerador(): void
    {
        $this->validate(['generadorCsv' => ['required','file','mimes:csv,txt','max:10240']]);
        $handle = fopen($this->generadorCsv->getRealPath(), 'r');
        if (! $handle) {
            $this->addError('generadorCsv', 'No se pudo leer el CSV.');
            return;
        }
        $headers = fgetcsv($handle);
        if (! $headers) {
            fclose($handle);
            $this->addError('generadorCsv', 'El CSV está vacío.');
            return;
        }
        $headers = array_map(fn ($h) => Str::of((string) $h)->trim()->lower()->ascii()->replace(' ', '_')->toString(), $headers);
        $rows = [];
        while (($row = fgetcsv($handle)) !== false && count($rows) < 1000) {
            if (count($row) !== count($headers)) {
                continue;
            }
            $assoc = array_combine($headers, $row);
            if (collect($assoc)->filter(fn ($value) => trim((string) $value) !== '')->isNotEmpty()) {
                $rows[] = $assoc;
            }
        }
        fclose($handle);
        $this->generadorCabeceras = $headers;
        $this->generadorFilas = $rows;
        $this->generadorCsv = null;
        $this->alert(count($rows).' filas cargadas para revisión');
    }

    public function cargarPersonasGenerador(): void
    {
        $rows = Persona::query()
            ->where('activo', true)
            ->when($this->generadorMarcaId, fn ($q) => $q->where('marca_id', $this->generadorMarcaId))
            ->orderBy('nombre')
            ->limit(1000)
            ->get()
            ->map(fn ($p) => [
                'nombre' => $p->nombre,
                'cargo' => $p->cargo ?? '',
                'organizacion' => $p->organizacion ?? '',
                'email' => $p->email ?? '',
                'telefono' => $p->telefono ?? '',
                'identificador' => $p->identificador ?? '',
                'fecha' => now()->format('Y-m-d'),
            ])->all();

        $this->generadorFilas = $rows;
        $this->generadorCabeceras = $rows ? array_keys($rows[0]) : [];
        $this->alert(count($rows).' personas cargadas');
    }

    public function limpiarGenerador(): void
    {
        $this->reset(['generadorCsv','generadorFilas','generadorCabeceras']);
    }

    public function descargarLote()
    {
        $this->validate([
            'generadorPlantillaId' => ['required','exists:plantillas_creativas,id'],
            'generadorMarcaId' => ['nullable','exists:marcas,id'],
            'generadorProyectoId' => ['nullable','exists:proyectos_creativos,id'],
            'generadorNombrePatron' => ['required','string','max:120'],
            'generadorFilas' => ['required','array','min:1','max:1000'],
        ]);

        @set_time_limit(0);
        $template = PlantillaCreativa::with('fondo')->findOrFail($this->generadorPlantillaId);
        $tmp = tempnam(sys_get_temp_dir(), 'creative_batch_');
        abort_if($tmp === false, 500, 'No fue posible crear el archivo temporal.');
        $zip = new ZipArchive();
        abort_if($zip->open($tmp, ZipArchive::OVERWRITE) !== true, 500, 'No fue posible crear el ZIP.');

        $manifest = [];
        foreach ($this->generadorFilas as $index => $row) {
            $number = $index + 1;
            $html = view('pdf.creative-template', [
                'template' => $template,
                'row' => $row,
            ])->render();

            $pdf = Pdf::loadHTML($html)
                ->setPaper([0, 0, $template->ancho * 0.75, $template->alto * 0.75], 'portrait')
                ->output();

            $name = str_replace(
                ['{nombre}','{index}','{date}'],
                [
                    Str::slug((string) ($row['nombre'] ?? 'documento'), '_'),
                    str_pad((string) $number, 3, '0', STR_PAD_LEFT),
                    now()->format('Ymd'),
                ],
                $this->generadorNombrePatron
            );
            $name = trim(preg_replace('/[^a-zA-Z0-9_\-]+/', '_', $name), '_-') ?: 'documento_'.$number;
            $file = $name.'.pdf';
            $zip->addFromString($file, $pdf);
            $manifest[] = ['index' => $number, 'output' => $file, 'data' => $row];
        }

        $zip->addFromString('manifest.json', json_encode([
            'generated_at' => now()->toIso8601String(),
            'template' => ['id' => $template->id, 'nombre' => $template->nombre, 'version' => $template->version],
            'files' => $manifest,
        ], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
        $zip->close();

        if (Schema::hasTable('historial_exportaciones')) {
            HistorialExportacion::create([
                'user_id' => auth()->id(),
                'marca_id' => $this->generadorMarcaId,
                'proyecto_creativo_id' => $this->generadorProyectoId,
                'plantilla_creativa_id' => $template->id,
                'tipo' => 'generador_masivo',
                'formato' => 'zip/pdf',
                'cantidad' => count($this->generadorFilas),
                'configuracion' => ['patron' => $this->generadorNombrePatron, 'version' => $template->version],
            ]);
        }
        CreativeActivity::log('generador', 'exportar_lote', $template, count($this->generadorFilas).' documentos');

        return response()->download($tmp, 'lote_'.$template->id.'_'.now()->format('Ymd_His').'.zip')->deleteFileAfterSend(true);
    }

    // SOLICITUDES
    public function guardarSolicitud(): void
    {
        $data = $this->validate([
            'solicitudMarcaId' => ['nullable','exists:marcas,id'],
            'solicitudProyectoId' => ['nullable','exists:proyectos_creativos,id'],
            'solicitudTitulo' => ['required','string','max:180'],
            'solicitudTipo' => ['required','in:diseño,reconocimiento,credencial,redes,impresión,edición,otro'],
            'solicitudEstado' => ['required','in:pendiente,en_proceso,revision,aprobada,entregada,cancelada'],
            'solicitudPrioridad' => ['required','in:baja,media,alta,urgente'],
            'solicitudSolicitante' => ['nullable','string','max:180'],
            'solicitudContacto' => ['nullable','string','max:180'],
            'solicitudEntrega' => ['nullable','date'],
            'solicitudDescripcion' => ['nullable','string','max:5000'],
            'solicitudNotas' => ['nullable','string','max:5000'],
        ]);

        $request = $this->solicitudId ? SolicitudCreativa::findOrFail($this->solicitudId) : new SolicitudCreativa();
        $isNew = ! $request->exists;
        $request->fill([
            'user_id' => $request->user_id ?: auth()->id(),
            'marca_id' => $data['solicitudMarcaId'],
            'proyecto_creativo_id' => $data['solicitudProyectoId'],
            'titulo' => trim($data['solicitudTitulo']),
            'tipo' => $data['solicitudTipo'],
            'estado' => $data['solicitudEstado'],
            'prioridad' => $data['solicitudPrioridad'],
            'solicitante' => trim($data['solicitudSolicitante']) ?: null,
            'contacto' => trim($data['solicitudContacto']) ?: null,
            'fecha_entrega' => $data['solicitudEntrega'] ?: null,
            'descripcion' => trim($data['solicitudDescripcion']) ?: null,
            'notas' => trim($data['solicitudNotas']) ?: null,
        ])->save();

        CreativeActivity::log('solicitudes', $isNew ? 'crear' : 'actualizar', $request, $request->titulo);
        $this->limpiarSolicitud();
        $this->alert($isNew ? 'Solicitud registrada' : 'Solicitud actualizada');
    }

    public function editarSolicitud(int $id): void
    {
        $s = SolicitudCreativa::findOrFail($id);
        $this->solicitudId = $s->id;
        $this->solicitudMarcaId = $s->marca_id;
        $this->solicitudProyectoId = $s->proyecto_creativo_id;
        $this->solicitudTitulo = $s->titulo;
        $this->solicitudTipo = $s->tipo;
        $this->solicitudEstado = $s->estado;
        $this->solicitudPrioridad = $s->prioridad;
        $this->solicitudSolicitante = $s->solicitante ?? '';
        $this->solicitudContacto = $s->contacto ?? '';
        $this->solicitudEntrega = $s->fecha_entrega?->format('Y-m-d\TH:i') ?? '';
        $this->solicitudDescripcion = $s->descripcion ?? '';
        $this->solicitudNotas = $s->notas ?? '';
        $this->resetValidation();
        $this->dispatch('scroll-form');
    }

    public function eliminarSolicitud(int $id): void
    {
        $s = SolicitudCreativa::findOrFail($id);
        $s->delete();
        CreativeActivity::log('solicitudes', 'archivar', $s, $s->titulo);
        $this->alert('Solicitud archivada');
    }

    public function limpiarSolicitud(): void
    {
        $this->reset([
            'solicitudId','solicitudMarcaId','solicitudProyectoId','solicitudTitulo',
            'solicitudSolicitante','solicitudContacto','solicitudEntrega',
            'solicitudDescripcion','solicitudNotas',
        ]);
        $this->solicitudTipo = 'diseño';
        $this->solicitudEstado = 'pendiente';
        $this->solicitudPrioridad = 'media';
        $this->resetValidation();
    }

    // PUBLICACIONES
    public function guardarPublicacion(): void
    {
        $data = $this->validate([
            'publicacionMarcaId' => ['nullable','exists:marcas,id'],
            'publicacionProyectoId' => ['nullable','exists:proyectos_creativos,id'],
            'publicacionArchivoId' => ['nullable','exists:archivos_multimedia,id'],
            'publicacionTitulo' => ['required','string','max:180'],
            'publicacionRed' => ['required','in:Instagram,Facebook,WhatsApp,TikTok,YouTube,LinkedIn,X,Otra'],
            'publicacionEstado' => ['required','in:borrador,revision,aprobada,programada,publicada,cancelada'],
            'publicacionProgramada' => ['nullable','date'],
            'publicacionCopy' => ['nullable','string','max:10000'],
            'publicacionHashtags' => ['nullable','string','max:2000'],
            'publicacionUrl' => ['nullable','url','max:500'],
            'publicacionNotas' => ['nullable','string','max:5000'],
        ]);

        $post = $this->publicacionId ? PublicacionSocial::findOrFail($this->publicacionId) : new PublicacionSocial();
        $isNew = ! $post->exists;
        $post->fill([
            'user_id' => $post->user_id ?: auth()->id(),
            'marca_id' => $data['publicacionMarcaId'],
            'proyecto_creativo_id' => $data['publicacionProyectoId'],
            'archivo_multimedia_id' => $data['publicacionArchivoId'],
            'titulo' => trim($data['publicacionTitulo']),
            'red_social' => $data['publicacionRed'],
            'estado' => $data['publicacionEstado'],
            'programada_at' => $data['publicacionProgramada'] ?: null,
            'publicada_at' => $data['publicacionEstado'] === 'publicada' ? ($post->publicada_at ?: now()) : null,
            'copy' => trim($data['publicacionCopy']) ?: null,
            'hashtags' => trim($data['publicacionHashtags']) ?: null,
            'url_publicacion' => trim($data['publicacionUrl']) ?: null,
            'notas' => trim($data['publicacionNotas']) ?: null,
        ])->save();

        CreativeActivity::log('publicaciones', $isNew ? 'crear' : 'actualizar', $post, $post->titulo);
        $this->limpiarPublicacion();
        $this->alert($isNew ? 'Publicación registrada' : 'Publicación actualizada');
    }

    public function editarPublicacion(int $id): void
    {
        $p = PublicacionSocial::findOrFail($id);
        $this->publicacionId = $p->id;
        $this->publicacionMarcaId = $p->marca_id;
        $this->publicacionProyectoId = $p->proyecto_creativo_id;
        $this->publicacionArchivoId = $p->archivo_multimedia_id;
        $this->publicacionTitulo = $p->titulo;
        $this->publicacionRed = $p->red_social;
        $this->publicacionEstado = $p->estado;
        $this->publicacionProgramada = $p->programada_at?->format('Y-m-d\TH:i') ?? '';
        $this->publicacionCopy = $p->copy ?? '';
        $this->publicacionHashtags = $p->hashtags ?? '';
        $this->publicacionUrl = $p->url_publicacion ?? '';
        $this->publicacionNotas = $p->notas ?? '';
        $this->resetValidation();
        $this->dispatch('scroll-form');
    }

    public function eliminarPublicacion(int $id): void
    {
        $p = PublicacionSocial::findOrFail($id);
        $p->delete();
        CreativeActivity::log('publicaciones', 'archivar', $p, $p->titulo);
        $this->alert('Publicación archivada');
    }

    public function limpiarPublicacion(): void
    {
        $this->reset([
            'publicacionId','publicacionMarcaId','publicacionProyectoId','publicacionArchivoId',
            'publicacionTitulo','publicacionProgramada','publicacionCopy','publicacionHashtags',
            'publicacionUrl','publicacionNotas',
        ]);
        $this->publicacionRed = 'Instagram';
        $this->publicacionEstado = 'borrador';
        $this->resetValidation();
    }

    // PRESETS
    public function guardarPreset(): void
    {
        $data = $this->validate([
            'presetNombre' => ['required','string','max:160'],
            'presetRed' => ['required','string','max:80'],
            'presetAncho' => ['required','integer','between:200,8000'],
            'presetAlto' => ['required','integer','between:200,8000'],
            'presetDescripcion' => ['nullable','string','max:2000'],
            'presetActivo' => ['boolean'],
        ]);

        $preset = $this->presetId ? PresetSocial::findOrFail($this->presetId) : new PresetSocial();
        $isNew = ! $preset->exists;
        $preset->fill([
            'nombre' => trim($data['presetNombre']),
            'red_social' => trim($data['presetRed']),
            'ancho' => $data['presetAncho'],
            'alto' => $data['presetAlto'],
            'orientacion' => $data['presetAncho'] === $data['presetAlto'] ? 'cuadrada' : ($data['presetAncho'] > $data['presetAlto'] ? 'horizontal' : 'vertical'),
            'descripcion' => trim($data['presetDescripcion']) ?: null,
            'activo' => $data['presetActivo'],
        ])->save();

        CreativeActivity::log('presets', $isNew ? 'crear' : 'actualizar', $preset, $preset->nombre);
        $this->limpiarPreset();
        $this->alert($isNew ? 'Preset creado' : 'Preset actualizado');
    }

    public function editarPreset(int $id): void
    {
        $p = PresetSocial::findOrFail($id);
        $this->presetId = $p->id;
        $this->presetNombre = $p->nombre;
        $this->presetRed = $p->red_social;
        $this->presetAncho = $p->ancho;
        $this->presetAlto = $p->alto;
        $this->presetDescripcion = $p->descripcion ?? '';
        $this->presetActivo = $p->activo;
        $this->resetValidation();
        $this->dispatch('scroll-form');
    }

    public function eliminarPreset(int $id): void
    {
        PresetSocial::findOrFail($id)->delete();
        $this->alert('Preset eliminado');
    }

    public function limpiarPreset(): void
    {
        $this->reset(['presetId','presetNombre','presetDescripcion']);
        $this->presetRed = 'General';
        $this->presetAncho = 1080;
        $this->presetAlto = 1080;
        $this->presetActivo = true;
        $this->resetValidation();
    }

    // VALIDACIONES
    public function generarCodigoValidacion(): void
    {
        do {
            $code = strtoupper(Str::random(4).'-'.Str::random(4).'-'.Str::random(4));
        } while (RegistroValidacion::where('codigo', $code)->exists());
        $this->validacionCodigo = $code;
    }

    public function guardarValidacion(): void
    {
        if (! $this->validacionCodigo) {
            $this->generarCodigoValidacion();
        }

        $data = $this->validate([
            'validacionPersonaId' => ['nullable','exists:personas,id'],
            'validacionProyectoId' => ['nullable','exists:proyectos_creativos,id'],
            'validacionCodigo' => ['required','string','max:80', Rule::unique('registros_validacion','codigo')->ignore($this->validacionId)],
            'validacionTipo' => ['required','in:documento,reconocimiento,credencial,constancia,diploma,otro'],
            'validacionTitulo' => ['required','string','max:180'],
            'validacionEstado' => ['required','in:valido,cancelado,vencido'],
            'validacionEmitido' => ['nullable','date'],
            'validacionVence' => ['nullable','date','after_or_equal:validacionEmitido'],
            'validacionDatos' => ['nullable','string','max:5000'],
            'validacionNotas' => ['nullable','string','max:5000'],
        ]);

        $record = $this->validacionId ? RegistroValidacion::findOrFail($this->validacionId) : new RegistroValidacion();
        $isNew = ! $record->exists;
        $record->fill([
            'user_id' => $record->user_id ?: auth()->id(),
            'persona_id' => $data['validacionPersonaId'],
            'proyecto_creativo_id' => $data['validacionProyectoId'],
            'codigo' => strtoupper(trim($data['validacionCodigo'])),
            'tipo' => $data['validacionTipo'],
            'titulo' => trim($data['validacionTitulo']),
            'estado' => $data['validacionEstado'],
            'emitido_at' => $data['validacionEmitido'] ?: null,
            'vence_at' => $data['validacionVence'] ?: null,
            'datos_publicos' => ['descripcion' => trim($data['validacionDatos'])],
            'notas' => trim($data['validacionNotas']) ?: null,
        ])->save();

        CreativeActivity::log('validaciones', $isNew ? 'crear' : 'actualizar', $record, $record->codigo);
        $this->limpiarValidacion();
        $this->alert($isNew ? 'Registro de validación creado' : 'Registro de validación actualizado');
    }

    public function editarValidacion(int $id): void
    {
        $v = RegistroValidacion::findOrFail($id);
        $this->validacionId = $v->id;
        $this->validacionPersonaId = $v->persona_id;
        $this->validacionProyectoId = $v->proyecto_creativo_id;
        $this->validacionCodigo = $v->codigo;
        $this->validacionTipo = $v->tipo;
        $this->validacionTitulo = $v->titulo;
        $this->validacionEstado = $v->estado;
        $this->validacionEmitido = $v->emitido_at?->format('Y-m-d') ?? '';
        $this->validacionVence = $v->vence_at?->format('Y-m-d') ?? '';
        $this->validacionDatos = $v->datos_publicos['descripcion'] ?? '';
        $this->validacionNotas = $v->notas ?? '';
        $this->resetValidation();
        $this->dispatch('scroll-form');
    }

    public function eliminarValidacion(int $id): void
    {
        $v = RegistroValidacion::findOrFail($id);
        $v->delete();
        CreativeActivity::log('validaciones', 'eliminar', $v, $v->codigo);
        $this->alert('Registro de validación eliminado');
    }

    public function limpiarValidacion(): void
    {
        $this->reset([
            'validacionId','validacionPersonaId','validacionProyectoId','validacionCodigo',
            'validacionTitulo','validacionVence','validacionDatos','validacionNotas',
        ]);
        $this->validacionTipo = 'documento';
        $this->validacionEstado = 'valido';
        $this->validacionEmitido = now()->format('Y-m-d');
        $this->resetValidation();
    }

    public function eliminarExportacion(int $id): void
    {
        HistorialExportacion::findOrFail($id)->delete();
        $this->alert('Registro de exportación eliminado');
    }

    public function render()
    {
        $search = trim($this->buscar);
        $like = '%'.$search.'%';

        $brands = Marca::query()->where('activo', true)->orderBy('nombre')->get();
        $people = Persona::query()->where('activo', true)->orderBy('nombre')->get();
        $projects = ProyectoCreativo::query()->orderByDesc('created_at')->get();
        $assets = ArchivoMultimedia::query()->where('activo', true)->orderByDesc('created_at')->get();
        $templates = PlantillaCreativa::query()->where('activo', true)->orderBy('nombre')->get();
        $presets = PresetSocial::query()->where('activo', true)->orderBy('red_social')->orderBy('nombre')->get();

        $items = match ($this->section) {
            'marcas' => Marca::query()
                ->when($search, fn ($q) => $q->where(fn ($x) => $x->where('nombre','like',$like)->orWhere('contacto','like',$like)->orWhere('email','like',$like)))
                ->when($this->filtroEstado !== 'todos', fn ($q) => $q->where('activo', $this->filtroEstado === 'activos'))
                ->orderByDesc('created_at')->limit(200)->get(),
            'personas' => Persona::with('marca')
                ->when($search, fn ($q) => $q->where(fn ($x) => $x->where('nombre','like',$like)->orWhere('cargo','like',$like)->orWhere('organizacion','like',$like)->orWhere('email','like',$like)))
                ->when($this->filtroEstado !== 'todos', fn ($q) => $q->where('activo', $this->filtroEstado === 'activos'))
                ->when($this->filtroTipo !== 'todos', fn ($q) => $q->where('tipo', $this->filtroTipo))
                ->orderBy('nombre')->limit(300)->get(),
            'proyectos' => ProyectoCreativo::with(['marca','personas'])
                ->when($search, fn ($q) => $q->where('nombre','like',$like))
                ->when($this->filtroEstado !== 'todos', fn ($q) => $q->where('estado', $this->filtroEstado))
                ->orderByDesc('created_at')->limit(200)->get(),
            'biblioteca' => ArchivoMultimedia::with(['marca','proyecto'])
                ->when($search, fn ($q) => $q->where(fn ($x) => $x->where('nombre','like',$like)->orWhere('descripcion','like',$like)))
                ->when($this->filtroEstado !== 'todos', fn ($q) => $q->where('activo', $this->filtroEstado === 'activos'))
                ->when($this->filtroTipo !== 'todos', fn ($q) => $q->where('categoria', $this->filtroTipo))
                ->orderByDesc('created_at')->limit(250)->get(),
            'plantillas' => PlantillaCreativa::with(['marca','fondo','versiones'])
                ->when($search, fn ($q) => $q->where('nombre','like',$like))
                ->when($this->filtroEstado !== 'todos', fn ($q) => $q->where('estado', $this->filtroEstado))
                ->orderByDesc('updated_at')->limit(200)->get(),
            'solicitudes' => SolicitudCreativa::with(['marca','proyecto'])
                ->when($search, fn ($q) => $q->where(fn ($x) => $x->where('titulo','like',$like)->orWhere('solicitante','like',$like)))
                ->when($this->filtroEstado !== 'todos', fn ($q) => $q->where('estado', $this->filtroEstado))
                ->orderByRaw("FIELD(prioridad, 'urgente','alta','media','baja')")
                ->orderBy('fecha_entrega')->limit(250)->get(),
            'publicaciones' => PublicacionSocial::with(['marca','proyecto','archivo'])
                ->when($search, fn ($q) => $q->where(fn ($x) => $x->where('titulo','like',$like)->orWhere('copy','like',$like)))
                ->when($this->filtroEstado !== 'todos', fn ($q) => $q->where('estado', $this->filtroEstado))
                ->orderByDesc('programada_at')->orderByDesc('created_at')->limit(250)->get(),
            'presets' => PresetSocial::query()
                ->when($search, fn ($q) => $q->where(fn ($x) => $x->where('nombre','like',$like)->orWhere('red_social','like',$like)))
                ->orderBy('red_social')->orderBy('nombre')->get(),
            'exportaciones' => HistorialExportacion::with(['marca','proyecto','plantilla'])
                ->when($search, fn ($q) => $q->where('tipo','like',$like))
                ->orderByDesc('created_at')->limit(300)->get(),
            'validaciones' => RegistroValidacion::with(['persona','proyecto'])
                ->when($search, fn ($q) => $q->where(fn ($x) => $x->where('codigo','like',$like)->orWhere('titulo','like',$like)))
                ->when($this->filtroEstado !== 'todos', fn ($q) => $q->where('estado', $this->filtroEstado))
                ->orderByDesc('created_at')->limit(300)->get(),
            'actividad' => ActividadCreativa::query()
                ->when($search, fn ($q) => $q->where(fn ($x) => $x->where('modulo','like',$like)->orWhere('accion','like',$like)->orWhere('descripcion','like',$like)))
                ->orderByDesc('created_at')->limit(400)->get(),
            default => collect(),
        };

        return view('livewire.creative.creative-hub', compact(
            'items','brands','people','projects','assets','templates','presets'
        ));
    }
}
