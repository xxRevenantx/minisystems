<div class="space-y-6">
    <div class="grid gap-6 xl:grid-cols-[1.25fr_.75fr]">
        <form wire:submit="guardarReconocimiento"
            class="overflow-hidden rounded-2xl border border-neutral-200 bg-white shadow-sm dark:border-neutral-700 dark:bg-neutral-900">
            <div
                class="flex flex-wrap items-center justify-between gap-4 border-b border-neutral-200 bg-gradient-to-r from-[#006492]/10 to-[#88AC2E]/10 px-5 py-4 dark:border-neutral-700">
                <div>
                    <flux:heading size="lg">Nuevo reconocimiento</flux:heading>
                    <flux:text class="mt-1">Creación individual o masiva desde el padrón de credenciales.</flux:text>
                </div>

                <div class="inline-flex rounded-xl border border-neutral-200 bg-white p-1 dark:border-neutral-700 dark:bg-neutral-800">
                    <flux:button type="button" size="sm" wire:click="$set('modo','individual')"
                        class="{{ $modo === 'individual' ? '!bg-[#006492] !text-white' : '' }}">
                        Individual
                    </flux:button>
                    <flux:button type="button" size="sm" wire:click="$set('modo','masivo')"
                        class="{{ $modo === 'masivo' ? '!bg-[#88AC2E] !text-white' : '' }}">
                        Masivo
                    </flux:button>
                </div>
            </div>

            <div class="space-y-6 p-5">
                <div class="grid gap-4 md:grid-cols-3">
                    <flux:select wire:model.live="reconocimiento_evento_id" label="Evento o lote">
                        <flux:select.option value="">Sin evento</flux:select.option>
                        @foreach($eventos as $evento)
                            <flux:select.option value="{{ $evento->id }}">{{ $evento->nombre }}</flux:select.option>
                        @endforeach
                    </flux:select>

                    <flux:select wire:model.live="reconocimiento_tipo_id" label="Tipo reutilizable">
                        <flux:select.option value="">Personalizado</flux:select.option>
                        @foreach($tipos as $tipo)
                            <flux:select.option value="{{ $tipo->id }}">{{ $tipo->nombre }}</flux:select.option>
                        @endforeach
                    </flux:select>

                    <flux:select wire:model="estado" label="Estado inicial">
                        <flux:select.option value="borrador">Borrador</flux:select.option>
                        <flux:select.option value="revision">Pendiente de revisión</flux:select.option>
                    </flux:select>
                </div>

                @if($modo === 'individual')
                    <div class="grid gap-4 md:grid-cols-2">
                        <flux:input wire:model.live.debounce.300ms="reconocimiento" label="Destinatario"
                            badge="Obligatorio" maxlength="255"
                            placeholder="Nombre completo, institución o invitado" />

                        <flux:input wire:model.live.debounce.300ms="lugar_obtenido" label="Lugar obtenido"
                            maxlength="255" placeholder="Ej. Primer lugar" />
                    </div>
                @else
                    <section class="rounded-2xl border border-[#006492]/20 bg-[#006492]/5 p-4">
                        <div class="mb-4 flex flex-wrap items-center justify-between gap-3">
                            <div>
                                <flux:heading size="md" class="!text-[#006492]">Seleccionar destinatarios</flux:heading>
                                <flux:text class="mt-1 text-xs">
                                    {{ count($credencialesSeleccionadas) }} seleccionado(s). Se mostrarán como máximo 100 resultados.
                                </flux:text>
                            </div>
                            <flux:button type="button" size="sm" variant="filled" wire:click="limpiarAlumnos">
                                Limpiar selección
                            </flux:button>
                        </div>

                        <div class="grid gap-3 md:grid-cols-5">
                            <flux:input wire:model.live.debounce.300ms="buscarAlumno" label="Buscar"
                                placeholder="Nombre o matrícula" />

                            <flux:select wire:model.live="nivelFiltro" label="Nivel">
                                <flux:select.option value="">Todos</flux:select.option>
                                @foreach($niveles as $v)
                                    <flux:select.option value="{{ $v }}">{{ $v }}</flux:select.option>
                                @endforeach
                            </flux:select>

                            <flux:select wire:model.live="gradoFiltro" label="Grado">
                                <flux:select.option value="">Todos</flux:select.option>
                                @foreach($grados as $v)
                                    <flux:select.option value="{{ $v }}">{{ $v }}</flux:select.option>
                                @endforeach
                            </flux:select>

                            <flux:select wire:model.live="grupoFiltro" label="Grupo">
                                <flux:select.option value="">Todos</flux:select.option>
                                @foreach($grupos as $v)
                                    <flux:select.option value="{{ $v }}">{{ $v }}</flux:select.option>
                                @endforeach
                            </flux:select>

                            <flux:select wire:model.live="licenciaturaFiltro" label="Licenciatura">
                                <flux:select.option value="">Todas</flux:select.option>
                                @foreach($licenciaturas as $v)
                                    <flux:select.option value="{{ $v }}">{{ $v }}</flux:select.option>
                                @endforeach
                            </flux:select>
                        </div>

                        <div class="mt-4 flex flex-wrap items-end gap-3">
                            <flux:button type="button" size="sm" variant="primary"
                                class="!bg-[#006492] hover:!bg-[#00557b]"
                                wire:click="seleccionarPagina({{ $credenciales->pluck('id')->values()->toJson() }})">
                                Seleccionar resultados visibles
                            </flux:button>

                            <flux:button href="{{ route('reconocimientos.plantilla.csv') }}" size="sm" variant="filled">
                                Descargar plantilla CSV
                            </flux:button>

                            <div class="min-w-64 flex-1">
                                <flux:input type="file" wire:model="archivoCsv" label="Importar CSV de Excel"
                                    accept=".csv,.txt" />
                            </div>

                            @if($archivoCsv)
                                <flux:button type="button" size="sm" variant="primary"
                                    class="!bg-[#88AC2E] hover:!bg-[#759726]" wire:click="importarCsv">
                                    Procesar archivo
                                </flux:button>
                            @endif
                        </div>

                        @error('credencialesSeleccionadas')
                            <flux:text class="mt-2 !text-red-600">{{ $message }}</flux:text>
                        @enderror

                        <div class="mt-4 max-h-64 overflow-auto rounded-xl border bg-white dark:border-neutral-700 dark:bg-neutral-900">
                            <table class="min-w-full text-sm">
                                <thead class="sticky top-0 bg-neutral-100 dark:bg-neutral-800">
                                    <tr>
                                        <th class="p-2"></th>
                                        <th class="p-2 text-left">Destinatario</th>
                                        <th class="p-2 text-left">Nivel / grupo</th>
                                        <th class="p-2 text-left">Matrícula</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y dark:divide-neutral-800">
                                    @forelse($credenciales as $alumno)
                                        <tr>
                                            <td class="p-2">
                                                <flux:checkbox wire:model="credencialesSeleccionadas" value="{{ $alumno->id }}" />
                                            </td>
                                            <td class="p-2 font-semibold">{{ $alumno->nombre }}</td>
                                            <td class="p-2 text-neutral-500">
                                                {{ $alumno->nivel }} {{ $alumno->grado }} {{ $alumno->grupo }} {{ $alumno->licenciatura }}
                                            </td>
                                            <td class="p-2">{{ $alumno->matricula }}</td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="4" class="p-6 text-center text-neutral-500">No hay coincidencias.</td>
                                        </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    </section>
                @endif

                <div class="grid gap-4 md:grid-cols-[1fr_220px]">
                    <flux:textarea wire:model.live.debounce.400ms="descripcion" label="Descripción"
                        badge="Obligatorio" rows="6" maxlength="5000"
                        placeholder="Motivo del reconocimiento"
                        description="Puedes usar negritas y listas mediante un tipo reutilizable; el contenido se sanitiza antes de guardarse." />

                    <flux:input wire:model.live="fecha" type="date" label="Fecha" badge="Obligatorio" />
                </div>

                <section>
                    <div class="mb-3 flex items-center justify-between">
                        <flux:heading size="sm">Firmantes</flux:heading>
                        <flux:badge color="zinc">Máximo 5</flux:badge>
                    </div>

                    <div class="grid gap-2 md:grid-cols-2">
                        @forelse($directivosLista as $d)
                            <label class="flex cursor-pointer items-start gap-3 rounded-xl border border-neutral-200 p-3 transition hover:border-[#88AC2E] dark:border-neutral-700">
                                <flux:checkbox wire:model="directivos" value="{{ $d->id }}" />
                                <span>
                                    <strong>{{ $d->nombre_completo }}</strong>
                                    <small class="block text-neutral-500">{{ $d->cargo }}</small>
                                </span>
                            </label>
                        @empty
                            <flux:text>No hay firmantes activos.</flux:text>
                        @endforelse
                    </div>

                    @error('directivos')
                        <flux:text class="mt-2 !text-red-600">{{ $message }}</flux:text>
                    @enderror
                </section>

                <section>
                    <div class="mb-3 flex items-center justify-between">
                        <flux:heading size="sm">Plantilla <span class="text-red-500">*</span></flux:heading>
                        <flux:button type="button" size="sm" variant="ghost" wire:click="limpiarSeleccion">
                            Limpiar selección
                        </flux:button>
                    </div>

                    <div class="grid gap-3 sm:grid-cols-2 lg:grid-cols-3">
                        @forelse($reconocimientosImagenes as $plantilla)
                            <label
                                class="relative cursor-pointer overflow-hidden rounded-xl border-2 transition {{ (int)$reconocimiento_imagen_id === $plantilla->id ? 'border-[#88AC2E] ring-2 ring-[#88AC2E]/20' : 'border-neutral-200 dark:border-neutral-700' }}">
                                <input type="radio" wire:model.live="reconocimiento_imagen_id" value="{{ $plantilla->id }}"
                                    class="absolute left-3 top-3 z-10 h-4 w-4 accent-[#88AC2E]">
                                <img src="{{ asset('storage/imagenesReconocimientos/'.$plantilla->imagen) }}"
                                    class="h-32 w-full object-cover" alt="Plantilla">
                                <div class="p-2 text-xs font-semibold">
                                    {{ $plantilla->nombre ?: ($plantilla->descripcion ?: 'Plantilla '.$plantilla->id) }}
                                </div>
                            </label>
                        @empty
                            <flux:text>No hay plantillas activas.</flux:text>
                        @endforelse
                    </div>

                    @error('reconocimiento_imagen_id')
                        <flux:text class="mt-2 !text-red-600">{{ $message }}</flux:text>
                    @enderror
                </section>
            </div>

            <div
                class="flex flex-wrap justify-end gap-3 border-t border-neutral-200 bg-neutral-50 px-5 py-4 dark:border-neutral-700 dark:bg-neutral-800/50">
                <flux:button type="button" variant="filled" wire:click="resetFormulario">
                    Limpiar
                </flux:button>

                <flux:button type="submit" variant="primary" wire:loading.attr="disabled"
                    class="!bg-[#006492] hover:!bg-[#00557b] disabled:opacity-50">
                    <span wire:loading.remove wire:target="guardarReconocimiento">
                        {{ $modo === 'masivo' ? 'Generar reconocimientos' : 'Guardar reconocimiento' }}
                    </span>
                    <span wire:loading wire:target="guardarReconocimiento">Guardando…</span>
                </flux:button>
            </div>
        </form>

        <aside class="xl:sticky xl:top-4 xl:self-start">
            <div class="overflow-hidden rounded-2xl border border-neutral-200 bg-white shadow-sm dark:border-neutral-700 dark:bg-neutral-900">
                <div class="border-b border-neutral-200 px-4 py-3 dark:border-neutral-700">
                    <flux:heading size="md">Vista previa</flux:heading>
                    <flux:text class="mt-1 text-xs">Comprueba nombre, texto, fecha y firmantes antes de guardar.</flux:text>
                </div>

                <div class="relative aspect-[11/8.5] overflow-hidden bg-neutral-100">
                    @php $preview = $reconocimientosImagenes->firstWhere('id', (int)$reconocimiento_imagen_id); @endphp
                    @if($preview)
                        <img src="{{ asset('storage/imagenesReconocimientos/'.$preview->imagen) }}"
                            class="absolute inset-0 h-full w-full object-cover">
                    @endif

                    <div class="absolute inset-0 flex flex-col items-center px-8 text-center">
                        <div class="mt-[25%] max-w-full truncate font-serif text-2xl font-bold text-[#006492]">
                            {{ $modo === 'masivo' ? (count($credencialesSeleccionadas).' destinatarios seleccionados') : ($reconocimiento ?: 'Nombre del destinatario') }}
                        </div>
                        @if($lugar_obtenido)
                            <div class="mt-2 text-sm font-bold text-[#88AC2E]">{{ $lugar_obtenido }}</div>
                        @endif
                        <div class="mt-3 max-h-24 overflow-hidden text-xs leading-relaxed text-neutral-700">
                            {{ \Illuminate\Support\Str::limit(strip_tags($descripcion ?: 'Aquí aparecerá la descripción del reconocimiento.'), 340) }}
                        </div>
                        <div class="mt-4 text-xs font-semibold">
                            {{ $fecha ? \Carbon\Carbon::parse($fecha)->format('d/m/Y') : 'Fecha' }}
                        </div>
                        <div class="mt-auto mb-6 grid w-full grid-cols-2 gap-3 text-[9px]">
                            @foreach($directivosLista->whereIn('id', $directivos)->take(4) as $d)
                                <div class="border-t border-neutral-500 pt-1">
                                    <strong>{{ $d->nombre_completo }}</strong><br>{{ $d->cargo }}
                                </div>
                            @endforeach
                        </div>
                    </div>
                </div>

                @if(!$preview)
                    <div class="p-3 text-center text-xs text-amber-700">
                        Selecciona una plantilla para completar la vista previa.
                    </div>
                @endif

                @php $textoPlano = trim(strip_tags($descripcion)); @endphp
                @if(($modo === 'individual' && mb_strlen($reconocimiento) > 48) || mb_strlen($textoPlano) > 520 || count($directivos) > 3)
                    <div class="border-t border-amber-200 bg-amber-50 p-3 text-xs text-amber-800">
                        <strong>Revisa la distribución:</strong>
                        @if($modo === 'individual' && mb_strlen($reconocimiento) > 48) el nombre es largo; @endif
                        @if(mb_strlen($textoPlano) > 520) la descripción podría desbordarse; @endif
                        @if(count($directivos) > 3) hay varios firmantes y las firmas serán más compactas; @endif
                    </div>
                @endif
            </div>
        </aside>
    </div>

    <div class="rounded-2xl border border-neutral-200 bg-white p-5 shadow-sm dark:border-neutral-700 dark:bg-neutral-900">
        <livewire:reconocimientos.mostrar-reconocimientos />
    </div>
</div>
