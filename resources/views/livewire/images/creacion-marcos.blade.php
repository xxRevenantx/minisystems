<div
    x-data="{
        previewOpen: false,
        previewUrl: '',
        previewTitle: '',
        confirmDelete(id, name) {
            Swal.fire({
                title: '¿Enviar a la papelera?',
                text: `${name}. El marco dejará de estar disponible para procesar imágenes.`,
                icon: 'warning',
                showCancelButton: true,
                confirmButtonText: 'Sí, eliminar',
                cancelButtonText: 'Cancelar',
                confirmButtonColor: '#dc2626',
            }).then(result => {
                if (result.isConfirmed) $wire.eliminarMarco(id)
            })
        },
        confirmForceDelete(id, name) {
            Swal.fire({
                title: '¿Eliminar definitivamente?',
                text: `${name}. También se eliminarán sus archivos. Esta acción no se puede deshacer.`,
                icon: 'error',
                showCancelButton: true,
                confirmButtonText: 'Eliminar definitivamente',
                cancelButtonText: 'Cancelar',
                confirmButtonColor: '#b91c1c',
            }).then(result => {
                if (result.isConfirmed) $wire.eliminarDefinitivamente(id)
            })
        },
        confirmRestore(id, name) {
            Swal.fire({
                title: 'Restaurar marco',
                text: name,
                icon: 'question',
                showCancelButton: true,
                confirmButtonText: 'Restaurar',
                cancelButtonText: 'Cancelar',
                confirmButtonColor: '#16a34a',
            }).then(result => {
                if (result.isConfirmed) $wire.restaurarMarco(id)
            })
        },
        confirmToggle(id, active, name) {
            const action = active ? 'desactivar' : 'activar'
            Swal.fire({
                title: `¿${action.charAt(0).toUpperCase() + action.slice(1)} marco?`,
                text: name,
                icon: 'question',
                showCancelButton: true,
                confirmButtonText: `Sí, ${action}`,
                cancelButtonText: 'Cancelar',
                confirmButtonColor: active ? '#d97706' : '#16a34a',
            }).then(result => {
                if (result.isConfirmed) $wire.alternarEstado(id)
            })
        },
        confirmDuplicate(id, name) {
            Swal.fire({
                title: 'Duplicar marco',
                text: `Se creará una copia inactiva de ${name}.`,
                icon: 'info',
                showCancelButton: true,
                confirmButtonText: 'Duplicar',
                cancelButtonText: 'Cancelar',
                confirmButtonColor: '#2563eb',
            }).then(result => {
                if (result.isConfirmed) $wire.duplicarMarco(id)
            })
        },
        openPreview(url, title) {
            this.previewUrl = url
            this.previewTitle = title
            this.previewOpen = true
        }
    }"
    x-on:marco-form-focus.window="$nextTick(() => document.getElementById('marco-formulario')?.scrollIntoView({ behavior: 'smooth', block: 'start' }))"
    x-on:keydown.escape.window="previewOpen = false"
    class="space-y-6"
