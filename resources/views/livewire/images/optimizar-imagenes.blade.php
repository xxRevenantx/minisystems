@php
    $formatBytes = function (int|float $bytes): string {
        if ($bytes <= 0) {
            return '0 KB';
        }

        $units = ['B', 'KB', 'MB', 'GB'];
        $power = min((int) floor(log($bytes, 1024)), count($units) - 1);
        $value = $bytes / (1024 ** $power);

        return number_format($value, $power === 0 ? 0 : 2) . ' ' . $units[$power];
    };
@endphp

<div x-data="{
    isUploading: false,
    uploadProgress: 0,
    dragActive: false,
    submitDrop(event) {
        const files = event.dataTransfer.files;
        if (!files?.length) return;
        this.$refs.optimizerInput.files = files;
        this.$refs.optimizerInput.dispatchEvent(new Event('change', { bubbles: true }));
    }
}" x-on:livewire-upload-start.window="isUploading=true; uploadProgress=0"
    x-on:livewire-upload-finish.window="isUploading=false; uploadProgress=100"
    x-on:livewire-upload-error.window="isUploading=false"
    x-on:livewire-upload-progress.window="uploadProgress=$event.detail.progress" class="space-y-6">

    <section
        class="relative overflow-hidden rounded-[30px] border border-emerald-200/70 bg-gradient-to-br from-emerald-600 via-teal-600 to-cyan-700 px-6 py-8 text-white shadow-[0_24px_70px_-24px_rgba(5,150,105,.55)] dark:border-emerald-900/60 sm:px-9 sm:py-10">
        <div class="pointer-events-none absolute inset-0 opacity-30">
            <div class="absolute -left-16 -top-16 h-52 w-52 rounded-full bg-white/30 blur-3xl"></div>
            <div class="absolute right-0 top-0 h-72 w-72 rounded-full bg-cyan-200/25 blur-3xl"></div>
            <div class="absolute bottom-0 left-1/3 h-48 w-48 rounded-full bg-teal-200/25 blur-3xl"></div>
        </div>

        <div class="relative flex flex-col gap-7 lg:flex-row lg:items-center lg:justify-between">
            <div class="max-w-3xl">
                <span
                    class="inline-flex items-center gap-2 rounded-full border border-white/20 bg-white/10 px-4 py-1.5 text-[11px] font-black uppercase tracking-[.18em] backdrop-blur">
                    <span class="h-2 w-2 rounded-full bg-lime-300"></span>
                    Optimización inteligente
                </span>
                <h2 class="mt-4 text-3xl font-black tracking-tight sm:text-4xl">Reduce peso sin perder calidad visible</h2>
                <p class="mt-3 max-w-2xl text-sm leading-6 text-emerald-50/95 sm:text-[15px]">
                    Comprime lotes, limita dimensiones, convierte formatos y define un peso objetivo. Los archivos se
                    guardan de forma privada durante 24 horas.
                </p>
            </div>

            <div class="grid grid-cols-2 gap-3 sm:grid-cols-4 lg:grid-cols-2">
                <div class="rounded-2xl border border-white/15 bg-white/10 px-4 py-3 backdrop-blur">
                    <p class="text-[10px] font-black uppercase tracking-[.14em] text-emerald-100">Motor</p>
                    <p class="mt-1 text-sm font-black">{{ $driverName ?: 'No disponible' }}</p>
                </div>
                <div class="rounded-2xl border border-white/15 bg-white/10 px-4 py-3 backdrop-blur">
                    <p class="text-[10px] font-black uppercase tracking-[.14em] text-emerald-100">Lote</p>
                    <p class="mt-1 text-sm font-black">Hasta 50 imágenes</p>
                </div>
                <div class="rounded-2xl border border-white/15 bg-white/10 px-4 py-3 backdrop-blur">
                    <p class="text-[10px] font-black uppercase tracking-[.14em] text-emerald-100">Formatos</p>
                    <p class="mt-1 text-sm font-black">JPG · PNG · WebP · AVIF</p>
                </div>
                <div class="rounded-2xl border border-white/15 bg-white/10 px-4 py-3 backdrop-blur">
                    <p class="text-[10px] font-black uppercase tracking-[.14em] text-emerald-100">Descarga</p>
                    <p class="mt-1 text-sm font-black">Individual o ZIP</p>
                </div>
            </div>
        </div>
    </section>

    @if ($systemError)
        <div
            class="rounded-2xl border border-red-200 bg-red-50 px-5 py-4 text-sm font-semibold text-red-700 dark:border-red-900/60 dark:bg-red-950/30 dark:text-red-300">
            <p class="font-black">El optimizador no puede iniciar.</p>
            <p class="mt-1">{{ $systemError }}</p>
        </div>
    @endif

    @if ($results === [])
        <div class="grid gap-6 xl:grid-cols-[1.05fr_.95fr]">
            <section
                class="overflow-hidden rounded-[26px] border border-slate-200 bg-white shadow-sm dark:border-neutral-800 dark:bg-neutral-900">
                <div class="border-b border-slate-200 px-5 py-4 dark:border-neutral-800 sm:px-6">
                    <div class="flex items-center justify-between gap-4">
                        <div>
                            <p class="text-[11px] font-black uppercase tracking-[.16em] text-emerald-600 dark:text-emerald-400">
                                Paso 1</p>
                            <h3 class="mt-1 text-xl font-black text-slate-950 dark:text-white">Selecciona las imágenes</h3>
                        </div>
                        @if (count($images))
                            <flux:button type="button" wire:click="clearImages" variant="ghost" size="sm">
                                Limpiar
                            </flux:button>
                        @endif
                    </div>
                </div>

                <div class="p-5 sm:p-6">
                    <div @dragenter.prevent="dragActive=true" @dragover.prevent="dragActive=true"
                        @dragleave.prevent="dragActive=false" @drop.prevent="dragActive=false; submitDrop($event)"
                        :class="dragActive
                            ? 'border-emerald-500 bg-emerald-50 shadow-lg shadow-emerald-100/60 dark:bg-emerald-950/20'
                            : 'border-slate-300 bg-slate-50/80 dark:border-neutral-700 dark:bg-neutral-950/50'"
                        class="relative overflow-hidden rounded-[24px] border-2 border-dashed px-5 py-10 text-center transition-all">
                        <flux:input x-ref="optimizerInput" id="optimizer-images" type="file" wire:model="images"
                            multiple accept="image/jpeg,image/png,image/webp" class="sr-only" />

                        <div
                            class="mx-auto flex h-20 w-20 items-center justify-center rounded-3xl bg-white text-4xl font-black text-emerald-600 shadow-[0_14px_35px_-14px_rgba(5,150,105,.6)] ring-1 ring-emerald-100 dark:bg-neutral-900 dark:ring-neutral-800">
                            ⇩
                        </div>
                        <h4 class="mt-5 text-xl font-black text-slate-950 dark:text-white">Arrastra tus imágenes aquí</h4>
                        <p class="mx-auto mt-2 max-w-xl text-sm leading-6 text-slate-500 dark:text-slate-400">
                            Acepta JPG, PNG y WebP. Máximo 20 MB por archivo y 50 imágenes por lote.
                        </p>
                        <label for="optimizer-images"
                            class="mt-6 inline-flex cursor-pointer items-center justify-center rounded-2xl bg-gradient-to-r from-emerald-600 to-teal-600 px-6 py-3 text-sm font-black text-white shadow-lg shadow-emerald-500/20 transition hover:scale-[1.02] hover:from-emerald-700 hover:to-teal-700">
                            Elegir imágenes
                        </label>

                        <div x-show="isUploading" x-cloak class="mx-auto mt-7 max-w-xl">
                            <div class="mb-2 flex justify-between text-xs font-bold text-slate-600 dark:text-slate-300">
                                <span>Subiendo lote...</span>
                                <span x-text="uploadProgress + '%'">0%</span>
                            </div>
                            <div class="h-3 overflow-hidden rounded-full bg-slate-200 dark:bg-neutral-800">
                                <div class="h-full rounded-full bg-gradient-to-r from-emerald-500 to-cyan-500 transition-all"
                                    :style="`width:${uploadProgress}%`"></div>
                            </div>
                        </div>
                    </div>

                    @error('images')
                        <p
                            class="mt-4 rounded-xl border border-red-200 bg-red-50 px-4 py-3 text-sm font-semibold text-red-700 dark:border-red-900/60 dark:bg-red-950/25 dark:text-red-300">
                            {{ $message }}
                        </p>
                    @enderror
                    @error('images.*')
                        <p
                            class="mt-4 rounded-xl border border-red-200 bg-red-50 px-4 py-3 text-sm font-semibold text-red-700 dark:border-red-900/60 dark:bg-red-950/25 dark:text-red-300">
                            {{ $message }}
                        </p>
                    @enderror

                    @if (count($images))
                        <div class="mt-6">
                            <div class="mb-3 flex items-center justify-between">
                                <p class="text-sm font-black text-slate-900 dark:text-white">
                                    {{ count($images) }} archivo(s) listo(s)
                                </p>
                                <p class="text-xs font-semibold text-slate-500">Se procesarán en el orden mostrado</p>
                            </div>
                            <div class="grid max-h-[480px] gap-3 overflow-y-auto pr-1 sm:grid-cols-2">
                                @foreach ($images as $image)
                                    @php
                                        $temporaryName = (string) $image->getFilename();
                                        try {
                                            $temporaryUrl = $image->temporaryUrl();
                                        } catch (\Throwable) {
                                            $temporaryUrl = null;
                                        }
                                    @endphp
                                    <article wire:key="optimizer-upload-{{ sha1($temporaryName) }}"
                                        class="group flex min-w-0 items-center gap-3 rounded-2xl border border-slate-200 bg-slate-50 p-3 dark:border-neutral-800 dark:bg-neutral-950/60">
                                        <div
                                            class="h-16 w-16 shrink-0 overflow-hidden rounded-xl bg-slate-200 dark:bg-neutral-800">
                                            @if ($temporaryUrl)
                                                <img src="{{ $temporaryUrl }}" alt="" class="h-full w-full object-cover">
                                            @endif
                                        </div>
                                        <div class="min-w-0 flex-1">
                                            <p class="truncate text-sm font-black text-slate-900 dark:text-white"
                                                title="{{ $image->getClientOriginalName() }}">
                                                {{ $image->getClientOriginalName() }}
                                            </p>
                                            <p class="mt-1 text-xs font-semibold text-slate-500">
                                                {{ $formatBytes((int) $image->getSize()) }}
                                            </p>
                                        </div>
                                        <button type="button" wire:click="removeImage('{{ $temporaryName }}')"
                                            class="flex h-9 w-9 shrink-0 items-center justify-center rounded-xl text-lg font-black text-slate-400 transition hover:bg-red-50 hover:text-red-600 dark:hover:bg-red-950/30">
                                            ×
                                        </button>
                                    </article>
                                @endforeach
                            </div>
                        </div>
                    @endif
                </div>
            </section>

            <section
                class="overflow-hidden rounded-[26px] border border-slate-200 bg-white shadow-sm dark:border-neutral-800 dark:bg-neutral-900">
                <div class="border-b border-slate-200 px-5 py-4 dark:border-neutral-800 sm:px-6">
                    <p class="text-[11px] font-black uppercase tracking-[.16em] text-emerald-600 dark:text-emerald-400">
                        Paso 2</p>
                    <h3 class="mt-1 text-xl font-black text-slate-950 dark:text-white">Configura la reducción</h3>
                </div>

                <div class="space-y-6 p-5 sm:p-6">
                    <div>
                        <flux:select wire:model.live="profile" label="Perfil de optimización">
                            @foreach ($profiles as $key => $profileData)
                                <flux:select.option value="{{ $key }}">{{ $profileData['label'] }}</flux:select.option>
                            @endforeach
                        </flux:select>
                        <p
                            class="mt-2 rounded-xl border border-emerald-100 bg-emerald-50 px-3 py-2 text-xs font-semibold leading-5 text-emerald-800 dark:border-emerald-900/50 dark:bg-emerald-950/20 dark:text-emerald-300">
                            {{ $selectedProfile['description'] ?? 'Configuración manual.' }}
                        </p>
                    </div>

                    <div class="grid gap-4 sm:grid-cols-2">
                        <flux:select wire:model.live="format" label="Formato de salida">
                            <flux:select.option value="original">Conservar original</flux:select.option>
                            <flux:select.option value="jpg">JPG</flux:select.option>
                            <flux:select.option value="png">PNG</flux:select.option>
                            <flux:select.option value="webp">WebP</flux:select.option>
                            <flux:select.option value="avif">AVIF</flux:select.option>
                        </flux:select>
                        <flux:input wire:model="targetKb" type="number" min="50" max="20000" step="10"
                            label="Peso objetivo por imagen (KB)" placeholder="Opcional" />
                    </div>
                    @error('format') <p class="text-sm text-red-600">{{ $message }}</p> @enderror
                    @error('targetKb') <p class="text-sm text-red-600">{{ $message }}</p> @enderror

                    <div class="rounded-2xl border border-slate-200 bg-slate-50 p-4 dark:border-neutral-800 dark:bg-neutral-950/50">
                        <div class="flex items-center justify-between gap-4">
                            <div>
                                <p class="text-sm font-black text-slate-900 dark:text-white">Calidad de salida</p>
                                <p class="mt-1 text-xs text-slate-500">PNG utiliza compresión sin pérdida.</p>
                            </div>
                            <span
                                class="rounded-xl bg-white px-3 py-2 text-sm font-black text-emerald-700 shadow-sm ring-1 ring-slate-200 dark:bg-neutral-900 dark:text-emerald-400 dark:ring-neutral-800">
                                {{ $quality }}%
                            </span>
                        </div>
                        <input type="range" wire:model.live="quality" min="35" max="100" step="1"
                            class="mt-4 h-2 w-full cursor-pointer appearance-none rounded-full bg-slate-200 accent-emerald-600 dark:bg-neutral-700">
                        @error('quality') <p class="mt-2 text-sm text-red-600">{{ $message }}</p> @enderror
                    </div>

                    <div>
                        <p class="mb-3 text-sm font-black text-slate-900 dark:text-white">Dimensiones máximas</p>
                        <div class="grid gap-4 sm:grid-cols-2">
                            <flux:input wire:model="maxWidth" type="number" min="320" max="6000" label="Ancho máximo"
                                suffix="px" />
                            <flux:input wire:model="maxHeight" type="number" min="320" max="6000" label="Alto máximo"
                                suffix="px" />
                        </div>
                        <p class="mt-2 text-xs leading-5 text-slate-500">
                            Se mantiene la proporción original. La imagen no se recorta.
                        </p>
                        @error('maxWidth') <p class="mt-2 text-sm text-red-600">{{ $message }}</p> @enderror
                        @error('maxHeight') <p class="mt-2 text-sm text-red-600">{{ $message }}</p> @enderror
                    </div>

                    <div class="grid gap-3">
                        <label
                            class="flex cursor-pointer items-start gap-3 rounded-2xl border border-slate-200 p-4 transition hover:border-emerald-300 hover:bg-emerald-50/50 dark:border-neutral-800 dark:hover:border-emerald-800 dark:hover:bg-emerald-950/10">
                            <input type="checkbox" wire:model="preserveTransparency"
                                class="mt-1 h-5 w-5 rounded border-slate-300 text-emerald-600 focus:ring-emerald-500">
                            <span>
                                <span class="block text-sm font-black text-slate-900 dark:text-white">Conservar transparencia</span>
                                <span class="mt-1 block text-xs leading-5 text-slate-500">Disponible para PNG, WebP y AVIF. JPG siempre usa fondo blanco.</span>
                            </span>
                        </label>
                        <label
                            class="flex cursor-pointer items-start gap-3 rounded-2xl border border-slate-200 p-4 transition hover:border-emerald-300 hover:bg-emerald-50/50 dark:border-neutral-800 dark:hover:border-emerald-800 dark:hover:bg-emerald-950/10">
                            <input type="checkbox" wire:model="allowUpscale"
                                class="mt-1 h-5 w-5 rounded border-slate-300 text-emerald-600 focus:ring-emerald-500">
                            <span>
                                <span class="block text-sm font-black text-slate-900 dark:text-white">Permitir ampliar imágenes pequeñas</span>
                                <span class="mt-1 block text-xs leading-5 text-slate-500">Déjalo desactivado para evitar pérdida de nitidez.</span>
                            </span>
                        </label>
                    </div>

                    <div>
                        <flux:input wire:model="renamePattern" label="Patrón de nombre"
                            description="Variables: {name}, {index}, {date}, {format}" />
                        @error('renamePattern') <p class="mt-2 text-sm text-red-600">{{ $message }}</p> @enderror
                    </div>

                    <flux:button type="button" wire:click="optimizar" wire:loading.attr="disabled"
                        wire:target="optimizar" :disabled="$systemError || count($images) === 0" variant="primary"
                        class="w-full justify-center rounded-2xl bg-gradient-to-r from-emerald-600 to-teal-600 px-6 py-3.5 font-black text-white shadow-lg shadow-emerald-500/20 hover:from-emerald-700 hover:to-teal-700 disabled:cursor-not-allowed disabled:opacity-50">
                        <span wire:loading.remove wire:target="optimizar">Optimizar {{ count($images) ?: '' }} imagen(es)</span>
                        <span wire:loading wire:target="optimizar">Procesando el lote...</span>
                    </flux:button>
                    <p class="text-center text-[11px] leading-5 text-slate-400">
                        El sistema elimina metadatos innecesarios al volver a codificar y corrige la orientación EXIF.
                    </p>
                </div>
            </section>
        </div>
    @else
        @php
            $completedResults = collect($results)->where('status', 'completed');
            $failedResults = collect($results)->where('status', 'failed');
            $totalVariation = $this->totalReduction();
            $totalByteDifference = $this->totalDifferenceBytes();
        @endphp

        <section
            class="overflow-hidden rounded-[28px] border border-slate-200 bg-white shadow-sm dark:border-neutral-800 dark:bg-neutral-900">
            <div class="border-b border-slate-200 bg-slate-50/70 px-5 py-5 dark:border-neutral-800 dark:bg-neutral-950/50 sm:px-7">
                <div class="flex flex-col gap-4 lg:flex-row lg:items-center lg:justify-between">
                    <div>
                        <span
                            class="inline-flex rounded-full bg-emerald-100 px-3 py-1 text-[11px] font-black uppercase tracking-[.14em] text-emerald-700 dark:bg-emerald-950/50 dark:text-emerald-300">
                            Lote terminado
                        </span>
                        <h3 class="mt-2 text-2xl font-black tracking-tight text-slate-950 dark:text-white">
                            {{ $completedResults->count() }} imagen(es) optimizada(s)
                        </h3>
                        <p class="mt-1 text-sm text-slate-500">
                            Los archivos estarán disponibles temporalmente durante 24 horas.
                            @if ($failedResults->isNotEmpty())
                                <span class="font-bold text-amber-600 dark:text-amber-400">{{ $failedResults->count() }} archivo(s) no se pudieron procesar.</span>
                            @endif
                        </p>
                    </div>
                    <div class="flex flex-col gap-2 sm:flex-row">
                        @if ($batchUuid && $completedResults->isNotEmpty())
                            <a href="{{ route('images.optimizer.download-batch', $batchUuid) }}"
                                class="inline-flex items-center justify-center rounded-2xl bg-gradient-to-r from-emerald-600 to-teal-600 px-5 py-3 text-sm font-black text-white shadow-lg shadow-emerald-500/20 transition hover:from-emerald-700 hover:to-teal-700">
                                Descargar todas en ZIP
                            </a>
                        @endif
                        <flux:button type="button" wire:click="limpiarResultados" variant="ghost"
                            class="justify-center rounded-2xl border border-slate-200 px-5 py-3 font-bold dark:border-neutral-700">
                            Nuevo lote
                        </flux:button>
                    </div>
                </div>
            </div>

            <div class="grid gap-3 border-b border-slate-200 p-5 dark:border-neutral-800 sm:grid-cols-2 lg:grid-cols-4 sm:p-7">
                <div class="rounded-2xl border border-slate-200 bg-slate-50 p-4 dark:border-neutral-800 dark:bg-neutral-950/50">
                    <p class="text-[10px] font-black uppercase tracking-[.14em] text-slate-500">Peso original</p>
                    <p class="mt-2 text-xl font-black text-slate-950 dark:text-white">{{ $formatBytes($this->totalOriginalBytes()) }}</p>
                </div>
                <div class="rounded-2xl border border-slate-200 bg-slate-50 p-4 dark:border-neutral-800 dark:bg-neutral-950/50">
                    <p class="text-[10px] font-black uppercase tracking-[.14em] text-slate-500">Peso optimizado</p>
                    <p class="mt-2 text-xl font-black text-slate-950 dark:text-white">{{ $formatBytes($this->totalOptimizedBytes()) }}</p>
                </div>
                <div @class([
                    'rounded-2xl border p-4',
                    'border-emerald-200 bg-emerald-50 dark:border-emerald-900/60 dark:bg-emerald-950/20' => $totalByteDifference >= 0,
                    'border-amber-200 bg-amber-50 dark:border-amber-900/60 dark:bg-amber-950/20' => $totalByteDifference < 0,
                ])>
                    <p @class([
                        'text-[10px] font-black uppercase tracking-[.14em]',
                        'text-emerald-700 dark:text-emerald-400' => $totalByteDifference >= 0,
                        'text-amber-700 dark:text-amber-400' => $totalByteDifference < 0,
                    ])>{{ $totalByteDifference >= 0 ? 'Espacio ahorrado' : 'Peso adicional' }}</p>
                    <p @class([
                        'mt-2 text-xl font-black',
                        'text-emerald-700 dark:text-emerald-300' => $totalByteDifference >= 0,
                        'text-amber-700 dark:text-amber-300' => $totalByteDifference < 0,
                    ])>{{ $formatBytes(abs($totalByteDifference)) }}</p>
                </div>
                <div @class([
                    'rounded-2xl border p-4',
                    'border-emerald-200 bg-emerald-50 dark:border-emerald-900/60 dark:bg-emerald-950/20' => $totalVariation >= 0,
                    'border-amber-200 bg-amber-50 dark:border-amber-900/60 dark:bg-amber-950/20' => $totalVariation < 0,
                ])>
                    <p @class([
                        'text-[10px] font-black uppercase tracking-[.14em]',
                        'text-emerald-700 dark:text-emerald-400' => $totalVariation >= 0,
                        'text-amber-700 dark:text-amber-400' => $totalVariation < 0,
                    ])>{{ $totalVariation >= 0 ? 'Reducción total' : 'Aumento total' }}</p>
                    <p @class([
                        'mt-2 text-xl font-black',
                        'text-emerald-700 dark:text-emerald-300' => $totalVariation >= 0,
                        'text-amber-700 dark:text-amber-300' => $totalVariation < 0,
                    ])>{{ abs($totalVariation) }}%</p>
                </div>
            </div>

            <div class="space-y-5 p-5 sm:p-7">
                @foreach ($results as $index => $result)
                    @if ($result['status'] === 'completed')
                        <article wire:key="optimized-result-{{ $index }}"
                            class="overflow-hidden rounded-[24px] border border-slate-200 bg-white dark:border-neutral-800 dark:bg-neutral-950/30">
                            <div class="grid lg:grid-cols-[minmax(0,1.15fr)_minmax(340px,.85fr)]">
                                <div x-data="{ compare: 50 }"
                                    class="relative min-h-[280px] overflow-hidden bg-[linear-gradient(45deg,#e2e8f0_25%,transparent_25%),linear-gradient(-45deg,#e2e8f0_25%,transparent_25%),linear-gradient(45deg,transparent_75%,#e2e8f0_75%),linear-gradient(-45deg,transparent_75%,#e2e8f0_75%)] bg-[length:24px_24px] bg-[position:0_0,0_12px,12px_-12px,-12px_0] dark:bg-none dark:bg-neutral-900">
                                    <img
                                        src="{{ route('images.optimizer.preview', [$batchUuid, 'originals', $result['original_file']]) }}"
                                        alt="Original" class="absolute inset-0 h-full w-full object-contain">
                                    <div class="absolute inset-0 overflow-hidden" :style="`clip-path: inset(0 ${100-compare}% 0 0)`">
                                        <img
                                            src="{{ route('images.optimizer.preview', [$batchUuid, 'outputs', $result['optimized_file']]) }}"
                                            alt="Optimizada" class="absolute inset-0 h-full w-full object-contain">
                                    </div>
                                    <div class="pointer-events-none absolute inset-y-0 w-0.5 bg-white shadow-lg"
                                        :style="`left:${compare}%`"></div>
                                    <span
                                        class="absolute left-3 top-3 rounded-lg bg-slate-950/75 px-2.5 py-1 text-[10px] font-black uppercase tracking-wider text-white backdrop-blur">Original</span>
                                    <span
                                        class="absolute right-3 top-3 rounded-lg bg-emerald-600/90 px-2.5 py-1 text-[10px] font-black uppercase tracking-wider text-white backdrop-blur">Optimizada</span>
                                    <input type="range" x-model="compare" min="0" max="100"
                                        class="absolute inset-x-5 bottom-4 h-2 cursor-ew-resize accent-emerald-600">
                                </div>

                                <div class="flex flex-col p-5 sm:p-6">
                                    <div class="flex items-start justify-between gap-4">
                                        <div class="min-w-0">
                                            <h4 class="truncate text-lg font-black text-slate-950 dark:text-white"
                                                title="{{ $result['original_name'] }}">
                                                {{ $result['original_name'] }}
                                            </h4>
                                            <p class="mt-1 truncate text-xs font-semibold text-slate-500"
                                                title="{{ $result['optimized_file'] }}">
                                                {{ $result['optimized_file'] }}
                                            </p>
                                        </div>
                                        <span @class([
                                            'shrink-0 rounded-xl px-3 py-2 text-sm font-black',
                                            'bg-emerald-100 text-emerald-700 dark:bg-emerald-950/50 dark:text-emerald-300' => $result['reduction'] >= 0,
                                            'bg-amber-100 text-amber-700 dark:bg-amber-950/50 dark:text-amber-300' => $result['reduction'] < 0,
                                        ])>
                                            {{ $result['reduction'] > 0 ? '-' : ($result['reduction'] < 0 ? '+' : '') }}{{ abs($result['reduction']) }}%
                                        </span>
                                    </div>

                                    <dl class="mt-5 grid grid-cols-2 gap-3 text-sm">
                                        <div class="rounded-xl bg-slate-50 p-3 dark:bg-neutral-900">
                                            <dt class="text-[10px] font-black uppercase tracking-wider text-slate-500">Antes</dt>
                                            <dd class="mt-1 font-black text-slate-900 dark:text-white">{{ $formatBytes($result['original_size']) }}</dd>
                                        </div>
                                        <div class="rounded-xl bg-emerald-50 p-3 dark:bg-emerald-950/20">
                                            <dt class="text-[10px] font-black uppercase tracking-wider text-emerald-700 dark:text-emerald-400">Después</dt>
                                            <dd class="mt-1 font-black text-emerald-700 dark:text-emerald-300">{{ $formatBytes($result['optimized_size']) }}</dd>
                                        </div>
                                        <div class="rounded-xl bg-slate-50 p-3 dark:bg-neutral-900">
                                            <dt class="text-[10px] font-black uppercase tracking-wider text-slate-500">Dimensiones</dt>
                                            <dd class="mt-1 font-black text-slate-900 dark:text-white">{{ $result['width'] }} × {{ $result['height'] }}</dd>
                                        </div>
                                        <div class="rounded-xl bg-slate-50 p-3 dark:bg-neutral-900">
                                            <dt class="text-[10px] font-black uppercase tracking-wider text-slate-500">Salida</dt>
                                            <dd class="mt-1 font-black text-slate-900 dark:text-white">
                                                {{ $result['format'] }}
                                                @if ($result['quality']) · {{ $result['quality'] }}% @endif
                                            </dd>
                                        </div>
                                    </dl>

                                    @if (!empty($result['warnings']))
                                        <div class="mt-4 space-y-1 rounded-xl border border-amber-200 bg-amber-50 px-3 py-2.5 text-xs font-semibold text-amber-800 dark:border-amber-900/60 dark:bg-amber-950/20 dark:text-amber-300">
                                            @foreach ($result['warnings'] as $warning)
                                                <p>• {{ $warning }}</p>
                                            @endforeach
                                        </div>
                                    @endif

                                    <a href="{{ route('images.optimizer.download', [$batchUuid, $result['optimized_file']]) }}"
                                        class="mt-auto inline-flex items-center justify-center rounded-2xl bg-slate-950 px-5 py-3 text-sm font-black text-white transition hover:bg-emerald-700 dark:bg-white dark:text-neutral-950 dark:hover:bg-emerald-300">
                                        Descargar imagen
                                    </a>
                                </div>
                            </div>
                        </article>
                    @else
                        <article wire:key="optimized-error-{{ $index }}"
                            class="rounded-2xl border border-red-200 bg-red-50 p-5 dark:border-red-900/60 dark:bg-red-950/20">
                            <p class="font-black text-red-800 dark:text-red-300">{{ $result['original_name'] }}</p>
                            <p class="mt-1 text-sm text-red-700 dark:text-red-400">{{ $result['error'] }}</p>
                        </article>
                    @endif
                @endforeach
            </div>
        </section>
    @endif
</div>
