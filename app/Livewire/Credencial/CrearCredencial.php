<?php

namespace App\Livewire\Credencial;

use App\Models\Credencial;
use App\Models\Marca;
use App\Models\Persona;
use App\Models\ProyectoCreativo;
use App\Models\RegistroValidacion;
use App\Services\CreativeActivity;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\File;
use Livewire\Component;
use Livewire\WithFileUploads;

class CrearCredencial extends Component
{
    use WithFileUploads;

    public ?int $marca_id = null;
    public ?int $proyecto_creativo_id = null;
    public ?int $persona_id = null;

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
    public $foto;
    public bool $generarValidacion = true;
    public bool $tiene_reverso = false;
    public string $reverso_texto = '';
    public $reverso_imagen;

    // Datos escolares opcionales y compatibles con credenciales anteriores.
    public string $matricula = '';
    public string $curp = '';
    public string $nivel = '';
    public ?string $grado = null;
    public ?string $grupo = null;
    public ?string $licenciatura = null;
    public ?string $ciclo_escolar = null;

    public bool $guardado = false;

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
            'marca_id' => ['nullable','exists:marcas,id'],
            'proyecto_creativo_id' => ['nullable','exists:proyectos_creativos,id'],
            'persona_id' => ['nullable','exists:personas,id'],
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
            'foto' => ['nullable', File::image()->types(['jpg','jpeg','png','webp'])->max(10 * 1024)],
            'generarValidacion' => ['boolean'],
            'tiene_reverso' => ['boolean'],
            'reverso_texto' => ['nullable','string','max:3000'],
            'reverso_imagen' => ['nullable', File::image()->types(['jpg','jpeg','png','webp'])->max(10 * 1024)],

