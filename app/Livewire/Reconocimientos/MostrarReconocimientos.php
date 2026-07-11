<?php

namespace App\Livewire\Reconocimientos;

use App\Models\Reconocimiento;
use App\Models\ReconocimientoEvento;
use App\Models\ReconocimientoImagen;
use App\Models\ReconocimientoTipo;
use Illuminate\Support\Facades\DB;
use Livewire\Attributes\On;
use Livewire\Component;
use Livewire\WithPagination;

class MostrarReconocimientos extends Component
{
    use WithPagination;

    public string $search = '';
    public int $perPage = 10;
    public string $estadoFiltro = '';
    public string $eventoFiltro = '';
    public string $tipoFiltro = '';
    public string $plantillaFiltro = '';
    public string $fechaDesde = '';
    public string $fechaHasta = '';
    public bool $verPapelera = false;
    public array $seleccionados = [];
    public string $accionMasiva = '';
    public string $estadoMasivo = '';
    public string $plantillaMasiva = '';
    public string $eventoMasivo = '';
    public string $fechaMasiva = '';
    public string $metodoEntrega = 'impreso';
    public string $recibidoPor = '';
    public string $observacionesEntrega = '';
    public string $motivoCancelacion = '';

    protected $queryString = [
        'search' => ['except' => ''], 'estadoFiltro' => ['except' => ''],
        'eventoFiltro' => ['except' => ''], 'tipoFiltro' => ['except' => ''],
    ];

    public function mount(): void
    {
        abort_unless(auth()->user()?->puedeReconocimientos('ver'), 403);
    }

    public function updating($name): void
    {
        if (in_array($name, ['search','estadoFiltro','eventoFiltro','tipoFiltro','plantillaFiltro','fechaDesde','fechaHasta','verPapelera'], true)) {
            $this->resetPage();
            $this->seleccionados = [];
        }
    }

    public function clearFilters(): void
    {
        $this->reset(['search','estadoFiltro','eventoFiltro','tipoFiltro','plantillaFiltro','fechaDesde','fechaHasta']);
        $this->resetPage();
    }

    protected function baseQuery()
    {
        return Reconocimiento::query()
            ->when($this->verPapelera, fn($q) => $q->onlyTrashed())
            ->with(['directivos', 'evento', 'tipo', 'reconocimientoImagen'])
            ->when(trim($this->search), function ($q) {
                $s = '%'.trim($this->search).'%';
                $q->where(fn($w) => $w->where('reconocimiento_a', 'like', $s)
                    ->orWhere('descripcion', 'like', $s)
                    ->orWhere('lugar_obtenido', 'like', $s)
                    ->orWhereHas('directivos', fn($d) => $d->where('nombre', 'like', $s)->orWhere('cargo', 'like', $s))
                    ->orWhereHas('evento', fn($e) => $e->where('nombre', 'like', $s)));
            })
            ->when($this->estadoFiltro, fn($q) => $q->where('estado', $this->estadoFiltro))
            ->when($this->eventoFiltro, fn($q) => $q->where('reconocimiento_evento_id', $this->eventoFiltro))
            ->when($this->tipoFiltro, fn($q) => $q->where('reconocimiento_tipo_id', $this->tipoFiltro))
            ->when($this->plantillaFiltro, fn($q) => $q->where('reconocimiento_imagen_id', $this->plantillaFiltro))
            ->when($this->fechaDesde, fn($q) => $q->whereDate('fecha', '>=', $this->fechaDesde))
            ->when($this->fechaHasta, fn($q) => $q->whereDate('fecha', '<=', $this->fechaHasta));
    }

    public function seleccionarPagina(array $ids): void
    {
        $this->seleccionados = array_values(array_unique(array_merge($this->seleccionados, array_map('intval', $ids))));
    }

    public function aplicarAccionMasiva(): void
    {
        abort_unless(auth()->user()?->puedeReconocimientos('editar'), 403);
        $this->validate(['seleccionados' => 'required|array|min:1', 'accionMasiva' => 'required|in:estado,plantilla,evento,fecha,entregar,cancelar,papelera']);
        $query = Reconocimiento::whereIn('id', $this->seleccionados);

        DB::transaction(function () use ($query) {
            $items = $query->get();
            foreach ($items as $rec) {
                switch ($this->accionMasiva) {
                    case 'estado':
                        $this->validate(['estadoMasivo' => 'required|in:borrador,revision,aprobado,generado']);
                        $this->cambiarEstado($rec, $this->estadoMasivo);
                        break;
                    case 'plantilla':
                        $this->validate(['plantillaMasiva' => 'required|exists:reconocimiento_imagenes,id']);
                        $rec->update(['reconocimiento_imagen_id' => $this->plantillaMasiva]);
                        $rec->registrarHistorial('plantilla_actualizada', 'Plantilla modificada mediante acción masiva.');
                        break;
                    case 'evento':
                        $this->validate(['eventoMasivo' => 'required|exists:reconocimiento_eventos,id']);
                        $rec->update(['reconocimiento_evento_id' => $this->eventoMasivo]);
                        $rec->registrarHistorial('evento_actualizado', 'Evento modificado mediante acción masiva.');
                        break;
                    case 'fecha':
                        $this->validate(['fechaMasiva' => 'required|date']);
                        $rec->update(['fecha' => $this->fechaMasiva]);
                        $rec->registrarHistorial('fecha_actualizada', 'Fecha modificada mediante acción masiva.');
                        break;
                    case 'entregar':
                        $rec->update([
                            'estado' => 'entregado', 'delivered_at' => now(),
                            'delivery_method' => $this->metodoEntrega,
                            'delivery_to' => $this->recibidoPor ?: null,
                            'delivery_notes' => $this->observacionesEntrega ?: null,
                        ]);
                        $rec->registrarHistorial('entregado', 'Entrega registrada mediante acción masiva.');
                        break;
                    case 'cancelar':
                        abort_unless(auth()->user()?->puedeReconocimientos('cancelar'), 403);
                        $this->validate(['motivoCancelacion' => 'required|string|min:5|max:1000']);
                        $rec->update(['estado' => 'cancelado', 'cancel_reason' => $this->motivoCancelacion]);
                        $rec->registrarHistorial('cancelado', $this->motivoCancelacion);
                        break;
                    case 'papelera':
                        $rec->registrarHistorial('enviado_papelera', 'Eliminación lógica mediante acción masiva.');
                        $rec->delete();
                        break;
                }
            }
        });

        $this->seleccionados = [];
        $this->reset(['accionMasiva','estadoMasivo','plantillaMasiva','eventoMasivo','fechaMasiva','recibidoPor','observacionesEntrega','motivoCancelacion']);
        $this->dispatch('swal', ['title' => 'Acción masiva aplicada correctamente', 'icon' => 'success', 'position' => 'top-end']);
    }

