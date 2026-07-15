<?php

namespace App\Livewire\Reconocimientos;

use App\Models\Credencial;
use App\Models\Directivo;
use App\Models\Marca;
use App\Models\Persona;
use App\Models\ProyectoCreativo;
use App\Models\Reconocimiento;
use App\Models\ReconocimientoEvento;
use App\Models\ReconocimientoImagen;
use App\Models\ReconocimientoTipo;
use App\Models\RegistroValidacion;
use App\Support\ReconocimientoHtml;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;
use Livewire\Component;
use Livewire\WithFileUploads;

class CreacionReconocimientos extends Component
{
    use WithFileUploads;

    public string $modo = 'individual';
    public ?int $reconocimiento_imagen_id = null;
    public ?int $reconocimiento_evento_id = null;
    public ?int $reconocimiento_tipo_id = null;
    public string $reconocimiento = '';
    public string $descripcion = '';
    public ?string $lugar_obtenido = null;
    public ?string $fecha = null;
    public array $directivos = [];
    public string $estado = 'borrador';
    public ?int $marca_id = null;
    public ?int $proyecto_creativo_id = null;
    public ?int $persona_id = null;
    public bool $generarValidacion = true;

    public string $buscarAlumno = '';
    public string $nivelFiltro = '';
    public string $gradoFiltro = '';
    public string $grupoFiltro = '';
    public string $licenciaturaFiltro = '';
    public array $credencialesSeleccionadas = [];
    public $archivoCsv = null;

    public function mount(): void
    {
        abort_unless(auth()->user()?->puedeReconocimientos('crear'), 403);
        $this->fecha = now()->toDateString();
    }

    protected function rules(): array
    {
        $rules = [
            'reconocimiento_imagen_id' => 'required|integer|exists:reconocimiento_imagenes,id',
            'reconocimiento_evento_id' => 'nullable|integer|exists:reconocimiento_eventos,id',
            'reconocimiento_tipo_id' => 'nullable|integer|exists:reconocimiento_tipos,id',
            'descripcion' => 'required|string|max:5000',
            'lugar_obtenido' => 'nullable|string|max:255',
            'fecha' => 'required|date',
            'directivos' => 'required|array|min:1|max:5',
            'directivos.*' => 'integer|exists:directivos,id',
            'estado' => 'required|in:borrador,revision',
            'marca_id' => 'nullable|integer|exists:marcas,id',
            'proyecto_creativo_id' => 'nullable|integer|exists:proyectos_creativos,id',
            'persona_id' => 'nullable|integer|exists:personas,id',
            'generarValidacion' => 'boolean',
        ];

        if ($this->modo === 'masivo') {
            $rules['credencialesSeleccionadas'] = 'required|array|min:1|max:300';
            $rules['credencialesSeleccionadas.*'] = 'integer|exists:credenciales,id';
        } else {
            $rules['reconocimiento'] = 'required|string|min:3|max:255';
        }

        return $rules;
    }

    protected $messages = [
        'reconocimiento_imagen_id.required' => 'Selecciona una plantilla.',
        'reconocimiento.required' => 'Escribe el nombre del destinatario.',
        'descripcion.required' => 'La descripción es obligatoria.',
        'directivos.required' => 'Selecciona al menos un firmante.',
        'directivos.max' => 'Selecciona como máximo cinco firmantes.',
        'credencialesSeleccionadas.required' => 'Selecciona al menos un alumno.',
    ];

    public function updatedMarcaId($value): void
    {
        if ($this->proyecto_creativo_id && ! ProyectoCreativo::query()
            ->whereKey($this->proyecto_creativo_id)
            ->when($value, fn($query) => $query->where('marca_id', $value))
            ->exists()) {
            $this->proyecto_creativo_id = null;
        }

        if ($this->persona_id && ! Persona::query()
            ->whereKey($this->persona_id)
            ->when($value, fn($query) => $query->where('marca_id', $value))
            ->exists()) {
            $this->persona_id = null;
        }
    }

    public function updatedPersonaId($value): void
    {
        if ($this->modo !== 'individual' || ! $value || ! ($persona = Persona::find($value))) {
            return;
        }

        $this->reconocimiento = $persona->nombre;
        $this->marca_id ??= $persona->marca_id;
    }

    public function updatedProyectoCreativoId($value): void
    {
        if (! $value || ! ($proyecto = ProyectoCreativo::find($value))) {
            return;
        }

        $this->marca_id ??= $proyecto->marca_id;
    }

    public function updatedReconocimientoTipoId($value): void
    {
        if (!$value || !($tipo = ReconocimientoTipo::find($value)))
            return;
        $this->descripcion = $tipo->descripcion;
        if ($tipo->reconocimiento_imagen_id) {
            $this->reconocimiento_imagen_id = (int) $tipo->reconocimiento_imagen_id;
            $this->resetValidation('reconocimiento_imagen_id');
        }
        if (!$tipo->usa_lugar)
            $this->lugar_obtenido = null;
        $this->dispatch('reconocimiento-descripcion-actualizada', html: $this->descripcion);
    }

