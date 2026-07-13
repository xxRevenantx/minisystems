<?php

namespace App\Livewire\Etiquetas;

use App\Models\EtiquetaAlumno;
use App\Models\EtiquetaPlantilla;
use App\Models\HistorialExportacion;
use App\Models\EtiquetaPermiso;
use App\Models\User;
use App\Models\Persona;
use App\Imports\EtiquetasAlumnosImport;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\File;
use Maatwebsite\Excel\Facades\Excel;
use Livewire\Component;
use Livewire\WithFileUploads;
use Livewire\WithPagination;

class GestionEtiquetas extends Component
{
    use WithFileUploads, WithPagination;

    public string $tab = 'alumnos';
    public string $buscar = '';
    public string $filtroNivel = '';
    public string $filtroGeneracion = '';
    public string $filtroGrado = '';
    public string $filtroGrupo = '';
    public string $filtroEstado = 'activos';
    public int $porPagina = 15;
    public array $seleccionados = [];

    public bool $modalAlumno = false;
    public ?int $alumnoId = null;
    public ?int $personaId = null;
    public string $nombre = '';
    public string $apellidoPaterno = '';
    public string $apellidoMaterno = '';
    public string $nivel = '';
    public string $generacion = '';
    public ?string $grado = null;
    public ?string $grupo = null;
    public bool $activo = true;

    public bool $modalPlantilla = false;
    public ?int $plantillaId = null;
    public string $plantillaNombre = '';
    public ?string $plantillaNivel = null;
    public string $plantillaDescripcion = '';
    public $plantillaFondo;
    public ?string $plantillaFondoActual = null;
    public bool $plantillaPredeterminada = false;
    public bool $plantillaActiva = true;
    public string $superiorTop = '3.15';
    public string $inferiorTop = '17.10';
    public string $anchoBloque = '90';
    public string $nombreTamano = '60';
    public string $nombreTamanoMedio = '52';
    public string $nombreTamanoLargo = '44';
    public string $datosTamano = '21';
    public string $nombreColor = '#111827';
    public string $datosColor = '#334155';
    public string $alineacion = 'center';
    public bool $mayusculas = true;
    public bool $mostrarGrado = true;
    public bool $mostrarGrupo = true;
    public bool $mostrarGeneracion = true;

    public bool $modalImportar = false;
    public $archivoExcel;
    public array $reporteImportacion = [];

    public bool $modalImprimir = false;
    public ?int $impresionPlantillaId = null;
    public string $modoImpresion = 'diferentes';
    public string $ordenImpresion = 'academico';

    public bool $modalPapelera = false;

    public bool $modalEdicionMasiva = false;
    public bool $mostrarVistaPreviaMasiva = false;
    public array $vistaPreviaMasiva = [];
    public int $totalVistaPreviaMasiva = 0;
    public int $duplicadosVistaPreviaMasiva = 0;
    public string $accionNivel = 'sin_cambios';
    public string $valorNivel = '';
    public string $accionGeneracion = 'sin_cambios';
    public string $valorGeneracion = '';
    public string $accionGrado = 'sin_cambios';
    public string $valorGrado = '';
    public string $accionGrupo = 'sin_cambios';
    public string $valorGrupo = '';
    public string $accionEstado = 'sin_cambios';
    public string $valorEstado = 'activo';

    public array $niveles = [
        'Preescolar','Primaria','Secundaria','Bachillerato','Licenciatura','Personal','Curso','Taller','Otro',
    ];

    public function mount(): void
    {
        abort_unless(auth()->user()?->puedeEtiquetas('ver'), 403);
        $this->impresionPlantillaId = EtiquetaPlantilla::query()
            ->where('activo', true)->orderByDesc('es_predeterminada')->value('id');
    }

    protected function alumnoRules(): array
    {
        return [
            'personaId' => ['nullable', 'exists:personas,id'],
            'nombre' => ['required', 'string', 'min:2', 'max:255'],
            'apellidoPaterno' => ['nullable', 'string', 'max:255'],
            'apellidoMaterno' => ['nullable', 'string', 'max:255'],
            'nivel' => ['required', Rule::in($this->niveles)],
            'generacion' => ['required', 'string', 'max:100'],
            'grado' => ['nullable', 'string', 'max:50'],
            'grupo' => ['nullable', 'string', 'max:50'],
            'activo' => ['boolean'],
        ];
    }

