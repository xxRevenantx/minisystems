<?php

namespace App\Livewire\Credencial;

use App\Models\Credencial;
use Illuminate\Validation\Rule;
use Livewire\Attributes\On;
use Livewire\Component;
use Livewire\WithPagination;

class MostrarCredenciales extends Component
{
    use WithPagination;

    public string $buscar = '';

    public int $porPagina = 10;

    public bool $modalEditar = false;

    public ?int $credencialId = null;

    public string $nombre = '';
    public string $matricula = '';
    public string $curp = '';
    public string $nivel = '';

    public ?string $grado = null;
    public ?string $grupo = null;
    public ?string $licenciatura = null;
    public ?string $ciclo_escolar = null;
    public ?string $vigencia = null;
    public ?string $telefono = null;
    public ?string $domicilio = null;

    public array $niveles = [
        'Preescolar',
        'Primaria',
        'Secundaria',
        'Bachillerato',
        'Licenciatura',
    ];

    public array $grados = [
        '1°',
        '2°',
        '3°',
        '4°',
        '5°',
        '6°',
    ];

    public array $grupos = [
        'A',
        'B',
        'C',
        'D',
    ];

    public array $licenciaturas = [
        'Arquitectura y Diseño de Interiores',
        'Contaduría Pública',
        'Cultura Física y Deportes',
        'Ciencias de la Educación',
        'Criminalística, Criminología y Técnicas Periciales',
        'Ciencias Políticas y Administración Pública',
        'Administración Empresarial',
        'Nutrición',
    ];

    protected function rules(): array
    {
        return [
            'nombre' => ['required', 'string', 'min:3', 'max:255'],
            'matricula' => ['required', 'string', 'max:255'],
            'curp' => ['required', 'string', 'size:18'],

            'nivel' => [
                'required',
                'string',
                Rule::in($this->niveles),
            ],

            'grado' => [
                Rule::requiredIf(fn() => $this->nivel !== 'Licenciatura'),
                'nullable',
                'string',
                'max:255',
            ],

            'grupo' => [
                Rule::requiredIf(fn() => $this->nivel !== 'Licenciatura'),
                'nullable',
                'string',
                'max:255',
            ],

            'licenciatura' => [
                Rule::requiredIf(fn() => $this->nivel === 'Licenciatura'),
                'nullable',
                'string',
                Rule::in($this->licenciaturas),
            ],

            'ciclo_escolar' => ['nullable', 'string', 'max:255'],
            'vigencia' => ['nullable', 'string', 'max:255'],
            'telefono' => ['nullable', 'string', 'max:20'],
            'domicilio' => ['nullable', 'string', 'max:255'],
        ];
    }

    protected array $messages = [
        'nombre.required' => 'El nombre es obligatorio.',
        'nombre.min' => 'El nombre debe tener al menos 3 caracteres.',

        'matricula.required' => 'La matrícula es obligatoria.',

        'curp.required' => 'La CURP es obligatoria.',
        'curp.size' => 'La CURP debe tener exactamente 18 caracteres.',

        'nivel.required' => 'Selecciona un nivel.',
        'nivel.in' => 'El nivel seleccionado no es válido.',

        'grado.required' => 'Selecciona el grado.',
        'grupo.required' => 'Selecciona el grupo.',

        'licenciatura.required' => 'Selecciona la licenciatura.',
        'licenciatura.in' => 'La licenciatura seleccionada no es válida.',

        'telefono.max' => 'El teléfono no debe exceder 20 caracteres.',
        'domicilio.max' => 'El domicilio no debe exceder 255 caracteres.',
    ];

    public function updatingBuscar(): void
    {
        $this->resetPage();
    }

    public function updatedNivel(): void
    {
        $this->resetErrorBag(['nivel', 'grado', 'grupo', 'licenciatura']);

        if ($this->nivel === 'Licenciatura') {
            $this->grado = null;
            $this->grupo = null;
            return;
        }

        $this->licenciatura = null;
    }

    #[On('credencial-creada')]
    public function actualizarTabla(): void
    {
        $this->resetPage();
    }

    public function abrirEditar(int $id): void
    {
        $credencial = Credencial::findOrFail($id);

        $this->credencialId = $credencial->id;
        $this->nombre = $credencial->nombre;
        $this->matricula = $credencial->matricula;
        $this->curp = $credencial->curp;
        $this->nivel = $credencial->nivel;
        $this->grado = $credencial->grado;
        $this->grupo = $credencial->grupo;
        $this->licenciatura = $credencial->licenciatura;
        $this->ciclo_escolar = $credencial->ciclo_escolar;
        $this->vigencia = $credencial->vigencia;
        $this->telefono = $credencial->telefono;
        $this->domicilio = $credencial->domicilio;

        $this->resetErrorBag();

        $this->modalEditar = true;
    }

    public function actualizar(): void
    {
        $datos = $this->validate();

        if ($this->nivel === 'Licenciatura') {
            $datos['grado'] = null;
            $datos['grupo'] = null;
        } else {
            $datos['licenciatura'] = null;
        }

        $credencial = Credencial::findOrFail($this->credencialId);

        $credencial->update($datos);

        $this->cerrarModal();

        $this->dispatch('credencial-actualizada');
    }

    public function eliminar(int $id): void
    {
        $credencial = Credencial::findOrFail($id);

        $credencial->delete();

        if ($this->credencialesEnPaginaActual() <= 1 && $this->getPage() > 1) {
            $this->previousPage();
        }

        $this->dispatch('credencial-eliminada');
    }

    private function credencialesEnPaginaActual(): int
    {
        return Credencial::query()
            ->when($this->buscar !== '', function ($query) {
                $query->where(function ($subquery) {
                    $subquery->where('nombre', 'like', '%' . $this->buscar . '%')
                        ->orWhere('matricula', 'like', '%' . $this->buscar . '%')
                        ->orWhere('curp', 'like', '%' . $this->buscar . '%')
                        ->orWhere('nivel', 'like', '%' . $this->buscar . '%')
                        ->orWhere('licenciatura', 'like', '%' . $this->buscar . '%');
                });
            })
            ->paginate($this->porPagina)
            ->count();
    }

    public function cerrarModal(): void
    {
        $this->modalEditar = false;

        $this->limpiarCampos();
    }

    public function limpiarCampos(): void
    {
        $this->reset([
            'credencialId',
            'nombre',
            'matricula',
            'curp',
            'nivel',
            'grado',
            'grupo',
            'licenciatura',
            'ciclo_escolar',
            'vigencia',
            'telefono',
            'domicilio',
        ]);

        $this->resetErrorBag();
    }

    public function render()
    {
        $credenciales = Credencial::query()
            ->when($this->buscar !== '', function ($query) {
                $query->where(function ($subquery) {
                    $subquery->where('nombre', 'like', '%' . $this->buscar . '%')
                        ->orWhere('matricula', 'like', '%' . $this->buscar . '%')
                        ->orWhere('curp', 'like', '%' . $this->buscar . '%')
                        ->orWhere('nivel', 'like', '%' . $this->buscar . '%')
                        ->orWhere('grado', 'like', '%' . $this->buscar . '%')
                        ->orWhere('grupo', 'like', '%' . $this->buscar . '%')
                        ->orWhere('licenciatura', 'like', '%' . $this->buscar . '%')
                        ->orWhere('ciclo_escolar', 'like', '%' . $this->buscar . '%');
                });
            })
            ->latest()
            ->paginate($this->porPagina);

        return view('livewire.credencial.mostrar-credenciales', [
            'credenciales' => $credenciales,
        ]);
    }
}
