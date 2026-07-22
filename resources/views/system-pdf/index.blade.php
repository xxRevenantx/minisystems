@php
    $toolsReady = collect($toolStatus)->every(fn (array $tool) => $tool['available']);
    $toolLabels = [
        'ghostscript' => ['Ghostscript', 'Compresión avanzada'],
        'qpdf' => ['qpdf', 'Combinar, dividir, ordenar y cifrar'],
        'pdfinfo' => ['pdfinfo', 'Lectura de páginas y metadatos'],
        'pdftoppm' => ['pdftoppm', 'Miniaturas visuales'],
    ];
@endphp

<x-layouts.app :title="__('System PDF')">
    <div
        x-data="systemPdfManager(@js([
            'csrf' => csrf_token(),
            'maxFiles' => $maxFiles,
            'maxFileMb' => $maxFileMb,
            'uploadConcurrency' => $uploadConcurrency,
            'zipPartMaxFiles' => $zipPartMaxFiles,
            'zipPartMaxMb' => $zipPartMaxMb,
            'canProcess' => auth()->user()->puedePdf('procesar'),
            'canDelete' => auth()->user()->puedePdf('eliminar'),
            'canAdmin' => auth()->user()->puedePdf('administrar'),
            'urls' => [
                'active' => route('system-pdf.api.active'),
                'history' => route('system-pdf.api.history'),
                'permissions' => route('system-pdf.api.permissions'),
                'permissionUpdate' => url('/system-pdf/api/permisos/{user}'),
                'downloadZip' => url('/system-pdf/{batch}/descargar-zip'),
                'store' => route('system-pdf.api.store'),
                'show' => url('/system-pdf/api/lotes/{batch}'),
                'upload' => url('/system-pdf/api/lotes/{batch}/archivos/{item}'),
                'password' => url('/system-pdf/api/lotes/{batch}/archivos/{item}/contrasena'),
                'start' => url('/system-pdf/api/lotes/{batch}/iniciar'),
                'retry' => url('/system-pdf/api/lotes/{batch}/archivos/{item}/reintentar'),
                'destroy' => url('/system-pdf/api/lotes/{batch}'),
            ],
        ]))"
        class="mx-auto w-full max-w-[1600px] space-y-7 p-4 sm:p-6 lg:p-8"
    >
        <section class="relative overflow-hidden rounded-[32px] border border-slate-200 bg-white shadow-sm dark:border-neutral-800 dark:bg-neutral-900">
            <div class="absolute inset-y-0 right-0 hidden w-2/5 bg-gradient-to-br from-sky-100 via-cyan-50 to-emerald-100 opacity-90 lg:block dark:from-sky-950/30 dark:via-cyan-950/20 dark:to-emerald-950/30"></div>
            <div class="relative grid gap-8 p-6 sm:p-8 lg:grid-cols-[1.2fr_.8fr] lg:p-10">
                <div>
                    <div class="inline-flex items-center gap-2 rounded-full border border-sky-200 bg-sky-50 px-3 py-1 text-[11px] font-black uppercase tracking-[.16em] text-sky-700 dark:border-sky-900 dark:bg-sky-950/30 dark:text-sky-300">
                        <span class="h-2 w-2 rounded-full bg-sky-500"></span>
                        Herramientas profesionales
                    </div>
                    <h1 class="mt-5 text-3xl font-black tracking-tight text-slate-950 sm:text-4xl dark:text-white">System PDF</h1>
                    <p class="mt-3 max-w-3xl text-sm font-medium leading-7 text-slate-600 sm:text-base dark:text-slate-300">
                        Reduce peso, combina, divide, reordena y protege documentos. Los lotes se procesan mediante colas y sus archivos temporales se eliminan automáticamente después de 24 horas.
                    </p>
                    <div class="mt-6 flex flex-wrap gap-2 text-xs font-bold text-slate-600 dark:text-slate-300">
                        <span class="rounded-full bg-slate-100 px-3 py-2 dark:bg-neutral-800">Hasta {{ $maxFiles }} archivos</span>
                        <span class="rounded-full bg-slate-100 px-3 py-2 dark:bg-neutral-800">{{ $maxFileMb }} MB por archivo</span>
                        <span class="rounded-full bg-slate-100 px-3 py-2 dark:bg-neutral-800">Exportación individual y ZIP</span>
                        <span class="rounded-full bg-slate-100 px-3 py-2 dark:bg-neutral-800">Historial temporal</span>
                    </div>
                </div>

                <div class="relative rounded-[24px] border border-white/70 bg-white/80 p-5 shadow-lg backdrop-blur dark:border-neutral-700 dark:bg-neutral-900/80">
                    <div class="flex items-center justify-between gap-3">
                        <div>
                            <p class="text-xs font-black uppercase tracking-wider text-slate-500">Motor local</p>
                            <p class="mt-1 text-lg font-black text-slate-950 dark:text-white">Dependencias PDF</p>
                        </div>
                        <span class="rounded-full px-3 py-1 text-xs font-black ring-1 {{ $toolsReady ? 'bg-emerald-50 text-emerald-700 ring-emerald-200' : 'bg-amber-50 text-amber-700 ring-amber-200' }}">
                            {{ $toolsReady ? 'Listo' : 'Configuración pendiente' }}
                        </span>
                    </div>
                    <div class="mt-4 grid gap-2 sm:grid-cols-2">
                        @foreach ($toolLabels as $key => [$name, $description])
                            <div class="rounded-2xl border border-slate-200 bg-white p-3 dark:border-neutral-800 dark:bg-neutral-950/50">
                                <div class="flex items-center gap-2">
                                    <span class="h-2.5 w-2.5 rounded-full {{ $toolStatus[$key]['available'] ? 'bg-emerald-500' : 'bg-red-500' }}"></span>
                                    <span class="text-sm font-black text-slate-900 dark:text-white">{{ $name }}</span>
                                </div>
                                <p class="mt-1 text-[11px] leading-4 text-slate-500">{{ $description }}</p>
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>
        </section>

        @unless ($toolsReady)
            <section class="rounded-[26px] border border-amber-200 bg-amber-50 p-5 text-amber-950 shadow-sm dark:border-amber-900/60 dark:bg-amber-950/20 dark:text-amber-100">
                <div class="flex flex-col gap-4 lg:flex-row lg:items-start lg:justify-between">
                    <div>
                        <h2 class="text-lg font-black">Completa la instalación en Laragon</h2>
                        <p class="mt-1 text-sm leading-6">Instala Ghostscript, qpdf y Poppler; después configura sus rutas en <code class="rounded bg-amber-100 px-1.5 py-0.5 font-bold dark:bg-amber-900/50">.env</code>.</p>
                        <pre class="mt-4 overflow-x-auto rounded-2xl bg-slate-950 p-4 text-xs leading-6 text-slate-100">SYSTEM_PDF_GHOSTSCRIPT_BINARY="C:\Program Files\gs\gs10.xx.x\bin\gswin64c.exe"