    protected function plantillaRules(): array
    {
        return [
            'plantillaNombre' => ['required', 'string', 'min:3', 'max:150'],
            'plantillaNivel' => ['nullable', Rule::in($this->niveles)],
            'plantillaDescripcion' => ['nullable', 'string', 'max:1000'],
            'plantillaFondo' => [Rule::requiredIf(fn () => ! $this->plantillaId), 'nullable', File::image()->types(['jpg','jpeg','png','webp'])->max(10 * 1024)],
            'plantillaPredeterminada' => ['boolean'],
            'plantillaActiva' => ['boolean'],
            'superiorTop' => ['required', 'numeric', 'between:0,27'],
            'inferiorTop' => ['required', 'numeric', 'between:0,27'],
            'anchoBloque' => ['required', 'numeric', 'between:40,100'],
            'nombreTamano' => ['required', 'integer', 'between:20,100'],
            'nombreTamanoMedio' => ['required', 'integer', 'between:20,100'],
            'nombreTamanoLargo' => ['required', 'integer', 'between:18,100'],
            'datosTamano' => ['required', 'integer', 'between:10,50'],
            'nombreColor' => ['required', 'regex:/^#[0-9A-Fa-f]{6}$/'],
            'datosColor' => ['required', 'regex:/^#[0-9A-Fa-f]{6}$/'],
            'alineacion' => ['required', Rule::in(['left','center','right'])],
            'mayusculas' => ['boolean'],
            'mostrarGrado' => ['boolean'],
            'mostrarGrupo' => ['boolean'],
            'mostrarGeneracion' => ['boolean'],
        ];
    }

    public function updated($property): void
    {
        if (in_array($property, ['buscar','filtroNivel','filtroGeneracion','filtroGrado','filtroGrupo','filtroEstado','porPagina'], true)) {
            $this->resetPage();
            $this->seleccionados = [];
        }

        if (str_starts_with((string) $property, 'accion') || str_starts_with((string) $property, 'valor')) {
            $this->mostrarVistaPreviaMasiva = false;
            $this->vistaPreviaMasiva = [];
        }
    }

    public function updatedPersonaId($value): void
    {
        if ($value && ($persona = Persona::find($value))) {
            $this->nombre = $persona->nombre;
            $this->apellidoPaterno = '';
            $this->apellidoMaterno = '';
        }
    }

    public function abrirCrearAlumno(): void
    {
        abort_unless(auth()->user()?->puedeEtiquetas('crear'), 403);
        $this->limpiarAlumno();
        $this->modalAlumno = true;
    }

    public function abrirEditarAlumno(int $id): void
    {
        abort_unless(auth()->user()?->puedeEtiquetas('editar'), 403);
        $alumno = EtiquetaAlumno::findOrFail($id);
        $this->alumnoId = $alumno->id;
        $this->personaId = $alumno->persona_id;
        $this->nombre = $alumno->nombre;
        $this->apellidoPaterno = $alumno->apellido_paterno ?? '';
        $this->apellidoMaterno = $alumno->apellido_materno ?? '';
        $this->nivel = $alumno->nivel;
        $this->generacion = $alumno->generacion;
        $this->grado = $alumno->grado;
        $this->grupo = $alumno->grupo;
        $this->activo = (bool) $alumno->activo;
        $this->resetValidation();
        $this->modalAlumno = true;
    }

    public function guardarAlumno(): void
    {
        abort_unless(auth()->user()?->puedeEtiquetas($this->alumnoId ? 'editar' : 'crear'), 403);
        $data = $this->validate($this->alumnoRules());
        $payload = [
            'user_id' => auth()->id(),
            'persona_id' => $data['personaId'] ?: null,
            'nombre' => $this->normalizarNombre($data['nombre']),
            'apellido_paterno' => filled($data['apellidoPaterno']) ? $this->normalizarNombre($data['apellidoPaterno']) : null,
            'apellido_materno' => filled($data['apellidoMaterno']) ? $this->normalizarNombre($data['apellidoMaterno']) : null,
            'nivel' => $data['nivel'],
            'generacion' => Str::squish($data['generacion']),
            'grado' => filled($data['grado']) ? Str::squish($data['grado']) : null,
            'grupo' => filled($data['grupo']) ? Str::upper(Str::squish($data['grupo'])) : null,
            'activo' => $data['activo'],
        ];

        $duplicado = EtiquetaAlumno::query()
            ->when($this->alumnoId, fn (Builder $q) => $q->where('id', '!=', $this->alumnoId))
            ->where('nombre', $payload['nombre'])
            ->where(fn (Builder $q) => $payload['apellido_paterno'] === null ? $q->whereNull('apellido_paterno') : $q->where('apellido_paterno', $payload['apellido_paterno']))
            ->where(fn (Builder $q) => $payload['apellido_materno'] === null ? $q->whereNull('apellido_materno') : $q->where('apellido_materno', $payload['apellido_materno']))
            ->where('nivel', $payload['nivel'])
            ->where('generacion', $payload['generacion'])
            ->where(fn (Builder $q) => $payload['grado'] === null ? $q->whereNull('grado') : $q->where('grado', $payload['grado']))
            ->where(fn (Builder $q) => $payload['grupo'] === null ? $q->whereNull('grupo') : $q->where('grupo', $payload['grupo']))
            ->exists();
        if ($duplicado) {
            $this->addError('nombre', 'Ya existe un alumno con los mismos datos académicos.');
            return;
        }

        $editando = (bool) $this->alumnoId;
        if ($this->alumnoId) {
            $alumno = EtiquetaAlumno::findOrFail($this->alumnoId);
            $alumno->update($payload);
        } else {
            $alumno = EtiquetaAlumno::create($payload);
        }

        HistorialExportacion::create([
            'user_id' => auth()->id(),
            'tipo' => 'etiquetas',
            'formato' => 'edicion',
            'cantidad' => 1,
            'configuracion' => [
                'operacion' => $editando ? 'edicion_individual' : 'creacion_individual',
                'alumnos' => [$alumno->id],
                'campos' => array_keys($payload),
            ],
            'notas' => $editando ? 'Edición individual de alumno' : 'Alta individual de alumno',
        ]);

        $this->modalAlumno = false;
        $this->limpiarAlumno();
        $this->dispatch('swal', icon: 'success', title: $editando ? 'Alumno actualizado' : 'Alumno registrado');
    }