    public function updatedReconocimientoEventoId($value): void
    {
        if (!$value || !($evento = ReconocimientoEvento::find($value)))
            return;
        $this->fecha = optional($evento->fecha)->toDateString() ?: $this->fecha;
        $this->reconocimiento_tipo_id = $evento->reconocimiento_tipo_id ?: $this->reconocimiento_tipo_id;
        if ($evento->reconocimiento_tipo_id)
            $this->updatedReconocimientoTipoId($evento->reconocimiento_tipo_id);
        if ($evento->reconocimiento_imagen_id) {
            $this->reconocimiento_imagen_id = (int) $evento->reconocimiento_imagen_id;
            $this->resetValidation('reconocimiento_imagen_id');
        }
    }

    public function seleccionarPlantilla(int $plantillaId): void
    {
        $plantilla = ReconocimientoImagen::query()
            ->whereKey($plantillaId)
            ->where('activo', true)
            ->first();

        if (! $plantilla) {
            $this->reconocimiento_imagen_id = null;
            $this->addError('reconocimiento_imagen_id', 'La plantilla seleccionada ya no está disponible.');
            return;
        }

        $this->reconocimiento_imagen_id = (int) $plantilla->id;
        $this->resetValidation('reconocimiento_imagen_id');
    }

    public function limpiarSeleccion(): void
    {
        $this->reconocimiento_imagen_id = null;
        $this->resetValidation('reconocimiento_imagen_id');
    }

    public function seleccionarPagina(array $ids): void
    {
        $this->credencialesSeleccionadas = array_values(array_unique(array_merge($this->credencialesSeleccionadas, array_map('intval', $ids))));
    }

    public function limpiarAlumnos(): void
    {
        $this->credencialesSeleccionadas = [];
    }

    public function importarCsv(): void
    {
        $this->validate(['archivoCsv' => 'required|file|mimes:csv,txt|max:2048']);
        $handle = fopen($this->archivoCsv->getRealPath(), 'r');
        $ids = [];
        $noEncontrados = [];
        $primera = true;

        while (($row = fgetcsv($handle, 0, ',')) !== false) {
            if ($primera) {
                $primera = false;
                if (isset($row[0]) && in_array(mb_strtolower(trim($row[0])), ['matricula', 'matrícula', 'nombre'], true))
                    continue;
            }
            $dato = trim((string) ($row[0] ?? ''));
            if ($dato === '')
                continue;
            $credencial = Credencial::where('matricula', $dato)->orWhere('nombre', $dato)->first();
            $credencial ? $ids[] = $credencial->id : $noEncontrados[] = $dato;
        }
        fclose($handle);

        $this->credencialesSeleccionadas = array_values(array_unique(array_merge($this->credencialesSeleccionadas, $ids)));
        $this->archivoCsv = null;
        $this->dispatch('swal', [
            'title' => count($ids) . ' destinatario(s) importado(s)' . ($noEncontrados ? '; ' . count($noEncontrados) . ' no encontrado(s)' : ''),
            'icon' => $noEncontrados ? 'warning' : 'success',
            'position' => 'top-end',
        ]);
    }

    private function nuevoCodigoValidacion(): string
    {
        do {
            $codigo = strtoupper(Str::random(4) . '-' . Str::random(4) . '-' . Str::random(4));
        } while (RegistroValidacion::where('codigo', $codigo)->exists());

        return $codigo;
    }

