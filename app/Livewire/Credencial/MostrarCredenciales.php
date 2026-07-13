<?php

namespace App\Livewire\Credencial;

use App\Models\Credencial;
use App\Services\CreativeActivity;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\File;
use Livewire\Attributes\On;
use Livewire\Component;
use Livewire\WithFileUploads;
use Livewire\WithPagination;

class MostrarCredenciales extends Component
{
    use WithPagination, WithFileUploads;

    public string $buscar = '';
    public int $porPagina = 12;
    public bool $modalEditar = false;
    public ?int $credencialId = null;

    public string $tipo = 'general';
    public string $nombre = '';
    public string $folio = '';
    public string $cargo = '';
    public string $organizacion = '';
    public string $correo = '';
    public string $telefono = '';
    public string $domicilio = '';
    public string $vigencia = '';
    public string $estado = 'activa';
    public bool $tiene_reverso = false;
    public string $reverso_texto = '';
    public $reverso_imagen;
    public ?string $reversoImagenActual = null;
    public bool $eliminarReversoImagen = false;

    public string $matricula = '';
    public string $curp = '';
    public string $nivel = '';
    public ?string $grado = null;
    public ?string $grupo = null;
    public ?string $licenciatura = null;
    public ?string $ciclo_escolar = null;

    public array $tipos = [
        'general' => 'Identificación general',
        'evento' => 'Gafete de evento',
        'empleado' => 'Personal o empleado',
        'visitante' => 'Visitante',
        'membresia' => 'Membresía',
        'escolar' => 'Escolar',
    ];
    public array $niveles = ['Preescolar','Primaria','Secundaria','Bachillerato','Licenciatura'];
    public array $grados = ['1°','2°','3°','4°','5°','6°'];
    public array $grupos = ['A','B','C','D'];
    public array $licenciaturas = [
        'Arquitectura y Diseño de Interiores','Contaduría Pública','Cultura Física y Deportes',
        'Ciencias de la Educación','Criminalística, Criminología y Técnicas Periciales',
        'Ciencias Políticas y Administración Pública','Administración Empresarial','Nutrición',
    ];

    protected function rules(): array
    {
        return [
            'tipo' => ['required', Rule::in(array_keys($this->tipos))],
            'nombre' => ['required','string','min:3','max:255'],
            'folio' => ['nullable','string','max:100'],
            'cargo' => ['nullable','string','max:180'],
            'organizacion' => ['nullable','string','max:180'],
            'correo' => ['nullable','email','max:180'],
            'telefono' => ['nullable','string','max:40'],
            'domicilio' => ['nullable','string','max:500'],
            'vigencia' => ['nullable','string','max:120'],
            'estado' => ['required','in:activa,inactiva,vencida,cancelada'],
            'tiene_reverso' => ['boolean'],
            'reverso_texto' => ['nullable','string','max:3000'],
            'reverso_imagen' => ['nullable', File::image()->types(['jpg','jpeg','png','webp'])->max(10 * 1024)],
            'eliminarReversoImagen' => ['boolean'],
            'matricula' => [Rule::requiredIf(fn () => $this->tipo === 'escolar'),'nullable','string','max:255'],
            'curp' => ['nullable','string','size:18'],
            'nivel' => [Rule::requiredIf(fn () => $this->tipo === 'escolar'),'nullable',Rule::in($this->niveles)],
            'grado' => [Rule::requiredIf(fn () => $this->tipo === 'escolar' && $this->nivel !== 'Licenciatura'),'nullable','string'],
            'grupo' => [Rule::requiredIf(fn () => $this->tipo === 'escolar' && $this->nivel !== 'Licenciatura'),'nullable','string'],
            'licenciatura' => [Rule::requiredIf(fn () => $this->tipo === 'escolar' && $this->nivel === 'Licenciatura'),'nullable','string'],
            'ciclo_escolar' => ['nullable','string','max:100'],
        ];
    }

    public function updatingBuscar(): void { $this->resetPage(); }

    public function updatedTipo(): void
    {
        if ($this->tipo !== 'escolar') {
            $this->matricula = $this->curp = $this->nivel = '';
            $this->grado = $this->grupo = $this->licenciatura = $this->ciclo_escolar = null;
        }
        $this->resetValidation();
    }

    public function updatedNivel(): void
    {
        if ($this->nivel === 'Licenciatura') {
            $this->grado = $this->grupo = null;
        } else {
            $this->licenciatura = null;
        }
    }

    #[On('credencial-creada')]
    public function actualizarTabla(): void { $this->resetPage(); }