    public function eliminarAlumno(int $id): void
    {
        abort_unless(auth()->user()?->puedeEtiquetas('eliminar'), 403);
        EtiquetaAlumno::findOrFail($id)->delete();
        $this->seleccionados = array_values(array_diff($this->seleccionados, [$id, (string) $id]));
        $this->dispatch('swal', icon: 'success', title: 'Alumno enviado a la papelera');
    }

    public function restaurarAlumno(int $id): void
    {
        abort_unless(auth()->user()?->puedeEtiquetas('eliminar'), 403);
        EtiquetaAlumno::onlyTrashed()->findOrFail($id)->restore();
        $this->dispatch('swal', icon: 'success', title: 'Alumno restaurado');
    }

    public function eliminarDefinitivamente(int $id): void
    {
        abort_unless(auth()->user()?->puedeEtiquetas('administrar'), 403);
        EtiquetaAlumno::onlyTrashed()->findOrFail($id)->forceDelete();
        $this->dispatch('swal', icon: 'success', title: 'Registro eliminado definitivamente');
    }

    public function seleccionarPagina(): void
    {
        $ids = $this->consultaAlumnos()->forPage((int) ($this->paginators['page'] ?? 1), $this->porPagina)->pluck('id')->map(fn ($id) => (string) $id)->all();
        $this->seleccionados = array_values(array_unique(array_merge($this->seleccionados, $ids)));
    }

    public function seleccionarTodosFiltrados(): void
    {
        $this->seleccionados = $this->consultaAlumnos()->pluck('id')->map(fn ($id) => (string) $id)->all();
        $this->dispatch('swal', icon: 'success', title: count($this->seleccionados).' alumnos seleccionados');
    }

    public function limpiarSeleccion(): void { $this->seleccionados = []; }

    public function accionMasiva(string $accion): void
    {
        abort_if(empty($this->seleccionados), 422, 'No hay alumnos seleccionados.');
        $ids = collect($this->seleccionados)->map(fn ($id) => (int) $id)->unique()->all();
        if (in_array($accion, ['activar','desactivar'], true)) {
            abort_unless(auth()->user()?->puedeEtiquetas('editar'), 403);
            EtiquetaAlumno::whereIn('id', $ids)->update(['activo' => $accion === 'activar']);
        } elseif ($accion === 'eliminar') {
            abort_unless(auth()->user()?->puedeEtiquetas('eliminar'), 403);
            EtiquetaAlumno::whereIn('id', $ids)->delete();
        } else {
            abort(422);
        }
        HistorialExportacion::create([
            'user_id' => auth()->id(),
            'tipo' => 'etiquetas',
            'formato' => 'edicion',
            'cantidad' => count($ids),
            'configuracion' => [
                'operacion' => 'accion_masiva',
                'accion' => $accion,
                'alumnos' => $ids,
            ],
            'notas' => 'Acción masiva: '.$accion,
        ]);

        $this->seleccionados = [];
        $this->dispatch('swal', icon: 'success', title: 'Acción masiva completada');
    }

    public function abrirEdicionMasiva(): void
    {
        abort_unless(auth()->user()?->puedeEtiquetas('editar'), 403);

        if (empty($this->seleccionados)) {
            $this->dispatch('swal', icon: 'warning', title: 'Selecciona al menos un alumno');
            return;
        }

        $this->resetEdicionMasiva();
        $this->modalEdicionMasiva = true;
    }

    public function previsualizarEdicionMasiva(): void
    {
        abort_unless(auth()->user()?->puedeEtiquetas('editar'), 403);
        $this->validate($this->reglasEdicionMasiva());

        $resultado = $this->evaluarEdicionMasiva();
        $this->vistaPreviaMasiva = $resultado['vista_previa'];
        $this->totalVistaPreviaMasiva = $resultado['aplicables'];
        $this->duplicadosVistaPreviaMasiva = $resultado['duplicados'];
        $this->mostrarVistaPreviaMasiva = true;
    }