    public function guardarReconocimiento(): void
    {
        if ($this->reconocimiento_imagen_id !== null) {
            $this->reconocimiento_imagen_id = (int) $this->reconocimiento_imagen_id;
        }

        $this->validate();
        $descripcion = ReconocimientoHtml::limpiar($this->descripcion);
        $destinatarios = $this->modo === 'masivo'
            ? Credencial::whereIn('id', $this->credencialesSeleccionadas)->orderBy('nombre')->get()
            : collect([null]);

        DB::transaction(function () use ($destinatarios, $descripcion) {
            foreach ($destinatarios as $credencial) {
                $nombreDestinatario = trim($credencial?->nombre ?? $this->reconocimiento);
                $personaId = $credencial?->persona_id ?: ($credencial ? null : $this->persona_id);
                $validation = null;

                if ($this->generarValidacion && Schema::hasTable('registros_validacion')) {
                    $validation = RegistroValidacion::create([
                        'user_id' => auth()->id(),
                        'persona_id' => $personaId,
                        'proyecto_creativo_id' => $this->proyecto_creativo_id,
                        'codigo' => $this->nuevoCodigoValidacion(),
                        'tipo' => 'reconocimiento',
                        'titulo' => 'Reconocimiento de ' . $nombreDestinatario,
                        'estado' => 'valido',
                        'emitido_at' => $this->fecha,
                        'datos_publicos' => [
                            'descripcion' => trim(strip_tags($descripcion)),
                            'destinatario' => $nombreDestinatario,
                        ],
                    ]);
                }

                $rec = Reconocimiento::create([
                    'marca_id' => $this->marca_id,
                    'proyecto_creativo_id' => $this->proyecto_creativo_id,
                    'persona_id' => $personaId,
                    'registro_validacion_id' => $validation?->id,
                    'reconocimiento_evento_id' => $this->reconocimiento_evento_id,
                    'reconocimiento_tipo_id' => $this->reconocimiento_tipo_id,
                    'credencial_id' => $credencial?->id,
                    'destinatario_tipo' => $credencial ? 'credencial' : ($this->persona_id ? 'persona' : 'externo'),
                    'reconocimiento_imagen_id' => $this->reconocimiento_imagen_id,
                    'reconocimiento_a' => $nombreDestinatario,
                    'descripcion' => $descripcion,
                    'lugar_obtenido' => $this->lugar_obtenido ? trim($this->lugar_obtenido) : null,
                    'fecha' => $this->fecha,
                    'estado' => $this->estado,
                    'created_by' => auth()->id(),
                ]);
                $rec->directivos()->sync($this->directivos);
                $rec->registrarHistorial('creado', $credencial ? 'Creado mediante generación masiva.' : 'Creado manualmente.');
            }
        });

        $total = $destinatarios->count();
        $this->dispatch('swal', ['title' => $total . ' reconocimiento(s) creado(s) correctamente', 'icon' => 'success', 'position' => 'top-end']);
        $this->dispatch('reconocimientoCreado');
        $this->resetFormulario();
    }

    public function resetFormulario(): void
    {
        $this->reset([
            'reconocimiento_imagen_id',
            'reconocimiento_evento_id',
            'reconocimiento_tipo_id',
            'reconocimiento',
            'descripcion',
            'lugar_obtenido',
            'directivos',
            'estado',
            'marca_id',
            'proyecto_creativo_id',
            'persona_id',
            'generarValidacion',
            'credencialesSeleccionadas',
            'archivoCsv',
        ]);
        $this->estado = 'borrador';
        $this->generarValidacion = true;
        $this->fecha = now()->toDateString();
        $this->dispatch('reconocimiento-descripcion-actualizada', html: '');
        $this->resetValidation();
    }

    public function render()
    {
        $credenciales = Credencial::query()
            ->when($this->buscarAlumno, fn($q) => $q->where(fn($s) => $s->where('nombre', 'like', '%' . $this->buscarAlumno . '%')->orWhere('matricula', 'like', '%' . $this->buscarAlumno . '%')))
            ->when($this->nivelFiltro, fn($q) => $q->where('nivel', $this->nivelFiltro))
            ->when($this->gradoFiltro, fn($q) => $q->where('grado', $this->gradoFiltro))
            ->when($this->grupoFiltro, fn($q) => $q->where('grupo', $this->grupoFiltro))
            ->when($this->licenciaturaFiltro, fn($q) => $q->where('licenciatura', $this->licenciaturaFiltro))
            ->orderBy('nombre')->limit(100)->get();

        $marcas = Schema::hasTable('marcas')
            ? Marca::query()->where('activo', true)->orderBy('nombre')->get()
            : collect();
        $proyectosCreativos = Schema::hasTable('proyectos_creativos')
            ? ProyectoCreativo::query()->when($this->marca_id, fn($query) => $query->where('marca_id', $this->marca_id))->orderByDesc('created_at')->get()
            : collect();
        $personas = Schema::hasTable('personas')
            ? Persona::query()->where('activo', true)->when($this->marca_id, fn($query) => $query->where('marca_id', $this->marca_id))->orderBy('nombre')->limit(300)->get()
            : collect();

        return view('livewire.reconocimientos.creacion-reconocimientos', [
            'reconocimientosImagenes' => ReconocimientoImagen::where('activo', true)->latest()->get(),
            'directivosLista' => Directivo::where('activo', true)->orderBy('orden')->orderBy('id')->get(),
            'eventos' => ReconocimientoEvento::where('estado', 'activo')->orderByDesc('fecha')->orderBy('nombre')->get(),
            'tipos' => ReconocimientoTipo::where('activo', true)->orderBy('nombre')->get(),
            'credenciales' => $credenciales,
            'niveles' => Credencial::whereNotNull('nivel')->distinct()->orderBy('nivel')->pluck('nivel'),
            'grados' => Credencial::whereNotNull('grado')->distinct()->orderBy('grado')->pluck('grado'),
            'grupos' => Credencial::whereNotNull('grupo')->distinct()->orderBy('grupo')->pluck('grupo'),
            'licenciaturas' => Credencial::whereNotNull('licenciatura')->distinct()->orderBy('licenciatura')->pluck('licenciatura'),
            'marcas' => $marcas,
            'proyectosCreativos' => $proyectosCreativos,
            'personas' => $personas,
        ]);
    }
}
