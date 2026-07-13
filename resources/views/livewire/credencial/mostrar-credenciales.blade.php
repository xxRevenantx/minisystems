<div x-data="{ modalEditar: @entangle('modalEditar').live }" class="space-y-6">
    <section class="overflow-hidden rounded-3xl border border-slate-200 bg-white shadow-xl shadow-slate-200/50 dark:border-zinc-800 dark:bg-zinc-950 dark:shadow-none">
        <header class="relative overflow-hidden bg-gradient-to-r from-slate-950 via-slate-800 to-[#006492] px-6 py-6 text-white">
            <div class="absolute -right-20 -top-20 h-52 w-52 rounded-full bg-white/10"></div>
            <div class="relative flex flex-col gap-4 lg:flex-row lg:items-end lg:justify-between">
                <div>
                    <p class="text-xs font-black uppercase tracking-[.28em] text-sky-200">Credenciales registradas</p>
                    <h2 class="mt-2 text-2xl font-black">Directorio de identificaciones</h2>
                    <p class="mt-2 text-sm text-slate-300">Consulta, edita, valida o exporta credenciales generales y escolares.</p>
                </div>
                <div class="rounded-2xl bg-white/10 px-4 py-3 backdrop-blur">
                    <strong class="block text-2xl">{{ $credenciales->total() }}</strong>
                    <span class="text-xs text-slate-300">registros encontrados</span>
                </div>
            </div>
        </header>

        <div class="space-y-5 p-6">
            <div class="flex flex-col gap-3 lg:flex-row lg:items-center lg:justify-between">
                <flux:input wire:model.live.debounce.300ms="buscar" type="search" placeholder="Buscar por nombre, folio, cargo, organización o matrícula..."
                    class="w-full rounded-2xl border-slate-300 px-4 py-3 text-sm dark:border-zinc-700 dark:bg-zinc-900 lg:max-w-xl" />
                <div class="flex gap-2">
                    <flux:select wire:model.live="porPagina" class="rounded-2xl border-slate-300 px-4 py-3 text-sm dark:border-zinc-700 dark:bg-zinc-900">
                        @foreach([8,12,24,48] as $size)<flux:select.option value="{{ $size }}">{{ $size }} por página</flux:select.option>@endforeach
                    </flux:select>
                    <a href="{{ route('credenciales.pdf.todas') }}" target="_blank" class="rounded-2xl bg-[#006492] px-4 py-3 text-sm font-black text-white">PDF general</a>
                </div>
            </div>

            <div class="grid gap-4 md:grid-cols-2 xl:grid-cols-3">
                @forelse($credenciales as $credencial)
                    <article class="overflow-hidden rounded-3xl border border-slate-200 bg-white shadow-sm dark:border-zinc-800 dark:bg-zinc-950">
                        <div class="h-2 bg-gradient-to-r from-[#006492] to-[#88AC2E]"></div>
                        <div class="p-5">
                            <div class="flex items-start gap-4">
                                <div class="h-16 w-16 shrink-0 overflow-hidden rounded-2xl bg-slate-100 dark:bg-zinc-900">
                                    @if($credencial->foto)
                                        <img src="{{ asset('storage/'.$credencial->foto) }}" class="h-full w-full object-cover">
                                    @else
                                        <div class="flex h-full items-center justify-center text-lg font-black">{{ Str::upper(Str::substr($credencial->nombre,0,2)) }}</div>
                                    @endif
                                </div>
                                <div class="min-w-0 flex-1">
                                    <div class="flex flex-wrap items-center gap-2">
                                        <h3 class="truncate text-lg font-black">{{ $credencial->nombre }}</h3>
                                        <span class="rounded-full px-2 py-1 text-[10px] font-black uppercase {{ $credencial->estado === 'activa' ? 'bg-emerald-100 text-emerald-700' : 'bg-slate-200 text-slate-600' }}">{{ $credencial->estado ?: 'activa' }}</span>
                                    </div>
                                    <p class="mt-1 text-xs uppercase tracking-wider text-[#006492]">{{ $tipos[$credencial->tipo ?? 'general'] ?? ucfirst($credencial->tipo ?? 'general') }}</p>
                                    <p class="mt-2 text-sm font-semibold">{{ $credencial->cargo ?: ($credencial->nivel ?: 'Sin cargo') }}</p>
                                    <p class="text-xs text-slate-500">{{ $credencial->organizacion ?: ($credencial->licenciatura ?: 'Sin organización') }}</p>
                                </div>
                            </div>

                            <dl class="mt-5 grid grid-cols-2 gap-3 text-xs">
                                <div class="rounded-xl bg-slate-50 p-3 dark:bg-zinc-900"><dt class="font-bold uppercase text-slate-500">Folio</dt><dd class="mt-1 font-black">{{ $credencial->folio ?: ($credencial->matricula ?: '—') }}</dd></div>
                                <div class="rounded-xl bg-slate-50 p-3 dark:bg-zinc-900"><dt class="font-bold uppercase text-slate-500">Vigencia</dt><dd class="mt-1 font-black">{{ $credencial->vigencia ?: 'Sin vigencia' }}</dd></div>
                                <div class="rounded-xl bg-slate-50 p-3 dark:bg-zinc-900"><dt class="font-bold uppercase text-slate-500">Marca</dt><dd class="mt-1 font-black">{{ $credencial->marca?->nombre ?? 'Sin marca' }}</dd></div>
                                <div class="rounded-xl bg-slate-50 p-3 dark:bg-zinc-900"><dt class="font-bold uppercase text-slate-500">Validación</dt><dd class="mt-1 font-black">{{ $credencial->registroValidacion?->codigo ?? 'No generada' }}</dd></div>
                            </dl>

                            @if($credencial->tiene_reverso)
                                <div class="mt-3 inline-flex rounded-full bg-violet-100 px-3 py-1 text-[10px] font-black uppercase text-violet-700 dark:bg-violet-950/40 dark:text-violet-200">Frente y reverso</div>
                            @endif

                            <div class="mt-4 grid grid-cols-2 gap-2">
                                <a href="{{ route('credenciales.pdf.individual', $credencial) }}" target="_blank" class="rounded-xl border border-sky-200 bg-sky-50 px-3 py-2 text-center text-xs font-black text-sky-700 dark:border-sky-900 dark:bg-sky-950/30 dark:text-sky-200">PDF</a>
                                @if($credencial->registroValidacion)
                                    <a href="{{ route('validacion.publica', $credencial->registroValidacion->codigo) }}" target="_blank" class="rounded-xl border border-emerald-200 bg-emerald-50 px-3 py-2 text-center text-xs font-black text-emerald-700 dark:border-emerald-900 dark:bg-emerald-950/30 dark:text-emerald-200">Validar</a>
                                @else
                                    <span class="rounded-xl border border-slate-200 px-3 py-2 text-center text-xs font-black text-slate-400 dark:border-zinc-700">Sin validación</span>
                                @endif
                                <flux:button type="button" wire:click="abrirEditar({{ $credencial->id }})" class="rounded-xl border border-amber-200 bg-amber-50 px-3 py-2 text-xs font-black text-amber-700 dark:border-amber-900 dark:bg-amber-950/30 dark:text-amber-200">Editar</flux:button>
                                <flux:button type="button" wire:click="eliminar({{ $credencial->id }})" wire:confirm="¿Seguro que deseas eliminar esta credencial?" class="rounded-xl border border-red-200 bg-red-50 px-3 py-2 text-xs font-black text-red-700 dark:border-red-900 dark:bg-red-950/30 dark:text-red-200">Eliminar</flux:button>
                            </div>
                        </div>
                    </article>
                @empty
                    <div class="col-span-full rounded-3xl border border-dashed border-slate-300 p-12 text-center text-slate-500 dark:border-zinc-700">No hay credenciales registradas.</div>
                @endforelse
            </div>

            @if($credenciales->hasPages())
                <div class="border-t border-slate-200 pt-5 dark:border-zinc-800">{{ $credenciales->links() }}</div>
            @endif
        </div>
    </section>

    <div x-show="modalEditar" x-cloak x-transition.opacity class="fixed inset-0 z-50 flex items-center justify-center bg-slate-950/70 p-4 backdrop-blur-sm" @keydown.escape.window="$wire.cerrarModal()">
        <div x-show="modalEditar" x-transition class="max-h-[92vh] w-full max-w-5xl overflow-hidden rounded-3xl bg-white shadow-2xl dark:bg-zinc-950" @click.outside="$wire.cerrarModal()">
            <header class="flex items-center justify-between bg-gradient-to-r from-amber-500 to-orange-600 px-6 py-5 text-white">
                <div><p class="text-xs font-black uppercase tracking-widest text-white/75">Editar credencial</p><h3 class="text-xl font-black">Actualizar información</h3></div>
                <flux:button type="button" wire:click="cerrarModal" class="rounded-xl bg-white/15 px-3 py-2 text-xs font-black">Cerrar</flux:button>
            </header>

            <form wire:submit.prevent="actualizar" class="max-h-[78vh] space-y-6 overflow-y-auto p-6">
                @if($errors->any())
                    <div class="rounded-2xl border border-red-200 bg-red-50 p-4 text-sm text-red-800 dark:border-red-900 dark:bg-red-950/30 dark:text-red-200"><ul class="list-disc pl-5">@foreach($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul></div>
                @endif

                <div class="grid gap-4 md:grid-cols-2 xl:grid-cols-3">
                    <label class="text-sm font-bold">Tipo<flux:select wire:model.live="tipo" class="mt-1 w-full rounded-2xl border-slate-300 dark:border-zinc-700 dark:bg-zinc-900">@foreach($tipos as $value=>$label)<flux:select.option value="{{ $value }}">{{ $label }}</flux:select.option>@endforeach</flux:select></label>
                    <label class="text-sm font-bold xl:col-span-2">Nombre<flux:input wire:model="nombre" class="mt-1 w-full rounded-2xl border-slate-300 dark:border-zinc-700 dark:bg-zinc-900" /></label>
                    <label class="text-sm font-bold">Folio<flux:input wire:model="folio" class="mt-1 w-full rounded-2xl border-slate-300 dark:border-zinc-700 dark:bg-zinc-900" /></label>
                    <label class="text-sm font-bold">Cargo<flux:input wire:model="cargo" class="mt-1 w-full rounded-2xl border-slate-300 dark:border-zinc-700 dark:bg-zinc-900" /></label>
                    <label class="text-sm font-bold">Organización<flux:input wire:model="organizacion" class="mt-1 w-full rounded-2xl border-slate-300 dark:border-zinc-700 dark:bg-zinc-900" /></label>
                    <label class="text-sm font-bold">Correo<flux:input wire:model="correo" type="email" class="mt-1 w-full rounded-2xl border-slate-300 dark:border-zinc-700 dark:bg-zinc-900" /></label>
                    <label class="text-sm font-bold">Teléfono<flux:input wire:model="telefono" class="mt-1 w-full rounded-2xl border-slate-300 dark:border-zinc-700 dark:bg-zinc-900" /></label>
                    <label class="text-sm font-bold">Vigencia<flux:input wire:model="vigencia" class="mt-1 w-full rounded-2xl border-slate-300 dark:border-zinc-700 dark:bg-zinc-900" /></label>
                    <label class="text-sm font-bold">Estado<flux:select wire:model="estado" class="mt-1 w-full rounded-2xl border-slate-300 dark:border-zinc-700 dark:bg-zinc-900">@foreach(['activa','inactiva','vencida','cancelada'] as $value)<flux:select.option value="{{ $value }}">{{ ucfirst($value) }}</flux:select.option>@endforeach</flux:select></label>
                    <label class="text-sm font-bold md:col-span-2 xl:col-span-3">Domicilio o información adicional<flux:textarea wire:model="domicilio" rows="3" class="mt-1 w-full rounded-2xl border-slate-300 dark:border-zinc-700 dark:bg-zinc-900"></flux:textarea></label>
                </div>

                @if($tipo === 'escolar')
                    <div class="grid gap-4 rounded-3xl border border-blue-200 bg-blue-50/60 p-5 md:grid-cols-2 xl:grid-cols-4 dark:border-blue-900 dark:bg-blue-950/20">
                        <label class="text-sm font-bold">Matrícula<flux:input wire:model="matricula" class="mt-1 w-full rounded-2xl border-blue-200 dark:border-blue-900 dark:bg-zinc-950" /></label>
                        <label class="text-sm font-bold">CURP<flux:input wire:model="curp" maxlength="18" class="mt-1 w-full rounded-2xl border-blue-200 uppercase dark:border-blue-900 dark:bg-zinc-950" /></label>
                        <label class="text-sm font-bold">Nivel<flux:select wire:model.live="nivel" class="mt-1 w-full rounded-2xl border-blue-200 dark:border-blue-900 dark:bg-zinc-950"><flux:select.option value="">Seleccionar</flux:select.option>@foreach($niveles as $item)<flux:select.option value="{{ $item }}">{{ $item }}</flux:select.option>@endforeach</flux:select></label>
                        <label class="text-sm font-bold">Ciclo<flux:input wire:model="ciclo_escolar" class="mt-1 w-full rounded-2xl border-blue-200 dark:border-blue-900 dark:bg-zinc-950" /></label>
                        @if($nivel !== 'Licenciatura')
                            <label class="text-sm font-bold">Grado<flux:select wire:model="grado" class="mt-1 w-full rounded-2xl border-blue-200 dark:border-blue-900 dark:bg-zinc-950"><flux:select.option value="">Seleccionar</flux:select.option>@foreach($grados as $item)<flux:select.option value="{{ $item }}">{{ $item }}</flux:select.option>@endforeach</flux:select></label>
                            <label class="text-sm font-bold">Grupo<flux:select wire:model="grupo" class="mt-1 w-full rounded-2xl border-blue-200 dark:border-blue-900 dark:bg-zinc-950"><flux:select.option value="">Seleccionar</flux:select.option>@foreach($grupos as $item)<flux:select.option value="{{ $item }}">{{ $item }}</flux:select.option>@endforeach</flux:select></label>
                        @else
                            <label class="text-sm font-bold md:col-span-2">Licenciatura<flux:select wire:model="licenciatura" class="mt-1 w-full rounded-2xl border-blue-200 dark:border-blue-900 dark:bg-zinc-950"><flux:select.option value="">Seleccionar</flux:select.option>@foreach($licenciaturas as $item)<flux:select.option value="{{ $item }}">{{ $item }}</flux:select.option>@endforeach</flux:select></label>
                        @endif
                    </div>
                @endif

                <section class="rounded-3xl border border-violet-200 bg-violet-50/70 p-5 dark:border-violet-900 dark:bg-violet-950/20">
                    <label class="flex items-center justify-between gap-4">
                        <span><strong class="block text-sm text-violet-900 dark:text-violet-200">Credencial con reverso</strong><small class="text-xs text-slate-500">Conserva o reemplaza la parte posterior.</small></span>
                        <flux:checkbox wire:model.live="tiene_reverso" class="h-5 w-5 rounded border-violet-300 text-violet-600" />
                    </label>
                    @if($tiene_reverso)
                        <div class="mt-4 grid gap-4 md:grid-cols-2">
                            <label class="text-sm font-bold">Texto del reverso<flux:textarea wire:model="reverso_texto" rows="5" class="mt-1 w-full rounded-2xl border-violet-200 dark:border-violet-900 dark:bg-zinc-950"></flux:textarea></label>
                            <div>
                                <label class="text-sm font-bold">Reemplazar imagen del reverso<flux:input wire:model="reverso_imagen" type="file" accept="image/jpeg,image/png,image/webp" class="mt-1 block w-full rounded-2xl border border-dashed border-violet-300 p-3 text-xs dark:border-violet-800" /></label>
                                @if($reversoImagenActual)
                                    <img src="{{ asset('storage/'.$reversoImagenActual) }}" class="mt-3 h-24 w-full rounded-xl object-cover" alt="Reverso actual">
                                    <label class="mt-2 flex items-center gap-2 text-xs font-bold text-red-600"><flux:checkbox wire:model="eliminarReversoImagen" class="rounded" /> Eliminar imagen actual</label>
                                @endif
                            </div>
                        </div>
                    @endif
                </section>

                <div class="flex justify-end gap-3 border-t border-slate-200 pt-5 dark:border-zinc-800">
                    <flux:button type="button" wire:click="cerrarModal" class="rounded-2xl border border-slate-300 px-5 py-3 text-sm font-black dark:border-zinc-700">Cancelar</flux:button>
                    <flux:button type="submit" class="rounded-2xl bg-amber-500 px-6 py-3 text-sm font-black text-white">Guardar cambios</flux:button>
                </div>
            </form>
        </div>
    </div>
</div>