    public function aplicarEdicionMasiva(): void
    {
        abort_unless(auth()->user()?->puedeEtiquetas('editar'), 403);
        $this->validate($this->reglasEdicionMasiva());

        $resultado = $this->evaluarEdicionMasiva(true);
        $actualizados = 0;
        $idsActualizados = [];

        DB::transaction(function () use ($resultado, &$actualizados, &$idsActualizados): void {
            foreach ($resultado['registros'] as $registro) {
                if ($registro['duplicado'] || $registro['cambios'] === []) {
                    continue;
                }

                /** @var EtiquetaAlumno $alumno */
                $alumno = $registro['alumno'];
                $alumno->update($registro['payload']);
                $actualizados++;
                $idsActualizados[] = $alumno->id;
            }

            HistorialExportacion::create([
                'user_id' => auth()->id(),
                'tipo' => 'etiquetas',
                'formato' => 'edicion',
                'cantidad' => $actualizados,
                'configuracion' => [
                    'operacion' => 'edicion_masiva',
                    'alumnos' => $idsActualizados,
                    'campos' => $this->camposEdicionMasivaActivos(),
                    'duplicados_omitidos' => $resultado['duplicados'],
                ],
                'notas' => 'Edición masiva desde el módulo de Etiquetas',
            ]);
        });

        $omitidos = $resultado['duplicados'];
        $this->seleccionados = [];
        $this->modalEdicionMasiva = false;
        $this->resetEdicionMasiva();
        $this->resetPage();

        $this->dispatch(
            'swal',
            icon: $omitidos > 0 ? 'warning' : 'success',
            title: "{$actualizados} registro(s) actualizado(s)",
            text: $omitidos > 0 ? "{$omitidos} duplicado(s) fueron omitidos." : 'Los cambios se guardaron correctamente.',
        );
    }

    public function cerrarEdicionMasiva(): void
    {
        $this->modalEdicionMasiva = false;
        $this->resetEdicionMasiva();
    }

    public function abrirImpresion(): void
    {
        abort_unless(auth()->user()?->puedeEtiquetas('descargar'), 403);
        if (empty($this->seleccionados)) {
            $this->dispatch('swal', icon: 'warning', title: 'Selecciona al menos un alumno');
            return;
        }
        $this->impresionPlantillaId ??= EtiquetaPlantilla::where('activo', true)->orderByDesc('es_predeterminada')->value('id');
        if (! $this->impresionPlantillaId) {
            $this->dispatch('swal', icon: 'warning', title: 'Primero registra una plantilla de fondo');
            return;
        }
        $this->modalImprimir = true;
    }

    public function abrirCrearPlantilla(): void
    {
        abort_unless(auth()->user()?->puedeEtiquetas('administrar'), 403);
        $this->limpiarPlantilla();
        $this->modalPlantilla = true;
    }

    public function abrirEditarPlantilla(int $id): void
    {
        abort_unless(auth()->user()?->puedeEtiquetas('administrar'), 403);
        $p = EtiquetaPlantilla::findOrFail($id);
        $c = $p->configuracionConValores();
        $this->plantillaId = $p->id;
        $this->plantillaNombre = $p->nombre;
        $this->plantillaNivel = $p->nivel;
        $this->plantillaDescripcion = $p->descripcion ?? '';
        $this->plantillaFondoActual = $p->url;
        $this->plantillaPredeterminada = (bool) $p->es_predeterminada;
        $this->plantillaActiva = (bool) $p->activo;
        $this->superiorTop = (string) $c['superior_top'];
        $this->inferiorTop = (string) $c['inferior_top'];
        $this->anchoBloque = (string) $c['ancho_bloque'];
        $this->nombreTamano = (string) $c['nombre_tamano'];
        $this->nombreTamanoMedio = (string) $c['nombre_tamano_medio'];
        $this->nombreTamanoLargo = (string) $c['nombre_tamano_largo'];
        $this->datosTamano = (string) $c['datos_tamano'];
        $this->nombreColor = $c['nombre_color'];
        $this->datosColor = $c['datos_color'];
        $this->alineacion = $c['alineacion'];
        $this->mayusculas = (bool) $c['mayusculas'];
        $this->mostrarGrado = (bool) $c['mostrar_grado'];
        $this->mostrarGrupo = (bool) $c['mostrar_grupo'];
        $this->mostrarGeneracion = (bool) $c['mostrar_generacion'];
        $this->plantillaFondo = null;
        $this->resetValidation();
        $this->modalPlantilla = true;
    }

