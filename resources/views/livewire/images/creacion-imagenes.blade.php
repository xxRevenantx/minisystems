<div x-data="{
    isUploading: false,
    progress: 0,
    dragActive: false,
    compare: 52,
    submitDrop(event) {
        const files = event.dataTransfer.files;
        if (!files?.length) return;
        this.$refs.imagesInput.files = files;
        this.$refs.imagesInput.dispatchEvent(new Event('change', { bubbles: true }));
    }
}" x-on:livewire-upload-start.window="isUploading=true; progress=0"
    x-on:livewire-upload-finish.window="isUploading=false; progress=100"
    x-on:livewire-upload-error.window="isUploading=false"
    x-on:livewire-upload-progress.window="progress=$event.detail.progress" class="space-y-6 px-1 sm:px-2">
    {{-- Encabezado y pasos --}}
    <section
        class="overflow-hidden rounded-[28px] border border-slate-200/80 bg-white shadow-[0_20px_60px_-20px_rgba(15,23,42,0.18)] ring-1 ring-slate-100 dark:border-neutral-800 dark:bg-neutral-900 dark:ring-neutral-800">
        <div
            class="relative overflow-hidden bg-gradient-to-br from-sky-500 via-blue-600 to-indigo-700 px-6 py-8 text-white sm:px-8 sm:py-10">
            <div class="pointer-events-none absolute inset-0 opacity-25">
                <div class="absolute -left-14 top-0 h-44 w-44 rounded-full bg-white/25 blur-3xl"></div>
                <div class="absolute right-0 top-5 h-56 w-56 rounded-full bg-cyan-300/25 blur-3xl"></div>
                <div class="absolute bottom-0 left-1/3 h-44 w-44 rounded-full bg-indigo-300/25 blur-3xl"></div>
                <div
                    class="absolute inset-0 bg-[radial-gradient(circle_at_top_right,rgba(255,255,255,0.14),transparent_35%)]">
                </div>
            </div>

            <div class="relative flex flex-col gap-7 lg:flex-row lg:items-center lg:justify-between">
                <div class="max-w-3xl">
                    <span
                        class="inline-flex items-center rounded-full border border-white/15 bg-white/10 px-4 py-1.5 text-[11px] font-extrabold uppercase tracking-[0.18em] text-white/95 backdrop-blur">
                        Procesamiento inteligente
                    </span>

                    <h2 class="mt-4 text-3xl font-black tracking-tight sm:text-4xl">
                        System Images
                    </h2>

                    <p class="mt-3 max-w-2xl text-sm leading-6 text-blue-50/95 sm:text-[15px]">
                        Mezcla imágenes horizontales, verticales y cuadradas en un mismo lote.
                        El sistema detecta su orientación y aplica automáticamente el marco correcto.
                    </p>
                </div>

                <div class="grid grid-cols-1 gap-3 sm:grid-cols-3">
                    <div class="rounded-2xl border border-white/10 bg-white/10 px-4 py-3 text-center backdrop-blur">
                        <p class="text-[10px] font-extrabold uppercase tracking-[0.16em] text-blue-100">Capacidad</p>
                        <p class="mt-1 text-sm font-black text-white">Hasta 100 imágenes</p>
                    </div>
                    <div class="rounded-2xl border border-white/10 bg-white/10 px-4 py-3 text-center backdrop-blur">
                        <p class="text-[10px] font-extrabold uppercase tracking-[0.16em] text-blue-100">Formatos</p>
                        <p class="mt-1 text-sm font-black text-white">JPG · PNG · WebP</p>
                    </div>
                    <div class="rounded-2xl border border-white/10 bg-white/10 px-4 py-3 text-center backdrop-blur">
                        <p class="text-[10px] font-extrabold uppercase tracking-[0.16em] text-blue-100">Salida</p>
                        <p class="mt-1 text-sm font-black text-white">ZIP organizado</p>
                    </div>
                </div>
            </div>
        </div>

        @php
            $pasos = [
                1 => ['titulo' => 'Cargar imágenes', 'subtitulo' => 'Selecciona el lote'],
                2 => ['titulo' => 'Configurar', 'subtitulo' => 'Marco y ajustes'],
                3 => ['titulo' => 'Revisar', 'subtitulo' => 'Vista previa y descarga'],
            ];
        @endphp

        <div
            class="border-b border-slate-200 bg-slate-50/70 px-4 py-4 dark:border-neutral-800 dark:bg-neutral-900/60 sm:px-6">
            <div class="grid gap-3 md:grid-cols-3">
                @foreach ($pasos as $numero => $datosPaso)
                    <button type="button" wire:click="irAlPaso({{ $numero }})" @disabled($numero > $paso)
                        class="group relative overflow-hidden rounded-2xl border px-4 py-4 text-left transition-all duration-200
                            {{ $paso === $numero
                                ? 'border-blue-200 bg-white shadow-md shadow-blue-100/70 ring-1 ring-blue-100 dark:border-blue-900 dark:bg-neutral-950 dark:shadow-none dark:ring-blue-950'
                                : 'border-slate-200 bg-white/80 hover:border-slate-300 hover:shadow-sm dark:border-neutral-800 dark:bg-neutral-900' }}
                            {{ $numero > $paso ? 'cursor-not-allowed opacity-60' : '' }}">
                        <div class="flex items-center gap-3">
                            <span
                                class="flex h-11 w-11 shrink-0 items-center justify-center rounded-2xl text-sm font-black shadow-sm
                                {{ $paso > $numero
                                    ? 'bg-emerald-500 text-white'
                                    : ($paso === $numero
                                        ? 'bg-blue-600 text-white'
                                        : 'bg-slate-100 text-slate-500 dark:bg-neutral-800 dark:text-neutral-300') }}">
                                {{ $paso > $numero ? '✓' : $numero }}
                            </span>

                            <span class="min-w-0">
                                <span class="block truncate text-sm font-black text-slate-900 dark:text-white">
                                    {{ $datosPaso['titulo'] }}
                                </span>
                                <span class="block truncate text-xs text-slate-500 dark:text-slate-400">
                                    {{ $datosPaso['subtitulo'] }}
                                </span>
                            </span>
                        </div>

                        <span
                            class="absolute inset-x-0 bottom-0 h-1
                            {{ $paso === $numero ? 'bg-gradient-to-r from-sky-500 via-blue-600 to-indigo-600' : 'bg-transparent' }}">
                        </span>
                    </button>
                @endforeach
            </div>
        </div>

        {{-- PASO 1 --}}
        @if ($paso === 1)
            <div class="space-y-6 bg-slate-50/40 p-5 dark:bg-neutral-950/20 sm:p-6">
                <div @dragenter.prevent="dragActive=true" @dragover.prevent="dragActive=true"
                    @dragleave.prevent="dragActive=false" @drop.prevent="dragActive=false; submitDrop($event)"
                    :class="dragActive
                        ?
                        'border-blue-500 bg-gradient-to-br from-blue-50 to-indigo-50 shadow-lg shadow-blue-100/60 dark:from-blue-950/30 dark:to-indigo-950/20' :
                        'border-slate-300 bg-gradient-to-br from-slate-50 to-white dark:border-neutral-700 dark:from-neutral-950/50 dark:to-neutral-900/40'"
                    class="relative overflow-hidden rounded-[26px] border-2 border-dashed px-6 py-12 text-center transition-all duration-200">
                    <div class="pointer-events-none absolute inset-0 opacity-60">
                        <div
                            class="absolute left-8 top-8 h-20 w-20 rounded-full bg-blue-100 blur-2xl dark:bg-blue-500/10">
                        </div>
                        <div
                            class="absolute bottom-8 right-10 h-24 w-24 rounded-full bg-indigo-100 blur-2xl dark:bg-indigo-500/10">
                        </div>
                    </div>

                    <flux:input x-ref="imagesInput" id="images-upload" type="file" wire:model="images" multiple
                        accept="image/jpeg,image/png,image/webp" class="sr-only" />

                    <div
                        class="relative mx-auto flex h-20 w-20 items-center justify-center rounded-3xl bg-white shadow-[0_12px_30px_-12px_rgba(37,99,235,0.45)] ring-1 ring-blue-100 dark:bg-neutral-900 dark:ring-neutral-800">
                        <span class="text-4xl font-black text-blue-600">⇧</span>
                    </div>

                    <h3 class="relative mt-6 text-2xl font-black tracking-tight text-slate-900 dark:text-white">
                        Arrastra aquí tus imágenes
                    </h3>

                    <p class="relative mx-auto mt-3 max-w-2xl text-sm leading-6 text-slate-500 dark:text-slate-400">
                        Puedes mezclar archivos horizontales, verticales y cuadrados.
                        También puedes agregarlos manualmente desde tu equipo.
                    </p>

                    <div class="relative mt-6 flex flex-col items-center gap-3 sm:flex-row sm:justify-center">
                        <label for="images-upload"
                            class="inline-flex cursor-pointer items-center justify-center rounded-2xl bg-gradient-to-r from-blue-600 to-indigo-600 px-6 py-3 text-sm font-black text-white shadow-[0_12px_30px_-12px_rgba(37,99,235,0.7)] transition hover:scale-[1.02] hover:from-blue-700 hover:to-indigo-700">
                            Elegir imágenes
                        </label>

                        <span
                            class="rounded-2xl border border-slate-200 bg-white px-4 py-3 text-xs font-semibold text-slate-500 shadow-sm dark:border-neutral-800 dark:bg-neutral-900 dark:text-slate-400">
                            Máximo 20 MB por archivo
                        </span>
                    </div>

                    <p class="relative mt-4 text-xs text-slate-400 dark:text-slate-500">
                        Hasta 100 archivos por lote
                    </p>

                    <div x-show="isUploading" x-cloak class="relative mx-auto mt-8 w-full max-w-2xl">
                        <div class="mb-2 flex justify-between text-xs font-bold text-slate-600 dark:text-neutral-300">
                            <span>Subiendo imágenes...</span>
                            <span x-text="progress + '%'">0%</span>
                        </div>

                        <div class="h-3 overflow-hidden rounded-full bg-slate-200 dark:bg-neutral-800">
                            <div class="h-full rounded-full bg-gradient-to-r from-sky-500 via-blue-600 to-indigo-600 transition-all duration-300"
                                :style="`width:${progress}%`"></div>
                        </div>
                    </div>
                </div>

                @error('images')
                    <div
                        class="rounded-xl border border-red-200 bg-red-50 px-4 py-3 text-sm font-semibold text-red-700 dark:border-red-900/60 dark:bg-red-950/25 dark:text-red-300">
                        {{ $message }}</div>
                @enderror
                @error('images.*')
                    <div
                        class="rounded-xl border border-red-200 bg-red-50 px-4 py-3 text-sm font-semibold text-red-700 dark:border-red-900/60 dark:bg-red-950/25 dark:text-red-300">
                        {{ $message }}</div>
                @enderror

                @if (count($images))
                    <div
                        class="flex flex-col gap-3 rounded-2xl border border-slate-200 bg-white p-4 shadow-sm dark:border-neutral-800 dark:bg-neutral-950 sm:flex-row sm:items-center sm:justify-between">
                        <div>
                            <h3 class="font-black text-slate-900 dark:text-white">
                                {{ count($images) }} imagen(es) seleccionada(s)
                            </h3>
                            <p class="mt-1 text-xs text-slate-500 dark:text-slate-400">
                                Arrastra las tarjetas para definir el orden de los archivos dentro del ZIP.
                            </p>
                        </div>
                        <flux:button type="button" wire:click="limpiarLote"
                            class="rounded-xl border border-red-200 bg-white px-4 py-2 text-xs font-bold text-red-600 shadow-sm transition hover:bg-red-50 dark:border-red-900/60 dark:bg-neutral-900 dark:text-red-400 dark:hover:bg-red-950/30">
                            Limpiar lote
                        </flux:button>
                    </div>

                    <div x-data x-ref="imagesGrid" x-init="const initSortable = () => {
                        if (!window.Sortable || !$refs.imagesGrid || $refs.imagesGrid._sortable) return;
                        $refs.imagesGrid._sortable = new Sortable($refs.imagesGrid, {
                            animation: 180,
                            handle: '.image-handle',
                            ghostClass: 'opacity-40',
                            onEnd() { $wire.reorder([...$refs.imagesGrid.children].map(el => el.dataset.id)) }
                        })
                    };
                    initSortable()"
                        class="grid gap-4 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4">
                        @foreach ($images as $index => $img)
                            @php
                                $tempId = (string) $img->getFilename();
                                $key = sha1($tempId);
                                $meta = $imageSettings[$key] ?? [];
                                $detected = $meta['detected'] ?? 'desktop';
                            @endphp
                            <article wire:key="upload-{{ $key }}" data-id="{{ $tempId }}"
                                class="group overflow-hidden rounded-[22px] border border-slate-200 bg-white shadow-sm transition-all duration-200 hover:-translate-y-1 hover:shadow-xl hover:shadow-slate-200/70 dark:border-neutral-800 dark:bg-neutral-950 dark:hover:shadow-none">
                                <div class="relative aspect-[4/3] overflow-hidden bg-slate-100 dark:bg-neutral-900">
                                    <img src="{{ $img->temporaryUrl() }}" alt="{{ $img->getClientOriginalName() }}"
                                        class="h-full w-full object-cover transition duration-300 group-hover:scale-105">

                                    <div
                                        class="absolute inset-0 bg-gradient-to-t from-black/30 via-transparent to-black/10 opacity-0 transition group-hover:opacity-100">
                                    </div>

                                    <flux:button type="button"
                                        class="image-handle absolute left-3 top-3 cursor-grab rounded-xl border border-white/20 bg-black/50 px-2.5 py-1.5 text-xs font-black text-white backdrop-blur">
                                        ☰
                                    </flux:button>

                                    <flux:button type="button" wire:click="removeByTemp('{{ $tempId }}')"
                                        class="absolute right-3 top-3 flex h-9 w-9 items-center justify-center rounded-full border border-white/20 bg-black/50 text-lg font-bold text-white backdrop-blur transition hover:scale-105 hover:bg-red-600">
                                        ×
                                    </flux:button>

                                    <span
                                        class="absolute bottom-3 left-3 rounded-full px-3 py-1 text-[10px] font-black uppercase tracking-wide text-white shadow-lg {{ $detected === 'mobile' ? 'bg-violet-600' : ($detected === 'square' ? 'bg-amber-500' : 'bg-blue-600') }}">
                                        {{ $detected === 'mobile' ? 'Vertical' : ($detected === 'square' ? 'Cuadrada' : 'Horizontal') }}
                                    </span>
                                </div>

                                <div class="space-y-2 p-4">
                                    <p class="truncate text-sm font-black text-slate-800 dark:text-neutral-100"
                                        title="{{ $img->getClientOriginalName() }}">
                                        {{ $img->getClientOriginalName() }}
                                    </p>

                                    <div
                                        class="flex items-center justify-between text-[11px] text-slate-500 dark:text-slate-400">
                                        <span>{{ $meta['width'] ?? '—' }} × {{ $meta['height'] ?? '—' }} px</span>
                                        <span>{{ isset($meta['size']) ? number_format($meta['size'] / 1048576, 2) . ' MB' : '—' }}</span>
                                    </div>
                                </div>
                            </article>
                        @endforeach
                    </div>
                @else
                    <div class="grid gap-4 md:grid-cols-3">
                        <div
                            class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm transition hover:-translate-y-0.5 hover:shadow-md dark:border-neutral-800 dark:bg-neutral-950">
                            <div
                                class="mb-3 flex h-11 w-11 items-center justify-center rounded-2xl bg-blue-100 text-lg font-black text-blue-600 dark:bg-blue-500/15 dark:text-blue-300">
                                A</div>
                            <p class="text-sm font-black text-slate-900 dark:text-white">Detección automática</p>
                            <p class="mt-2 text-xs leading-5 text-slate-500 dark:text-slate-400">
                                Cada imagen recibe su orientación correcta automáticamente para agilizar el
                                procesamiento.
                            </p>
                        </div>

                        <div
                            class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm transition hover:-translate-y-0.5 hover:shadow-md dark:border-neutral-800 dark:bg-neutral-950">
                            <div
                                class="mb-3 flex h-11 w-11 items-center justify-center rounded-2xl bg-violet-100 text-lg font-black text-violet-600 dark:bg-violet-500/15 dark:text-violet-300">
                                M</div>
                            <p class="text-sm font-black text-slate-900 dark:text-white">Marcos adaptables</p>
                            <p class="mt-2 text-xs leading-5 text-slate-500 dark:text-slate-400">
                                Usa marcos desktop o mobile según la orientación detectada o definida manualmente.
                            </p>
                        </div>

                        <div
                            class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm transition hover:-translate-y-0.5 hover:shadow-md dark:border-neutral-800 dark:bg-neutral-950">
                            <div
                                class="mb-3 flex h-11 w-11 items-center justify-center rounded-2xl bg-emerald-100 text-lg font-black text-emerald-600 dark:bg-emerald-500/15 dark:text-emerald-300">
                                C</div>
                            <p class="text-sm font-black text-slate-900 dark:text-white">Control individual</p>
                            <p class="mt-2 text-xs leading-5 text-slate-500 dark:text-slate-400">
                                Ajusta enfoque, zoom, marco u orientación para cada archivo sin afectar el resto del
                                lote.
                            </p>
                        </div>
                    </div>
                @endif

                <div class="flex justify-end border-t border-slate-200 pt-5 dark:border-neutral-800">
                    <flux:button type="button" wire:click="siguientePaso" variant="primary"
                        :disabled="count($images) === 0"
                        class="rounded-2xl bg-gradient-to-r from-blue-600 to-indigo-600 px-5 py-3 text-sm font-black text-white shadow-[0_14px_30px_-14px_rgba(37,99,235,0.8)] transition hover:scale-[1.02] hover:from-blue-700 hover:to-indigo-700">
                        Continuar a configuración →
                    </flux:button>
                </div>
            </div>
        @endif

        {{-- PASO 2 --}}
        @if ($paso === 2)
            <div class="space-y-6 bg-slate-50/40 p-5 dark:bg-neutral-950/20 sm:p-6">
                <section class="grid gap-5 xl:grid-cols-2">
                    <div
                        class="space-y-4 rounded-[24px] border border-slate-200 bg-white p-5 shadow-sm dark:border-neutral-800 dark:bg-neutral-950">
                        <div>
                            <h3 class="font-black text-slate-900 dark:text-white">Marco y orientación</h3>
                            <p class="text-xs text-slate-500">La configuración general se aplica a todo el lote, salvo
                                excepciones individuales.</p>
                        </div>

                        <flux:select wire:model.live="marco" label="Marco general" placeholder="Procesar sin marco">
                            <flux:select.option value="">Sin marco</flux:select.option>
                            @foreach ($marcos as $item)
                                <flux:select.option value="{{ $item->id }}">
                                    {{ $item->nombre ?: $item->descripcion }}
                                    {{ $item->completo ? '· Desktop + Móvil' : '· Incompleto' }}
                                </flux:select.option>
                            @endforeach
                        </flux:select>
                        @error('marco')
                            <p class="text-sm text-red-600">{{ $message }}</p>
                        @enderror

                        @if ($marco && ($selectedMarco = $marcos->firstWhere('id', (int) $marco)))
                            <div class="grid grid-cols-2 gap-3">
                                @php $desktopFile = $selectedMarco->marco_desktop ?: $selectedMarco->marco; @endphp
                                <div
                                    class="rounded-xl border p-3 {{ $desktopFile ? 'border-blue-200 bg-blue-50/60 dark:border-blue-900 dark:bg-blue-950/20' : 'border-amber-200 bg-amber-50 dark:border-amber-900 dark:bg-amber-950/20' }}">
                                    <p
                                        class="text-[10px] font-black uppercase tracking-wide {{ $desktopFile ? 'text-blue-700 dark:text-blue-300' : 'text-amber-700 dark:text-amber-300' }}">
                                        Desktop</p>
                                    <p class="mt-1 text-xs font-semibold text-slate-700 dark:text-neutral-200">
                                        {{ $desktopFile ? 'Disponible' : 'No disponible' }}</p>
                                </div>
                                <div
                                    class="rounded-xl border p-3 {{ $selectedMarco->marco_mobile ? 'border-violet-200 bg-violet-50/60 dark:border-violet-900 dark:bg-violet-950/20' : 'border-amber-200 bg-amber-50 dark:border-amber-900 dark:bg-amber-950/20' }}">
                                    <p
                                        class="text-[10px] font-black uppercase tracking-wide {{ $selectedMarco->marco_mobile ? 'text-violet-700 dark:text-violet-300' : 'text-amber-700 dark:text-amber-300' }}">
                                        Móvil</p>
                                    <p class="mt-1 text-xs font-semibold text-slate-700 dark:text-neutral-200">
                                        {{ $selectedMarco->marco_mobile ? 'Disponible' : 'No disponible' }}</p>
                                </div>
                            </div>
                        @endif

                        <div class="grid gap-3 sm:grid-cols-2">
                            <flux:select wire:model.live="orientationMode" label="Orientación del lote">
                                <flux:select.option value="auto">Automática por imagen</flux:select.option>
                                <flux:select.option value="desktop">Forzar desktop</flux:select.option>
                                <flux:select.option value="mobile">Forzar móvil</flux:select.option>
                            </flux:select>
                            <flux:select wire:model.live="squareMode" label="Imágenes cuadradas">
                                <flux:select.option value="desktop">Tratarlas como desktop</flux:select.option>
                                <flux:select.option value="mobile">Tratarlas como móvil</flux:select.option>
                            </flux:select>
                        </div>

                        <flux:select wire:model.live="missingFrameBehavior"
                            label="Si falta una orientación del marco">
                            <flux:select.option value="skip">Procesar esa imagen sin marco</flux:select.option>
                            <flux:select.option value="alternate">Usar y adaptar la versión alterna
                            </flux:select.option>
                        </flux:select>
                    </div>

                    <div class="space-y-4 rounded-2xl border border-slate-200 p-4 dark:border-neutral-800">
                        <div>
                            <h3 class="font-black text-slate-900 dark:text-white">Salida del lote</h3>
                            <p class="text-xs text-slate-500">Controla proporción, formato, calidad y nombres.</p>
                        </div>

                        @if ($presetsSociales->isNotEmpty())
                            <flux:select wire:model.live="presetSocialId" label="Preset de red social">
                                <flux:select.option value="">Medidas personalizadas</flux:select.option>
                                @foreach ($presetsSociales as $preset)
                                    <flux:select.option value="{{ $preset->id }}">
                                        {{ $preset->red_social }} · {{ $preset->nombre }} ({{ $preset->ancho }} ×
                                        {{ $preset->alto }})
                                    </flux:select.option>
                                @endforeach
                            </flux:select>
                            <p class="-mt-2 text-[11px] text-slate-500">Seleccionar un preset ajusta automáticamente
                                las medidas y la orientación del lote.</p>
                            @error('presetSocialId')
                                <p class="text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        @endif

                        <div class="grid gap-3 sm:grid-cols-2">
                            <flux:select wire:model.live="fitMode" label="Ajuste de imagen">
                                <flux:select.option value="cover">Recortar para llenar</flux:select.option>
                                <flux:select.option value="contain">Mostrar completa con fondo blanco
                                </flux:select.option>
                                <flux:select.option value="blur">Mostrar completa con fondo desenfocado
                                </flux:select.option>
                            </flux:select>
                            <flux:select wire:model.live="format" label="Formato de salida">
                                <flux:select.option value="jpg">JPG</flux:select.option>
                                <flux:select.option value="png">PNG</flux:select.option>
                                <flux:select.option value="webp">WebP</flux:select.option>
                                <flux:select.option value="original">Conservar formato compatible</flux:select.option>
                            </flux:select>
                        </div>

                        <div>
                            <div
                                class="mb-2 flex items-center justify-between text-xs font-semibold text-slate-600 dark:text-neutral-300">
                                <span>Calidad de salida</span><span>{{ $quality }}%</span>
                            </div>
                            <flux:input type="range" wire:model.live="quality" min="60" max="100"
                                step="1" class="w-full accent-blue-600" />
                        </div>

                        <flux:input wire:model="renamePattern" label="Patrón de nombre" placeholder="{orig}_{index}"
                            description="Variables: {orig}, {index}, {date}, {orientation}." />
                        @error('renamePattern')
                            <p class="text-sm text-red-600">{{ $message }}</p>
                        @enderror

                        <div class="grid grid-cols-2 gap-3">
                            <flux:input wire:model="desktopWidth" type="number" label="Desktop ancho" />
                            <flux:input wire:model="desktopHeight" type="number" label="Desktop alto" />
                            <flux:input wire:model="mobileWidth" type="number" label="Móvil ancho" />
                            <flux:input wire:model="mobileHeight" type="number" label="Móvil alto" />
                        </div>

                        <label
                            class="flex cursor-pointer items-center gap-3 rounded-xl border border-slate-200 p-3 dark:border-neutral-800">
                            <flux:checkbox wire:model="organizeFolders"
                                class="h-5 w-5 rounded border-neutral-300 text-blue-600" />
                            <span><span class="block text-sm font-bold text-slate-800 dark:text-white">Organizar el ZIP
                                    por orientación</span><span class="block text-xs text-slate-500">Crea las carpetas
                                    desktop/ y mobile/.</span></span>
                        </label>
                    </div>
                </section>

                <section class="space-y-4">
                    <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
                        <div>
                            <h3 class="font-black text-slate-900 dark:text-white">Ajustes individuales</h3>
                            <p class="text-xs text-slate-500">Modifica únicamente las imágenes que necesiten una
                                excepción.</p>
                        </div>
                        <div class="flex flex-wrap gap-2">
                            <flux:button type="button" wire:click="aplicarAImagenes('orientation')"
                                class="rounded-lg border border-slate-200 px-3 py-2 text-xs font-bold hover:bg-slate-50 dark:border-neutral-700 dark:hover:bg-neutral-800">
                                Aplicar orientación a todas</flux:button>
                            <flux:button type="button" wire:click="aplicarAImagenes('fit')"
                                class="rounded-lg border border-slate-200 px-3 py-2 text-xs font-bold hover:bg-slate-50 dark:border-neutral-700 dark:hover:bg-neutral-800">
                                Aplicar ajuste a todas</flux:button>
                            <flux:button type="button" wire:click="aplicarAImagenes('frame')"
                                class="rounded-lg border border-slate-200 px-3 py-2 text-xs font-bold hover:bg-slate-50 dark:border-neutral-700 dark:hover:bg-neutral-800">
                                Aplicar marco a todas</flux:button>
                        </div>
                    </div>

                    <div class="space-y-3">
                        @foreach ($images as $index => $img)
                            @php
                                $key = sha1((string) $img->getFilename());
                                $meta = $imageSettings[$key] ?? [];
                                $detected = $meta['detected'] ?? 'desktop';
                            @endphp
                            <article wire:key="settings-{{ $key }}"
                                class="grid gap-4 rounded-[22px] border border-slate-200 bg-white p-4 shadow-sm transition hover:shadow-md dark:border-neutral-800 dark:bg-neutral-950 lg:grid-cols-[150px_1fr]">
                                <div
                                    class="relative aspect-[4/3] overflow-hidden rounded-xl bg-slate-100 dark:bg-neutral-900">
                                    <img src="{{ $img->temporaryUrl() }}" class="h-full w-full object-cover"
                                        alt="{{ $img->getClientOriginalName() }}">
                                    <span
                                        class="absolute bottom-2 left-2 rounded-full bg-black/65 px-2 py-1 text-[10px] font-bold uppercase text-white backdrop-blur">#{{ $index + 1 }}</span>
                                </div>
                                <div class="min-w-0 space-y-3">
                                    <div class="flex flex-col gap-1 sm:flex-row sm:items-center sm:justify-between">
                                        <div class="min-w-0">
                                            <p class="truncate text-sm font-black text-slate-900 dark:text-white">
                                                {{ $img->getClientOriginalName() }}</p>
                                            <p class="text-[11px] text-slate-500">{{ $meta['width'] ?? '—' }} ×
                                                {{ $meta['height'] ?? '—' }} px · Detectada como
                                                {{ $detected === 'square' ? 'cuadrada' : ($detected === 'mobile' ? 'vertical' : 'horizontal') }}
                                            </p>
                                        </div>
                                    </div>

                                    <div class="grid gap-3 sm:grid-cols-2 xl:grid-cols-5">
                                        <label
                                            class="text-xs font-semibold text-slate-600 dark:text-neutral-300">Orientación
                                            <flux:select
                                                wire:model.live="imageSettings.{{ $key }}.orientation"
                                                class="mt-1 block w-full rounded-lg border-neutral-300 bg-white text-xs dark:border-neutral-700 dark:bg-neutral-900">
                                                <flux:select.option value="inherit">Usar configuración general
                                                </flux:select.option>
                                                <flux:select.option value="desktop">Desktop</flux:select.option>
                                                <flux:select.option value="mobile">Móvil</flux:select.option>
                                            </flux:select>
                                        </label>
                                        <label class="text-xs font-semibold text-slate-600 dark:text-neutral-300">Marco
                                            <flux:select wire:model.live="imageSettings.{{ $key }}.frame"
                                                class="mt-1 block w-full rounded-lg border-neutral-300 bg-white text-xs dark:border-neutral-700 dark:bg-neutral-900">
                                                <flux:select.option value="global">Usar marco general
                                                </flux:select.option>
                                                <flux:select.option value="none">Sin marco</flux:select.option>
                                                @foreach ($marcos as $item)
                                                    <flux:select.option value="{{ $item->id }}">
                                                        {{ $item->nombre ?: $item->descripcion }}</flux:select.option>
                                                @endforeach
                                            </flux:select>
                                        </label>
                                        <label
                                            class="text-xs font-semibold text-slate-600 dark:text-neutral-300">Ajuste
                                            <flux:select wire:model.live="imageSettings.{{ $key }}.fit"
                                                class="mt-1 block w-full rounded-lg border-neutral-300 bg-white text-xs dark:border-neutral-700 dark:bg-neutral-900">
                                                <flux:select.option value="inherit">Usar ajuste general
                                                </flux:select.option>
                                                <flux:select.option value="cover">Recortar</flux:select.option>
                                                <flux:select.option value="contain">Completa</flux:select.option>
                                                <flux:select.option value="blur">Fondo desenfocado
                                                </flux:select.option>
                                            </flux:select>
                                        </label>
                                        <label
                                            class="text-xs font-semibold text-slate-600 dark:text-neutral-300">Enfoque
                                            <flux:select wire:model.live="imageSettings.{{ $key }}.focus"
                                                class="mt-1 block w-full rounded-lg border-neutral-300 bg-white text-xs dark:border-neutral-700 dark:bg-neutral-900">
                                                <flux:select.option value="center">Centro</flux:select.option>
                                                <flux:select.option value="top">Arriba</flux:select.option>
                                                <flux:select.option value="bottom">Abajo</flux:select.option>
                                                <flux:select.option value="left">Izquierda</flux:select.option>
                                                <flux:select.option value="right">Derecha</flux:select.option>
                                                <flux:select.option value="top-left">Superior izquierda
                                                </flux:select.option>
                                                <flux:select.option value="top-right">Superior derecha
                                                </flux:select.option>
                                                <flux:select.option value="bottom-left">Inferior izquierda
                                                </flux:select.option>
                                                <flux:select.option value="bottom-right">Inferior derecha
                                                </flux:select.option>
                                            </flux:select>
                                        </label>
                                        <label class="text-xs font-semibold text-slate-600 dark:text-neutral-300">Zoom
                                            <span class="float-right">{{ $meta['zoom'] ?? 100 }}%</span>
                                            <flux:input type="range"
                                                wire:model.live="imageSettings.{{ $key }}.zoom"
                                                min="100" max="180" step="5"
                                                class="mt-3 w-full accent-blue-600" />
                                        </label>
                                    </div>
                                </div>
                            </article>
                        @endforeach
                    </div>
                </section>

                <div class="flex items-center justify-between border-t border-slate-200 pt-5 dark:border-neutral-800">
                    <flux:button type="button" wire:click="pasoAnterior" variant="ghost"
                        class="rounded-2xl border border-slate-200 bg-white px-4 py-3 text-sm font-bold text-slate-700 shadow-sm transition hover:bg-slate-50 dark:border-neutral-700 dark:bg-neutral-900 dark:text-neutral-200 dark:hover:bg-neutral-800">
                        ← Regresar
                    </flux:button>

                    <flux:button type="button" wire:click="siguientePaso" variant="primary"
                        class="rounded-2xl bg-gradient-to-r from-blue-600 to-indigo-600 px-5 py-3 text-sm font-black text-white shadow-[0_14px_30px_-14px_rgba(37,99,235,0.8)] transition hover:scale-[1.02] hover:from-blue-700 hover:to-indigo-700">
                        Revisar resultados →
                    </flux:button>
                </div>
            </div>
        @endif

        {{-- PASO 3 --}}
        @if ($paso === 3)
            @php
                $selectedFile =
                    collect($images)->first(fn($file) => sha1((string) $file->getFilename()) === $selectedPreviewKey) ?:
                    $images[0] ?? null;
                $selectedKey = $selectedFile ? sha1((string) $selectedFile->getFilename()) : null;
                $selectedMeta = $selectedKey ? $imageSettings[$selectedKey] ?? [] : [];
                $individualOrientation = $selectedMeta['orientation'] ?? 'inherit';
                if (in_array($individualOrientation, ['desktop', 'mobile'], true)) {
                    $previewOrientation = $individualOrientation;
                } elseif (in_array($orientationMode, ['desktop', 'mobile'], true)) {
                    $previewOrientation = $orientationMode;
                } elseif (($selectedMeta['detected'] ?? 'desktop') === 'square') {
                    $previewOrientation = $squareMode;
                } else {
                    $previewOrientation = ($selectedMeta['detected'] ?? 'desktop') === 'mobile' ? 'mobile' : 'desktop';
                }
                $previewFit = ($selectedMeta['fit'] ?? 'inherit') === 'inherit' ? $fitMode : $selectedMeta['fit'];
                $previewFrameSelection = $selectedMeta['frame'] ?? 'global';
                $previewFrameId =
                    $previewFrameSelection === 'global'
                        ? $marco
                        : ($previewFrameSelection === 'none'
                            ? null
                            : (int) $previewFrameSelection);
                $previewMarco = $previewFrameId ? $marcos->firstWhere('id', (int) $previewFrameId) : null;
                $previewFrameFile = $previewMarco?->archivoPara($previewOrientation);
                if (!$previewFrameFile && $previewMarco && $missingFrameBehavior === 'alternate') {
                    $previewFrameFile = $previewMarco->archivoAlterno($previewOrientation);
                }
                $previewFrameUrl = $previewFrameFile ? asset('storage/imagenesMarcos/' . $previewFrameFile) : null;
                $focusX = (int) ($selectedMeta['focus_x'] ?? 50);
                $focusY = (int) ($selectedMeta['focus_y'] ?? 50);
                $objectPosition = $focusX . '% ' . $focusY . '%';
                $previewAspect =
                    $previewOrientation === 'mobile'
                        ? $mobileWidth . '/' . $mobileHeight
                        : $desktopWidth . '/' . $desktopHeight;
            @endphp

            <div class="space-y-6 bg-slate-50/40 p-5 dark:bg-neutral-950/20 sm:p-6">
                <div class="grid gap-5 xl:grid-cols-[1fr_360px]">
                    <section class="space-y-4">
                        <div>
                            <h3 class="font-black text-slate-900 dark:text-white">Comparación antes / después</h3>
                            <p class="text-xs text-slate-500">La vista previa representa el encuadre, orientación y
                                marco seleccionados. El ZIP se genera con las dimensiones exactas.</p>
                        </div>

                        @if ($selectedFile)
                            <div x-data="{
                                px: {{ $focusX }},
                                py: {{ $focusY }},
                                dragging: false,
                                movePoint(event) {
                                    if (!this.dragging) return;
                                    const rect = this.$refs.focusArea.getBoundingClientRect();
                                    this.px = Math.max(0, Math.min(100, Math.round(((event.clientX - rect.left) / rect.width) * 100)));
                                    this.py = Math.max(0, Math.min(100, Math.round(((event.clientY - rect.top) / rect.height) * 100)));
                                },
                                commitPoint() {
                                    if (!this.dragging) return;
                                    this.dragging = false;
                                    $wire.set('imageSettings.{{ $selectedKey }}.focus_x', this.px);
                                    $wire.set('imageSettings.{{ $selectedKey }}.focus_y', this.py);
                                }
                            }" x-ref="focusArea"
                                @pointerdown.prevent="dragging=true; movePoint($event); $el.setPointerCapture?.($event.pointerId)"
                                @pointermove.prevent="movePoint($event)" @pointerup.prevent="commitPoint()"
                                @pointercancel="commitPoint()"
                                class="relative mx-auto max-w-4xl cursor-crosshair touch-none overflow-hidden rounded-[26px] border border-slate-200 bg-neutral-950 shadow-[0_24px_60px_-22px_rgba(15,23,42,0.6)] dark:border-neutral-800"
                                style="aspect-ratio: {{ $previewAspect }};">
                                <div class="absolute inset-0">
                                    <img src="{{ $selectedFile->temporaryUrl() }}"
                                        class="h-full w-full object-cover opacity-65" style="object-position: center"
                                        alt="Original">
                                    <span
                                        class="absolute left-3 top-3 rounded-full bg-black/65 px-3 py-1 text-[10px] font-black uppercase text-white backdrop-blur">Original</span>
                                </div>

                                <div class="absolute inset-y-0 left-0 overflow-hidden border-r-2 border-white shadow-xl"
                                    :style="`width:${compare}%`">
                                    <div class="absolute inset-y-0 left-0" :style="`width:${10000/compare}%`">
                                        @if ($previewFit === 'blur')
                                            <img src="{{ $selectedFile->temporaryUrl() }}"
                                                class="absolute inset-0 h-full w-full scale-110 object-cover blur-xl"
                                                :style="`object-position:${px}% ${py}%`" alt="Fondo">
                                            <img src="{{ $selectedFile->temporaryUrl() }}"
                                                class="absolute inset-0 h-full w-full object-contain p-[3%]"
                                                alt="Resultado">
                                        @else
                                            <img src="{{ $selectedFile->temporaryUrl() }}"
                                                class="absolute inset-0 h-full w-full {{ $previewFit === 'contain' ? 'object-contain bg-white' : 'object-cover' }}"
                                                :style="`object-position:${px}% ${py}%; transform:scale({{ max(100, (int) ($selectedMeta['zoom'] ?? 100)) / 100 }})`"
                                                alt="Resultado">
                                        @endif
                                        @if ($previewFrameUrl)
                                            <img src="{{ $previewFrameUrl }}"
                                                class="pointer-events-none absolute inset-0 h-full w-full object-fill"
                                                alt="Marco">
                                        @endif
                                        <span
                                            class="absolute left-3 top-3 rounded-full bg-blue-600 px-3 py-1 text-[10px] font-black uppercase text-white shadow">Resultado</span>
                                    </div>
                                </div>

                                <div class="pointer-events-none absolute inset-y-0 flex -translate-x-1/2 items-center"
                                    :style="`left:${compare}%`">
                                    <div
                                        class="flex h-10 w-10 items-center justify-center rounded-full border-2 border-white bg-blue-600 font-black text-white shadow-xl">
                                        ↔</div>
                                </div>
                                <div
                                    class="pointer-events-none absolute bottom-3 right-3 rounded-full bg-black/65 px-3 py-1 text-[10px] font-bold text-white backdrop-blur">
                                    Arrastra para mover el punto focal
                                </div>
                            </div>
                            <div
                                class="mx-auto mt-3 flex max-w-4xl items-center gap-3 rounded-2xl border border-slate-200 bg-white px-4 py-3 shadow-sm dark:border-neutral-800 dark:bg-neutral-950">
                                <span class="shrink-0 text-xs font-bold text-slate-500">Original</span>
                                <flux:input type="range" min="8" max="92" x-model="compare"
                                    class="w-full accent-blue-600" aria-label="Comparar antes y después" />
                                <span class="shrink-0 text-xs font-bold text-blue-600">Resultado</span>
                            </div>
                        @endif

                        <div class="flex gap-2 overflow-x-auto pb-2">
                            @foreach ($images as $index => $img)
                                @php $key = sha1((string)$img->getFilename()); @endphp
                                <flux:button type="button" wire:click="seleccionarPreview('{{ $key }}')"
                                    class="relative h-20 w-28 shrink-0 overflow-hidden rounded-xl border-2 transition {{ $selectedPreviewKey === $key ? 'border-blue-600 ring-2 ring-blue-100 dark:ring-blue-900' : 'border-transparent opacity-70 hover:opacity-100' }}">
                                    <img src="{{ $img->temporaryUrl() }}" class="h-full w-full object-cover"
                                        alt="Vista {{ $index + 1 }}">
                                    <span
                                        class="absolute bottom-1 left-1 rounded bg-black/65 px-1.5 py-0.5 text-[9px] font-bold text-white">{{ $index + 1 }}</span>
                                </flux:button>
                            @endforeach
                        </div>
                    </section>

                    <aside class="space-y-4">
                        <div
                            class="rounded-[24px] border border-slate-200 bg-white p-5 shadow-sm dark:border-neutral-800 dark:bg-neutral-950">
                            <h3 class="font-black text-slate-900 dark:text-white">Resumen del lote</h3>
                            @php
                                $desktopCount = 0;
                                $mobileCount = 0;
                                $squareCount = 0;
                                foreach ($imageSettings as $s) {
                                    if (($s['detected'] ?? '') === 'mobile') {
                                        $mobileCount++;
                                    } elseif (($s['detected'] ?? '') === 'square') {
                                        $squareCount++;
                                    } else {
                                        $desktopCount++;
                                    }
                                }
                            @endphp
                            <dl class="mt-4 space-y-3 text-sm">
                                <div class="flex justify-between">
                                    <dt class="text-slate-500">Imágenes</dt>
                                    <dd class="font-black text-slate-900 dark:text-white">{{ count($images) }}</dd>
                                </div>
                                <div class="flex justify-between">
                                    <dt class="text-slate-500">Horizontales detectadas</dt>
                                    <dd class="font-bold">{{ $desktopCount }}</dd>
                                </div>
                                <div class="flex justify-between">
                                    <dt class="text-slate-500">Verticales detectadas</dt>
                                    <dd class="font-bold">{{ $mobileCount }}</dd>
                                </div>
                                <div class="flex justify-between">
                                    <dt class="text-slate-500">Cuadradas</dt>
                                    <dd class="font-bold">{{ $squareCount }}</dd>
                                </div>
                                <div
                                    class="border-t border-slate-200 pt-3 dark:border-neutral-800 flex justify-between">
                                    <dt class="text-slate-500">Marco general</dt>
                                    <dd class="max-w-40 truncate text-right font-bold">
                                        {{ $marco ? $marcos->firstWhere('id', (int) $marco)?->nombre ?? 'Seleccionado' : 'Sin marco' }}
                                    </dd>
                                </div>
                                <div class="flex justify-between">
                                    <dt class="text-slate-500">Formato</dt>
                                    <dd class="font-bold uppercase">{{ $format }}</dd>
                                </div>
                                <div class="flex justify-between">
                                    <dt class="text-slate-500">Calidad</dt>
                                    <dd class="font-bold">{{ $quality }}%</dd>
                                </div>
                                <div class="flex justify-between">
                                    <dt class="text-slate-500">Carpetas</dt>
                                    <dd class="font-bold">
                                        {{ $organizeFolders ? 'desktop / mobile' : 'Una sola carpeta' }}</dd>
                                </div>
                            </dl>
                        </div>

                        <div
                            class="rounded-2xl border border-blue-200 bg-blue-50 p-4 dark:border-blue-900 dark:bg-blue-950/25">
                            <p class="text-sm font-black text-blue-900 dark:text-blue-100">El ZIP incluirá
                                manifest.json</p>
                            <p class="mt-1 text-xs text-blue-700 dark:text-blue-300">Contiene orientación, dimensiones,
                                marco aplicado, formato, avisos y errores de cada archivo.</p>
                        </div>

                        <div
                            class="rounded-2xl border border-amber-200 bg-amber-50 p-4 dark:border-amber-900 dark:bg-amber-950/25">
                            <p class="text-xs font-semibold text-amber-800 dark:text-amber-200">El procesamiento de
                                imágenes grandes puede tardar algunos segundos. No cierres la página mientras se prepara
                                el archivo.</p>
                        </div>
                    </aside>
                </div>

                @error('images')
                    <div
                        class="rounded-xl border border-red-200 bg-red-50 px-4 py-3 text-sm font-semibold text-red-700 dark:border-red-900/60 dark:bg-red-950/25 dark:text-red-300">
                        {{ $message }}</div>
                @enderror

                <div
                    class="flex flex-col gap-3 border-t border-slate-200 pt-5 sm:flex-row sm:items-center sm:justify-between dark:border-neutral-800">
                    <flux:button type="button" wire:click="pasoAnterior" variant="ghost"
                        class="rounded-2xl border border-slate-200 bg-white px-4 py-3 text-sm font-bold text-slate-700 shadow-sm transition hover:bg-slate-50 dark:border-neutral-700 dark:bg-neutral-900 dark:text-neutral-200 dark:hover:bg-neutral-800">
                        ← Volver a configurar
                    </flux:button>

                    <div class="flex items-center gap-3">
                        <span wire:loading wire:target="submit" class="text-xs font-semibold text-blue-600">
                            Procesando lote...
                        </span>

                        <flux:button type="button" wire:click="submit" wire:loading.attr="disabled"
                            wire:target="submit" variant="primary"
                            class="rounded-2xl bg-gradient-to-r from-blue-600 to-indigo-600 px-5 py-3 text-sm font-black text-white shadow-[0_14px_30px_-14px_rgba(37,99,235,0.8)] transition hover:scale-[1.02] hover:from-blue-700 hover:to-indigo-700">
                            <span wire:loading.remove wire:target="submit">Descargar ZIP procesado</span>
                            <span wire:loading wire:target="submit">Preparando ZIP...</span>
                        </flux:button>
                    </div>
                </div>
            </div>
        @endif
    </section>
</div>

@push('scripts')
    <script src="https://cdn.jsdelivr.net/npm/sortablejs@1.15.2/Sortable.min.js" defer></script>
@endpush
