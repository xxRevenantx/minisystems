<div x-data="{
    dragging: false,
    fileName: '',
    uploadProgress: 0,
    uploading: false,
    search: '',
    status: 'todos',

    openFilePicker() {
        this.$refs.designInput.click();
    },

    fileSelected(event) {
        const file = event.target.files?.[0];
        this.fileName = file?.name ?? '';
    },

    dropFile(event) {
        this.dragging = false;
        const file = event.dataTransfer?.files?.[0];

        if (!file) return;

        const allowedTypes = ['image/jpeg', 'image/png'];
        const maxSize = 5 * 1024 * 1024;

        if (!allowedTypes.includes(file.type)) {
            Swal.fire({
                icon: 'error',
                title: 'Formato no permitido',
                text: 'Selecciona una imagen JPG, JPEG o PNG.',
                confirmButtonColor: '#006492',
            });
            return;
        }

        if (file.size > maxSize) {
            Swal.fire({
                icon: 'error',
                title: 'Archivo demasiado grande',
                text: 'La imagen no debe superar los 5 MB.',
                confirmButtonColor: '#006492',
            });
            return;
        }

        const transfer = new DataTransfer();
        transfer.items.add(file);
        this.$refs.designInput.files = transfer.files;
        this.$refs.designInput.dispatchEvent(new Event('change', { bubbles: true }));
        this.fileName = file.name;
    },

    confirmarEliminacion(id, nombre) {
        Swal.fire({
            title: '¿Eliminar este diseño?',
            html: `<span>Se procesará <strong>${this.escapeHtml(nombre)}</strong>.<br>Si está en uso, se desactivará para conservar el historial.</span>`,
            icon: 'warning',
            showCancelButton: true,
            reverseButtons: true,
            focusCancel: true,
            confirmButtonText: 'Sí, continuar',
            cancelButtonText: 'Cancelar',
            confirmButtonColor: '#dc2626',
            cancelButtonColor: '#52525b',
        }).then((result) => {
            if (result.isConfirmed) {
                this.$wire.eliminarImagenReconocimiento(id);
            }
        });
    },

    escapeHtml(value) {
        const element = document.createElement('div');
        element.textContent = value ?? '';
        return element.innerHTML;
    },

    cardVisible(element) {
        const matchesSearch = element.dataset.search.includes(this.search.toLowerCase().trim());
        const matchesStatus = this.status === 'todos' || element.dataset.status === this.status;
        return matchesSearch && matchesStatus;
    },
}" x-on:livewire-upload-start.window="uploading = true; uploadProgress = 0"
    x-on:livewire-upload-progress.window="uploadProgress = $event.detail.progress"
    x-on:livewire-upload-finish.window="uploading = false; uploadProgress = 100"
    x-on:livewire-upload-error.window="uploading = false; uploadProgress = 0"
    x-on:limpiar-dropzone.window="fileName = ''; uploadProgress = 0; if ($refs.designInput) $refs.designInput.value = ''"
    class="space-y-6">
    {{-- Resumen --}}
    <div class="grid gap-4 sm:grid-cols-3">
        <div
            class="relative overflow-hidden rounded-2xl border border-neutral-200 bg-white p-5 shadow-sm dark:border-neutral-700 dark:bg-neutral-900">
            <div class="absolute -right-5 -top-5 h-24 w-24 rounded-full bg-[#006492]/10"></div>
            <div class="relative flex items-center gap-4">
                <div
                    class="flex size-11 items-center justify-center rounded-xl bg-[#006492]/10 text-[#006492] dark:bg-[#006492]/20 dark:text-sky-300">
                    <svg class="size-6" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8"
                            d="m2.25 15.75 5.159-5.159a2.25 2.25 0 0 1 3.182 0l5.159 5.159m-1.5-1.5 1.409-1.409a2.25 2.25 0 0 1 3.182 0l2.909 2.909m-18 3.75h16.5A1.5 1.5 0 0 0 21.75 18V6A1.5 1.5 0 0 0 20.25 4.5H3.75A1.5 1.5 0 0 0 2.25 6v12A1.5 1.5 0 0 0 3.75 19.5Zm10.5-11.25h.008v.008h-.008V8.25Z" />
                    </svg>
                </div>
                <div>
                    <p class="text-sm text-neutral-500 dark:text-neutral-400">Diseños registrados</p>
                    <p class="text-2xl font-bold text-neutral-950 dark:text-white">{{ $totalDisenos }}</p>
                </div>
            </div>
        </div>

        <div
            class="relative overflow-hidden rounded-2xl border border-neutral-200 bg-white p-5 shadow-sm dark:border-neutral-700 dark:bg-neutral-900">
            <div class="absolute -right-5 -top-5 h-24 w-24 rounded-full bg-[#88AC2E]/[0.12]"></div>
            <div class="relative flex items-center gap-4">
                <div
                    class="flex size-11 items-center justify-center rounded-xl bg-[#88AC2E]/[0.15] text-[#66871c] dark:bg-[#88AC2E]/20 dark:text-lime-300">
                    <svg class="size-6" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8"
                            d="M9 12.75 11.25 15 15 9.75M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z" />
                    </svg>
                </div>
                <div>
                    <p class="text-sm text-neutral-500 dark:text-neutral-400">Diseños activos</p>
                    <p class="text-2xl font-bold text-neutral-950 dark:text-white">{{ $totalActivos }}</p>
                </div>
            </div>
        </div>

        <div
            class="relative overflow-hidden rounded-2xl border border-neutral-200 bg-white p-5 shadow-sm dark:border-neutral-700 dark:bg-neutral-900">
            <div class="absolute -right-5 -top-5 h-24 w-24 rounded-full bg-neutral-500/10"></div>
            <div class="relative flex items-center gap-4">
                <div
                    class="flex size-11 items-center justify-center rounded-xl bg-neutral-100 text-neutral-600 dark:bg-neutral-800 dark:text-neutral-300">
                    <svg class="size-6" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8"
                            d="M18.364 18.364A9 9 0 0 0 5.636 5.636m12.728 12.728A9 9 0 0 1 5.636 5.636m12.728 12.728L5.636 5.636" />
                    </svg>
                </div>
                <div>
                    <p class="text-sm text-neutral-500 dark:text-neutral-400">Diseños inactivos</p>
                    <p class="text-2xl font-bold text-neutral-950 dark:text-white">{{ $totalInactivos }}</p>
                </div>
            </div>
        </div>
    </div>

    {{-- Carga drag and drop --}}
    <form wire:submit="guardarImagenReconocimiento"
        class="overflow-hidden rounded-3xl border border-neutral-200 bg-white shadow-sm dark:border-neutral-700 dark:bg-neutral-900">
        <div
            class="border-b border-neutral-200 bg-gradient-to-r from-[#006492]/[0.08] via-white to-[#88AC2E]/10 px-6 py-5 dark:border-neutral-700 dark:from-[#006492]/[0.15] dark:via-neutral-900 dark:to-[#88AC2E]/10">
            <div class="flex flex-wrap items-center justify-between gap-4">
                <div class="flex items-start gap-3">
                    <div
                        class="mt-0.5 flex size-10 shrink-0 items-center justify-center rounded-xl bg-[#006492] text-white shadow-sm">
                        <svg class="size-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8"
                                d="M12 16.5V9.75m0 0 3 3m-3-3-3 3M6.75 19.5h10.5A2.25 2.25 0 0 0 19.5 17.25V6.75A2.25 2.25 0 0 0 17.25 4.5H6.75A2.25 2.25 0 0 0 4.5 6.75v10.5a2.25 2.25 0 0 0 2.25 2.25Z" />
                        </svg>
                    </div>
                    <div>
                        <flux:heading size="lg">Agregar nuevo diseño</flux:heading>
                        <flux:text class="mt-1">Arrastra el fondo del reconocimiento o selecciónalo desde tu equipo.
                        </flux:text>
                    </div>
                </div>

                <flux:button href="{{ route('reconocimiento', ['tab' => 'plantillas']) }}" variant="filled"
                    icon="adjustments-horizontal">
                    Configurar posiciones
                </flux:button>
            </div>
        </div>

        <div class="grid gap-6 p-6 lg:grid-cols-[1.25fr_.75fr]">
            <div>
                <input x-ref="designInput" type="file" wire:model="reconocimiento" x-on:change="fileSelected($event)"
                    accept="image/png,image/jpeg" class="sr-only" id="reconocimiento-design-input">

                <div role="button" tabindex="0" x-on:click="openFilePicker()"
                    x-on:keydown.enter.prevent="openFilePicker()" x-on:keydown.space.prevent="openFilePicker()"
                    x-on:dragenter.prevent="dragging = true" x-on:dragover.prevent="dragging = true"
                    x-on:dragleave.prevent="dragging = false" x-on:drop.prevent="dropFile($event)"
                    x-bind:class="dragging
                        ?
                        'border-[#006492] bg-[#006492]/[0.07] ring-4 ring-[#006492]/10 dark:bg-[#006492]/[0.12]' :
                        'border-neutral-300 bg-neutral-50/70 hover:border-[#006492]/70 hover:bg-[#006492]/[.035] dark:border-neutral-700 dark:bg-neutral-950/40'"
                    class="group relative flex min-h-64 cursor-pointer flex-col items-center justify-center overflow-hidden rounded-2xl border-2 border-dashed px-6 py-8 text-center transition duration-200">
                    <div class="pointer-events-none absolute inset-0 opacity-[.035] dark:opacity-[.07]"
                        style="background-image: radial-gradient(circle at 1px 1px, currentColor 1px, transparent 0); background-size: 20px 20px;">
                    </div>

                    @if ($reconocimiento)
                        <div class="relative z-10 w-full max-w-lg">
                            <div
                                class="overflow-hidden rounded-xl border border-white/80 bg-white shadow-lg dark:border-neutral-700 dark:bg-neutral-900">
                                <img src="{{ $reconocimiento->temporaryUrl() }}"
                                    class="h-44 w-full object-contain bg-neutral-100 dark:bg-neutral-950"
                                    alt="Vista previa del diseño seleccionado">
                                <div class="flex items-center justify-between gap-3 px-4 py-3 text-left">
                                    <div class="min-w-0">
                                        <p class="truncate text-sm font-semibold text-neutral-900 dark:text-white">
                                            {{ $reconocimiento->getClientOriginalName() }}
                                        </p>
                                        <p class="text-xs text-neutral-500">Vista previa lista para guardar</p>
                                    </div>
                                    <span
                                        class="shrink-0 rounded-full bg-[#88AC2E]/[0.15] px-2.5 py-1 text-xs font-semibold text-[#66871c] dark:text-lime-300">
                                        Imagen válida
                                    </span>
                                </div>
                            </div>
                            <p class="mt-3 text-xs font-medium text-[#006492] dark:text-sky-300">Haz clic o arrastra
                                otra imagen para reemplazarla</p>
                        </div>
                    @else
                        <div class="relative z-10 flex max-w-md flex-col items-center">
                            <div
                                class="mb-4 flex size-16 items-center justify-center rounded-2xl bg-white text-[#006492] shadow-sm ring-1 ring-neutral-200 transition group-hover:-translate-y-1 group-hover:shadow-md dark:bg-neutral-900 dark:ring-neutral-700">
                                <svg class="size-8" fill="none" stroke="currentColor" viewBox="0 0 24 24"
                                    aria-hidden="true">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.6"
                                        d="m2.25 15.75 5.159-5.159a2.25 2.25 0 0 1 3.182 0l5.159 5.159m-1.5-1.5 1.409-1.409a2.25 2.25 0 0 1 3.182 0l2.909 2.909m-18 3.75h16.5A1.5 1.5 0 0 0 21.75 18V6A1.5 1.5 0 0 0 20.25 4.5H3.75A1.5 1.5 0 0 0 2.25 6v12A1.5 1.5 0 0 0 3.75 19.5Zm10.5-11.25h.008v.008h-.008V8.25Z" />
                                </svg>
                            </div>
                            <p class="text-base font-bold text-neutral-900 dark:text-white">
                                <span class="text-[#006492] dark:text-sky-300">Selecciona una imagen</span> o
                                arrástrala aquí
                            </p>
                            <p class="mt-2 text-sm text-neutral-500 dark:text-neutral-400">JPG o PNG · máximo 5 MB ·
                                carta horizontal o vertical</p>
                            <span
                                class="mt-5 inline-flex items-center rounded-xl bg-[#006492] px-4 py-2 text-sm font-semibold text-white shadow-sm transition group-hover:bg-[#00557b]">
                                Explorar archivos
                            </span>
                        </div>
                    @endif
                </div>

                @error('reconocimiento')
                    <p class="mt-2 flex items-center gap-1.5 text-sm font-medium text-red-600 dark:text-red-400">
                        <svg class="size-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M12 9v3.75m9-.75a9 9 0 1 1-18 0 9 9 0 0 1 18 0Zm-9 3.75h.008v.008H12v-.008Z" />
                        </svg>
                        {{ $message }}
                    </p>
                @enderror

                <div x-show="uploading" x-cloak class="mt-4 rounded-xl border border-[#006492]/20 bg-[#006492]/5 p-3">
                    <div
                        class="mb-2 flex items-center justify-between text-xs font-semibold text-[#006492] dark:text-sky-300">
                        <span>Subiendo imagen…</span>
                        <span x-text="uploadProgress + '%'">0%</span>
                    </div>
                    <div class="h-2 overflow-hidden rounded-full bg-[#006492]/[0.15]">
                        <div class="h-full rounded-full bg-[#006492] transition-all duration-200"
                            x-bind:style="`width: ${uploadProgress}%`"></div>
                    </div>
                </div>
            </div>

            <div
                class="flex flex-col justify-between rounded-2xl border border-neutral-200 bg-neutral-50/70 p-5 dark:border-neutral-700 dark:bg-neutral-950/35">
                <div>
                    <flux:heading size="md">Información del diseño</flux:heading>
                    <flux:text class="mt-1">Usa un nombre claro para localizarlo rápidamente.</flux:text>

                    <div class="mt-5">
                        <flux:input wire:model="descripcion" label="Nombre o descripción" badge="Obligatorio"
                            placeholder="Ej. Reconocimiento Primaria 2026" />
                    </div>

                    <div
                        class="mt-5 rounded-xl border border-[#88AC2E]/25 bg-[#88AC2E]/[0.08] p-4 text-sm text-neutral-700 dark:text-neutral-300">
                        <div class="flex gap-3">
                            <svg class="mt-0.5 size-5 shrink-0 text-[#6f901f] dark:text-lime-300" fill="none"
                                stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8"
                                    d="M11.25 11.25 12 10.5m0 0 .75.75M12 10.5v4.5m9-3a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z" />
                            </svg>
                            <p>La orientación se detectará automáticamente al guardar la imagen.</p>
                        </div>
                    </div>
                </div>

                <flux:button type="submit" variant="primary" icon="cloud-arrow-up" wire:loading.attr="disabled"
                    wire:target="reconocimiento,guardarImagenReconocimiento"
                    class="mt-6 w-full justify-center !bg-[#006492] hover:!bg-[#00557b] disabled:opacity-50">
                    <span wire:loading.remove wire:target="guardarImagenReconocimiento">Guardar diseño</span>
                    <span wire:loading wire:target="guardarImagenReconocimiento">Guardando diseño…</span>
                </flux:button>
            </div>
        </div>
    </form>

    {{-- Galería --}}
    <section
        class="overflow-hidden rounded-3xl border border-neutral-200 bg-white shadow-sm dark:border-neutral-700 dark:bg-neutral-900">
        <div class="border-b border-neutral-200 px-6 py-5 dark:border-neutral-700">
            <div class="flex flex-col gap-4 xl:flex-row xl:items-end xl:justify-between">
                <div>
                    <flux:heading size="lg">Biblioteca de diseños</flux:heading>
                    <flux:text class="mt-1">Edita, consulta o elimina los fondos disponibles.</flux:text>
                </div>

                <div class="grid gap-3 sm:grid-cols-[minmax(240px,1fr)_180px]">
                    <label class="relative block">
                        <span class="sr-only">Buscar diseño</span>
                        <svg class="pointer-events-none absolute left-3 top-1/2 size-4 -translate-y-1/2 text-neutral-400"
                            fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="m21 21-4.35-4.35m1.1-5.4a6.5 6.5 0 1 1-13 0 6.5 6.5 0 0 1 13 0Z" />
                        </svg>
                        <input x-model.debounce.200ms="search" type="search" placeholder="Buscar por nombre…"
                            class="h-10 w-full rounded-xl border border-neutral-300 bg-white pl-9 pr-3 text-sm outline-none transition focus:border-[#006492] focus:ring-2 focus:ring-[#006492]/[0.15] dark:border-neutral-700 dark:bg-neutral-950">
                    </label>

                    <select x-model="status"
                        class="h-10 rounded-xl border border-neutral-300 bg-white px-3 text-sm outline-none transition focus:border-[#006492] focus:ring-2 focus:ring-[#006492]/[0.15] dark:border-neutral-700 dark:bg-neutral-950">
                        <option value="todos">Todos los estados</option>
                        <option value="activo">Activos</option>
                        <option value="inactivo">Inactivos</option>
                    </select>
                </div>
            </div>
        </div>

        <div class="p-6">
            <div class="grid gap-5 sm:grid-cols-2 xl:grid-cols-3 2xl:grid-cols-4">
                @forelse($imagenes as $imagen)
                    @php
                        $nombreImagen = $imagen->nombre ?: ($imagen->descripcion ?: 'Diseño ' . $imagen->id);
                        $estadoImagen = $imagen->activo ? 'activo' : 'inactivo';
                    @endphp

                    <article wire:key="reconocimiento-imagen-{{ $imagen->id }}"
                        data-search="{{ mb_strtolower($nombreImagen . ' ' . ($imagen->orientacion ?? 'horizontal')) }}"
                        data-status="{{ $estadoImagen }}" x-show="cardVisible($el)"
                        x-transition.opacity.duration.150ms
                        class="group overflow-hidden rounded-2xl border border-neutral-200 bg-white shadow-sm transition duration-200 hover:-translate-y-0.5 hover:border-[#006492]/[0.35] hover:shadow-lg dark:border-neutral-700 dark:bg-neutral-900">
                        <button type="button" wire:click="editarImagen({{ $imagen->id }})"
                            class="relative block h-52 w-full overflow-hidden bg-neutral-100 text-left dark:bg-neutral-950"
                            aria-label="Editar {{ $nombreImagen }}">
                            <img src="{{ asset('storage/imagenesReconocimientos/' . $imagen->imagen) }}"
                                class="h-full w-full object-contain transition duration-300 group-hover:scale-[1.025]"
                                alt="{{ $nombreImagen }}">

                            <div
                                class="absolute inset-0 bg-gradient-to-t from-black/45 via-transparent to-transparent opacity-0 transition group-hover:opacity-100">
                            </div>
                            <span
                                class="absolute bottom-3 left-3 translate-y-2 rounded-lg bg-white/95 px-3 py-1.5 text-xs font-bold text-[#006492] opacity-0 shadow-sm transition group-hover:translate-y-0 group-hover:opacity-100">
                                Clic para editar
                            </span>

                            <span
                                class="absolute right-3 top-3 rounded-full border border-white/60 bg-white/90 px-2.5 py-1 text-[11px] font-bold uppercase tracking-wide text-neutral-700 shadow-sm backdrop-blur dark:border-neutral-600 dark:bg-neutral-900/90 dark:text-neutral-200">
                                {{ ucfirst($imagen->orientacion ?? 'horizontal') }}
                            </span>
                        </button>

                        <div class="p-4">
                            <div class="flex items-start justify-between gap-3">
                                <div class="min-w-0">
                                    <h3 class="truncate font-bold text-neutral-950 dark:text-white"
                                        title="{{ $nombreImagen }}">
                                        {{ $nombreImagen }}
                                    </h3>
                                    <p class="mt-1 text-xs text-neutral-500">Diseño
                                        #{{ str_pad((string) $imagen->id, 3, '0', STR_PAD_LEFT) }}</p>
                                </div>

                                <span
                                    class="inline-flex shrink-0 items-center gap-1.5 rounded-full px-2.5 py-1 text-xs font-semibold {{ $imagen->activo ? 'bg-emerald-50 text-emerald-700 ring-1 ring-emerald-600/15 dark:bg-emerald-500/10 dark:text-emerald-300' : 'bg-neutral-100 text-neutral-600 ring-1 ring-neutral-500/15 dark:bg-neutral-800 dark:text-neutral-300' }}">
                                    <span
                                        class="size-1.5 rounded-full {{ $imagen->activo ? 'bg-emerald-500' : 'bg-neutral-400' }}"></span>
                                    {{ $imagen->activo ? 'Activo' : 'Inactivo' }}
                                </span>
                            </div>

                            <div
                                class="mt-4 grid grid-cols-2 gap-2 border-t border-neutral-100 pt-4 dark:border-neutral-800">
                                <button type="button" wire:click="editarImagen({{ $imagen->id }})"
                                    wire:loading.attr="disabled" wire:target="editarImagen({{ $imagen->id }})"
                                    class="inline-flex h-9 items-center justify-center gap-2 rounded-xl bg-[#006492]/10 px-3 text-sm font-semibold text-[#006492] transition hover:bg-[#006492] hover:text-white disabled:opacity-50 dark:bg-[#006492]/20 dark:text-sky-300 dark:hover:bg-[#006492] dark:hover:text-white">
                                    <svg class="size-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8"
                                            d="m16.862 4.487 1.687-1.688a1.875 1.875 0 1 1 2.652 2.652L10.582 16.07a4.5 4.5 0 0 1-1.897 1.13l-2.685.8.8-2.685a4.5 4.5 0 0 1 1.13-1.897l8.932-8.931Zm0 0L19.5 7.125M18 14.25v4.125A1.875 1.875 0 0 1 16.125 20.25H5.625A1.875 1.875 0 0 1 3.75 18.375V7.875A1.875 1.875 0 0 1 5.625 6H9.75" />
                                    </svg>
                                    Editar
                                </button>

                                <button type="button" data-name="{{ $nombreImagen }}"
                                    x-on:click="confirmarEliminacion({{ $imagen->id }}, $el.dataset.name)"
                                    class="inline-flex h-9 items-center justify-center gap-2 rounded-xl bg-red-50 px-3 text-sm font-semibold text-red-600 transition hover:bg-red-600 hover:text-white dark:bg-red-500/10 dark:text-red-300 dark:hover:bg-red-600 dark:hover:text-white">
                                    <svg class="size-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8"
                                            d="m14.74 9-.346 9m-4.788 0L9.26 9m9.968-3.21c.342.052.682.107 1.022.166m-1.022-.165L18.16 19.673A2.25 2.25 0 0 1 15.916 21.75H8.084a2.25 2.25 0 0 1-2.244-2.077L4.772 5.79m14.456 0a48.108 48.108 0 0 0-3.478-.397m-12 .562c.34-.059.68-.114 1.022-.165m0 0a48.11 48.11 0 0 1 3.478-.397m7.5 0V4.477c0-1.18-.91-2.164-2.09-2.201a51.964 51.964 0 0 0-3.32 0c-1.18.037-2.09 1.022-2.09 2.201v.916m7.5 0a48.667 48.667 0 0 0-7.5 0" />
                                    </svg>
                                    Eliminar
                                </button>
                            </div>
                        </div>
                    </article>
                @empty
                    <div
                        class="col-span-full rounded-2xl border-2 border-dashed border-neutral-300 px-6 py-16 text-center dark:border-neutral-700">
                        <div
                            class="mx-auto flex size-14 items-center justify-center rounded-2xl bg-neutral-100 text-neutral-500 dark:bg-neutral-800 dark:text-neutral-300">
                            <svg class="size-7" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8"
                                    d="m2.25 15.75 5.159-5.159a2.25 2.25 0 0 1 3.182 0l5.159 5.159m-1.5-1.5 1.409-1.409a2.25 2.25 0 0 1 3.182 0l2.909 2.909m-18 3.75h16.5A1.5 1.5 0 0 0 21.75 18V6A1.5 1.5 0 0 0 20.25 4.5H3.75A1.5 1.5 0 0 0 2.25 6v12A1.5 1.5 0 0 0 3.75 19.5Z" />
                            </svg>
                        </div>
                        <flux:heading size="md" class="mt-4">Aún no hay diseños</flux:heading>
                        <flux:text class="mt-1">Sube el primer fondo de reconocimiento desde el área superior.
                        </flux:text>
                    </div>
                @endforelse
            </div>

            @if ($imagenes->isNotEmpty())
                <div x-show="[...$root.querySelectorAll('[data-status]')].every(card => !cardVisible(card))" x-cloak
                    class="rounded-2xl border-2 border-dashed border-neutral-300 px-6 py-12 text-center dark:border-neutral-700">
                    <p class="font-semibold text-neutral-800 dark:text-neutral-200">No se encontraron diseños</p>
                    <p class="mt-1 text-sm text-neutral-500">Prueba con otro nombre o cambia el filtro de estado.</p>
                </div>
            @endif
        </div>
    </section>

    {{-- Modal de edición --}}
    @if ($isModalOpen)
        @teleport('body')
            <div x-data="{ open: true }" x-show="open" x-transition.opacity
                x-on:keydown.escape.window="$wire.closeModal()"
                class="fixed inset-0 z-[100] flex items-center justify-center p-4 sm:p-6" role="dialog"
                aria-modal="true" aria-labelledby="editar-diseno-titulo">
                <div class="absolute inset-0 bg-neutral-950/65 backdrop-blur-sm" wire:click="closeModal"></div>

                <form wire:submit="actualizarImagenReconocimiento" x-transition:enter="transition duration-200 ease-out"
                    x-transition:enter-start="translate-y-4 scale-95 opacity-0"
                    x-transition:enter-end="translate-y-0 scale-100 opacity-100"
                    class="relative z-10 max-h-[92vh] w-full max-w-4xl overflow-y-auto rounded-3xl border border-white/10 bg-white shadow-2xl dark:bg-neutral-900">
                    <div
                        class="sticky top-0 z-20 flex items-start justify-between gap-4 border-b border-neutral-200 bg-white/95 px-6 py-5 backdrop-blur dark:border-neutral-700 dark:bg-neutral-900/95">
                        <div>
                            <h2 id="editar-diseno-titulo" class="text-xl font-bold text-neutral-950 dark:text-white">
                                Editar diseño</h2>
                            <p class="mt-1 text-sm text-neutral-500 dark:text-neutral-400">Actualiza el nombre o reemplaza
                                la imagen del fondo.</p>
                        </div>
                        <button type="button" wire:click="closeModal"
                            class="flex size-10 shrink-0 items-center justify-center rounded-xl bg-neutral-100 text-neutral-600 transition hover:bg-neutral-200 hover:text-neutral-900 dark:bg-neutral-800 dark:text-neutral-300 dark:hover:bg-neutral-700 dark:hover:text-white"
                            aria-label="Cerrar modal">
                            <svg class="size-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M6 18 18 6M6 6l12 12" />
                            </svg>
                        </button>
                    </div>

                    <div class="grid gap-6 p-6 lg:grid-cols-[1.15fr_.85fr]">
                        <div>
                            <p class="mb-2 text-sm font-semibold text-neutral-800 dark:text-neutral-200">
                                {{ $nuevaImagen ? 'Nueva imagen' : 'Imagen actual' }}
                            </p>
                            <div
                                class="overflow-hidden rounded-2xl border border-neutral-200 bg-neutral-100 dark:border-neutral-700 dark:bg-neutral-950">
                                @if ($nuevaImagen)
                                    <img src="{{ $nuevaImagen->temporaryUrl() }}" class="h-72 w-full object-contain"
                                        alt="Nueva imagen del diseño">
                                @elseif($imagenActual)
                                    <img src="{{ asset('storage/imagenesReconocimientos/' . $imagenActual) }}"
                                        class="h-72 w-full object-contain" alt="Imagen actual del diseño">
                                @endif
                            </div>

                            <div class="mt-3 flex items-center justify-between gap-3 text-xs text-neutral-500">
                                <span>Orientación actual: <strong
                                        class="text-neutral-700 dark:text-neutral-300">{{ ucfirst($orientacionEdit) }}</strong></span>
                                @if ($nuevaImagen)
                                    <span
                                        class="rounded-full bg-amber-50 px-2.5 py-1 font-semibold text-amber-700 dark:bg-amber-500/10 dark:text-amber-300">Pendiente
                                        de guardar</span>
                                @endif
                            </div>
                        </div>

                        <div class="space-y-5">
                            <flux:input wire:model="descripcionEdit" label="Nombre del diseño" badge="Obligatorio"
                                placeholder="Ej. Reconocimiento Primaria 2026" />

                            <div>
                                <flux:input type="file" wire:model="nuevaImagen" label="Reemplazar imagen"
                                    accept="image/png,image/jpeg" />
                                <p class="mt-2 text-xs text-neutral-500">Opcional. JPG o PNG, máximo 5 MB.</p>
                            </div>

                            <div wire:loading wire:target="nuevaImagen"
                                class="rounded-xl border border-[#006492]/20 bg-[#006492]/5 p-3 text-sm font-medium text-[#006492] dark:text-sky-300">
                                Procesando nueva imagen…
                            </div>

                            <div
                                class="rounded-xl border border-neutral-200 bg-neutral-50 p-4 text-sm text-neutral-600 dark:border-neutral-700 dark:bg-neutral-950/50 dark:text-neutral-300">
                                Al reemplazar el archivo, la imagen anterior se eliminará del almacenamiento después de
                                guardar correctamente.
                            </div>
                        </div>
                    </div>

                    <div
                        class="sticky bottom-0 flex flex-col-reverse gap-3 border-t border-neutral-200 bg-white/95 px-6 py-4 backdrop-blur sm:flex-row sm:justify-end dark:border-neutral-700 dark:bg-neutral-900/95">
                        <button type="button" wire:click="closeModal"
                            class="inline-flex h-10 items-center justify-center rounded-xl border border-neutral-300 px-4 text-sm font-semibold text-neutral-700 transition hover:bg-neutral-100 dark:border-neutral-700 dark:text-neutral-200 dark:hover:bg-neutral-800">
                            Cancelar
                        </button>
                        <button type="submit" wire:loading.attr="disabled"
                            wire:target="actualizarImagenReconocimiento,nuevaImagen"
                            class="inline-flex h-10 items-center justify-center gap-2 rounded-xl bg-[#006492] px-5 text-sm font-semibold text-white shadow-sm transition hover:bg-[#00557b] disabled:cursor-not-allowed disabled:opacity-50">
                            <svg wire:loading.remove wire:target="actualizarImagenReconocimiento" class="size-4"
                                fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8"
                                    d="M9 12.75 11.25 15 15 9.75M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z" />
                            </svg>
                            <svg wire:loading wire:target="actualizarImagenReconocimiento" class="size-4 animate-spin"
                                viewBox="0 0 24 24" fill="none">
                                <circle class="opacity-25" cx="12" cy="12" r="9" stroke="currentColor"
                                    stroke-width="3" />
                                <path class="opacity-75" fill="currentColor"
                                    d="M12 3a9 9 0 0 1 9 9h-3a6 6 0 0 0-6-6V3Z" />
                            </svg>
                            <span wire:loading.remove wire:target="actualizarImagenReconocimiento">Guardar cambios</span>
                            <span wire:loading wire:target="actualizarImagenReconocimiento">Guardando…</span>
                        </button>
                    </div>
                </form>
            </div>
        @endteleport
    @endif
</div>