    public function guardarPlantilla(): void
    {
        abort_unless(auth()->user()?->puedeEtiquetas('administrar'), 403);
        $data = $this->validate($this->plantillaRules());
        $actual = $this->plantillaId ? EtiquetaPlantilla::findOrFail($this->plantillaId) : null;
        $path = $actual?->fondo;
        $nuevoPath = null;
        $advertirProporcion = false;

        if ($this->plantillaFondo) {
            $dimensiones = @getimagesize($this->plantillaFondo->getRealPath());
            if (is_array($dimensiones) && ($dimensiones[1] ?? 0) > 0) {
                $proporcion = $dimensiones[0] / $dimensiones[1];
                $carta = 8.5 / 11;
                $advertirProporcion = abs(($proporcion / $carta) - 1) > 0.04;
            }
            $nuevoPath = $this->plantillaFondo->store('etiquetas/fondos', 'public');
            $path = $nuevoPath;
        }

        try {
            DB::transaction(function () use ($data, $path, $actual, $nuevoPath) {
                if ($data['plantillaPredeterminada']) EtiquetaPlantilla::query()->update(['es_predeterminada' => false]);

                if ($actual && $nuevoPath && $actual->fondo) {
                    EtiquetaPlantilla::create([
                        'user_id' => auth()->id(),
                        'nombre' => Str::limit($actual->nombre.' · versión anterior '.now()->format('d-m-Y H:i'), 150, ''),
                        'nivel' => $actual->nivel,
                        'descripcion' => 'Fondo conservado automáticamente al reemplazar la imagen de la plantilla.',
                        'fondo' => $actual->fondo,
                        'disk' => $actual->disk ?: 'public',
                        'es_predeterminada' => false,
                        'activo' => false,
                        'configuracion' => $actual->configuracion,
                    ]);
                }

                $payload = [
                    'user_id' => auth()->id(),
                    'nombre' => Str::squish($data['plantillaNombre']),
                    'nivel' => $data['plantillaNivel'] ?: null,
                    'descripcion' => trim($data['plantillaDescripcion']) ?: null,
                    'fondo' => $path,
                    'disk' => 'public',
                    'es_predeterminada' => $data['plantillaPredeterminada'],
                    'activo' => $data['plantillaPredeterminada'] ? true : $data['plantillaActiva'],
                    'configuracion' => [
                        'superior_top' => (float) $data['superiorTop'], 'inferior_top' => (float) $data['inferiorTop'],
                        'ancho_bloque' => (float) $data['anchoBloque'], 'nombre_tamano' => (int) $data['nombreTamano'],
                        'nombre_tamano_medio' => (int) $data['nombreTamanoMedio'], 'nombre_tamano_largo' => (int) $data['nombreTamanoLargo'],
                        'datos_tamano' => (int) $data['datosTamano'], 'nombre_color' => $data['nombreColor'],
                        'datos_color' => $data['datosColor'], 'alineacion' => $data['alineacion'],
                        'mayusculas' => $data['mayusculas'], 'mostrar_grado' => $data['mostrarGrado'],
                        'mostrar_grupo' => $data['mostrarGrupo'], 'mostrar_generacion' => $data['mostrarGeneracion'],
                    ],
                ];

                if ($actual) {
                    $actual->update($payload);
                } else {
                    EtiquetaPlantilla::create($payload);
                }
            });
        } catch (\Throwable $e) {
            if ($nuevoPath) Storage::disk('public')->delete($nuevoPath);
            throw $e;
        }

        $this->modalPlantilla = false;
        $this->impresionPlantillaId = EtiquetaPlantilla::where('activo', true)->orderByDesc('es_predeterminada')->value('id');
        $this->dispatch(
            'swal',
            icon: $advertirProporcion ? 'warning' : 'success',
            title: $advertirProporcion ? 'Plantilla guardada con advertencia' : 'Plantilla guardada correctamente',
            text: $advertirProporcion ? 'La imagen no tiene proporción carta vertical y podría deformarse al cubrir la hoja.' : null,
        );
    }

    public function establecerPredeterminada(int $id): void
    {
        abort_unless(auth()->user()?->puedeEtiquetas('administrar'), 403);
        DB::transaction(function () use ($id) {
            EtiquetaPlantilla::query()->update(['es_predeterminada' => false]);
            EtiquetaPlantilla::findOrFail($id)->update(['es_predeterminada' => true, 'activo' => true]);
        });
        $this->impresionPlantillaId = $id;
        $this->dispatch('swal', icon: 'success', title: 'Plantilla predeterminada actualizada');
    }

    public function eliminarPlantilla(int $id): void
    {
        abort_unless(auth()->user()?->puedeEtiquetas('administrar'), 403);
        $p = EtiquetaPlantilla::findOrFail($id);
        if ($p->es_predeterminada) {
            $this->dispatch('swal', icon: 'warning', title: 'No puedes eliminar la plantilla predeterminada');
            return;
        }
        $p->delete();
        $this->dispatch('swal', icon: 'success', title: 'Plantilla enviada a la papelera');
    }

    public function alternarPermiso(int $userId, string $accion): void
    {
        abort_unless(auth()->user()?->puedeEtiquetas('administrar'), 403);
        abort_unless(in_array($accion, ['ver','crear','editar','eliminar','importar','descargar','administrar'], true), 422);
        if ($userId === 1) {
            $this->dispatch('swal', icon: 'warning', title: 'El administrador principal conserva acceso completo');
            return;
        }
        $permiso = EtiquetaPermiso::firstOrCreate(['user_id' => $userId], ['ver' => true, 'descargar' => true]);
        $permiso->update([$accion => ! (bool) $permiso->{$accion}]);
        $this->dispatch('swal', icon: 'success', title: 'Permiso actualizado');
    }

