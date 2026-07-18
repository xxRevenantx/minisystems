@php
    $uploaderConfig = [
        'csrf' => csrf_token(),
        'maxFiles' => $maxFiles,
        'maxFileMb' => $maxFileMb,
        'maxFileBytes' => (int) config('image_optimizer.max_file_kb', 20 * 1024) * 1024,
        'uploadConcurrency' => $uploadConcurrency,
        'urls' => [
            'active' => route('images.optimizer.api.active'),
            'store' => route('images.optimizer.api.store'),
            'show' => route('images.optimizer.api.show', ['batch' => '__BATCH__']),
            'upload' => route('images.optimizer.api.upload', [
                'batch' => '__BATCH__',
                'item' => '__ITEM__',
            ]),
            'uploadFailed' => route('images.optimizer.api.upload-failed', [
                'batch' => '__BATCH__',
                'item' => '__ITEM__',
            ]),
            'retry' => route('images.optimizer.api.retry', [
                'batch' => '__BATCH__',
                'item' => '__ITEM__',
            ]),
            'destroy' => route('images.optimizer.api.destroy', ['batch' => '__BATCH__']),
        ],
    ];
@endphp

<div
    x-data="imageOptimizerUploader(@js($uploaderConfig))"
    class="space-y-6"
>
    <section
        class="relative overflow-hidden rounded-[30px] border border-emerald-200/70 bg-gradient-to-br from-emerald-600 via-teal-600 to-cyan-700 px-6 py-8 text-white shadow-[0_24px_70px_-24px_rgba(5,150,105,.55)] dark:border-emerald-900/60 sm:px-9 sm:py-10"
    >
        <div class="pointer-events-none absolute inset-0 opacity-30">
            <div class="absolute -left-16 -top-16 h-52 w-52 rounded-full bg-white/30 blur-3xl"></div>
            <div class="absolute right-0 top-0 h-72 w-72 rounded-full bg-cyan-200/25 blur-3xl"></div>
            <div class="absolute bottom-0 left-1/3 h-48 w-48 rounded-full bg-teal-200/25 blur-3xl"></div>
        </div>

        <div class="relative flex flex-col gap-7 lg:flex-row lg:items-center lg:justify-between">
            <div class="max-w-3xl">
                <span
                    class="inline-flex items-center gap-2 rounded-full border border-white/20 bg-white/10 px-4 py-1.5 text-[11px] font-black uppercase tracking-[.18em] backdrop-blur"
                >
                    <span class="h-2 w-2 rounded-full bg-lime-300"></span>
                    Carga progresiva inteligente
                </span>

                <h2 class="mt-4 text-3xl font-black tracking-tight sm:text-4xl">
                    Optimiza fotografías {{ $maxFilesLabel }} sin subir todo el peso de golpe
                </h2>

                <p class="mt-3 max-w-2xl text-sm leading-6 text-emerald-50/95 sm:text-[15px]">
                    Selecciona archivos o una carpeta completa. Cada imagen se carga de manera independiente,
                    se procesa automáticamente en cola y puede reintentarse sin perder el resto del lote.
                </p>
            </div>

            <div class="grid grid-cols-2 gap-3 sm:grid-cols-4 lg:grid-cols-2">
                <div class="rounded-2xl border border-white/15 bg-white/10 px-4 py-3 backdrop-blur">
                    <p class="text-[10px] font-black uppercase tracking-[.14em] text-emerald-100">Motor</p>
                    <p class="mt-1 text-sm font-black">{{ $driverName ?: 'No disponible' }}</p>
                </div>

                <div class="rounded-2xl border border-white/15 bg-white/10 px-4 py-3 backdrop-blur">
                    <p class="text-[10px] font-black uppercase tracking-[.14em] text-emerald-100">Capacidad</p>
                    <p class="mt-1 text-sm font-black">{{ $maxFilesLabel }}</p>
                </div>

                <div class="rounded-2xl border border-white/15 bg-white/10 px-4 py-3 backdrop-blur">
                    <p class="text-[10px] font-black uppercase tracking-[.14em] text-emerald-100">Subida</p>
                    <p class="mt-1 text-sm font-black">Una por una</p>
                </div>

                <div class="rounded-2xl border border-white/15 bg-white/10 px-4 py-3 backdrop-blur">
                    <p class="text-[10px] font-black uppercase tracking-[.14em] text-emerald-100">Conservación</p>
                    <p class="mt-1 text-sm font-black">{{ $retentionHours }} horas</p>
                </div>
            </div>
        </div>
    </section>

    @if ($systemError)
        <div
            class="rounded-2xl border border-red-200 bg-red-50 px-5 py-4 text-sm font-semibold text-red-700 dark:border-red-900/60 dark:bg-red-950/30 dark:text-red-300"
        >
            <p class="font-black">El optimizador no puede iniciar.</p>
            <p class="mt-1">{{ $systemError }}</p>
        </div>
    @endif

    <div
        x-cloak
        x-show="loadingBatch"
        class="rounded-[26px] border border-slate-200 bg-white p-8 text-center shadow-sm dark:border-neutral-800 dark:bg-neutral-900"
    >
        <div class="mx-auto h-11 w-11 animate-spin rounded-full border-4 border-emerald-100 border-t-emerald-600 dark:border-emerald-950 dark:border-t-emerald-400"></div>
        <p class="mt-4 text-sm font-black text-slate-800 dark:text-white">Recuperando el último lote...</p>
        <p class="mt-1 text-xs text-slate-500 dark:text-slate-400">También se recuperan procesos que continúan en la cola.</p>
    </div>

    <div
        x-cloak
        x-show="globalError"
        class="rounded-2xl border border-red-200 bg-red-50 px-5 py-4 text-sm font-semibold text-red-700 dark:border-red-900/60 dark:bg-red-950/30 dark:text-red-300"
    >
        <p class="font-black">No fue posible completar una operación.</p>
        <p class="mt-1" x-text="globalError"></p>
    </div>

    @unless ($systemError)
        <div x-show="!loadingBatch" x-cloak class="grid gap-6 xl:grid-cols-[.9fr_1.1fr]">
            {{-- CONFIGURACIÓN --}}
            <section
                class="overflow-hidden rounded-[26px] border border-slate-200 bg-white shadow-sm dark:border-neutral-800 dark:bg-neutral-900"
            >
                <div class="border-b border-slate-200 px-5 py-4 dark:border-neutral-800 sm:px-6">
                    <div class="flex items-center justify-between gap-4">
                        <div>
                            <p class="text-[11px] font-black uppercase tracking-[.16em] text-emerald-600 dark:text-emerald-400">
                                Paso 1
                            </p>
                            <h3 class="mt-1 text-xl font-black text-slate-950 dark:text-white">
                                Configura la reducción
                            </h3>
                        </div>

                        <span
                            x-show="batch"
                            x-cloak
                            class="rounded-full border border-violet-200 bg-violet-50 px-3 py-1 text-[10px] font-black uppercase tracking-wider text-violet-700 dark:border-violet-900/50 dark:bg-violet-950/30 dark:text-violet-300"
                        >
                            Configuración bloqueada
                        </span>
                    </div>
                </div>

                <fieldset
                    :disabled="Boolean(batch)"
                    class="space-y-6 p-5 transition disabled:cursor-not-allowed disabled:opacity-65 sm:p-6"
                >
                    <div>
                        <flux:select wire:model.live="profile" label="Perfil de optimización">
                            @foreach ($profiles as $key => $profileData)
                                <flux:select.option value="{{ $key }}">
                                    {{ $profileData['label'] }}
                                </flux:select.option>
                            @endforeach
                        </flux:select>

                        <p
                            class="mt-2 rounded-xl border border-emerald-100 bg-emerald-50 px-3 py-2 text-xs font-semibold leading-5 text-emerald-800 dark:border-emerald-900/50 dark:bg-emerald-950/20 dark:text-emerald-300"
                        >
                            {{ $selectedProfile['description'] }}
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

                        <flux:input
                            wire:model="targetKb"
                            type="number"
                            min="50"
                            max="20000"
                            step="10"
                            label="Peso objetivo (KB)"
                            placeholder="Opcional"
                        />
                    </div>

                    <div
                        class="rounded-2xl border border-slate-200 bg-slate-50 p-4 dark:border-neutral-800 dark:bg-neutral-950/50"
                    >
                        <div class="flex items-center justify-between gap-4">
                            <div>
                                <p class="text-sm font-black text-slate-900 dark:text-white">Calidad de salida</p>
                                <p class="mt-1 text-xs text-slate-500">PNG utiliza compresión sin pérdida.</p>
                            </div>

                            <span
                                class="rounded-xl bg-white px-3 py-2 text-sm font-black text-emerald-700 shadow-sm ring-1 ring-slate-200 dark:bg-neutral-900 dark:text-emerald-400 dark:ring-neutral-800"
                            >
                                {{ $quality }}%
                            </span>
                        </div>

                        <input
                            type="range"
                            wire:model.live="quality"
                            min="35"
                            max="100"
                            step="1"
                            class="mt-4 h-2 w-full cursor-pointer appearance-none rounded-full bg-slate-200 accent-emerald-600 dark:bg-neutral-700"
                        >
                    </div>

                    <div>
                        <p class="mb-3 text-sm font-black text-slate-900 dark:text-white">Dimensiones máximas</p>

                        <div class="grid gap-4 sm:grid-cols-2">
                            <flux:input
                                wire:model="maxWidth"
                                type="number"
                                min="320"
                                max="6000"
                                label="Ancho máximo"
                                suffix="px"
                            />

                            <flux:input
                                wire:model="maxHeight"
                                type="number"
                                min="320"
                                max="6000"
                                label="Alto máximo"
                                suffix="px"
                            />
                        </div>

                        <p class="mt-2 text-xs leading-5 text-slate-500">
                            La proporción se conserva y las fotografías pequeñas no se amplían de forma predeterminada.
                        </p>
                    </div>

                    <div class="grid gap-3">
                        <label
                            class="flex cursor-pointer items-start gap-3 rounded-2xl border border-slate-200 p-4 transition hover:border-emerald-300 hover:bg-emerald-50/50 dark:border-neutral-800 dark:hover:border-emerald-800 dark:hover:bg-emerald-950/10"
                        >
                            <input
                                type="checkbox"
                                wire:model="preserveTransparency"
                                class="mt-1 h-5 w-5 rounded border-slate-300 text-emerald-600 focus:ring-emerald-500"
                            >

                            <span>
                                <span class="block text-sm font-black text-slate-900 dark:text-white">
                                    Conservar transparencia
                                </span>
                                <span class="mt-1 block text-xs leading-5 text-slate-500">
                                    Disponible para PNG, WebP y AVIF. JPG utiliza fondo blanco.
                                </span>
                            </span>
                        </label>

                        <label
                            class="flex cursor-pointer items-start gap-3 rounded-2xl border border-slate-200 p-4 transition hover:border-emerald-300 hover:bg-emerald-50/50 dark:border-neutral-800 dark:hover:border-emerald-800 dark:hover:bg-emerald-950/10"
                        >
                            <input
                                type="checkbox"
                                wire:model="allowUpscale"
                                class="mt-1 h-5 w-5 rounded border-slate-300 text-emerald-600 focus:ring-emerald-500"
                            >

                            <span>
                                <span class="block text-sm font-black text-slate-900 dark:text-white">
                                    Permitir ampliar imágenes pequeñas
                                </span>
                                <span class="mt-1 block text-xs leading-5 text-slate-500">
                                    Déjalo desactivado para evitar pérdida de nitidez.
                                </span>
                            </span>
                        </label>
                    </div>

                    <flux:input
                        wire:model="renamePattern"
                        label="Patrón de nombre"
                        description="Variables: {name}, {index}, {date}, {format}"
                    />
                </fieldset>

                <div
                    x-show="batch"
                    x-cloak
                    class="border-t border-slate-200 bg-slate-50 px-5 py-4 text-xs leading-5 text-slate-600 dark:border-neutral-800 dark:bg-neutral-950/40 dark:text-slate-400 sm:px-6"
                >
                    La configuración queda congelada al crear el lote para que todas las fotografías utilicen exactamente los mismos ajustes.
                </div>
            </section>

            {{-- CARGA PROGRESIVA --}}
            <section
                class="overflow-hidden rounded-[26px] border border-slate-200 bg-white shadow-sm dark:border-neutral-800 dark:bg-neutral-900"
            >
                <div class="border-b border-slate-200 px-5 py-4 dark:border-neutral-800 sm:px-6">
                    <div class="flex items-center justify-between gap-4">
                        <div>
                            <p class="text-[11px] font-black uppercase tracking-[.16em] text-emerald-600 dark:text-emerald-400">
                                Paso 2
                            </p>
                            <h3 class="mt-1 text-xl font-black text-slate-950 dark:text-white">
                                Selecciona imágenes o una carpeta
                            </h3>
                        </div>

                        <button
                            x-show="batch"
                            x-cloak
                            type="button"
                            @click="newBatch()"
                            :disabled="busy"
                            class="rounded-xl border border-slate-200 bg-white px-3 py-2 text-xs font-black text-slate-700 transition hover:border-red-200 hover:bg-red-50 hover:text-red-700 dark:border-neutral-700 dark:bg-neutral-800 dark:text-slate-200 dark:hover:border-red-900 dark:hover:bg-red-950/30 dark:hover:text-red-300"
                        >
                            Nuevo lote
                        </button>
                    </div>
                </div>

                <div class="p-5 sm:p-6">
                    <input
                        x-ref="fileInput"
                        type="file"
                        multiple
                        accept="image/jpeg,image/png,image/webp,.jpg,.jpeg,.png,.webp"
                        class="sr-only"
                        @change="selectFiles($event.target.files); $event.target.value = ''"
                    >

                    <input
                        x-ref="folderInput"
                        type="file"
                        multiple
                        webkitdirectory
                        directory
                        accept="image/jpeg,image/png,image/webp,.jpg,.jpeg,.png,.webp"
                        class="sr-only"
                        @change="selectFiles($event.target.files); $event.target.value = ''"
                    >

                    <div
                        x-show="!batch || pendingUploadCount > 0"
                        @dragenter.prevent="dragActive = true"
                        @dragover.prevent="dragActive = true"
                        @dragleave.prevent="dragActive = false"
                        @drop.prevent="dropFiles($event)"
                        :class="dragActive
                            ? 'border-emerald-500 bg-emerald-50 shadow-lg shadow-emerald-100/60 dark:bg-emerald-950/20'
                            : 'border-slate-300 bg-slate-50/80 dark:border-neutral-700 dark:bg-neutral-950/50'"
                        class="relative overflow-hidden rounded-[24px] border-2 border-dashed px-5 py-9 text-center transition-all"
                    >
                        <div
                            class="mx-auto flex h-20 w-20 items-center justify-center rounded-3xl bg-white text-4xl font-black text-emerald-600 shadow-[0_14px_35px_-14px_rgba(5,150,105,.6)] ring-1 ring-emerald-100 dark:bg-neutral-900 dark:ring-neutral-800"
                        >
                            ⇩
                        </div>

                        <h4 class="mt-5 text-xl font-black text-slate-950 dark:text-white">
                            <span x-show="!batch">Arrastra tus imágenes o una carpeta aquí</span>
                            <span x-show="batch" x-cloak>Continúa con las imágenes pendientes</span>
                        </h4>

                        <p class="mx-auto mt-2 max-w-xl text-sm leading-6 text-slate-500 dark:text-slate-400">
                            JPG, PNG y WebP. Hasta {{ $maxFileMb }} MB por archivo y {{ $maxFilesLabel }} de lote.
                            El sistema sube progresivamente y divide las descargas en partes de hasta {{ $zipPartMaxFiles }} imágenes o {{ $zipPartMaxMb }} MB.
                        </p>

                        <div
                            x-show="batch && pendingUploadCount > 0"
                            x-cloak
                            class="mx-auto mt-4 max-w-lg rounded-2xl border border-amber-200 bg-amber-50 px-4 py-3 text-left text-xs font-semibold leading-5 text-amber-800 dark:border-amber-900/50 dark:bg-amber-950/25 dark:text-amber-300"
                        >
                            Quedan <b x-text="pendingUploadCount"></b> archivo(s) por subir. Después de recargar la página,
                            selecciona nuevamente esos archivos o la misma carpeta; el sistema reconocerá cuáles faltan.
                        </div>

                        <div class="mt-6 flex flex-col justify-center gap-3 sm:flex-row">
                            <button
                                type="button"
                                @click="$refs.fileInput.click()"
                                :disabled="busy"
                                class="inline-flex items-center justify-center rounded-2xl bg-gradient-to-r from-emerald-600 to-teal-600 px-6 py-3 text-sm font-black text-white shadow-lg shadow-emerald-500/20 transition hover:scale-[1.02] hover:from-emerald-700 hover:to-teal-700 disabled:cursor-wait disabled:opacity-60"
                            >
                                Elegir imágenes
                            </button>

                            <button
                                type="button"
                                @click="$refs.folderInput.click()"
                                :disabled="busy"
                                class="inline-flex items-center justify-center rounded-2xl border border-emerald-200 bg-white px-6 py-3 text-sm font-black text-emerald-700 shadow-sm transition hover:border-emerald-400 hover:bg-emerald-50 disabled:cursor-wait disabled:opacity-60 dark:border-emerald-900/60 dark:bg-neutral-900 dark:text-emerald-300 dark:hover:bg-emerald-950/20"
                            >
                                Seleccionar carpeta
                            </button>
                        </div>
                    </div>

                    <div
                        x-show="batch && pendingUploadCount === 0"
                        x-cloak
                        class="rounded-[24px] border border-emerald-200 bg-gradient-to-br from-emerald-50 to-cyan-50 p-6 text-center dark:border-emerald-900/50 dark:from-emerald-950/20 dark:to-cyan-950/10"
                    >
                        <div class="mx-auto flex h-14 w-14 items-center justify-center rounded-2xl bg-emerald-600 text-2xl text-white shadow-lg shadow-emerald-500/20">
                            ✓
                        </div>
                        <p class="mt-4 text-lg font-black text-slate-950 dark:text-white">Todas las fotografías fueron recibidas</p>
                        <p class="mt-1 text-sm text-slate-600 dark:text-slate-400">
                            El procesamiento continúa automáticamente aunque recargues la página.
                        </p>
                    </div>

                    <div x-show="busy && queue.length" x-cloak class="mt-6 rounded-2xl border border-sky-200 bg-sky-50 p-4 dark:border-sky-900/50 dark:bg-sky-950/20">
                        <div class="flex items-center justify-between gap-4 text-xs font-black text-sky-800 dark:text-sky-300">
                            <span>Progreso total de subida</span>
                            <span x-text="uploadProgress + '%'">0%</span>
                        </div>

                        <div class="mt-3 h-3 overflow-hidden rounded-full bg-sky-100 dark:bg-sky-950/60">
                            <div
                                class="h-full rounded-full bg-gradient-to-r from-sky-500 via-cyan-500 to-emerald-500 transition-all duration-300"
                                :style="`width: ${uploadProgress}%`"
                            ></div>
                        </div>

                        <p class="mt-3 text-xs leading-5 text-sky-700 dark:text-sky-400">
                            No cierres esta pestaña mientras se estén subiendo archivos. Una vez recibidos, la cola seguirá trabajando por separado.
                        </p>
                    </div>
                </div>
            </section>
        </div>

        {{-- ESTADO DEL LOTE --}}
        <section
            x-show="batch"
            x-cloak
            class="overflow-hidden rounded-[28px] border border-slate-200 bg-white shadow-sm dark:border-neutral-800 dark:bg-neutral-900"
        >
            <div class="border-b border-slate-200 bg-slate-50/70 px-5 py-5 dark:border-neutral-800 dark:bg-neutral-950/50 sm:px-7">
                <div class="flex flex-col gap-5 lg:flex-row lg:items-center lg:justify-between">
                    <div>
                        <span
                            class="inline-flex rounded-full border px-3 py-1 text-[11px] font-black uppercase tracking-[.14em]"
                            :class="statusClasses(['partial', 'failed'].includes(batch?.status) ? 'failed' : (batch?.status === 'completed' ? 'completed' : 'processing'))"
                            x-text="batchStatusText(batch?.status)"
                        ></span>

                        <h3 class="mt-3 text-2xl font-black tracking-tight text-slate-950 dark:text-white">
                            <span x-text="batch?.completed_files ?? 0"></span>
                            de
                            <span x-text="batch?.total_files ?? 0"></span>
                            imágenes completadas
                        </h3>

                        <p class="mt-1 text-sm text-slate-500 dark:text-slate-400">
                            El lote se conserva hasta
                            <b x-text="formatDate(batch?.expires_at)"></b>.
                        </p>
                    </div>

                    <div class="flex flex-col gap-2 sm:flex-row">
                        <template x-if="(batch?.download_parts ?? []).length === 1">
                            <a
                                :href="batch.download_parts[0].url"
                                class="inline-flex items-center justify-center rounded-2xl bg-gradient-to-r from-emerald-600 to-teal-600 px-5 py-3 text-sm font-black text-white shadow-lg shadow-emerald-500/20 transition hover:from-emerald-700 hover:to-teal-700"
                            >
                                Descargar ZIP
                            </a>
                        </template>

                        <button
                            type="button"
                            @click="refreshBatch()"
                            class="inline-flex items-center justify-center rounded-2xl border border-slate-200 bg-white px-5 py-3 text-sm font-black text-slate-700 transition hover:bg-slate-50 dark:border-neutral-700 dark:bg-neutral-800 dark:text-slate-200 dark:hover:bg-neutral-700"
                        >
                            Actualizar estado
                        </button>

                        <button
                            type="button"
                            @click="newBatch()"
                            :disabled="busy"
                            class="inline-flex items-center justify-center rounded-2xl border border-slate-200 bg-white px-5 py-3 text-sm font-black text-slate-700 transition hover:border-red-200 hover:bg-red-50 hover:text-red-700 dark:border-neutral-700 dark:bg-neutral-800 dark:text-slate-200 dark:hover:border-red-900 dark:hover:bg-red-950/20 dark:hover:text-red-300"
                        >
                            Nuevo lote
                        </button>
                    </div>
                </div>
            </div>

            <div class="p-5 sm:p-7">
                <div
                    x-show="(batch?.download_parts ?? []).length > 1"
                    x-cloak
                    class="mb-5 rounded-2xl border border-emerald-200 bg-emerald-50 p-4 dark:border-emerald-900/50 dark:bg-emerald-950/20"
                >
                    <div class="flex flex-col gap-1 sm:flex-row sm:items-end sm:justify-between">
                        <div>
                            <p class="text-sm font-black text-emerald-900 dark:text-emerald-200">Descargas por partes</p>
                            <p class="mt-1 text-xs font-semibold leading-5 text-emerald-700 dark:text-emerald-300">
                                Para evitar ZIPs pesados, el sistema divide las imágenes completadas en partes descargables.
                            </p>
                        </div>
                        <span class="text-xs font-black text-emerald-700 dark:text-emerald-300">
                            <span x-text="batch?.download_part_count ?? 0"></span> partes
                        </span>
                    </div>

                    <div class="mt-3 flex flex-wrap gap-2">
                        <template x-for="part in (batch?.download_parts ?? [])" :key="part.number">
                            <a
                                :href="part.url"
                                class="inline-flex items-center justify-center rounded-xl bg-emerald-600 px-3 py-2 text-xs font-black text-white transition hover:bg-emerald-700"
                            >
                                <span x-text="`${part.label} · ${part.file_count} img · ${formatBytes(part.bytes)}`"></span>
                            </a>
                        </template>
                    </div>
                </div>

                <div class="grid gap-3 sm:grid-cols-2 xl:grid-cols-5">
                    <div class="rounded-2xl border border-slate-200 bg-slate-50 p-4 dark:border-neutral-800 dark:bg-neutral-950/50">
                        <p class="text-[10px] font-black uppercase tracking-[.14em] text-slate-500">Recibidas</p>
                        <p class="mt-2 text-xl font-black text-slate-950 dark:text-white">
                            <span x-text="batch?.uploaded_files ?? 0"></span>/<span x-text="batch?.total_files ?? 0"></span>
                        </p>
                    </div>

                    <div class="rounded-2xl border border-violet-200 bg-violet-50 p-4 dark:border-violet-900/50 dark:bg-violet-950/20">
                        <p class="text-[10px] font-black uppercase tracking-[.14em] text-violet-700 dark:text-violet-400">Procesadas</p>
                        <p class="mt-2 text-xl font-black text-violet-700 dark:text-violet-300">
                            <span x-text="batch?.processed_files ?? 0"></span>/<span x-text="batch?.total_files ?? 0"></span>
                        </p>
                    </div>

                    <div class="rounded-2xl border border-emerald-200 bg-emerald-50 p-4 dark:border-emerald-900/50 dark:bg-emerald-950/20">
                        <p class="text-[10px] font-black uppercase tracking-[.14em] text-emerald-700 dark:text-emerald-400">Completadas</p>
                        <p class="mt-2 text-xl font-black text-emerald-700 dark:text-emerald-300" x-text="batch?.completed_files ?? 0"></p>
                    </div>

                    <div class="rounded-2xl border border-red-200 bg-red-50 p-4 dark:border-red-900/50 dark:bg-red-950/20">
                        <p class="text-[10px] font-black uppercase tracking-[.14em] text-red-700 dark:text-red-400">Con error</p>
                        <p class="mt-2 text-xl font-black text-red-700 dark:text-red-300" x-text="batch?.failed_files ?? 0"></p>
                    </div>

                    <div class="rounded-2xl border border-cyan-200 bg-cyan-50 p-4 dark:border-cyan-900/50 dark:bg-cyan-950/20">
                        <p class="text-[10px] font-black uppercase tracking-[.14em] text-cyan-700 dark:text-cyan-400">Reducción total</p>
                        <p class="mt-2 text-xl font-black text-cyan-700 dark:text-cyan-300">
                            <span x-text="Math.abs(batch?.total_reduction ?? 0).toLocaleString('es-MX')"></span>%
                        </p>
                    </div>
                </div>

                <div class="mt-5 rounded-2xl border border-slate-200 p-4 dark:border-neutral-800">
                    <div class="flex items-center justify-between gap-4 text-xs font-black text-slate-700 dark:text-slate-300">
                        <span>Avance de procesamiento</span>
                        <span x-text="processingProgress + '%'">0%</span>
                    </div>

                    <div class="mt-3 h-3 overflow-hidden rounded-full bg-slate-100 dark:bg-neutral-800">
                        <div
                            class="h-full rounded-full bg-gradient-to-r from-violet-500 via-cyan-500 to-emerald-500 transition-all duration-500"
                            :style="`width: ${processingProgress}%`"
                        ></div>
                    </div>
                </div>

                <div
                    x-show="(batch?.queued_files ?? 0) > 0 || (batch?.processing_files ?? 0) > 0"
                    x-cloak
                    class="mt-5 rounded-2xl border border-violet-200 bg-violet-50 px-4 py-3 text-xs font-semibold leading-5 text-violet-800 dark:border-violet-900/50 dark:bg-violet-950/20 dark:text-violet-300"
                >
                    <b>La cola está trabajando.</b> En Laragon mantén abierta una terminal con:
                    <code class="mx-1 rounded bg-white px-2 py-1 font-mono text-[11px] text-violet-800 dark:bg-neutral-900 dark:text-violet-300">php artisan queue:work --queue=image-optimizer,default --timeout=600 --tries=1</code>
                </div>

                <div class="mt-6 flex items-center justify-between gap-4">
                    <div>
                        <h4 class="text-lg font-black text-slate-950 dark:text-white">Detalle por fotografía</h4>
                        <p class="mt-1 text-xs text-slate-500 dark:text-slate-400">
                            Los errores no detienen el lote y pueden reintentarse de manera individual.
                        </p>
                    </div>

                    <span class="rounded-full bg-slate-100 px-3 py-1.5 text-xs font-black text-slate-600 dark:bg-neutral-800 dark:text-slate-300">
                        <span x-text="batch?.items?.length ?? 0"></span> archivos
                    </span>
                </div>

                <div class="mt-4 grid max-h-[820px] gap-3 overflow-y-auto pr-1 lg:grid-cols-2">
                    <template x-for="item in (batch?.items ?? [])" :key="item.uuid">
                        <article class="overflow-hidden rounded-[22px] border border-slate-200 bg-slate-50/60 dark:border-neutral-800 dark:bg-neutral-950/45">
                            <div class="flex gap-4 p-4">
                                <div class="relative h-24 w-24 shrink-0 overflow-hidden rounded-2xl bg-slate-200 dark:bg-neutral-800">
                                    <template x-if="item.preview_output_url">
                                        <img
                                            :src="item.preview_output_url"
                                            :alt="item.original_name"
                                            loading="lazy"
                                            class="h-full w-full object-cover"
                                        >
                                    </template>

                                    <template x-if="!item.preview_output_url">
                                        <div class="flex h-full w-full flex-col items-center justify-center text-slate-400 dark:text-slate-500">
                                            <span class="text-2xl font-black" x-text="item.position"></span>
                                            <span class="mt-1 text-[9px] font-black uppercase tracking-wider">Imagen</span>
                                        </div>
                                    </template>

                                    <div
                                        x-show="['uploading', 'retrying', 'processing'].includes(visibleStatus(item))"
                                        x-cloak
                                        class="absolute inset-0 flex items-center justify-center bg-slate-950/55 backdrop-blur-sm"
                                    >
                                        <span class="h-8 w-8 animate-spin rounded-full border-[3px] border-white/30 border-t-white"></span>
                                    </div>
                                </div>

                                <div class="min-w-0 flex-1">
                                    <div class="flex items-start justify-between gap-3">
                                        <div class="min-w-0">
                                            <h5 class="truncate text-sm font-black text-slate-950 dark:text-white" :title="item.original_name" x-text="item.original_name"></h5>
                                            <p
                                                x-show="item.relative_path"
                                                class="mt-1 truncate text-[11px] font-semibold text-slate-500"
                                                :title="item.relative_path"
                                                x-text="item.relative_path"
                                            ></p>
                                            <p class="mt-1 text-xs font-semibold text-slate-500" x-text="formatBytes(item.original_size)"></p>
                                        </div>

                                        <span
                                            class="shrink-0 rounded-full border px-2.5 py-1 text-[9px] font-black uppercase tracking-wider"
                                            :class="statusClasses(visibleStatus(item))"
                                            x-text="statusText(visibleStatus(item))"
                                        ></span>
                                    </div>

                                    <div
                                        x-show="['waiting', 'uploading', 'retrying'].includes(visibleStatus(item))"
                                        x-cloak
                                        class="mt-3"
                                    >
                                        <div class="flex justify-between text-[10px] font-black text-sky-700 dark:text-sky-400">
                                            <span>Subida individual</span>
                                            <span x-text="visibleProgress(item) + '%'">0%</span>
                                        </div>
                                        <div class="mt-1.5 h-2 overflow-hidden rounded-full bg-sky-100 dark:bg-sky-950/50">
                                            <div
                                                class="h-full rounded-full bg-gradient-to-r from-sky-500 to-emerald-500 transition-all"
                                                :style="`width: ${visibleProgress(item)}%`"
                                            ></div>
                                        </div>
                                    </div>

                                    <div x-show="item.status === 'completed'" x-cloak class="mt-3 grid grid-cols-3 gap-2 text-center">
                                        <div class="rounded-xl bg-white px-2 py-2 dark:bg-neutral-900">
                                            <p class="text-[9px] font-black uppercase tracking-wider text-slate-400">Después</p>
                                            <p class="mt-1 text-xs font-black text-emerald-700 dark:text-emerald-300" x-text="formatBytes(item.optimized_size)"></p>
                                        </div>
                                        <div class="rounded-xl bg-white px-2 py-2 dark:bg-neutral-900">
                                            <p class="text-[9px] font-black uppercase tracking-wider text-slate-400">Reducción</p>
                                            <p class="mt-1 text-xs font-black text-emerald-700 dark:text-emerald-300">
                                                <span x-text="Math.abs(item.reduction ?? 0).toLocaleString('es-MX')"></span>%
                                            </p>
                                        </div>
                                        <div class="rounded-xl bg-white px-2 py-2 dark:bg-neutral-900">
                                            <p class="text-[9px] font-black uppercase tracking-wider text-slate-400">Salida</p>
                                            <p class="mt-1 text-xs font-black text-slate-800 dark:text-white" x-text="item.format ?? '—'"></p>
                                        </div>
                                    </div>

                                    <div
                                        x-show="item.error || localEntry(item)?.error"
                                        x-cloak
                                        class="mt-3 rounded-xl border border-red-200 bg-red-50 px-3 py-2 text-[11px] font-semibold leading-5 text-red-700 dark:border-red-900/50 dark:bg-red-950/20 dark:text-red-300"
                                        x-text="localEntry(item)?.error || item.error"
                                    ></div>

                                    <div x-show="item.warnings?.length" x-cloak class="mt-3 rounded-xl border border-amber-200 bg-amber-50 px-3 py-2 text-[11px] font-semibold leading-5 text-amber-800 dark:border-amber-900/50 dark:bg-amber-950/20 dark:text-amber-300">
                                        <template x-for="warning in item.warnings" :key="warning">
                                            <p>• <span x-text="warning"></span></p>
                                        </template>
                                    </div>

                                    <div class="mt-3 flex flex-wrap gap-2">
                                        <a
                                            x-show="item.download_url"
                                            x-cloak
                                            :href="item.download_url"
                                            class="inline-flex items-center justify-center rounded-xl bg-slate-950 px-3 py-2 text-[11px] font-black text-white transition hover:bg-emerald-700 dark:bg-white dark:text-neutral-950 dark:hover:bg-emerald-300"
                                        >
                                            Descargar
                                        </a>

                                        <button
                                            x-show="item.status === 'failed' && item.preview_original_url"
                                            x-cloak
                                            type="button"
                                            @click="retryProcessing(item)"
                                            class="inline-flex items-center justify-center rounded-xl border border-violet-200 bg-violet-50 px-3 py-2 text-[11px] font-black text-violet-700 transition hover:bg-violet-100 dark:border-violet-900/50 dark:bg-violet-950/25 dark:text-violet-300"
                                        >
                                            Reintentar optimización
                                        </button>

                                        <button
                                            x-show="['pending_upload', 'upload_failed'].includes(item.status)"
                                            x-cloak
                                            type="button"
                                            @click="localEntry(item)?.file ? retryLocalUpload(item) : chooseRetryUpload(item)"
                                            class="inline-flex items-center justify-center rounded-xl border border-sky-200 bg-sky-50 px-3 py-2 text-[11px] font-black text-sky-700 transition hover:bg-sky-100 dark:border-sky-900/50 dark:bg-sky-950/25 dark:text-sky-300"
                                        >
                                            <span x-text="localEntry(item)?.file ? 'Reintentar subida' : 'Seleccionar archivo'"></span>
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </article>
                    </template>
                </div>
            </div>
        </section>
    @endunless
</div>