SYSTEM_PDF_QPDF_BINARY="C:\Program Files\qpdf\bin\qpdf.exe"
SYSTEM_PDF_PDFINFO_BINARY="C:\poppler\Library\bin\pdfinfo.exe"
SYSTEM_PDF_PDFTOPPM_BINARY="C:\poppler\Library\bin\pdftoppm.exe"</pre>
                    </div>
                    <div class="shrink-0 rounded-2xl border border-amber-200 bg-white p-4 text-xs leading-6 dark:border-amber-900 dark:bg-neutral-900">
                        <p class="font-black">Verificación</p>
                        <code>php artisan config:clear</code><br>
                        <code>php artisan system-pdf:check</code>
                    </div>
                </div>
            </section>
        @endunless

        <section>
            <div class="mb-4 flex items-end justify-between gap-4">
                <div>
                    <p class="text-xs font-black uppercase tracking-[.16em] text-sky-600">Selecciona una herramienta</p>
                    <h2 class="mt-1 text-2xl font-black text-slate-950 dark:text-white">¿Qué deseas hacer?</h2>
                </div>
                <flux:button x-show="batch" x-cloak variant="ghost" icon="plus" x-on:click="newWorkspace()">Nuevo lote</flux:button>
            </div>

            <div class="grid gap-3 sm:grid-cols-2 xl:grid-cols-5">
                @foreach ([
                    ['compress', 'arrow-trending-down', 'Reducir peso', 'Compresión automática, baja, media, alta o personalizada.'],
                    ['combine', 'document-duplicate', 'Combinar', 'Une hasta 100 PDF o imágenes en el orden elegido.'],
                    ['split', 'scissors', 'Descombinar', 'Divide por página, rango, bloques o selección visual.'],
                    ['reorder', 'arrows-up-down', 'Ordenar', 'Arrastra, gira, duplica y elimina páginas.'],
                    ['security', 'lock-closed', 'Seguridad', 'Agrega contraseña AES-256 o desbloquea un PDF.'],
                ] as [$value, $icon, $title, $description])
                    <button
                        type="button"
                        x-on:click="chooseOperation('{{ $value }}')"
                        class="group rounded-[24px] border p-5 text-left transition"
                        :class="operation === '{{ $value }}' ? 'border-sky-400 bg-sky-50 shadow-md ring-2 ring-sky-100 dark:border-sky-700 dark:bg-sky-950/25 dark:ring-sky-950' : 'border-slate-200 bg-white hover:-translate-y-0.5 hover:border-sky-200 hover:shadow-md dark:border-neutral-800 dark:bg-neutral-900'"
                    >
                        <div class="flex h-11 w-11 items-center justify-center rounded-2xl bg-slate-100 text-slate-700 transition group-hover:bg-sky-100 group-hover:text-sky-700 dark:bg-neutral-800 dark:text-slate-200">
                            <flux:icon :name="$icon" class="size-5" />
                        </div>
                        <h3 class="mt-4 text-base font-black text-slate-950 dark:text-white">{{ $title }}</h3>
                        <p class="mt-2 text-xs font-medium leading-5 text-slate-500">{{ $description }}</p>
                    </button>
                @endforeach
            </div>
        </section>

        <div x-show="globalError" x-cloak class="rounded-2xl border border-red-200 bg-red-50 p-4 text-sm font-semibold text-red-800 dark:border-red-900 dark:bg-red-950/20 dark:text-red-300" x-text="globalError"></div>

        <section class="grid gap-6 xl:grid-cols-[.82fr_1.18fr]">
            <div class="space-y-6">
                <div class="overflow-hidden rounded-[28px] border border-slate-200 bg-white shadow-sm dark:border-neutral-800 dark:bg-neutral-900">
                    <div class="border-b border-slate-200 p-5 dark:border-neutral-800 sm:p-6">
                        <p class="text-xs font-black uppercase tracking-[.16em] text-sky-600">Configuración</p>
                        <h2 class="mt-1 text-xl font-black text-slate-950 dark:text-white" x-text="operationTitle()"></h2>
                        <p class="mt-2 text-sm leading-6 text-slate-500" x-text="operationHint()"></p>
                    </div>

                    <div class="space-y-5 p-5 sm:p-6" :class="batch ? 'opacity-80' : ''">
                        <template x-if="operation === 'compress'">
                            <div class="space-y-5">
                                <label class="block">
                                    <span class="text-sm font-black text-slate-800 dark:text-slate-100">Nivel de compresión</span>
                                    <select x-model="compressionProfile" :disabled="Boolean(batch)" class="mt-2 w-full rounded-xl border-slate-300 bg-white text-sm dark:border-neutral-700 dark:bg-neutral-950">
                                        <option value="auto">Automática recomendada</option>
                                        <option value="low">Baja — sin pérdida, máxima fidelidad</option>
                                        <option value="medium">Media — equilibrio para compartir</option>
                                        <option value="high">Alta — menor peso</option>
                                        <option value="custom">Personalizada</option>
                                    </select>
                                </label>
                                <div x-show="compressionProfile === 'custom'" x-cloak>
                                    <div class="flex items-center justify-between text-sm font-black text-slate-800 dark:text-slate-100"><span>Calidad</span><span x-text="`${customQuality}%`"></span></div>
                                    <input type="range" x-model="customQuality" min="35" max="100" class="mt-3 w-full accent-sky-600" :disabled="Boolean(batch)">
                                </div>
                                <div class="rounded-2xl border border-emerald-200 bg-emerald-50 p-4 text-xs leading-6 text-emerald-800 dark:border-emerald-900/50 dark:bg-emerald-950/20 dark:text-emerald-300">
                                    Si el resultado pesa igual o más, System PDF conserva el original. Para formularios o elementos interactivos complejos utiliza el perfil bajo, que prioriza la fidelidad estructural.
                                </div>
                            </div>
                        </template>

                        <template x-if="operation === 'combine'">
                            <div class="space-y-5">
                                <label class="block">
                                    <span class="text-sm font-black text-slate-800 dark:text-slate-100">Nombre del PDF final</span>
                                    <input x-model="outputName" :disabled="Boolean(batch)" maxlength="120" class="mt-2 w-full rounded-xl border-slate-300 bg-white text-sm dark:border-neutral-700 dark:bg-neutral-950" placeholder="documentos_combinados">
                                </label>
                                <div class="rounded-2xl border border-sky-200 bg-sky-50 p-4 text-xs leading-6 text-sky-800 dark:border-sky-900/50 dark:bg-sky-950/20 dark:text-sky-300">
                                    Arrastra los archivos de la lista para definir su orden. Las imágenes se convierten en páginas A4 antes de combinarse.
                                </div>
                            </div>
                        </template>

                        <template x-if="operation === 'split'">
                            <div class="space-y-5">
                                <label class="block">
                                    <span class="text-sm font-black text-slate-800 dark:text-slate-100">Método de división</span>
                                    <select x-model="splitMode" :disabled="Boolean(batch && batch.started_at)" class="mt-2 w-full rounded-xl border-slate-300 bg-white text-sm dark:border-neutral-700 dark:bg-neutral-950">
                                        <option value="each">Cada página en un PDF</option>
                                        <option value="ranges">Por rangos</option>
                                        <option value="every">Cada determinado número de páginas</option>
                                        <option value="selected">Selección visual en un PDF</option>
                                    </select>
                                </label>
                                <label x-show="splitMode === 'ranges'" x-cloak class="block">
                                    <span class="text-sm font-black text-slate-800 dark:text-slate-100">Rangos</span>
                                    <textarea x-model="splitRanges" rows="3" class="mt-2 w-full rounded-xl border-slate-300 bg-white text-sm dark:border-neutral-700 dark:bg-neutral-950" placeholder="1-3; 4,6,8-10"></textarea>
                                    <span class="mt-1 block text-xs text-slate-500">Separa cada archivo de salida con punto y coma.</span>
                                </label>
                                <label x-show="splitMode === 'every'" x-cloak class="block">
                                    <span class="text-sm font-black text-slate-800 dark:text-slate-100">Páginas por archivo</span>
                                    <input type="number" min="1" x-model="splitEvery" class="mt-2 w-full rounded-xl border-slate-300 bg-white text-sm dark:border-neutral-700 dark:bg-neutral-950">
                                </label>
                            </div>
                        </template>

                        <template x-if="operation === 'reorder'">
                            <div class="rounded-2xl border border-violet-200 bg-violet-50 p-4 text-xs leading-6 text-violet-800 dark:border-violet-900/50 dark:bg-violet-950/20 dark:text-violet-300">
                                Después de cargar el PDF aparecerán sus páginas. Puedes cambiar el orden, girarlas 90°, duplicarlas o eliminarlas de manera independiente.
                            </div>
                        </template>

                        <template x-if="operation === 'security'">
                            <div class="space-y-5">
                                <label class="block">
                                    <span class="text-sm font-black text-slate-800 dark:text-slate-100">Acción</span>
                                    <select x-model="securityMode" class="mt-2 w-full rounded-xl border-slate-300 bg-white text-sm dark:border-neutral-700 dark:bg-neutral-950">
                                        <option value="protect">Agregar contraseña</option>
                                        <option value="unlock">Quitar contraseña</option>
                                    </select>
                                </label>
                                <div x-show="securityMode === 'protect'" x-cloak class="space-y-4">
                                    <label class="block"><span class="text-sm font-black text-slate-800 dark:text-slate-100">Nueva contraseña</span><input type="password" x-model="newPassword" autocomplete="new-password" class="mt-2 w-full rounded-xl border-slate-300 bg-white text-sm dark:border-neutral-700 dark:bg-neutral-950"></label>
                                    <label class="block"><span class="text-sm font-black text-slate-800 dark:text-slate-100">Contraseña de propietario <span class="font-medium text-slate-400">(opcional)</span></span><input type="password" x-model="ownerPassword" autocomplete="new-password" class="mt-2 w-full rounded-xl border-slate-300 bg-white text-sm dark:border-neutral-700 dark:bg-neutral-950"></label>
                                    <div class="grid gap-3 sm:grid-cols-2">
                                        <label class="block"><span class="text-xs font-black text-slate-700 dark:text-slate-200">Impresión</span><select x-model="allowPrint" class="mt-2 w-full rounded-xl border-slate-300 bg-white text-sm dark:border-neutral-700 dark:bg-neutral-950"><option value="full">Completa</option><option value="low">Baja resolución</option><option value="none">No permitir</option></select></label>
                                        <label class="block"><span class="text-xs font-black text-slate-700 dark:text-slate-200">Modificaciones</span><select x-model="allowModify" class="mt-2 w-full rounded-xl border-slate-300 bg-white text-sm dark:border-neutral-700 dark:bg-neutral-950"><option value="none">No permitir</option><option value="assembly">Ensamblar páginas</option><option value="form">Formularios</option><option value="annotate">Anotaciones</option><option value="all">Todas</option></select></label>
                                    </div>
                                    <label class="flex items-center gap-3 rounded-xl border border-slate-200 p-3 text-sm font-bold dark:border-neutral-800"><input type="checkbox" x-model="allowExtract" class="rounded text-sky-600"> Permitir extracción de contenido</label>
                                </div>
                                <p x-show="securityMode === 'unlock'" x-cloak class="rounded-2xl border border-amber-200 bg-amber-50 p-4 text-xs leading-6 text-amber-800 dark:border-amber-900/50 dark:bg-amber-950/20 dark:text-amber-300">Carga el PDF. Cuando se detecte la protección, System PDF te solicitará su contraseña actual.</p>
                            </div>
                        </template>
                    </div>
                </div>

                <div class="rounded-[28px] border border-slate-200 bg-white p-5 shadow-sm dark:border-neutral-800 dark:bg-neutral-900 sm:p-6">
                    <div class="flex items-center justify-between gap-3">
                        <div><p class="text-xs font-black uppercase tracking-[.16em] text-slate-500">Estado del lote</p><h3 class="mt-1 text-lg font-black text-slate-950 dark:text-white" x-text="batch ? statusLabel(batch.status) : 'Sin iniciar'"></h3></div>
                        <span x-show="batch" x-cloak class="rounded-full px-3 py-1 text-xs font-black ring-1" :class="statusClass(batch?.status)" x-text="`${progressPercent()}%`"></span>
                    </div>
                    <div class="mt-4 h-2 overflow-hidden rounded-full bg-slate-100 dark:bg-neutral-800"><div class="h-full rounded-full bg-gradient-to-r from-sky-500 to-emerald-500 transition-all duration-500" :style="`width:${progressPercent()}%`"></div></div>
                    <div x-show="batch" x-cloak class="mt-4 grid grid-cols-2 gap-3 text-center sm:grid-cols-4 xl:grid-cols-2">
                        <div class="rounded-xl bg-slate-50 p-3 dark:bg-neutral-950"><p class="text-xl font-black text-slate-950 dark:text-white" x-text="batch?.total_files || 0"></p><p class="text-[10px] font-bold uppercase text-slate-500">Archivos</p></div>
                        <div class="rounded-xl bg-slate-50 p-3 dark:bg-neutral-950"><p class="text-xl font-black text-slate-950 dark:text-white" x-text="batch?.completed_files || 0"></p><p class="text-[10px] font-bold uppercase text-slate-500">Completados</p></div>
                        <div class="rounded-xl bg-slate-50 p-3 dark:bg-neutral-950"><p class="text-xl font-black text-slate-950 dark:text-white" x-text="formatBytes(batch?.original_bytes)"></p><p class="text-[10px] font-bold uppercase text-slate-500">Original</p></div>
                        <div class="rounded-xl bg-slate-50 p-3 dark:bg-neutral-950"><p class="text-xl font-black text-slate-950 dark:text-white" x-text="formatBytes(batch?.output_bytes)"></p><p class="text-[10px] font-bold uppercase text-slate-500">Resultado</p></div>
                    </div>
                </div>
            </div>

            <div class="space-y-6">
                <div class="overflow-hidden rounded-[28px] border border-slate-200 bg-white shadow-sm dark:border-neutral-800 dark:bg-neutral-900">
                    <div class="flex flex-col gap-3 border-b border-slate-200 p-5 dark:border-neutral-800 sm:flex-row sm:items-center sm:justify-between sm:p-6">
                        <div><p class="text-xs font-black uppercase tracking-[.16em] text-sky-600">Archivos</p><h2 class="mt-1 text-xl font-black text-slate-950 dark:text-white">Carga progresiva y orden</h2></div>
                        <span class="text-xs font-bold text-slate-500" x-text="`${batch?.items?.length || files.length}/${maxFiles} archivos`"></span>
                    </div>

                    <div class="p-5 sm:p-6">
                        <div
                            x-show="!batch"
                            class="relative flex min-h-52 cursor-pointer flex-col items-center justify-center rounded-[24px] border-2 border-dashed p-6 text-center transition"
                            :class="dragActive ? 'border-sky-500 bg-sky-50 dark:bg-sky-950/20' : 'border-slate-300 bg-slate-50 hover:border-sky-400 hover:bg-sky-50/50 dark:border-neutral-700 dark:bg-neutral-950/50'"
                            x-on:dragover.prevent="dragActive = true"
                            x-on:dragleave.prevent="dragActive = false"
                            x-on:drop.prevent="dropFiles($event)"
                            x-on:click="$refs.fileInput.click()"
                        >
                            <input x-ref="fileInput" type="file" class="hidden" multiple :accept="operation === 'combine' ? '.pdf,.jpg,.jpeg,.png,.webp' : '.pdf'" x-on:change="selectFiles($event.target.files)">
                            <div class="flex h-16 w-16 items-center justify-center rounded-3xl bg-white text-sky-600 shadow-sm ring-1 ring-slate-200 dark:bg-neutral-900 dark:ring-neutral-800"><flux:icon name="cloud-arrow-up" class="size-8" /></div>
                            <h3 class="mt-4 text-lg font-black text-slate-950 dark:text-white">Arrastra tus archivos aquí</h3>
                            <p class="mt-2 text-sm text-slate-500">o haz clic para explorar · <span x-text="acceptedText()"></span></p>
                            <p class="mt-1 text-xs font-bold text-slate-400">Máximo {{ $maxFileMb }} MB por archivo</p>
                        </div>

                        <div class="mt-5 space-y-3" x-show="files.length || batch" x-cloak>
                            <template x-for="(item, index) in (batch ? batch.items : files)" :key="item.uuid || item.id">
                                <article
                                    draggable="true"
                                    x-on:dragstart="dragFile(index)"
                                    x-on:dragover.prevent
                                    x-on:drop.prevent="dropFile(index)"
                                    class="group flex flex-col gap-3 rounded-2xl border border-slate-200 bg-white p-3 transition hover:border-sky-200 dark:border-neutral-800 dark:bg-neutral-950 sm:flex-row sm:items-center"
                                >
                                    <div class="flex h-14 w-12 shrink-0 items-center justify-center overflow-hidden rounded-xl bg-slate-100 text-slate-500 dark:bg-neutral-800">
                                        <img x-show="item.first_thumbnail_url" :src="item.first_thumbnail_url" class="h-full w-full object-cover" alt="">
                                        <flux:icon x-show="!item.first_thumbnail_url" name="document-text" class="size-6" />
                                    </div>
                                    <div class="min-w-0 flex-1">
                                        <div class="flex flex-wrap items-center gap-2">
                                            <p class="truncate text-sm font-black text-slate-900 dark:text-white" x-text="item.original_name || item.name"></p>
                                            <span x-show="batch" class="rounded-full px-2 py-0.5 text-[10px] font-black ring-1" :class="statusClass(item.status)" x-text="statusLabel(item.status)"></span>
                                        </div>
                                        <p class="mt-1 text-xs text-slate-500"><span x-text="formatBytes(item.original_size || item.size)"></span><span x-show="item.page_count" x-text="` · ${item.page_count} página(s)`"></span></p>
                                        <p x-show="item.error" class="mt-2 text-xs font-semibold text-red-600" x-text="item.error"></p>
                                        <template x-if="item.warnings?.length"><div class="mt-2 space-y-1"><template x-for="warning in item.warnings"><p class="text-xs font-semibold text-amber-600" x-text="warning"></p></template></div></template>
                                    </div>
                                    <div class="flex shrink-0 items-center gap-2">
                                        <flux:button x-show="batch && item.status === 'inspection_failed'" size="sm" variant="primary" icon="key" x-on:click="setPassword(item)">Contraseña</flux:button>
                                        <flux:button x-show="batch && item.status === 'failed'" size="sm" variant="ghost" icon="arrow-path" x-on:click="retryItem(item)">Reintentar</flux:button>
                                        <a x-show="item.download_url" :href="item.download_url" class="inline-flex items-center gap-1 rounded-lg bg-emerald-600 px-3 py-2 text-xs font-black text-white hover:bg-emerald-700"><span>Descargar</span></a>
                                        <button x-show="!batch" type="button" x-on:click="removeLocal(index)" class="rounded-lg p-2 text-slate-400 hover:bg-red-50 hover:text-red-600"><flux:icon name="trash" class="size-4" /></button>
                                        <span class="cursor-grab rounded-lg p-2 text-slate-400"><flux:icon name="bars-3" class="size-4" /></span>
                                    </div>
                                    <template x-if="item.results?.length"><div class="basis-full border-t border-slate-100 pt-3 dark:border-neutral-800"><div class="flex flex-wrap gap-2"><template x-for="result in item.results"><a :href="result.download_url" class="rounded-lg border border-slate-200 bg-slate-50 px-3 py-2 text-xs font-bold text-slate-700 hover:border-sky-300 dark:border-neutral-700 dark:bg-neutral-900 dark:text-slate-200"><span x-text="result.name"></span> · <span x-text="formatBytes(result.size)"></span></a></template></div></div></template>
                                </article>
                            </template>
                        </div>

                        <div class="mt-5 flex flex-col gap-3 sm:flex-row sm:justify-end">
                            <flux:button x-show="!batch && files.length" icon="x-mark" variant="ghost" x-on:click="files = []">Limpiar</flux:button>
                            <flux:button x-show="!batch && files.length" icon="cloud-arrow-up" variant="primary" x-on:click="createAndUpload()" x-bind:disabled="busy || !{{ $toolsReady ? 'true' : 'false' }} || !@js(auth()->user()->puedePdf('procesar'))">
                                <span x-text="busy ? 'Subiendo…' : 'Crear lote y subir'"></span>
                            </flux:button>
                            <flux:button x-show="batch && batch.status === 'ready'" icon="play" variant="primary" x-on:click="startProcessing()" x-bind:disabled="busy || !canStart()">Procesar lote</flux:button>
                            <flux:button x-show="batch && @js(auth()->user()->puedePdf('eliminar'))" icon="trash" variant="danger" x-on:click="deleteCurrent()" x-bind:disabled="busy">Eliminar lote</flux:button>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <section x-show="batch && operation === 'reorder' && pagePlan.length" x-cloak class="rounded-[28px] border border-slate-200 bg-white p-5 shadow-sm dark:border-neutral-800 dark:bg-neutral-900 sm:p-6">
            <div class="flex flex-col gap-3 sm:flex-row sm:items-end sm:justify-between"><div><p class="text-xs font-black uppercase tracking-[.16em] text-violet-600">Editor visual</p><h2 class="mt-1 text-2xl font-black text-slate-950 dark:text-white">Ordena las páginas</h2><p class="mt-2 text-sm text-slate-500">Arrastra las tarjetas. Cada página conserva su propia rotación y puede duplicarse.</p></div><span class="rounded-full bg-violet-50 px-3 py-1 text-xs font-black text-violet-700" x-text="`${pagePlan.length} página(s)`"></span></div>
            <div class="mt-6 grid grid-cols-2 gap-4 sm:grid-cols-3 md:grid-cols-4 lg:grid-cols-6 xl:grid-cols-8">
                <template x-for="(page, index) in pagePlan" :key="page.key">
                    <article draggable="true" x-on:dragstart="dragPage(index)" x-on:dragover.prevent x-on:drop.prevent="dropPage(index)" class="group overflow-hidden rounded-2xl border border-slate-200 bg-slate-50 shadow-sm dark:border-neutral-800 dark:bg-neutral-950">
                        <div class="relative aspect-[.72] overflow-hidden bg-white dark:bg-neutral-900"><img x-show="page.thumbnail_url" :src="page.thumbnail_url" class="h-full w-full object-contain transition" :style="`transform:rotate(${page.rotation}deg)`" alt=""><div x-show="!page.thumbnail_url" class="flex h-full items-center justify-center text-3xl font-black text-slate-300" x-text="page.source"></div><span class="absolute left-2 top-2 rounded-full bg-slate-950/80 px-2 py-1 text-[10px] font-black text-white" x-text="index + 1"></span></div>
                        <div class="grid grid-cols-3 border-t border-slate-200 dark:border-neutral-800"><button type="button" class="p-2 text-slate-500 hover:bg-sky-50 hover:text-sky-600" x-on:click="rotatePage(index)" title="Girar"><flux:icon name="arrow-path" class="mx-auto size-4" /></button><button type="button" class="p-2 text-slate-500 hover:bg-violet-50 hover:text-violet-600" x-on:click="duplicatePage(index)" title="Duplicar"><flux:icon name="document-duplicate" class="mx-auto size-4" /></button><button type="button" class="p-2 text-slate-500 hover:bg-red-50 hover:text-red-600" x-on:click="deletePage(index)" title="Eliminar"><flux:icon name="trash" class="mx-auto size-4" /></button></div>
                    </article>
                </template>
            </div>
        </section>

        <section x-show="batch && operation === 'split' && splitMode === 'selected' && batch.items?.[0]?.pages?.length" x-cloak class="rounded-[28px] border border-slate-200 bg-white p-5 shadow-sm dark:border-neutral-800 dark:bg-neutral-900 sm:p-6">
            <div class="flex flex-col gap-3 sm:flex-row sm:items-end sm:justify-between"><div><p class="text-xs font-black uppercase tracking-[.16em] text-emerald-600">Selección visual</p><h2 class="mt-1 text-2xl font-black text-slate-950 dark:text-white">Elige las páginas a extraer</h2></div><div class="flex gap-2"><flux:button size="sm" variant="ghost" x-on:click="selectAllPages()">Todas</flux:button><flux:button size="sm" variant="ghost" x-on:click="clearSelectedPages()">Ninguna</flux:button></div></div>
            <div class="mt-6 grid grid-cols-2 gap-4 sm:grid-cols-3 md:grid-cols-5 lg:grid-cols-7 xl:grid-cols-9">
                <template x-for="page in batch.items[0].pages" :key="page.number"><button type="button" x-on:click="toggleSelectedPage(page.number)" class="relative overflow-hidden rounded-2xl border-2 bg-slate-50 transition" :class="selectedPages.includes(page.number) ? 'border-emerald-500 ring-4 ring-emerald-100' : 'border-slate-200 hover:border-emerald-300'"><div class="aspect-[.72] bg-white"><img x-show="page.thumbnail_url" :src="page.thumbnail_url" class="h-full w-full object-contain" alt=""><div x-show="!page.thumbnail_url" class="flex h-full items-center justify-center text-3xl font-black text-slate-300" x-text="page.number"></div></div><span class="absolute bottom-2 left-1/2 -translate-x-1/2 rounded-full bg-slate-950/80 px-2 py-1 text-[10px] font-black text-white" x-text="`Página ${page.number}`"></span><span x-show="selectedPages.includes(page.number)" class="absolute right-2 top-2 flex h-6 w-6 items-center justify-center rounded-full bg-emerald-500 text-white">✓</span></button></template>
            </div>
        </section>

        <section x-show="batch && isTerminal(batch.status)" x-cloak class="rounded-[28px] border border-emerald-200 bg-gradient-to-br from-emerald-50 to-white p-5 shadow-sm dark:border-emerald-900/50 dark:from-emerald-950/20 dark:to-neutral-900 sm:p-6">
            <div class="flex flex-col gap-5 lg:flex-row lg:items-center lg:justify-between">
                <div><p class="text-xs font-black uppercase tracking-[.16em] text-emerald-600">Resultados</p><h2 class="mt-1 text-2xl font-black text-slate-950 dark:text-white" x-text="statusLabel(batch.status)"></h2><p class="mt-2 text-sm text-slate-600 dark:text-slate-300" x-text="summaryText(batch)"></p></div>
                <div class="flex flex-wrap gap-3"><a x-show="batch.download_output_url" :href="batch.download_output_url" class="inline-flex items-center rounded-xl bg-emerald-600 px-4 py-3 text-sm font-black text-white shadow-sm hover:bg-emerald-700">Descargar PDF</a><template x-for="part in batch.download_zip_parts"><a :href="part.url" class="inline-flex items-center rounded-xl border border-emerald-200 bg-white px-4 py-3 text-sm font-black text-emerald-700 shadow-sm hover:bg-emerald-50 dark:border-emerald-900 dark:bg-neutral-900 dark:text-emerald-300"><span x-text="batch.download_zip_parts.length > 1 ? `Descargar ZIP ${part.number}` : 'Descargar todo en ZIP'"></span></a></template><flux:button icon="plus" variant="primary" x-on:click="newWorkspace()">Otro proceso</flux:button></div>
            </div>

            <div x-show="batch.zip_entries?.length > 1" class="mt-6 rounded-2xl border border-emerald-200 bg-white/80 p-4 dark:border-emerald-900/50 dark:bg-neutral-900/70">
                <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
                    <div><h3 class="text-sm font-black text-slate-900 dark:text-white">Exportación personalizada</h3><p class="mt-1 text-xs text-slate-500">Selecciona exactamente qué resultados incluir en el ZIP.</p></div>
                    <div class="flex flex-wrap justify-end gap-2">
                        <template x-for="part in selectedZipParts()" :key="part.number">
                            <a :href="part.url" class="rounded-xl bg-slate-950 px-4 py-2.5 text-xs font-black text-white hover:bg-slate-800 dark:bg-white dark:text-slate-950">
                                <span x-text="part.total > 1 ? `Descargar selección ${part.number}/${part.total}` : 'Descargar seleccionados'"></span>
                            </a>
                        </template>
                        <span x-show="selectedZipEntries.length === 0" class="rounded-xl bg-slate-200 px-4 py-2.5 text-xs font-black text-slate-500 dark:bg-neutral-800">Selecciona resultados</span>
                    </div>
                </div>
                <div class="mt-4 grid gap-2 sm:grid-cols-2 lg:grid-cols-3">
                    <template x-for="entry in batch.zip_entries" :key="entry.key">
                        <label class="flex cursor-pointer items-center gap-3 rounded-xl border border-slate-200 p-3 dark:border-neutral-800">
                            <input type="checkbox" :checked="selectedZipEntries.includes(entry.key)" x-on:change="toggleZipEntry(entry.key)" class="rounded text-emerald-600">
                            <span class="min-w-0 flex-1"><span class="block truncate text-xs font-black text-slate-800 dark:text-slate-100" x-text="entry.name"></span><span class="text-[10px] text-slate-500" x-text="formatBytes(entry.size)"></span></span>
                        </label>
                    </template>
                </div>
            </div>
        </section>

        @if (auth()->user()->puedePdf('administrar'))
            <section class="overflow-hidden rounded-[28px] border border-slate-200 bg-white shadow-sm dark:border-neutral-800 dark:bg-neutral-900">
                <div class="flex items-center justify-between border-b border-slate-200 p-5 dark:border-neutral-800 sm:p-6">
                    <div><p class="text-xs font-black uppercase tracking-[.16em] text-violet-600">Administración</p><h2 class="mt-1 text-xl font-black text-slate-950 dark:text-white">Permisos de System PDF</h2><p class="mt-1 text-xs text-slate-500">Controla quién puede ver, procesar, descargar, eliminar o administrar el módulo.</p></div>
                    <flux:button size="sm" variant="ghost" icon="arrow-path" x-on:click="loadPermissions()">Actualizar</flux:button>
                </div>
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-slate-200 text-sm dark:divide-neutral-800">
                        <thead class="bg-slate-50 text-left text-[10px] font-black uppercase tracking-wider text-slate-500 dark:bg-neutral-950"><tr><th class="px-5 py-3">Usuario</th><th class="px-3 py-3 text-center">Ver</th><th class="px-3 py-3 text-center">Procesar</th><th class="px-3 py-3 text-center">Descargar</th><th class="px-3 py-3 text-center">Eliminar</th><th class="px-3 py-3 text-center">Administrar</th><th class="px-5 py-3 text-right">Guardar</th></tr></thead>
                        <tbody class="divide-y divide-slate-100 dark:divide-neutral-800">
                            <template x-for="user in permissions" :key="user.id">
                                <tr><td class="px-5 py-4"><p class="font-black text-slate-900 dark:text-white" x-text="user.name"></p><p class="text-xs text-slate-500" x-text="user.email"></p></td><template x-for="permission in ['ver','procesar','descargar','eliminar','administrar']"><td class="px-3 py-4 text-center"><input type="checkbox" x-model="user.permissions[permission]" :disabled="user.is_primary_admin" class="rounded text-violet-600"></td></template><td class="px-5 py-4 text-right"><button type="button" x-on:click="updatePermission(user)" :disabled="user.is_primary_admin || user.saving" class="rounded-lg bg-violet-600 px-3 py-2 text-xs font-black text-white disabled:cursor-not-allowed disabled:opacity-50"><span x-text="user.saving ? 'Guardando…' : (user.is_primary_admin ? 'Administrador' : 'Guardar')"></span></button></td></tr>
                            </template>
                            <tr x-show="permissions.length === 0"><td colspan="7" class="px-5 py-8 text-center text-sm text-slate-500">No hay usuarios disponibles.</td></tr>
                        </tbody>
                    </table>
                </div>
            </section>
        @endif

        <section class="overflow-hidden rounded-[28px] border border-slate-200 bg-white shadow-sm dark:border-neutral-800 dark:bg-neutral-900">
            <div class="flex items-center justify-between border-b border-slate-200 p-5 dark:border-neutral-800 sm:p-6"><div><p class="text-xs font-black uppercase tracking-[.16em] text-slate-500">Historial</p><h2 class="mt-1 text-xl font-black text-slate-950 dark:text-white">Operaciones recientes</h2></div><flux:button size="sm" variant="ghost" icon="arrow-path" x-on:click="loadHistory()">Actualizar</flux:button></div>
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-slate-200 text-sm dark:divide-neutral-800">
                    <thead class="bg-slate-50 text-left text-[11px] font-black uppercase tracking-wider text-slate-500 dark:bg-neutral-950"><tr><th class="px-5 py-3">Operación</th><th class="px-5 py-3">Estado</th><th class="px-5 py-3">Archivos</th><th class="px-5 py-3">Tamaño</th><th class="px-5 py-3">Fecha</th><th class="px-5 py-3 text-right">Acciones</th></tr></thead>
                    <tbody class="divide-y divide-slate-100 dark:divide-neutral-800">
                        <template x-for="record in history" :key="record.uuid"><tr class="hover:bg-slate-50/70 dark:hover:bg-neutral-950/50"><td class="px-5 py-4 font-black text-slate-900 dark:text-white" x-text="record.operation_label"></td><td class="px-5 py-4"><span class="rounded-full px-2.5 py-1 text-[10px] font-black ring-1" :class="statusClass(record.status)" x-text="statusLabel(record.status)"></span></td><td class="px-5 py-4 text-slate-600 dark:text-slate-300" x-text="record.total_files"></td><td class="px-5 py-4 text-slate-600 dark:text-slate-300"><span x-text="formatBytes(record.original_bytes)"></span><span x-show="record.output_bytes"> → </span><span x-show="record.output_bytes" x-text="formatBytes(record.output_bytes)"></span></td><td class="px-5 py-4 text-slate-500" x-text="formatDate(record.created_at)"></td><td class="px-5 py-4 text-right"><button type="button" x-on:click="openHistory(record)" class="rounded-lg border border-slate-200 px-3 py-2 text-xs font-black text-slate-700 hover:border-sky-300 hover:text-sky-700 dark:border-neutral-700 dark:text-slate-200">Abrir</button></td></tr></template>
                        <tr x-show="history.length === 0"><td colspan="6" class="px-5 py-10 text-center text-sm text-slate-500">Todavía no hay operaciones registradas.</td></tr>
                    </tbody>
                </table>
            </div>
        </section>
    </div>
</x-layouts.app>