    public function importarExcel(): void
    {
        abort_unless(auth()->user()?->puedeEtiquetas('importar'), 403);

        $this->validate(
            [
                'archivoExcel' => [
                    'required',
                    'file',
                    'mimes:xlsx,xls',
                    'max:10240',
                ],
            ],
            [
                'archivoExcel.required' => 'Selecciona la plantilla de Excel completada.',
                'archivoExcel.file' => 'El archivo seleccionado no es válido.',
                'archivoExcel.mimes' => 'El archivo debe estar en formato Excel .xlsx o .xls.',
                'archivoExcel.max' => 'El archivo de Excel no debe superar los 10 MB.',
            ],
        );

        try {
            $importacion = new EtiquetasAlumnosImport((int) auth()->id(), $this->niveles);
            Excel::import($importacion, $this->archivoExcel);

            $this->reporteImportacion = $importacion->reporte();
            $this->reset('archivoExcel');
            $this->resetPage();

            $importados = $this->reporteImportacion['importados'];
            $actualizados = $this->reporteImportacion['actualizados'];
            $omitidos = $this->reporteImportacion['omitidos'];
            $errores = $this->reporteImportacion['errores'];

            HistorialExportacion::create([
                'user_id' => auth()->id(),
                'tipo' => 'etiquetas',
                'formato' => 'xlsx',
                'cantidad' => $importados + $actualizados,
                'configuracion' => [
                    'operacion' => 'importacion_actualizacion_excel',
                    'importados' => $importados,
                    'actualizados' => $actualizados,
                    'duplicados_omitidos' => $omitidos,
                    'errores' => count($errores),
                ],
                'notas' => 'Importación o actualización masiva desde Excel',
            ]);

            $this->dispatch(
                'swal',
                icon: $errores || $omitidos ? 'warning' : 'success',
                title: ($importados + $actualizados).' registro(s) procesado(s)',
                text: $importados.' nuevos · '.$actualizados.' actualizados · '.$omitidos.' duplicados omitidos · '.count($errores).' filas con error',
            );
        } catch (\Throwable $exception) {
            report($exception);
            $this->addError('archivoExcel', $exception->getMessage());
        }
    }

    private function reglasEdicionMasiva(): array
    {
        $accionesComunes = ['sin_cambios', 'reemplazar', 'rellenar_vacios'];
        $accionesOpcionales = [...$accionesComunes, 'limpiar'];

        return [
            'accionNivel' => ['required', Rule::in($accionesComunes)],
            'valorNivel' => [Rule::requiredIf(fn () => $this->accionNivel !== 'sin_cambios'), 'nullable', Rule::in($this->niveles)],
            'accionGeneracion' => ['required', Rule::in($accionesComunes)],
            'valorGeneracion' => [Rule::requiredIf(fn () => $this->accionGeneracion !== 'sin_cambios'), 'nullable', 'string', 'max:100'],
            'accionGrado' => ['required', Rule::in($accionesOpcionales)],
            'valorGrado' => [Rule::requiredIf(fn () => in_array($this->accionGrado, ['reemplazar', 'rellenar_vacios'], true)), 'nullable', 'string', 'max:50'],
            'accionGrupo' => ['required', Rule::in($accionesOpcionales)],
            'valorGrupo' => [Rule::requiredIf(fn () => in_array($this->accionGrupo, ['reemplazar', 'rellenar_vacios'], true)), 'nullable', 'string', 'max:50'],
            'accionEstado' => ['required', Rule::in(['sin_cambios', 'reemplazar'])],
            'valorEstado' => [Rule::requiredIf(fn () => $this->accionEstado === 'reemplazar'), 'nullable', Rule::in(['activo', 'inactivo'])],
        ];
    }

