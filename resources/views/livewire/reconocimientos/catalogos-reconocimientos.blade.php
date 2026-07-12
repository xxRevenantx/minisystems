<div class="space-y-6">
    @if($modo === 'eventos')
        <div class="grid gap-5 xl:grid-cols-[420px_1fr]">
            <form wire:submit="guardarEvento"
                class="rounded-2xl border border-neutral-200 bg-white p-5 shadow-sm dark:border-neutral-700 dark:bg-neutral-900">
                <flux:heading size="lg">{{ $eventoId ? 'Editar evento' : 'Nuevo evento o lote' }}</flux:heading>
                <flux:text class="mt-1">Agrupa reconocimientos de una ceremonia, concurso o actividad.</flux:text>

                <div class="mt-5 space-y-4">
                    <flux:input wire:model="eventoNombre" label="Nombre del evento" badge="Obligatorio" />

                    <div class="grid grid-cols-2 gap-3">
                        <flux:input wire:model="eventoCategoria" label="Categoría" />
                        <flux:input wire:model="eventoFecha" type="date" label="Fecha" />
                    </div>

                    <flux:input wire:model="eventoLugar" label="Lugar" />

                    <div class="grid grid-cols-2 gap-3">
                        <flux:input wire:model="eventoNivel" label="Nivel" />
                        <flux:input wire:model="eventoCiclo" label="Ciclo escolar" />
                    </div>

                    <flux:select wire:model="eventoTipo" label="Tipo predeterminado">
                        <flux:select.option value="">Sin tipo predeterminado</flux:select.option>
                        @foreach($tipos as $t)
                            <flux:select.option value="{{ $t->id }}">{{ $t->nombre }}</flux:select.option>
                        @endforeach
                    </flux:select>

                    <flux:select wire:model="eventoImagen" label="Plantilla predeterminada">
                        <flux:select.option value="">Sin plantilla predeterminada</flux:select.option>
                        @foreach($plantillas as $p)
                            <flux:select.option value="{{ $p->id }}">{{ $p->nombre ?: $p->descripcion }}</flux:select.option>
                        @endforeach
                    </flux:select>

                    <flux:select wire:model="eventoEstado" label="Estado">
                        <flux:select.option value="activo">Activo</flux:select.option>
                        <flux:select.option value="cerrado">Cerrado</flux:select.option>
                        <flux:select.option value="archivado">Archivado</flux:select.option>
                    </flux:select>

                    <flux:textarea wire:model="eventoObservaciones" label="Observaciones" rows="3" />
                </div>

                <div class="mt-5 flex justify-end gap-2">
                    <flux:button type="button" variant="filled" wire:click="resetEvento">Limpiar</flux:button>
                    <flux:button type="submit" variant="primary" class="!bg-[#006492] hover:!bg-[#00557b]">
                        Guardar evento
                    </flux:button>
                </div>
            </form>

            <section class="rounded-2xl border border-neutral-200 bg-white p-5 shadow-sm dark:border-neutral-700 dark:bg-neutral-900">
                <flux:heading size="lg" class="mb-4">Eventos registrados</flux:heading>

                <div class="grid gap-3 md:grid-cols-2">
                    @forelse($eventos as $e)
                        <article class="rounded-xl border border-neutral-200 p-4 dark:border-neutral-700">
                            <div class="flex justify-between gap-3">
                                <div>
                                    <h3 class="font-bold">{{ $e->nombre }}</h3>
                                    <p class="text-xs text-neutral-500">
                                        {{ $e->fecha?->format('d/m/Y') }} · {{ $e->lugar ?: 'Sin lugar' }}
                                    </p>
                                </div>
                                <flux:badge color="green">{{ $e->reconocimientos_count }}</flux:badge>
                            </div>

                            <div class="mt-3 text-xs text-neutral-500">
                                {{ $e->tipo?->nombre ?: 'Tipo personalizado' }} · {{ ucfirst($e->estado) }}
                            </div>

                            <div class="mt-4 flex gap-2">
                                <flux:button type="button" size="sm" variant="primary"
                                    class="!bg-[#006492] hover:!bg-[#00557b]" wire:click="editarEvento({{ $e->id }})">
                                    Editar
                                </flux:button>
                                <flux:button type="button" size="sm" variant="filled"
                                    wire:click="eliminarEvento({{ $e->id }})">
                                    Archivar/eliminar
                                </flux:button>
                            </div>
                        </article>
                    @empty
                        <div class="col-span-2 py-10 text-center">
                            <flux:text>Sin eventos.</flux:text>
                        </div>
                    @endforelse
                </div>
            </section>
        </div>
    @elseif($modo === 'plantillas')
        <div class="grid gap-5 xl:grid-cols-[1fr_420px]">
            <section class="rounded-2xl border border-neutral-200 bg-white p-5 shadow-sm dark:border-neutral-700 dark:bg-neutral-900">
                <flux:heading size="lg" class="mb-4">Configuración por plantilla</flux:heading>

                <div class="grid gap-4 md:grid-cols-2">
                    @foreach($plantillas as $p)
                        <article class="overflow-hidden rounded-xl border border-neutral-200 dark:border-neutral-700">
                            <img src="{{ asset('storage/imagenesReconocimientos/'.$p->imagen) }}"
                                class="h-40 w-full object-cover" alt="Plantilla">
                            <div class="p-3">
                                <div class="flex justify-between gap-3">
                                    <strong>{{ $p->nombre ?: ($p->descripcion ?: 'Plantilla '.$p->id) }}</strong>
                                    <flux:badge color="zinc">{{ $p->reconocimientos_count }} usos</flux:badge>
                                </div>
                                <flux:button type="button" size="sm" variant="primary"
                                    class="mt-3 !bg-[#006492] hover:!bg-[#00557b]"
                                    wire:click="editarPlantilla({{ $p->id }})">
                                    Configurar posiciones
                                </flux:button>
                            </div>
                        </article>
                    @endforeach
                </div>
            </section>

            <form wire:submit="guardarPlantilla"
                class="h-fit rounded-2xl border border-neutral-200 bg-white p-5 shadow-sm dark:border-neutral-700 dark:bg-neutral-900">
                <flux:heading size="lg">Diseñador de posiciones</flux:heading>
                <flux:text class="mt-1">Selecciona una plantilla y ajusta la distribución usada por el PDF.</flux:text>

                @if($plantillaId)
                    <div class="mt-5 space-y-4">
                        <flux:input wire:model="plantillaNombre" label="Nombre" />

                        <flux:select wire:model="plantillaOrientacion" label="Orientación">
                            <flux:select.option value="horizontal">Carta horizontal</flux:select.option>
                            <flux:select.option value="vertical">Carta vertical</flux:select.option>
                        </flux:select>

                        <flux:checkbox wire:model="plantillaActiva" label="Plantilla activa" />

                        <div class="grid grid-cols-2 gap-3">
                            <flux:input wire:model="nombreTop" type="number" label="Nombre: posición" />
                            <flux:input wire:model="nombreTamano" type="number" label="Nombre: tamaño" />
                            <flux:input wire:model="descripcionTop" type="number" label="Descripción: posición" />
                            <flux:input wire:model="descripcionTamano" type="number" label="Descripción: tamaño" />
                            <flux:input wire:model="fechaTop" type="number" label="Fecha: posición" />
                            <flux:input wire:model="firmasTop" type="number" label="Firmas: posición" />
                        </div>

                        <flux:button type="submit" variant="primary"
                            class="w-full !bg-[#88AC2E] hover:!bg-[#759726]">
                            Guardar configuración
                        </flux:button>
                    </div>
                @else
                    <div class="mt-5 rounded-xl bg-neutral-50 p-8 text-center dark:bg-neutral-800">
                        <flux:text>Selecciona una plantilla.</flux:text>
                    </div>
                @endif
            </form>
        </div>
    @else
        <div x-data="{ sub: 'tipos' }" class="space-y-5">
            <div class="inline-flex flex-wrap gap-1 rounded-xl border border-neutral-200 bg-white p-1 dark:border-neutral-700 dark:bg-neutral-900">
                <flux:button type="button" size="sm" @click="sub='tipos'"
                    x-bind:class="sub === 'tipos' ? '!bg-[#006492] !text-white' : ''">
                    Tipos
                </flux:button>
                <flux:button type="button" size="sm" @click="sub='firmantes'"
                    x-bind:class="sub === 'firmantes' ? '!bg-[#006492] !text-white' : ''">
                    Firmantes
                </flux:button>
                <flux:button type="button" size="sm" @click="sub='permisos'"
                    x-bind:class="sub === 'permisos' ? '!bg-[#006492] !text-white' : ''">
                    Permisos
                </flux:button>
            </div>

            <div x-show="sub === 'tipos'" class="grid gap-5 xl:grid-cols-[420px_1fr]">
                <form wire:submit="guardarTipo"
                    class="rounded-2xl border border-neutral-200 bg-white p-5 shadow-sm dark:border-neutral-700 dark:bg-neutral-900">
                    <flux:heading size="lg">{{ $tipoId ? 'Editar' : 'Nuevo' }} tipo</flux:heading>

                    <div class="mt-5 space-y-4">
                        <flux:input wire:model="tipoNombre" label="Nombre" badge="Obligatorio" />
                        <flux:input wire:model="tipoTitulo" label="Título opcional" />
                        <flux:textarea wire:model="tipoDescripcion" label="Texto reutilizable" badge="Obligatorio" rows="5" />

                        <flux:select wire:model="tipoDestinatario" label="Tipo de destinatario">
                            <flux:select.option value="alumno">Alumno</flux:select.option>
                            <flux:select.option value="docente">Docente</flux:select.option>
                            <flux:select.option value="externo">Externo</flux:select.option>
                            <flux:select.option value="institucion">Institución</flux:select.option>
                        </flux:select>

                        <flux:input wire:model="tipoNiveles" label="Niveles"
                            description="Separa los niveles con comas." />

                        <flux:select wire:model="tipoImagen" label="Plantilla predeterminada">
                            <flux:select.option value="">Sin plantilla predeterminada</flux:select.option>
                            @foreach($plantillas as $p)
                                <flux:select.option value="{{ $p->id }}">{{ $p->nombre ?: $p->descripcion }}</flux:select.option>
                            @endforeach
                        </flux:select>

                        <div class="space-y-2">
                            <flux:checkbox wire:model="tipoUsaLugar" label="Utiliza lugar obtenido" />
                            <flux:checkbox wire:model="tipoActivo" label="Activo" />
                        </div>
                    </div>

                    <div class="mt-5 flex justify-end gap-2">
                        <flux:button type="button" variant="filled" wire:click="resetTipo">Limpiar</flux:button>
                        <flux:button type="submit" variant="primary" class="!bg-[#006492] hover:!bg-[#00557b]">
                            Guardar
                        </flux:button>
                    </div>
                </form>

                <section class="rounded-2xl border border-neutral-200 bg-white p-5 shadow-sm dark:border-neutral-700 dark:bg-neutral-900">
                    <div class="space-y-3">
                        @forelse($tipos as $t)
                            <article class="rounded-xl border border-neutral-200 p-4 dark:border-neutral-700">
                                <div class="flex justify-between gap-3">
                                    <div>
                                        <strong>{{ $t->nombre }}</strong>
                                        <p class="text-xs text-neutral-500">
                                            {{ $t->destinatario_tipo }} · {{ $t->activo ? 'Activo' : 'Inactivo' }}
                                        </p>
                                    </div>
                                    <div class="flex gap-2">
                                        <flux:button type="button" size="sm" variant="primary"
                                            class="!bg-[#006492] hover:!bg-[#00557b]"
                                            wire:click="editarTipo({{ $t->id }})">
                                            Editar
                                        </flux:button>
                                        <flux:button type="button" size="sm" variant="filled"
                                            wire:click="eliminarTipo({{ $t->id }})">
                                            Eliminar
                                        </flux:button>
                                    </div>
                                </div>
                                <p class="mt-2 text-sm text-neutral-600 dark:text-neutral-300">
                                    {{ \Illuminate\Support\Str::limit(strip_tags($t->descripcion), 160) }}
                                </p>
                            </article>
                        @empty
                            <div class="py-10 text-center"><flux:text>Sin tipos registrados.</flux:text></div>
                        @endforelse
                    </div>
                </section>
            </div>

            <div x-show="sub === 'firmantes'" class="grid gap-5 xl:grid-cols-[420px_1fr]">
                <form wire:submit="guardarDirectivo"
                    class="rounded-2xl border border-neutral-200 bg-white p-5 shadow-sm dark:border-neutral-700 dark:bg-neutral-900">
                    <flux:heading size="lg">{{ $directivoId ? 'Editar' : 'Nuevo' }} firmante</flux:heading>

                    <div class="mt-5 space-y-4">
                        <div class="grid grid-cols-[110px_1fr] gap-2">
                            <flux:input wire:model="directivoTitulo" label="Título" badge="Obligatorio" />
                            <flux:input wire:model="directivoNombre" label="Nombre completo" badge="Obligatorio" />
                        </div>

                        <flux:input wire:model="directivoCargo" label="Cargo" badge="Obligatorio" />
                        <flux:input wire:model="directivoNiveles" label="Niveles"
                            description="Separa los niveles con comas." />

                        <div class="grid grid-cols-2 gap-2">
                            <flux:input wire:model="directivoVigenciaInicio" type="date" label="Vigencia inicial" />
                            <flux:input wire:model="directivoVigenciaFin" type="date" label="Vigencia final" />
                        </div>

                        <flux:input wire:model="directivoOrden" type="number" label="Orden" />
                        <flux:input type="file" wire:model="firmaArchivo" accept="image/*" label="Firma digital" />
                        <flux:input type="file" wire:model="selloArchivo" accept="image/*" label="Sello" />
                        <flux:checkbox wire:model="directivoActivo" label="Activo" />
                    </div>

                    <div class="mt-5 flex justify-end gap-2">
                        <flux:button type="button" variant="filled" wire:click="resetDirectivo">Limpiar</flux:button>
                        <flux:button type="submit" variant="primary" class="!bg-[#88AC2E] hover:!bg-[#759726]">
                            Guardar
                        </flux:button>
                    </div>
                </form>

                <section class="rounded-2xl border border-neutral-200 bg-white p-5 shadow-sm dark:border-neutral-700 dark:bg-neutral-900">
                    <div class="space-y-3">
                        @forelse($directivos as $d)
                            <article class="flex items-center justify-between gap-3 rounded-xl border border-neutral-200 p-4 dark:border-neutral-700">
                                <div>
                                    <strong>{{ $d->nombre_completo }}</strong>
                                    <p class="text-xs text-neutral-500">
                                        {{ $d->cargo }} · Orden {{ $d->orden }} · {{ $d->activo ? 'Activo' : 'Inactivo' }}
                                    </p>
                                </div>
                                <flux:button type="button" size="sm" variant="primary"
                                    class="!bg-[#006492] hover:!bg-[#00557b]"
                                    wire:click="editarDirectivo({{ $d->id }})">
                                    Editar
                                </flux:button>
                            </article>
                        @empty
                            <div class="py-10 text-center"><flux:text>Sin firmantes registrados.</flux:text></div>
                        @endforelse
                    </div>
                </section>
            </div>

            <div x-show="sub === 'permisos'"
                class="rounded-2xl border border-neutral-200 bg-white p-5 shadow-sm dark:border-neutral-700 dark:bg-neutral-900">
                <flux:heading size="lg">Permisos del módulo</flux:heading>
                <flux:text class="mt-1">Controla qué puede hacer cada usuario en reconocimientos.</flux:text>

                <div class="mt-5 overflow-x-auto rounded-xl border border-neutral-200 dark:border-neutral-700">
                    <table class="min-w-full text-sm">
                        <thead class="bg-neutral-50 dark:bg-neutral-800">
                            <tr>
                                <th class="p-3 text-left">Usuario</th>
                                @foreach(['ver','crear','editar','aprobar','descargar','cancelar','administrar'] as $p)
                                    <th class="p-3">{{ ucfirst($p) }}</th>
                                @endforeach
                                <th class="p-3"></th>
                            </tr>
                        </thead>
                        <tbody class="divide-y dark:divide-neutral-800">
                            @foreach($usuarios as $u)
                                <tr>
                                    <td class="p-3">
                                        <strong>{{ $u->name }}</strong>
                                        <div class="text-xs text-neutral-500">{{ $u->email }}</div>
                                    </td>
                                    @foreach(['ver','crear','editar','aprobar','descargar','cancelar','administrar'] as $p)
                                        <td class="p-3 text-center">
                                            <flux:checkbox wire:model="permisos.{{ $u->id }}.{{ $p }}" />
                                        </td>
                                    @endforeach
                                    <td class="p-3">
                                        <flux:button type="button" size="sm" variant="primary"
                                            class="!bg-[#006492] hover:!bg-[#00557b]"
                                            wire:click="guardarPermisos({{ $u->id }})">
                                            Guardar
                                        </flux:button>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    @endif
</div>