            'matricula' => [Rule::requiredIf(fn () => $this->tipo === 'escolar'),'nullable','string','max:255'],
            'curp' => ['nullable','string','size:18'],
            'nivel' => [Rule::requiredIf(fn () => $this->tipo === 'escolar'),'nullable',Rule::in($this->niveles)],
            'grado' => [Rule::requiredIf(fn () => $this->tipo === 'escolar' && $this->nivel !== 'Licenciatura'),'nullable','string','max:50'],
            'grupo' => [Rule::requiredIf(fn () => $this->tipo === 'escolar' && $this->nivel !== 'Licenciatura'),'nullable','string','max:50'],
            'licenciatura' => [Rule::requiredIf(fn () => $this->tipo === 'escolar' && $this->nivel === 'Licenciatura'),'nullable','string','max:255'],
            'ciclo_escolar' => ['nullable','string','max:100'],
        ];
    }

    protected function messages(): array
    {
        return [
            'nombre.required' => 'El nombre es obligatorio.',
            'nombre.min' => 'El nombre debe tener al menos 3 caracteres.',
            'matricula.required' => 'La matrícula es obligatoria para una credencial escolar.',
            'nivel.required' => 'Selecciona el nivel escolar.',
            'grado.required' => 'Selecciona el grado.',
            'grupo.required' => 'Selecciona el grupo.',
            'licenciatura.required' => 'Selecciona la licenciatura.',
            'curp.size' => 'La CURP debe contener exactamente 18 caracteres.',
            'correo.email' => 'Escribe un correo electrónico válido.',
        ];
    }

    public function updatedPersonaId($value): void
    {
        if (! $value || ! ($persona = Persona::find($value))) {
            return;
        }

        $this->nombre = $persona->nombre;
        $this->cargo = $persona->cargo ?? '';
        $this->organizacion = $persona->organizacion ?? '';
        $this->correo = $persona->email ?? '';
        $this->telefono = $persona->telefono ?? '';
        $this->marca_id = $persona->marca_id;
        $this->folio = $persona->identificador ?? $this->folio;
    }

    public function updatedTipo(): void
    {
        $this->resetErrorBag();

        if ($this->tipo !== 'escolar') {
            $this->matricula = '';
            $this->curp = '';
            $this->nivel = '';
            $this->grado = null;
            $this->grupo = null;
            $this->licenciatura = null;
            $this->ciclo_escolar = null;
        }
    }

    public function updatedNivel(): void
    {
        $this->resetErrorBag(['nivel','grado','grupo','licenciatura']);

        if ($this->nivel === 'Licenciatura') {
            $this->grado = null;
            $this->grupo = null;
        } else {
            $this->licenciatura = null;
        }
    }

    public function generarFolio(): void
    {
        do {
            $folio = 'ID-'.now()->format('ym').'-'.strtoupper(Str::random(6));
        } while (Credencial::where('folio', $folio)->exists());

        $this->folio = $folio;
    }

    private function generarCodigoValidacion(): string
    {
        do {
            $code = strtoupper(Str::random(4).'-'.Str::random(4).'-'.Str::random(4));
        } while (RegistroValidacion::where('codigo', $code)->exists());

        return $code;
    }

    public function guardar(): void
    {
        $datos = $this->validate();

        if (! $this->folio) {
            $this->generarFolio();
            $datos['folio'] = $this->folio;
        }

        $fotoPath = $this->foto?->store('creative/credentials', 'public');
        $reversePath = $this->tiene_reverso ? $this->reverso_imagen?->store('creative/credentials/back', 'public') : null;

        if ($datos['tipo'] !== 'escolar') {
            $datos['matricula'] = null;
            $datos['curp'] = null;
            $datos['nivel'] = null;
            $datos['grado'] = null;
            $datos['grupo'] = null;
            $datos['licenciatura'] = null;
            $datos['ciclo_escolar'] = null;
        } elseif ($datos['nivel'] === 'Licenciatura') {
            $datos['grado'] = null;
            $datos['grupo'] = null;
        } else {
            $datos['licenciatura'] = null;
        }

        unset($datos['generarValidacion'], $datos['reverso_imagen']);
        $datos['foto'] = $fotoPath;
        $datos['reverso_imagen'] = $reversePath;
        $datos['reverso_texto'] = $this->tiene_reverso ? (trim($this->reverso_texto) ?: null) : null;

        try {
            $credential = DB::transaction(function () use ($datos) {
                $validation = null;

                if ($this->generarValidacion) {
                    $validation = RegistroValidacion::create([
                        'user_id' => auth()->id(),
                        'persona_id' => $datos['persona_id'],
                        'proyecto_creativo_id' => $datos['proyecto_creativo_id'],
                        'codigo' => $this->generarCodigoValidacion(),
                        'tipo' => 'credencial',
                        'titulo' => 'Credencial de '.$datos['nombre'],
                        'estado' => 'valido',
                        'emitido_at' => now()->toDateString(),
                        'datos_publicos' => [
                            'descripcion' => trim(($datos['cargo'] ?: 'Identificación').' '.($datos['organizacion'] ? '· '.$datos['organizacion'] : '')),
                        ],
                    ]);
                }

                $credentialData = $datos;
                $credentialData['registro_validacion_id'] = $validation?->id;

                return Credencial::create($credentialData);
            });
        } catch (\Throwable $e) {
            if ($fotoPath) {
                Storage::disk('public')->delete($fotoPath);
            }
            if ($reversePath) {
                Storage::disk('public')->delete($reversePath);
            }
            throw $e;
        }

        CreativeActivity::log('credenciales', 'crear', $credential, $credential->nombre, ['tipo' => $credential->tipo]);

        $this->limpiarFormulario();
        $this->guardado = true;
        $this->dispatch('credencial-creada');
    }

    public function limpiarFormulario(): void
    {
        $this->reset([
            'marca_id','proyecto_creativo_id','persona_id','nombre','folio','cargo','organizacion',
            'correo','telefono','domicilio','vigencia','foto','matricula','curp','nivel','grado',
            'grupo','licenciatura','ciclo_escolar','reverso_texto','reverso_imagen',
        ]);

        $this->tipo = 'general';
        $this->estado = 'activa';
        $this->generarValidacion = true;
        $this->tiene_reverso = false;
        $this->resetErrorBag();
    }

    public function render()
    {
        return view('livewire.credencial.crear-credencial', [
            'marcas' => Marca::where('activo', true)->orderBy('nombre')->get(),
            'proyectos' => ProyectoCreativo::orderByDesc('created_at')->get(),
            'personas' => Persona::where('activo', true)->orderBy('nombre')->get(),
        ]);
    }
}