    /**
     * @return array{
     *   vista_previa:array<int,array<string,mixed>>,
     *   registros:array<int,array<string,mixed>>,
     *   aplicables:int,
     *   duplicados:int
     * }
     */
    private function evaluarEdicionMasiva(bool $incluirModelos = false): array
    {
        $ids = collect($this->seleccionados)
            ->filter(fn ($id) => is_numeric($id))
            ->map(fn ($id) => (int) $id)
            ->unique()
            ->values();

        abort_if($ids->isEmpty(), 422, 'No hay alumnos seleccionados.');

        $alumnos = EtiquetaAlumno::query()
            ->whereIn('id', $ids)
            ->orderBy('id')
            ->get();

        $firmasExternas = EtiquetaAlumno::query()
            ->whereNotIn('id', $ids)
            ->get([
                'nombre', 'apellido_paterno', 'apellido_materno', 'nivel',
                'generacion', 'grado', 'grupo',
            ])
            ->mapWithKeys(fn (EtiquetaAlumno $alumno) => [
                $this->firmaDuplicado([
                    'nombre' => $alumno->nombre,
                    'apellido_paterno' => $alumno->apellido_paterno,
                    'apellido_materno' => $alumno->apellido_materno,
                    'nivel' => $alumno->nivel,
                    'generacion' => $alumno->generacion,
                    'grado' => $alumno->grado,
                    'grupo' => $alumno->grupo,
                ]) => true,
            ]);

        $firmasNuevas = [];
        $vistaPrevia = [];
        $registros = [];
        $duplicados = 0;
        $aplicables = 0;

        foreach ($alumnos as $alumno) {
            $payload = [
                'nivel' => $this->resolverValorMasivo($alumno->nivel, $this->accionNivel, $this->valorNivel, false),
                'generacion' => $this->resolverValorMasivo($alumno->generacion, $this->accionGeneracion, $this->valorGeneracion, false),
                'grado' => $this->resolverValorMasivo($alumno->grado, $this->accionGrado, $this->valorGrado, true),
                'grupo' => $this->resolverValorMasivo($alumno->grupo, $this->accionGrupo, $this->valorGrupo, true, true),
                'activo' => $this->accionEstado === 'reemplazar'
                    ? $this->valorEstado === 'activo'
                    : (bool) $alumno->activo,
            ];

            $firma = $this->firmaDuplicado([
                'nombre' => $alumno->nombre,
                'apellido_paterno' => $alumno->apellido_paterno,
                'apellido_materno' => $alumno->apellido_materno,
                'nivel' => $payload['nivel'],
                'generacion' => $payload['generacion'],
                'grado' => $payload['grado'],
                'grupo' => $payload['grupo'],
            ]);

            $duplicado = isset($firmasExternas[$firma]) || isset($firmasNuevas[$firma]);
            if ($duplicado) {
                $duplicados++;
            } else {
                $firmasNuevas[$firma] = true;
            }

            $cambios = [];
            foreach ([
                'nivel' => 'Nivel',
                'generacion' => 'Generación',
                'grado' => 'Grado',
                'grupo' => 'Grupo',
                'activo' => 'Estado',
            ] as $campo => $etiqueta) {
                $antes = $campo === 'activo'
                    ? ($alumno->activo ? 'Activo' : 'Inactivo')
                    : ($alumno->{$campo} ?? 'Vacío');
                $despues = $campo === 'activo'
                    ? ($payload[$campo] ? 'Activo' : 'Inactivo')
                    : ($payload[$campo] ?? 'Vacío');

                if ((string) $antes !== (string) $despues) {
                    $cambios[$etiqueta] = [
                        'antes' => $antes,
                        'despues' => $despues,
                    ];
                }
            }

            if (! $duplicado && $cambios !== []) {
                $aplicables++;
            }

            $item = [
                'id' => $alumno->id,
                'nombre' => $alumno->nombre_completo,
                'cambios' => $cambios,
                'duplicado' => $duplicado,
            ];

            if (count($vistaPrevia) < 60) {
                $vistaPrevia[] = $item;
            }

            $registros[] = $item + [
                'payload' => $payload,
                'alumno' => $incluirModelos ? $alumno : null,
            ];
        }

        return [
            'vista_previa' => $vistaPrevia,
            'registros' => $registros,
            'aplicables' => $aplicables,
            'duplicados' => $duplicados,
        ];
    }

    private function resolverValorMasivo(
        mixed $actual,
        string $accion,
        string $valor,
        bool $permiteLimpiar,
        bool $mayusculas = false,
    ): mixed {
        if ($accion === 'sin_cambios') {
            return $actual;
        }

        if ($accion === 'limpiar' && $permiteLimpiar) {
            return null;
        }

        if ($accion === 'rellenar_vacios' && filled($actual)) {
            return $actual;
        }

        $normalizado = Str::squish($valor);
        if ($mayusculas) {
            $normalizado = Str::upper($normalizado);
        }

        return $normalizado !== '' ? $normalizado : null;
    }

    /** @param array<string,mixed> $datos */
    private function firmaDuplicado(array $datos): string
    {
        return collect([
            $datos['nombre'] ?? null,
            $datos['apellido_paterno'] ?? null,
            $datos['apellido_materno'] ?? null,
            $datos['nivel'] ?? null,
            $datos['generacion'] ?? null,
            $datos['grado'] ?? null,
            $datos['grupo'] ?? null,
        ])
            ->map(fn ($valor) => Str::lower(Str::ascii(Str::squish((string) ($valor ?? '')))))
            ->implode('|');
    }

    /** @return array<int,string> */
    private function camposEdicionMasivaActivos(): array
    {
        return collect([
            'nivel' => $this->accionNivel,
            'generacion' => $this->accionGeneracion,
            'grado' => $this->accionGrado,
            'grupo' => $this->accionGrupo,
            'estado' => $this->accionEstado,
        ])
            ->reject(fn ($accion) => $accion === 'sin_cambios')
            ->keys()
            ->values()
            ->all();
    }

