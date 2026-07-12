<div class="space-y-5">
    <div class="grid gap-3 sm:grid-cols-2 lg:grid-cols-4 xl:grid-cols-7">
        @foreach(['total'=>'Total','borrador'=>'Borradores','revision'=>'En revisión','aprobado'=>'Aprobados','generado'=>'Generados','entregado'=>'Entregados','cancelado'=>'Cancelados'] as $key=>$label)
            <button type="button" wire:click="$set('estadoFiltro','{{ $key === 'total' ? '' : $key }}')"
                class="rounded-xl border border-neutral-200 bg-white p-3 text-left transition hover:-translate-y-0.5 hover:border-[#006492] dark:border-neutral-700 dark:bg-neutral-900">
                <span class="block text-2xl font-black {{ $key === 'cancelado' ? 'text-red-600' : ($key === 'entregado' ? 'text-[#88AC2E]' : 'text-[#006492]') }}">
                    {{ $stats[$key] }}
                </span>
                <span class="text-xs font-semibold text-neutral-500">{{ $label }}</span>
            </button>
        @endforeach
    </div>

    <div class="rounded-2xl border border-neutral-200 bg-neutral-50 p-4 dark:border-neutral-700 dark:bg-neutral-800/40">
        <div class="grid gap-3 md:grid-cols-2 xl:grid-cols-4">
            <flux:input wire:model.live.debounce.350ms="search" label="Buscar"
                placeholder="Destinatario, evento, descripción o firmante" />

            <flux:select wire:model.live="eventoFiltro" label="Evento">
                <flux:select.option value="">Todos los eventos</flux:select.option>
                @foreach($eventos as $e)
                    <flux:select.option value="{{ $e->id }}">{{ $e->nombre }}</flux:select.option>
                @endforeach
            </flux:select>

            <flux:select wire:model.live="tipoFiltro" label="Tipo">
                <flux:select.option value="">Todos los tipos</flux:select.option>
                @foreach($tipos as $t)
                    <flux:select.option value="{{ $t->id }}">{{ $t->nombre }}</flux:select.option>
                @endforeach
            </flux:select>

            <flux:select wire:model.live="plantillaFiltro" label="Plantilla">
                <flux:select.option value="">Todas las plantillas</flux:select.option>
                @foreach($plantillas as $p)
                    <flux:select.option value="{{ $p->id }}">
                        {{ $p->nombre ?: ($p->descripcion ?: 'Plantilla '.$p->id) }}
                    </flux:select.option>
                @endforeach
            </flux:select>

            <flux:select wire:model.live="estadoFiltro" label="Estado">
                <flux:select.option value="">Todos los estados</flux:select.option>
                @foreach(\App\Models\Reconocimiento::ESTADOS as $estado)
                    <flux:select.option value="{{ $estado }}">{{ ucfirst($estado) }}</flux:select.option>
                @endforeach
            </flux:select>

            <flux:input wire:model.live="fechaDesde" type="date" label="Fecha inicial" />
            <flux:input wire:model.live="fechaHasta" type="date" label="Fecha final" />

            <div class="flex items-end gap-2">
                <flux:button type="button" variant="filled" wire:click="clearFilters" class="flex-1">
                    Limpiar
                </flux:button>
                <div class="flex min-h-10 flex-1 items-center justify-center rounded-lg border border-neutral-200 bg-white px-3 dark:border-neutral-700 dark:bg-neutral-900">
                    <flux:checkbox wire:model.live="verPapelera" label="Papelera" />
                </div>
            </div>
        </div>
    </div>

    @if(!$verPapelera)
        <div class="rounded-2xl border border-[#006492]/20 bg-[#006492]/5 p-4">
            <div class="flex flex-wrap items-end gap-3">
                <div class="self-center">
                    <flux:badge color="blue">{{ count($seleccionados) }} seleccionado(s)</flux:badge>
                </div>

                <div class="min-w-52">
                    <flux:select wire:model.live="accionMasiva" label="Acción masiva">
                        <flux:select.option value="">Seleccionar acción</flux:select.option>
                        <flux:select.option value="estado">Cambiar estado</flux:select.option>
                        <flux:select.option value="plantilla">Cambiar plantilla</flux:select.option>
                        <flux:select.option value="evento">Mover a evento</flux:select.option>
                        <flux:select.option value="fecha">Cambiar fecha</flux:select.option>
                        <flux:select.option value="entregar">Registrar entrega</flux:select.option>
                        <flux:select.option value="cancelar">Cancelar</flux:select.option>
                        <flux:select.option value="papelera">Enviar a papelera</flux:select.option>
                    </flux:select>
                </div>

                @if($accionMasiva === 'estado')
                    <div class="min-w-48">
                        <flux:select wire:model="estadoMasivo" label="Nuevo estado">
                            <flux:select.option value="">Seleccionar</flux:select.option>
                            @foreach(['borrador','revision','aprobado','generado'] as $e)
                                <flux:select.option value="{{ $e }}">{{ ucfirst($e) }}</flux:select.option>
                            @endforeach
                        </flux:select>
                    </div>
                @endif

                @if($accionMasiva === 'plantilla')
                    <div class="min-w-56">
                        <flux:select wire:model="plantillaMasiva" label="Nueva plantilla">
                            <flux:select.option value="">Seleccionar</flux:select.option>
                            @foreach($plantillas as $p)
                                <flux:select.option value="{{ $p->id }}">{{ $p->nombre ?: $p->descripcion }}</flux:select.option>
                            @endforeach
                        </flux:select>
                    </div>
                @endif

                @if($accionMasiva === 'evento')
                    <div class="min-w-56">
                        <flux:select wire:model="eventoMasivo" label="Nuevo evento">
                            <flux:select.option value="">Seleccionar</flux:select.option>
                            @foreach($eventos as $e)
                                <flux:select.option value="{{ $e->id }}">{{ $e->nombre }}</flux:select.option>
                            @endforeach
                        </flux:select>
                    </div>
                @endif

                @if($accionMasiva === 'fecha')
                    <div class="min-w-48">
                        <flux:input wire:model="fechaMasiva" type="date" label="Nueva fecha" />
                    </div>
                @endif

                @if($accionMasiva === 'entregar')
                    <div class="min-w-48">
                        <flux:select wire:model="metodoEntrega" label="Método">
                            <flux:select.option value="impreso">Impreso</flux:select.option>
                            <flux:select.option value="correo">Correo</flux:select.option>
                            <flux:select.option value="whatsapp">WhatsApp</flux:select.option>
                            <flux:select.option value="digital">Descarga digital</flux:select.option>
                            <flux:select.option value="ceremonia">Ceremonia</flux:select.option>
                        </flux:select>
                    </div>
                    <div class="min-w-48">
                        <flux:input wire:model="recibidoPor" label="Recibido por" />
                    </div>
                    <div class="min-w-48">
                        <flux:input wire:model="observacionesEntrega" label="Observaciones" />
                    </div>
                @endif

                @if($accionMasiva === 'cancelar')
                    <div class="min-w-64">
                        <flux:input wire:model="motivoCancelacion" label="Motivo de cancelación" badge="Obligatorio" />
                    </div>
                @endif

                <flux:button type="button" variant="primary" wire:click="aplicarAccionMasiva"
                    wire:loading.attr="disabled" class="!bg-[#006492] hover:!bg-[#00557b]">
                    <span wire:loading.remove wire:target="aplicarAccionMasiva">Aplicar</span>
                    <span wire:loading wire:target="aplicarAccionMasiva">Aplicando…</span>
                </flux:button>

                <div class="ml-auto flex flex-wrap gap-2">
                    <flux:button href="{{ $downloadUrl }}" target="_blank" variant="primary"
                        class="!bg-[#88AC2E] hover:!bg-[#759726]">
                        PDF combinado
                    </flux:button>

                    @if(class_exists(\ZipArchive::class))
                        <flux:button
                            href="{{ str_replace(route('descargar.reconocimientos'), route('descargar.reconocimientos.zip'), $downloadUrl) }}"
                            variant="primary" class="!bg-[#006492] hover:!bg-[#00557b]">
                            ZIP individual
                        </flux:button>
                    @else
                        <flux:button variant="filled" disabled title="Habilita la extensión zip de PHP">
                            ZIP no disponible
                        </flux:button>
                    @endif

                    <flux:button
                        href="{{ str_replace(route('descargar.reconocimientos'), route('reconocimientos.exportar.csv'), $downloadUrl) }}"
                        variant="filled">
                        Exportar CSV
                    </flux:button>
                </div>
            </div>

            @error('seleccionados')
                <flux:text class="mt-2 !text-red-600">{{ $message }}</flux:text>
            @enderror
        </div>
    @endif

    <div class="relative overflow-x-auto rounded-2xl border border-neutral-200 dark:border-neutral-700">
        <div wire:loading.delay
            class="absolute inset-0 z-20 grid place-items-center bg-white/70 backdrop-blur-sm dark:bg-neutral-900/70">
            <div class="rounded-xl bg-white px-4 py-3 shadow dark:bg-neutral-800">
                <flux:text class="font-semibold">Actualizando…</flux:text>
            </div>
        </div>

        <table class="min-w-full text-sm">
            <thead class="bg-neutral-100 text-xs uppercase text-neutral-500 dark:bg-neutral-800">
                <tr>
                    <th class="p-3">
                        @if(!$verPapelera)
                            <flux:button type="button" size="sm" variant="filled"
                                wire:click="seleccionarPagina({{ $reconocimientos->pluck('id')->values()->toJson() }})">
                                Todos
                            </flux:button>
                        @endif
                    </th>
                    <th class="p-3 text-left">Destinatario</th>
                    <th class="p-3 text-left">Evento / tipo</th>
                    <th class="p-3 text-left">Estado</th>
                    <th class="p-3 text-left">Fecha</th>
                    <th class="p-3 text-left">Firmantes</th>
                    <th class="p-3 text-right">Acciones</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-neutral-200 bg-white dark:divide-neutral-800 dark:bg-neutral-900">
                @forelse($reconocimientos as $r)
                    <tr class="align-top hover:bg-neutral-50 dark:hover:bg-neutral-800/50">
                        <td class="p-3">
                            @if(!$verPapelera)
                                <flux:checkbox wire:model="seleccionados" value="{{ $r->id }}" />
                            @endif
                        </td>
                        <td class="p-3">
                            <div class="font-bold text-neutral-900 dark:text-white">{{ $r->reconocimiento_a }}</div>
                            @if($r->lugar_obtenido)
                                <div class="text-xs font-semibold text-[#88AC2E]">{{ $r->lugar_obtenido }}</div>
                            @endif
                            <div class="mt-1 max-w-xs text-xs text-neutral-500">
                                {{ \Illuminate\Support\Str::limit(strip_tags($r->descripcion), 100) }}
                            </div>
                            @if($r->version > 1)
                                <flux:badge color="zinc" size="sm" class="mt-1">Versión {{ $r->version }}</flux:badge>
                            @endif
                        </td>
                        <td class="p-3">
                            <div class="font-semibold">{{ $r->evento?->nombre ?: 'Sin evento' }}</div>
                            <div class="text-xs text-neutral-500">{{ $r->tipo?->nombre ?: 'Personalizado' }}</div>
                        </td>
                        <td class="p-3">
                            @php
                                $badgeColor = match($r->estado) {
                                    'revision' => 'amber',
                                    'aprobado' => 'blue',
                                    'generado' => 'purple',
                                    'entregado' => 'green',
                                    'cancelado' => 'red',
                                    default => 'zinc',
                                };
                            @endphp
                            <flux:badge :color="$badgeColor">{{ ucfirst($r->estado) }}</flux:badge>
                            @if($r->cancel_reason)
                                <div class="mt-2 max-w-40 text-xs text-red-600">{{ $r->cancel_reason }}</div>
                            @endif
                        </td>
                        <td class="whitespace-nowrap p-3">{{ $r->fecha?->format('d/m/Y') }}</td>
                        <td class="p-3">
                            <div class="max-w-56 text-xs">
                                @forelse($r->directivos as $d)
                                    <div>{{ $d->nombre_completo }}</div>
                                @empty
                                    <span class="text-red-500">Sin firmantes</span>
                                @endforelse
                            </div>
                        </td>
                        <td class="p-3">
                            <div class="flex justify-end gap-1">
                                @if($verPapelera)
                                    <flux:button type="button" size="sm" variant="primary"
                                        class="!bg-[#88AC2E] hover:!bg-[#759726]"
                                        wire:click="restaurar({{ $r->id }})">
                                        Restaurar
                                    </flux:button>
                                    <flux:button type="button" size="sm" variant="danger"
                                        wire:click="eliminarDefinitivo({{ $r->id }})"
                                        wire:confirm="Esta acción es definitiva. ¿Continuar?">
                                        Eliminar
                                    </flux:button>
                                @else
                                    <flux:button href="{{ route('reconocimiento.pdf', $r) }}" target="_blank" size="sm"
                                        variant="primary" class="!bg-[#88AC2E] hover:!bg-[#759726]">
                                        PDF
                                    </flux:button>
                                    <flux:button href="{{ route('reconocimiento.editar', $r) }}" size="sm"
                                        variant="primary" class="!bg-[#006492] hover:!bg-[#00557b]">
                                        Editar
                                    </flux:button>
                                    <flux:button type="button" size="sm" variant="filled"
                                        wire:click="duplicar({{ $r->id }})">
                                        Duplicar
                                    </flux:button>
                                    <flux:button type="button" size="sm" variant="danger"
                                        wire:click="eliminarReconocimiento({{ $r->id }})"
                                        wire:confirm="Se enviará a la papelera. ¿Continuar?">
                                        Papelera
                                    </flux:button>
                                @endif
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="7" class="p-12 text-center text-neutral-500">
                            No hay reconocimientos con los filtros seleccionados.
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div>{{ $reconocimientos->links() }}</div>
</div>