    protected function cambiarEstado(Reconocimiento $rec, string $estado): void
    {
        $data = ['estado' => $estado];
        if ($estado === 'aprobado') {
            abort_unless(auth()->user()?->puedeReconocimientos('aprobar'), 403);
            $data += ['approved_by' => auth()->id(), 'approved_at' => now()];
        }
        if ($estado === 'generado') $data['generated_at'] = now();
        $rec->update($data);
        $rec->registrarHistorial('estado_actualizado', 'Estado cambiado a '.$estado.'.');
    }

    public function duplicar(int $id): void
    {
        abort_unless(auth()->user()?->puedeReconocimientos('crear'), 403);
        $original = Reconocimiento::with('directivos')->findOrFail($id);
        $copia = $original->replicate(['approved_by','approved_at','generated_at','delivered_at','delivery_method','delivery_to','delivery_notes','cancel_reason']);
        $copia->estado = 'borrador';
        $copia->version = 1;
        $copia->duplicado_de_id = $original->id;
        $copia->created_by = auth()->id();
        $copia->save();
        $copia->directivos()->sync($original->directivos->pluck('id'));
        $copia->registrarHistorial('duplicado', 'Duplicado del reconocimiento #'.$original->id.'.');
        $this->dispatch('swal', ['title' => 'Reconocimiento duplicado', 'icon' => 'success', 'position' => 'top-end']);
    }

    public function eliminarReconocimiento(int $id): void
    {
        abort_unless(auth()->user()?->puedeReconocimientos('cancelar'), 403);
        $rec = Reconocimiento::findOrFail($id);
        $rec->registrarHistorial('enviado_papelera', 'Enviado a la papelera.');
        $rec->delete();
    }

    public function restaurar(int $id): void
    {
        abort_unless(auth()->user()?->puedeReconocimientos('administrar'), 403);
        $rec = Reconocimiento::onlyTrashed()->findOrFail($id);
        $rec->restore();
        $rec->registrarHistorial('restaurado', 'Restaurado desde la papelera.');
    }

    public function eliminarDefinitivo(int $id): void
    {
        abort_unless(auth()->user()?->puedeReconocimientos('administrar'), 403);
        Reconocimiento::onlyTrashed()->findOrFail($id)->forceDelete();
    }

    #[On('reconocimientoCreado')]
    public function refreshList(): void { $this->resetPage(); }

    public function render()
    {
        $reconocimientos = $this->baseQuery()->latest('id')->paginate($this->perPage);
        $stats = [
            'total' => Reconocimiento::count(),
            'borrador' => Reconocimiento::where('estado','borrador')->count(),
            'revision' => Reconocimiento::where('estado','revision')->count(),
            'aprobado' => Reconocimiento::where('estado','aprobado')->count(),
            'generado' => Reconocimiento::where('estado','generado')->count(),
            'entregado' => Reconocimiento::where('estado','entregado')->count(),
            'cancelado' => Reconocimiento::where('estado','cancelado')->count(),
        ];

        $query = http_build_query(array_filter([
            'ids' => $this->seleccionados ? implode(',', $this->seleccionados) : null,
            'search' => $this->search ?: null, 'estado' => $this->estadoFiltro ?: null,
            'evento' => $this->eventoFiltro ?: null, 'tipo' => $this->tipoFiltro ?: null,
            'plantilla' => $this->plantillaFiltro ?: null, 'desde' => $this->fechaDesde ?: null,
            'hasta' => $this->fechaHasta ?: null,
        ]));

        return view('livewire.reconocimientos.mostrar-reconocimientos', [
            'reconocimientos' => $reconocimientos, 'stats' => $stats,
            'eventos' => ReconocimientoEvento::orderByDesc('fecha')->get(),
            'tipos' => ReconocimientoTipo::orderBy('nombre')->get(),
            'plantillas' => ReconocimientoImagen::orderBy('nombre')->orderBy('id')->get(),
            'downloadUrl' => route('descargar.reconocimientos').($query ? '?'.$query : ''),
        ]);
    }
}