    private function resetEdicionMasiva(): void
    {
        $this->accionNivel = 'sin_cambios';
        $this->valorNivel = '';
        $this->accionGeneracion = 'sin_cambios';
        $this->valorGeneracion = '';
        $this->accionGrado = 'sin_cambios';
        $this->valorGrado = '';
        $this->accionGrupo = 'sin_cambios';
        $this->valorGrupo = '';
        $this->accionEstado = 'sin_cambios';
        $this->valorEstado = 'activo';
        $this->mostrarVistaPreviaMasiva = false;
        $this->vistaPreviaMasiva = [];
        $this->totalVistaPreviaMasiva = 0;
        $this->duplicadosVistaPreviaMasiva = 0;
        $this->resetValidation();
    }

    private function consultaAlumnos(): Builder
    {
        return EtiquetaAlumno::query()
            ->when(trim($this->buscar) !== '', function (Builder $q) {
                $buscar = trim($this->buscar);
                $q->where(fn (Builder $sub) => $sub->where('nombre', 'like', "%{$buscar}%")
                    ->orWhere('apellido_paterno', 'like', "%{$buscar}%")
                    ->orWhere('apellido_materno', 'like', "%{$buscar}%")
                    ->orWhere('nivel', 'like', "%{$buscar}%")->orWhere('generacion', 'like', "%{$buscar}%")
                    ->orWhere('grado', 'like', "%{$buscar}%")->orWhere('grupo', 'like', "%{$buscar}%"));
            })
            ->when($this->filtroNivel !== '', fn (Builder $q) => $q->where('nivel', $this->filtroNivel))
            ->when($this->filtroGeneracion !== '', fn (Builder $q) => $q->where('generacion', $this->filtroGeneracion))
            ->when($this->filtroGrado !== '', fn (Builder $q) => $q->where('grado', $this->filtroGrado))
            ->when($this->filtroGrupo !== '', fn (Builder $q) => $q->where('grupo', $this->filtroGrupo))
            ->when($this->filtroEstado === 'activos', fn (Builder $q) => $q->where('activo', true))
            ->when($this->filtroEstado === 'inactivos', fn (Builder $q) => $q->where('activo', false));
    }

    private function limpiarAlumno(): void
    {
        $this->reset(['alumnoId','personaId','nombre','apellidoPaterno','apellidoMaterno','nivel','generacion','grado','grupo']);
        $this->activo = true;
        $this->resetValidation();
    }

    private function limpiarPlantilla(): void
    {
        $this->reset(['plantillaId','plantillaNombre','plantillaNivel','plantillaDescripcion','plantillaFondo','plantillaFondoActual']);
        $this->plantillaPredeterminada = ! EtiquetaPlantilla::exists();
        $this->plantillaActiva = true;
        $this->superiorTop = '3.15'; $this->inferiorTop = '17.10'; $this->anchoBloque = '90';
        $this->nombreTamano = '60'; $this->nombreTamanoMedio = '52'; $this->nombreTamanoLargo = '44'; $this->datosTamano = '21';
        $this->nombreColor = '#111827'; $this->datosColor = '#334155'; $this->alineacion = 'center';
        $this->mayusculas = $this->mostrarGrado = $this->mostrarGrupo = $this->mostrarGeneracion = true;
        $this->resetValidation();
    }

    private function normalizarNombre(string $nombre): string
    {
        return Str::upper(Str::squish($nombre));
    }

    public function render()
    {
        $alumnos = $this->consultaAlumnos()->orderBy('nivel')->orderBy('generacion')->orderBy('grado')->orderBy('grupo')->orderBy('nombre')->orderBy('apellido_paterno')->orderBy('apellido_materno')->paginate($this->porPagina);
        $plantillas = EtiquetaPlantilla::query()->orderByDesc('es_predeterminada')->latest()->get();
        $plantillasActivas = $plantillas->where('activo', true);
        $personas = Persona::query()->where('activo', true)->orderBy('nombre')->limit(300)->get(['id','nombre']);
        $generaciones = EtiquetaAlumno::query()->select('generacion')->distinct()->orderBy('generacion')->pluck('generacion');
        $grados = EtiquetaAlumno::query()->whereNotNull('grado')->select('grado')->distinct()->orderBy('grado')->pluck('grado');
        $grupos = EtiquetaAlumno::query()->whereNotNull('grupo')->select('grupo')->distinct()->orderBy('grupo')->pluck('grupo');
        $papelera = $this->modalPapelera ? EtiquetaAlumno::onlyTrashed()->latest('deleted_at')->limit(50)->get() : collect();
        $historial = HistorialExportacion::query()->where('tipo', 'etiquetas')->latest()->limit(20)->get();
        $usuariosPermisos = auth()->user()?->puedeEtiquetas('administrar')
            ? User::query()->with('permisoEtiquetas')->orderBy('name')->get()
            : collect();
        $stats = [
            'total' => EtiquetaAlumno::count(), 'activos' => EtiquetaAlumno::where('activo', true)->count(),
            'niveles' => EtiquetaAlumno::distinct('nivel')->count('nivel'), 'plantillas' => $plantillasActivas->count(),
        ];

        return view('livewire.etiquetas.gestion-etiquetas', compact(
            'alumnos','plantillas','plantillasActivas','personas','generaciones','grados','grupos','papelera','historial','usuariosPermisos','stats'
        ));
    }
}