    public function abrirEditar(int $id): void
    {
        $c = Credencial::findOrFail($id);
        $this->credencialId = $c->id;
        foreach ([
            'tipo','nombre','folio','cargo','organizacion','correo','telefono','domicilio','vigencia',
            'estado','matricula','curp','nivel','grado','grupo','licenciatura','ciclo_escolar','reverso_texto'
        ] as $field) {
            $this->{$field} = $c->{$field} ?? (in_array($field, ['grado','grupo','licenciatura','ciclo_escolar'], true) ? null : '');
        }
        $this->tipo = $c->tipo ?: 'general';
        $this->estado = $c->estado ?: 'activa';
        $this->tiene_reverso = (bool) $c->tiene_reverso;
        $this->reversoImagenActual = $c->reverso_imagen;
        $this->reverso_imagen = null;
        $this->eliminarReversoImagen = false;
        $this->resetValidation();
        $this->modalEditar = true;
    }

    public function actualizar(): void
    {
        $data = $this->validate();
        $credential = Credencial::findOrFail($this->credencialId);

        $reversePath = $credential->reverso_imagen;
        if ($this->eliminarReversoImagen || ! $data['tiene_reverso']) {
            if ($reversePath) {
                Storage::disk('public')->delete($reversePath);
            }
            $reversePath = null;
        }
        if ($this->reverso_imagen) {
            if ($reversePath) {
                Storage::disk('public')->delete($reversePath);
            }
            $reversePath = $this->reverso_imagen->store('creative/credentials/back', 'public');
        }

        if ($data['tipo'] !== 'escolar') {
            foreach (['matricula','curp','nivel','grado','grupo','licenciatura','ciclo_escolar'] as $field) {
                $data[$field] = null;
            }
        } elseif ($data['nivel'] === 'Licenciatura') {
            $data['grado'] = $data['grupo'] = null;
        } else {
            $data['licenciatura'] = null;
        }

        unset($data['reverso_imagen'], $data['eliminarReversoImagen']);
        $data['reverso_imagen'] = $reversePath;
        $data['reverso_texto'] = $data['tiene_reverso'] ? (trim((string) $data['reverso_texto']) ?: null) : null;
        $credential->update($data);
        CreativeActivity::log('credenciales', 'actualizar', $credential, $credential->nombre);
        $this->cerrarModal();
        $this->dispatch('credencial-actualizada');
    }

    public function eliminar(int $id): void
    {
        $credential = Credencial::with('registroValidacion')->findOrFail($id);

        foreach ([$credential->foto, $credential->reverso_imagen] as $path) {
            if ($path) {
                Storage::disk('public')->delete($path);
            }
        }

        if ($credential->registroValidacion) {
            $credential->registroValidacion->update([
                'estado' => 'cancelado',
                'notas' => trim(($credential->registroValidacion->notas ? $credential->registroValidacion->notas."\n" : '').'Credencial eliminada el '.now()->format('d/m/Y H:i').'.'),
            ]);
        }

        $credential->delete();
        CreativeActivity::log('credenciales', 'eliminar', $credential, $credential->nombre);
        if ($this->credencialesEnPaginaActual() <= 1 && $this->getPage() > 1) {
            $this->previousPage();
        }
        $this->dispatch('credencial-eliminada');
    }

    private function credencialesEnPaginaActual(): int
    {
        return $this->query()->paginate($this->porPagina)->count();
    }

    private function query()
    {
        return Credencial::with(['marca','proyectoCreativo','registroValidacion'])
            ->when($this->buscar !== '', function ($query) {
                $term = '%'.$this->buscar.'%';
                $query->where(function ($subquery) use ($term) {
                    $subquery->where('nombre','like',$term)
                        ->orWhere('folio','like',$term)
                        ->orWhere('cargo','like',$term)
                        ->orWhere('organizacion','like',$term)
                        ->orWhere('matricula','like',$term)
                        ->orWhere('curp','like',$term)
                        ->orWhere('nivel','like',$term);
                });
            });
    }

    public function cerrarModal(): void
    {
        $this->modalEditar = false;
        $this->limpiarCampos();
    }

    public function limpiarCampos(): void
    {
        $this->reset([
            'credencialId','nombre','folio','cargo','organizacion','correo','telefono','domicilio',
            'vigencia','matricula','curp','nivel','grado','grupo','licenciatura','ciclo_escolar',
            'reverso_texto','reverso_imagen','reversoImagenActual','eliminarReversoImagen',
        ]);
        $this->tipo = 'general';
        $this->estado = 'activa';
        $this->tiene_reverso = false;
        $this->resetValidation();
    }

    public function render()
    {
        return view('livewire.credencial.mostrar-credenciales', [
            'credenciales' => $this->query()->latest()->paginate($this->porPagina),
        ]);
    }
}