>
    {{-- Estadísticas --}}
    <section class="grid grid-cols-2 gap-3 lg:grid-cols-4">
        <div class="rounded-2xl border border-neutral-200 bg-white p-4 shadow-sm dark:border-neutral-800 dark:bg-neutral-900">
            <p class="text-xs font-semibold uppercase tracking-wide text-neutral-500">Total</p>
            <div class="mt-2 flex items-end justify-between">
                <span class="text-3xl font-black text-neutral-900 dark:text-white">{{ $estadisticas['total'] }}</span>
                <span class="rounded-xl bg-blue-50 p-2 text-blue-600 dark:bg-blue-500/10 dark:text-blue-300">▣</span>
            </div>
        </div>
        <div class="rounded-2xl border border-neutral-200 bg-white p-4 shadow-sm dark:border-neutral-800 dark:bg-neutral-900">
            <p class="text-xs font-semibold uppercase tracking-wide text-neutral-500">Activos</p>
            <div class="mt-2 flex items-end justify-between">
                <span class="text-3xl font-black text-neutral-900 dark:text-white">{{ $estadisticas['activos'] }}</span>
                <span class="rounded-xl bg-emerald-50 p-2 text-emerald-600 dark:bg-emerald-500/10 dark:text-emerald-300">✓</span>
            </div>
        </div>
        <div class="rounded-2xl border border-neutral-200 bg-white p-4 shadow-sm dark:border-neutral-800 dark:bg-neutral-900">
            <p class="text-xs font-semibold uppercase tracking-wide text-neutral-500">Completos</p>
            <div class="mt-2 flex items-end justify-between">
                <span class="text-3xl font-black text-neutral-900 dark:text-white">{{ $estadisticas['completos'] }}</span>
                <span class="rounded-xl bg-violet-50 p-2 text-violet-600 dark:bg-violet-500/10 dark:text-violet-300">↔</span>
            </div>
        </div>
        <div class="rounded-2xl border border-neutral-200 bg-white p-4 shadow-sm dark:border-neutral-800 dark:bg-neutral-900">
            <p class="text-xs font-semibold uppercase tracking-wide text-neutral-500">Incompletos</p>
            <div class="mt-2 flex items-end justify-between">
                <span class="text-3xl font-black text-neutral-900 dark:text-white">{{ $estadisticas['incompletos'] }}</span>
                <span class="rounded-xl bg-amber-50 p-2 text-amber-600 dark:bg-amber-500/10 dark:text-amber-300">!</span>
            </div>
        </div>
    </section>

    {{-- Formulario --}}
    <section id="marco-formulario" class="overflow-hidden rounded-2xl border border-neutral-200 bg-white shadow-sm dark:border-neutral-800 dark:bg-neutral-900">
        <div class="flex flex-col gap-3 border-b border-neutral-200 bg-gradient-to-r from-slate-950 via-blue-950 to-indigo-950 px-5 py-5 text-white sm:flex-row sm:items-center sm:justify-between dark:border-neutral-800">
            <div>
                <div class="mb-1 flex items-center gap-2">
                    <span class="rounded-full bg-white/10 px-2.5 py-1 text-[11px] font-bold uppercase tracking-wider">
                        {{ $editandoId ? 'Editando' : 'Nuevo marco' }}
                    </span>
                    @if($editandoId)
                        <span class="text-xs text-blue-200">ID #{{ $editandoId }}</span>
                    @endif
                </div>
                <h2 class="text-xl font-black">{{ $editandoId ? 'Actualizar marco adaptable' : 'Crear marco adaptable' }}</h2>
                <p class="mt-1 max-w-2xl text-sm text-blue-100/80">
                    Sube una versión horizontal y otra vertical. Puedes guardar el marco con una sola orientación y completarlo después.
                </p>
            </div>
            @if($editandoId)
                <button type="button" wire:click="cancelarEdicion"
                    class="inline-flex items-center justify-center rounded-xl border border-white/20 bg-white/10 px-4 py-2 text-sm font-semibold text-white transition hover:bg-white/20">
                    Cancelar edición
                </button>
            @endif
        </div>

        <form wire:submit="guardarMarco" class="space-y-6 p-5">
            <div class="grid gap-5 lg:grid-cols-2">
                {{-- Desktop --}}
                <div class="rounded-2xl border border-dashed border-blue-300 bg-blue-50/50 p-4 dark:border-blue-800 dark:bg-blue-950/20">
                    <div class="mb-3 flex items-start justify-between gap-3">
                        <div>
                            <div class="flex items-center gap-2">
                                <h3 class="font-bold text-neutral-900 dark:text-white">Versión desktop</h3>
                                <span class="rounded-full bg-blue-100 px-2 py-0.5 text-[10px] font-bold uppercase text-blue-700 dark:bg-blue-500/15 dark:text-blue-300">Horizontal</span>
                            </div>
                            <p class="mt-1 text-xs text-neutral-500 dark:text-neutral-400">Recomendado: 2058 × 1365 px, PNG transparente.</p>
                        </div>
                    </div>

                    <label class="group flex min-h-44 cursor-pointer flex-col items-center justify-center rounded-xl border border-neutral-200 bg-white px-4 py-5 text-center transition hover:border-blue-400 hover:bg-blue-50 dark:border-neutral-700 dark:bg-neutral-950 dark:hover:border-blue-600 dark:hover:bg-blue-950/30">
                        <input type="file" wire:model="marcoDesktop" accept="image/jpeg,image/png,image/webp" class="sr-only">
                        @if($marcoDesktop)
                            @php [$dw, $dh] = @getimagesize($marcoDesktop->getRealPath()) ?: [null, null]; @endphp
                            <img src="{{ $marcoDesktop->temporaryUrl() }}" class="h-28 w-full rounded-lg object-contain" alt="Vista previa desktop">
                            <p class="mt-3 text-xs font-semibold text-blue-700 dark:text-blue-300">{{ $dw }} × {{ $dh }} px · {{ strtoupper($marcoDesktop->getClientOriginalExtension()) }}</p>
                        @elseif($marcoEditando && ($archivo = $marcoEditando->marco_desktop ?: $marcoEditando->marco))
                            <img src="{{ asset('storage/imagenesMarcos/'.$archivo) }}" class="h-28 w-full rounded-lg object-contain" alt="Marco desktop actual">
                            <p class="mt-3 text-xs font-semibold text-neutral-600 dark:text-neutral-300">Archivo actual · haz clic para reemplazar</p>
                        @else
                            <div class="flex h-12 w-12 items-center justify-center rounded-2xl bg-blue-100 text-2xl text-blue-600 transition group-hover:scale-105 dark:bg-blue-500/15 dark:text-blue-300">＋</div>
                            <p class="mt-3 text-sm font-bold text-neutral-800 dark:text-neutral-100">Seleccionar marco horizontal</p>
                            <p class="mt-1 text-xs text-neutral-500">JPG, PNG o WebP · máximo 20 MB</p>
                        @endif
                    </label>

                    @if($marcoEditando && ($marcoEditando->marco_desktop ?: $marcoEditando->marco))
                        <label class="mt-3 flex items-center gap-2 text-xs text-red-600 dark:text-red-400">
                            <input type="checkbox" wire:model="quitarDesktop" class="rounded border-neutral-300">
                            Quitar la versión desktop actual
                        </label>
                    @endif
                    @error('marcoDesktop') <p class="mt-2 text-sm font-medium text-red-600">{{ $message }}</p> @enderror
                </div>

                {{-- Mobile --}}
                <div class="rounded-2xl border border-dashed border-violet-300 bg-violet-50/50 p-4 dark:border-violet-800 dark:bg-violet-950/20">
                    <div class="mb-3 flex items-start justify-between gap-3">
                        <div>
                            <div class="flex items-center gap-2">
                                <h3 class="font-bold text-neutral-900 dark:text-white">Versión móvil</h3>
                                <span class="rounded-full bg-violet-100 px-2 py-0.5 text-[10px] font-bold uppercase text-violet-700 dark:bg-violet-500/15 dark:text-violet-300">Vertical</span>
                            </div>
                            <p class="mt-1 text-xs text-neutral-500 dark:text-neutral-400">Recomendado: 1365 × 2058 px, PNG transparente.</p>
                        </div>
                    </div>

                    <label class="group flex min-h-44 cursor-pointer flex-col items-center justify-center rounded-xl border border-neutral-200 bg-white px-4 py-5 text-center transition hover:border-violet-400 hover:bg-violet-50 dark:border-neutral-700 dark:bg-neutral-950 dark:hover:border-violet-600 dark:hover:bg-violet-950/30">
                        <input type="file" wire:model="marcoMobile" accept="image/jpeg,image/png,image/webp" class="sr-only">
                        @if($marcoMobile)
                            @php [$mw, $mh] = @getimagesize($marcoMobile->getRealPath()) ?: [null, null]; @endphp
                            <img src="{{ $marcoMobile->temporaryUrl() }}" class="h-28 w-full rounded-lg object-contain" alt="Vista previa móvil">
                            <p class="mt-3 text-xs font-semibold text-violet-700 dark:text-violet-300">{{ $mw }} × {{ $mh }} px · {{ strtoupper($marcoMobile->getClientOriginalExtension()) }}</p>
                        @elseif($marcoEditando && $marcoEditando->marco_mobile)
                            <img src="{{ asset('storage/imagenesMarcos/'.$marcoEditando->marco_mobile) }}" class="h-28 w-full rounded-lg object-contain" alt="Marco móvil actual">
                            <p class="mt-3 text-xs font-semibold text-neutral-600 dark:text-neutral-300">Archivo actual · haz clic para reemplazar</p>
                        @else
                            <div class="flex h-12 w-12 items-center justify-center rounded-2xl bg-violet-100 text-2xl text-violet-600 transition group-hover:scale-105 dark:bg-violet-500/15 dark:text-violet-300">＋</div>
                            <p class="mt-3 text-sm font-bold text-neutral-800 dark:text-neutral-100">Seleccionar marco vertical</p>
                            <p class="mt-1 text-xs text-neutral-500">JPG, PNG o WebP · máximo 20 MB</p>
                        @endif
                    </label>

                    @if($marcoEditando && $marcoEditando->marco_mobile)
                        <label class="mt-3 flex items-center gap-2 text-xs text-red-600 dark:text-red-400">
                            <input type="checkbox" wire:model="quitarMobile" class="rounded border-neutral-300">
                            Quitar la versión móvil actual
                        </label>
                    @endif
                    @error('marcoMobile') <p class="mt-2 text-sm font-medium text-red-600">{{ $message }}</p> @enderror
                </div>
            </div>

            <div class="grid gap-4 md:grid-cols-2 xl:grid-cols-3">
                <flux:input wire:model="nombre" label="Nombre del marco" badge="Obligatorio" placeholder="Ej. Clausura institucional 2026" />
                <flux:input wire:model="categoria" label="Categoría" badge="Obligatorio" placeholder="Institucional, clausura, publicidad..." list="categorias-marcos" />
                <datalist id="categorias-marcos">
                    @foreach($categorias as $cat)<option value="{{ $cat }}">@endforeach
                </datalist>
                <flux:input wire:model="tagsTexto" label="Etiquetas" placeholder="graduación, azul, universidad" description="Sepáralas con comas." />
                <div class="md:col-span-2 xl:col-span-2">
                    <flux:textarea wire:model="descripcion" label="Descripción" placeholder="Describe el uso y estilo del marco..." rows="3" />
                </div>
                <flux:textarea wire:model="notas" label="Notas internas" placeholder="Indicaciones para administradores..." rows="3" />
            </div>

            <div class="flex flex-col gap-4 rounded-xl border border-neutral-200 bg-neutral-50 p-4 sm:flex-row sm:items-center sm:justify-between dark:border-neutral-800 dark:bg-neutral-950/60">
                <label class="flex cursor-pointer items-center gap-3">
                    <input type="checkbox" wire:model="activo" class="h-5 w-5 rounded border-neutral-300 text-blue-600 focus:ring-blue-500">
                    <span>
                        <span class="block text-sm font-bold text-neutral-800 dark:text-neutral-100">Marco activo</span>
                        <span class="block text-xs text-neutral-500">Disponible inmediatamente en System Images.</span>
                    </span>
                </label>

                <div class="flex flex-wrap items-center gap-2">
                    @if($editandoId)
                        <flux:button type="button" wire:click="cancelarEdicion" variant="ghost">Cancelar</flux:button>
                    @endif
                    <flux:button type="submit" variant="primary" wire:loading.attr="disabled" wire:target="guardarMarco,marcoDesktop,marcoMobile">
                        <span wire:loading.remove wire:target="guardarMarco">{{ $editandoId ? 'Guardar cambios' : 'Crear marco' }}</span>
                        <span wire:loading wire:target="guardarMarco">Guardando...</span>
                    </flux:button>
                </div>
            </div>
        </form>
    </section>

    {{-- Filtros --}}
    <section class="rounded-2xl border border-neutral-200 bg-white p-4 shadow-sm dark:border-neutral-800 dark:bg-neutral-900">
        <div class="grid gap-3 md:grid-cols-[1fr_220px_200px_auto]">
            <flux:input wire:model.live.debounce.350ms="buscar" icon="magnifying-glass" placeholder="Buscar por nombre, descripción o categoría..." />
            <flux:select wire:model.live="filtroCategoria" placeholder="Todas las categorías">
                <flux:select.option value="">Todas las categorías</flux:select.option>
                @foreach($categorias as $cat)
                    <flux:select.option value="{{ $cat }}">{{ $cat }}</flux:select.option>
                @endforeach
            </flux:select>
            <flux:select wire:model.live="filtroEstado">
                <flux:select.option value="todos">Todos los estados</flux:select.option>
                <flux:select.option value="activos">Activos</flux:select.option>
                <flux:select.option value="inactivos">Inactivos</flux:select.option>
                <flux:select.option value="incompletos">Incompletos</flux:select.option>
                <flux:select.option value="papelera">Papelera ({{ $estadisticas['papelera'] }})</flux:select.option>
            </flux:select>
            <div class="flex items-center justify-end text-xs font-semibold text-neutral-500">
                {{ $marcos->count() }} resultado(s)
            </div>
        </div>
    </section>

    {{-- Galería --}}
    <section>
        <div class="mb-3 flex items-center justify-between">
            <div>
                <h2 class="text-lg font-black text-neutral-900 dark:text-white">Biblioteca de marcos</h2>
                <p class="text-xs text-neutral-500">Arrastra las tarjetas para cambiar su orden.</p>
            </div>
        </div>

        <div
            x-data
            x-ref="marcosGrid"
            x-init="
                const iniciar = () => {
                    if (!window.Sortable || !$refs.marcosGrid || $refs.marcosGrid._sortable) return;
                    $refs.marcosGrid._sortable = new Sortable($refs.marcosGrid, {
                        animation: 180,
                        handle: '.marco-handle',
                        ghostClass: 'opacity-40',
                        onEnd() {
                            $wire.reordenarMarcos([...$refs.marcosGrid.children].map(el => el.dataset.id))
                        }
                    })
                };
                iniciar();
                document.addEventListener('livewire:navigated', iniciar)
            "
            class="grid gap-4 sm:grid-cols-2 xl:grid-cols-3 2xl:grid-cols-4"
        >
            @forelse($marcos as $marco)
                @php
                    $desktop = $marco->marco_desktop ?: $marco->marco;
                    $mobile = $marco->marco_mobile;
                    $desktopUrl = $desktop ? asset('storage/imagenesMarcos/'.$desktop) : null;
                    $mobileUrl = $mobile ? asset('storage/imagenesMarcos/'.$mobile) : null;
                @endphp
                <article
                    wire:key="marco-card-{{ $marco->id }}"
                    data-id="{{ $marco->id }}"
                    x-data="{ tab: '{{ $desktop ? 'desktop' : 'mobile' }}' }"
                    class="group overflow-hidden rounded-2xl border border-neutral-200 bg-white shadow-sm transition hover:-translate-y-0.5 hover:shadow-lg dark:border-neutral-800 dark:bg-neutral-900"
                >
                    <div class="relative aspect-[16/10] overflow-hidden bg-[linear-gradient(45deg,#e5e7eb_25%,transparent_25%),linear-gradient(-45deg,#e5e7eb_25%,transparent_25%),linear-gradient(45deg,transparent_75%,#e5e7eb_75%),linear-gradient(-45deg,transparent_75%,#e5e7eb_75%)] bg-[length:18px_18px] bg-[position:0_0,0_9px,9px_-9px,-9px_0px] dark:bg-neutral-950">
                        <button type="button" class="marco-handle absolute left-2 top-2 z-20 cursor-grab rounded-lg bg-black/65 px-2 py-1 text-xs font-bold text-white backdrop-blur" title="Arrastrar">☰</button>

                        <div class="absolute right-2 top-2 z-20 flex rounded-lg bg-black/65 p-1 text-[10px] font-bold text-white backdrop-blur">
                            @if($desktop)
                                <button type="button" @click="tab='desktop'" :class="tab==='desktop' ? 'bg-white text-black' : ''" class="rounded-md px-2 py-1 transition">DESKTOP</button>
                            @endif
                            @if($mobile)
                                <button type="button" @click="tab='mobile'" :class="tab==='mobile' ? 'bg-white text-black' : ''" class="rounded-md px-2 py-1 transition">MÓVIL</button>
                            @endif
                        </div>

                        @if($desktop)
                            <img x-show="tab==='desktop'" x-transition.opacity src="{{ $desktopUrl }}" alt="{{ $marco->nombre }} desktop"
                                @click="openPreview(@js($desktopUrl), @js(($marco->nombre ?: $marco->descripcion).' · Desktop'))"
                                class="h-full w-full cursor-zoom-in object-contain p-3">
                        @endif
                        @if($mobile)
                            <img x-show="tab==='mobile'" x-transition.opacity src="{{ $mobileUrl }}" alt="{{ $marco->nombre }} móvil"
                                @click="openPreview(@js($mobileUrl), @js(($marco->nombre ?: $marco->descripcion).' · Móvil'))"
                                class="h-full w-full cursor-zoom-in object-contain p-3">
                        @endif

                        @if(!$desktop || !$mobile)
                            <span class="absolute bottom-2 left-2 rounded-full bg-amber-500 px-2.5 py-1 text-[10px] font-black uppercase tracking-wide text-white shadow">Incompleto</span>
                        @else
                            <span class="absolute bottom-2 left-2 rounded-full bg-emerald-500 px-2.5 py-1 text-[10px] font-black uppercase tracking-wide text-white shadow">Adaptable</span>
                        @endif
                    </div>

                    <div class="space-y-3 p-4">
                        <div class="flex items-start justify-between gap-3">
                            <div class="min-w-0">
                                <h3 class="truncate font-black text-neutral-900 dark:text-white">{{ $marco->nombre ?: $marco->descripcion }}</h3>
                                <p class="mt-1 line-clamp-2 text-xs text-neutral-500 dark:text-neutral-400">{{ $marco->descripcion ?: 'Sin descripción adicional.' }}</p>
                            </div>
                            @if($marco->trashed())
                                <span class="shrink-0 rounded-full bg-red-100 px-2 py-1 text-[10px] font-black uppercase text-red-700 dark:bg-red-500/15 dark:text-red-300">Papelera</span>
                            @else
                                <span class="shrink-0 rounded-full px-2 py-1 text-[10px] font-black uppercase {{ $marco->activo ? 'bg-emerald-100 text-emerald-700 dark:bg-emerald-500/15 dark:text-emerald-300' : 'bg-neutral-100 text-neutral-600 dark:bg-neutral-800 dark:text-neutral-300' }}">
                                    {{ $marco->activo ? 'Activo' : 'Inactivo' }}
                                </span>
                            @endif
                        </div>

                        <div class="flex flex-wrap gap-1.5">
                            <span class="rounded-lg bg-blue-50 px-2 py-1 text-[10px] font-bold text-blue-700 dark:bg-blue-500/10 dark:text-blue-300">{{ $marco->categoria }}</span>
                            @foreach(array_slice($marco->tags ?? [], 0, 3) as $tag)
                                <span class="rounded-lg bg-neutral-100 px-2 py-1 text-[10px] font-semibold text-neutral-600 dark:bg-neutral-800 dark:text-neutral-300">#{{ $tag }}</span>
                            @endforeach
                        </div>

                        <div class="grid grid-cols-2 gap-2 text-[11px] text-neutral-500">
                            <div class="rounded-lg bg-neutral-50 p-2 dark:bg-neutral-950/60">
                                <span class="block font-bold text-neutral-700 dark:text-neutral-200">Desktop</span>
                                <span>{{ $desktop ? (($marco->ancho_desktop ?: '—').' × '.($marco->alto_desktop ?: '—')) : 'No disponible' }}</span>
                                @if($desktop)
                                    <span class="mt-1 block text-[9px] font-semibold uppercase tracking-wide {{ $marco->transparencia_desktop ? 'text-emerald-600 dark:text-emerald-400' : 'text-neutral-400' }}">
                                        {{ strtoupper($marco->formato_desktop ?: pathinfo($desktop, PATHINFO_EXTENSION)) }} · {{ $marco->transparencia_desktop ? 'Con transparencia' : 'Sin transparencia' }}
                                    </span>
                                @endif
                            </div>
                            <div class="rounded-lg bg-neutral-50 p-2 dark:bg-neutral-950/60">
                                <span class="block font-bold text-neutral-700 dark:text-neutral-200">Móvil</span>
                                <span>{{ $mobile ? (($marco->ancho_mobile ?: '—').' × '.($marco->alto_mobile ?: '—')) : 'No disponible' }}</span>
                                @if($mobile)
                                    <span class="mt-1 block text-[9px] font-semibold uppercase tracking-wide {{ $marco->transparencia_mobile ? 'text-emerald-600 dark:text-emerald-400' : 'text-neutral-400' }}">
                                        {{ strtoupper($marco->formato_mobile ?: pathinfo($mobile, PATHINFO_EXTENSION)) }} · {{ $marco->transparencia_mobile ? 'Con transparencia' : 'Sin transparencia' }}
                                    </span>
                                @endif
                            </div>
                        </div>

                        <div class="flex items-center justify-between border-t border-neutral-100 pt-3 text-[11px] text-neutral-500 dark:border-neutral-800">
                            <span>{{ number_format($marco->usos) }} uso(s)</span>
                            <span>{{ $marco->ultimo_uso_at?->diffForHumans() ?: 'Sin uso todavía' }}</span>
                        </div>

                        @if($marco->trashed())
                            <div class="grid grid-cols-2 gap-2">
                                <button type="button" @click="confirmRestore({{ $marco->id }}, @js($marco->nombre ?: $marco->descripcion))" class="rounded-lg border border-emerald-200 px-3 py-2 text-xs font-bold text-emerald-700 transition hover:bg-emerald-50 dark:border-emerald-900/60 dark:text-emerald-400 dark:hover:bg-emerald-950/30">Restaurar</button>
                                <button type="button" @click="confirmForceDelete({{ $marco->id }}, @js($marco->nombre ?: $marco->descripcion))" class="rounded-lg border border-red-200 px-3 py-2 text-xs font-bold text-red-600 transition hover:bg-red-50 dark:border-red-900/60 dark:text-red-400 dark:hover:bg-red-950/30">Eliminar definitivamente</button>
                            </div>
                        @else
                            <div class="grid grid-cols-4 gap-2">
                                <button type="button" wire:click="editarMarco({{ $marco->id }})" class="rounded-lg border border-neutral-200 px-2 py-2 text-xs font-bold text-neutral-700 transition hover:border-blue-300 hover:bg-blue-50 hover:text-blue-700 dark:border-neutral-700 dark:text-neutral-200 dark:hover:border-blue-700 dark:hover:bg-blue-950/30" title="Editar">Editar</button>
                                <button type="button" @click="confirmDuplicate({{ $marco->id }}, @js($marco->nombre ?: $marco->descripcion))" class="rounded-lg border border-neutral-200 px-2 py-2 text-xs font-bold text-neutral-700 transition hover:border-violet-300 hover:bg-violet-50 hover:text-violet-700 dark:border-neutral-700 dark:text-neutral-200 dark:hover:border-violet-700 dark:hover:bg-violet-950/30" title="Duplicar">Copiar</button>
                                <button type="button" @click="confirmToggle({{ $marco->id }}, @js((bool)$marco->activo), @js($marco->nombre ?: $marco->descripcion))" class="rounded-lg border border-neutral-200 px-2 py-2 text-xs font-bold text-neutral-700 transition hover:border-amber-300 hover:bg-amber-50 hover:text-amber-700 dark:border-neutral-700 dark:text-neutral-200 dark:hover:border-amber-700 dark:hover:bg-amber-950/30" title="{{ $marco->activo ? 'Desactivar' : 'Activar' }}">{{ $marco->activo ? 'Pausar' : 'Activar' }}</button>
                                <button type="button" @click="confirmDelete({{ $marco->id }}, @js($marco->nombre ?: $marco->descripcion))" class="rounded-lg border border-red-200 px-2 py-2 text-xs font-bold text-red-600 transition hover:bg-red-50 dark:border-red-900/60 dark:text-red-400 dark:hover:bg-red-950/30" title="Eliminar">Eliminar</button>
                            </div>
                        @endif
                    </div>
                </article>
            @empty
                <div class="col-span-full rounded-2xl border border-dashed border-neutral-300 bg-white px-6 py-16 text-center dark:border-neutral-700 dark:bg-neutral-900">
                    <div class="mx-auto flex h-14 w-14 items-center justify-center rounded-2xl bg-neutral-100 text-2xl dark:bg-neutral-800">▣</div>
                    <h3 class="mt-4 font-black text-neutral-900 dark:text-white">No se encontraron marcos</h3>
                    <p class="mt-1 text-sm text-neutral-500">Cambia los filtros o crea el primer marco adaptable.</p>
                </div>
            @endforelse
        </div>
    </section>

    {{-- Modal de vista previa --}}
    <div x-show="previewOpen" x-transition.opacity x-cloak
        class="fixed inset-0 z-[100] flex items-center justify-center bg-black/75 p-4 backdrop-blur-sm"
        @click.self="previewOpen=false">
        <div class="relative max-h-[92vh] w-full max-w-6xl overflow-hidden rounded-2xl bg-white shadow-2xl dark:bg-neutral-900">
            <div class="flex items-center justify-between border-b border-neutral-200 px-4 py-3 dark:border-neutral-800">
                <h3 class="font-bold text-neutral-900 dark:text-white" x-text="previewTitle"></h3>
                <button type="button" @click="previewOpen=false" class="flex h-9 w-9 items-center justify-center rounded-full bg-neutral-100 text-xl hover:bg-neutral-200 dark:bg-neutral-800 dark:hover:bg-neutral-700">×</button>
            </div>
            <div class="max-h-[82vh] overflow-auto bg-[linear-gradient(45deg,#e5e7eb_25%,transparent_25%),linear-gradient(-45deg,#e5e7eb_25%,transparent_25%),linear-gradient(45deg,transparent_75%,#e5e7eb_75%),linear-gradient(-45deg,transparent_75%,#e5e7eb_75%)] bg-[length:24px_24px] bg-[position:0_0,0_12px,12px_-12px,-12px_0px] p-6 dark:bg-neutral-950">
                <img :src="previewUrl" :alt="previewTitle" class="mx-auto max-h-[76vh] max-w-full object-contain">
            </div>
        </div>
    </div>
</div>

@push('scripts')
    <script src="https://cdn.jsdelivr.net/npm/sortablejs@1.15.2/Sortable.min.js" defer></script>
@endpush
