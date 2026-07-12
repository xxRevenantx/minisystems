<?php

namespace App\Livewire\Reconocimientos;

use App\Models\Credencial;
use App\Models\Directivo;
use App\Models\Reconocimiento;
use App\Models\ReconocimientoEvento;
use App\Models\ReconocimientoImagen;
use App\Models\ReconocimientoTipo;
use App\Support\ReconocimientoHtml;
use Illuminate\Support\Facades\DB;
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

    public function updatedReconocimientoTipoId($value): void
    {
        if (!$value || !($tipo = ReconocimientoTipo::find($value)))
            return;
        $this->descripcion = $tipo->descripcion;
        $this->reconocimiento_imagen_id = $tipo->reconocimiento_imagen_id ?: $this->reconocimiento_imagen_id;
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
        $this->reconocimiento_imagen_id = $evento->reconocimiento_imagen_id ?: $this->reconocimiento_imagen_id;
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

    public function guardarReconocimiento(): void
    {
        $this->validate();
        $descripcion = ReconocimientoHtml::limpiar($this->descripcion);
        $destinatarios = $this->modo === 'masivo'
            ? Credencial::whereIn('id', $this->credencialesSeleccionadas)->orderBy('nombre')->get()
            : collect([null]);

        DB::transaction(function () use ($destinatarios, $descripcion) {
            foreach ($destinatarios as $credencial) {
                $rec = Reconocimiento::create([
                    'reconocimiento_evento_id' => $this->reconocimiento_evento_id,
                    'reconocimiento_tipo_id' => $this->reconocimiento_tipo_id,
                    'credencial_id' => $credencial?->id,
                    'destinatario_tipo' => $credencial ? 'alumno' : 'externo',
                    'reconocimiento_imagen_id' => $this->reconocimiento_imagen_id,
                    'reconocimiento_a' => trim($credencial?->nombre ?? $this->reconocimiento),
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
            'credencialesSeleccionadas',
            'archivoCsv',
        ]);
        $this->estado = 'borrador';
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
        ]);
    }
}
