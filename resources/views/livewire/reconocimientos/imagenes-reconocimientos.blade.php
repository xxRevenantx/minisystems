<div x-data ="{
        destroyImagenReconocimiento(id, nombre) {
            Swal.fire({
                title: '¿Estás seguro?',
                text: `La imagen de reconocimiento en ${nombre} se eliminará de forma permanente`,
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#2563EB',
                cancelButtonColor: '#EF4444',
                cancelButtonText: 'Cancelar',
                confirmButtonText: 'Sí, eliminar'
            }).then((result) => {
                if (result.isConfirmed) {
                    @this.call('eliminarImagenReconocimiento', id);
                }
            })
        }
    }"
    class="container mx-auto">
    <form wire:submit.prevent="guardarImagenReconocimiento">
        <div class="lg:col-span-1">
            <div
                class="rounded-xl border border-dashed border-gray-300 dark:border-neutral-700 p-4 sm:p-5 bg-white dark:bg-neutral-900">
                <flux:input wire:model.live="reconocimiento" badge="Obligatorio" :label="__('Imagen del Reconocimiento')"
                    type="file" accept="image/jpeg,image/jpg,image/png" />
                <p class="mt-2 text-xs text-gray-500 dark:text-gray-400">
                    Tamaño preferible: 21.59 cm x 27.94 cm (8.5 x 11 pulgadas) (PNG o JPG).
                </p>

                @if ($reconocimiento)
                    <div class="mt-4 flex flex-col items-center">
                        <div class="relative">
                            <img src="{{ $reconocimiento->temporaryUrl() }}" alt="{{ __('Vista previa') }}"
                                class="h-24 w-24 rounded-xl object-cover ring-1 ring-gray-200 dark:ring-neutral-700 cursor-pointer"
                                x-data="{}"
                                @click="$dispatch('open-preview', { url: '{{ $reconocimiento->temporaryUrl() }}' })">

                            <!-- Modal de PREVIEW con blur -->
                            <div x-data="{ showPreview: false, previewUrl: '' }"
                                @open-preview.window="showPreview = true; previewUrl = $event.detail.url"
                                @keydown.escape.window="showPreview = false">
                                <div x-show="showPreview" x-transition
                                    class="fixed inset-0 z-50 flex items-center justify-center bg-black/50 backdrop-blur-sm supports-[backdrop-filter:blur(0)]:bg-black/40"
                                    @click.self="showPreview = false" role="dialog" aria-modal="true">
                                    <div class="relative max-w-5xl max-h-[90vh] overflow-auto bg-transparent p-2">
                                        <img :src="previewUrl" class="max-w-full h-auto rounded-lg">
                                        <!-- Botón cerrar -->
                                        <button @click="showPreview = false"
                                            class="absolute -top-3 -right-3 inline-flex items-center justify-center rounded-full bg-neutral-900/80 text-white p-2 shadow ring-1 ring-white/20"
                                            aria-label="Cerrar vista previa">
                                            <svg class="w-5 h-5" fill="none" stroke="currentColor"
                                                viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                    d="M6 18L18 6M6 6l12 12" />
                                            </svg>
                                        </button>
                                    </div>
                                </div>
                            </div>

                            <span
                                class="absolute -bottom-2 left-1/2 -translate-x-1/2 text-[10px] px-2 py-0.5 rounded-full bg-gray-900 text-white dark:bg-white dark:text-gray-900">
                                Previa
                            </span>
                        </div>
                    </div>
                @endif
            </div>

            <div class="mt-4">
                <flux:textarea wire:model.live="descripcion" label="Descripción de la imagen"
                    placeholder="Recomendado de Secundaria" />
            </div>
        </div>
        <div class="mt-4">
            <button type="submit"
                class="inline-flex items-center cursor-pointer px-4 py-2 bg-blue-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 disabled:opacity-25 transition">
                {{ __('Guardar Imagen de Reconocimiento') }}
            </button>
        </div>
    </form>

    <!-- Galería + Modal con blur -->
    <div x-data="{ open: false, selectedImage: '' }" @keydown.escape.window="open = false">
        <!-- Grid de imágenes -->
        <div
            class="grid mt-3    grid-cols-1 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-4 p-4 rounded-xl border border-dashed border-gray-300 dark:border-neutral-700 p-4 sm:p-5 bg-white dark:bg-neutral-900">

            @forelse($imagenes as $imagen)
                <div class="relative overflow-hidden rounded-lg shadow-lg hover:shadow-xl transition-shadow duration-300"
                    wire:key="imagen-{{ $imagen->id }}">
                    <!-- Botón eliminar -->
                    <button
                        @click="destroyImagenReconocimiento({{ $imagen->id }}, '{{ $imagen->descripcion ?? 'Sin descripción' }}')"
                        class="absolute cursor-pointer top-2 right-2 z-10 bg-red-500 hover:bg-red-600 text-white rounded-full p-1.5 shadow-md transition-colors duration-200">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M6 18L18 6M6 6l12 12" />
                        </svg>
                    </button>

                    {{-- Botón de editar --}}
                    <!-- resources/views/livewire/user-list.blade.php -->
                    <div>
                        <!-- Botón para abrir el modal -->
                        <flux:button
                            wire:click="editarImagen({{ $imagen->id }}, '{{ $imagen->descripcion ?? 'Sin descripción' }}')"
                            class="p-2 bg-blue-500 text-white rounded cursor-pointer absolute top-2 left-2 z-10 hover:bg-blue-600 transition-colors duration-200">
                            Editar
                        </flux:button>

                        <!-- Modal (Alpine + Tailwind) mejorado con fondo transparente -->
                        <div x-data="{
                            isOpen: @entangle('isModalOpen').live,
                            userId: @entangle('userId').live,
                            userName: @entangle('userName').live,
                        }" x-cloak x-show="isOpen"
                            @keydown.escape.window="isOpen = false; $wire.closeModal()"
                            class="fixed inset-0 z-50 flex items-center justify-center p-4" aria-live="polite">
                            <!-- Overlay (TRANSPARENTE + blur) -->
                            <div class="absolute inset-0 bg-black/20 backdrop-blur-sm" x-show="isOpen"
                                x-transition:enter="transition-opacity duration-200"
                                x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100"
                                x-transition:leave="transition-opacity duration-150"
                                x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0"
                                @click.self="isOpen = false; $wire.closeModal()"></div>

                            <!-- Panel -->
                            <div class="relative w-full max-w-sm overflow-hidden rounded-2xl border border-white/15 bg-white/10 p-6 shadow-2xl ring-1 ring-black/10 backdrop-blur-xl
           dark:border-white/10 dark:bg-white/5"
                                role="dialog" aria-modal="true" x-show="isOpen"
                                x-transition:enter="transition duration-300 ease-out"
                                x-transition:enter-start="opacity-0 translate-y-6 sm:translate-y-0 sm:scale-95 blur-sm"
                                x-transition:enter-end="opacity-100 translate-y-0 sm:scale-100 blur-0"
                                x-transition:leave="transition duration-200 ease-in"
                                x-transition:leave-start="opacity-100 translate-y-0 sm:scale-100 blur-0"
                                x-transition:leave-end="opacity-0 translate-y-6 sm:translate-y-0 sm:scale-95 blur-sm">
                                <!-- Header -->
                                <div class="flex items-start justify-between gap-3">
                                    <div>
                                        <h2 class="text-lg font-semibold text-white/95">Detalles del Usuario</h2>
                                        <p class="mt-1 text-sm text-white/70">Información básica del registro.</p>
                                    </div>

                                    <button type="button"
                                        class="rounded-full p-2 text-white/70 hover:text-white hover:bg-white/10 focus:outline-none focus:ring-2 focus:ring-white/30"
                                        @click="isOpen = false; $wire.closeModal()" aria-label="Cerrar">
                                        ✕
                                    </button>
                                </div>

                                <!-- Body -->
                                <!-- Body -->
                                <div class="mt-5 space-y-4 text-sm">

                                    <!-- Subir nueva imagen (pro) -->
                                    <div class="rounded-2xl border border-white/10 bg-white/5 p-4">
                                        <div class="flex items-start justify-between gap-3">
                                            <div>
                                                <p class="text-sm font-semibold text-white/90">Actualizar imagen</p>
                                                <p class="mt-0.5 text-xs text-white/65">PNG/JPG. Arrastra o selecciona
                                                    un archivo.</p>
                                            </div>

                                            <span
                                                class="inline-flex items-center rounded-full border border-white/10 bg-white/10 px-2 py-1 text-[11px] font-semibold text-white/80">
                                                Opcional
                                            </span>
                                        </div>

                                        <label class="mt-3 block cursor-pointer">
                                            <input type="file" class="sr-only"
                                                accept="image/jpeg,image/jpg,image/png"
                                                wire:model.live="nuevaImagen" />

                                            <div
                                                class="group relative mt-2 flex min-h-[120px] items-center justify-center rounded-2xl border border-dashed border-white/20 bg-black/10 p-4
               transition hover:bg-white/5">
                                                <div class="flex flex-col items-center text-center">
                                                    <div
                                                        class="mb-2 inline-flex h-11 w-11 items-center justify-center rounded-2xl border border-white/10 bg-white/10 text-white/80">
                                                        <!-- icon -->
                                                        <svg class="h-5 w-5" fill="none" stroke="currentColor"
                                                            viewBox="0 0 24 24">
                                                            <path stroke-linecap="round" stroke-linejoin="round"
                                                                stroke-width="2"
                                                                d="M7 16a4 4 0 01.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12" />
                                                        </svg>
                                                    </div>

                                                    <p class="text-sm font-semibold text-white/90">
                                                        Suelta tu imagen aquí o <span
                                                            class="underline underline-offset-4">haz clic para
                                                            elegir</span>
                                                    </p>
                                                    <p class="mt-1 text-xs text-white/60">Recomendado: 8.5 x 11
                                                        pulgadas</p>
                                                </div>

                                                <!-- Loading -->
                                                <div wire:loading wire:target="nuevaImagen"
                                                    class="absolute inset-0 grid place-items-center rounded-2xl bg-black/40 backdrop-blur-sm">
                                                    <div class="flex items-center gap-2 text-white/90">
                                                        <svg class="h-5 w-5 animate-spin" viewBox="0 0 24 24"
                                                            fill="none">
                                                            <circle class="opacity-25" cx="12" cy="12"
                                                                r="10" stroke="currentColor" stroke-width="4">
                                                            </circle>
                                                            <path class="opacity-75" fill="currentColor"
                                                                d="M4 12a8 8 0 018-8v4a4 4 0 00-4 4H4z"></path>
                                                        </svg>
                                                        <span class="text-sm font-semibold">Cargando…</span>
                                                    </div>
                                                </div>
                                            </div>
                                        </label>

                                        <!-- Preview -->
                                        @if ($nuevaImagen)
                                            <div class="mt-4">
                                                <p class="text-xs font-semibold text-white/70 mb-2">Vista previa</p>
                                                <div
                                                    class="relative overflow-hidden rounded-2xl border border-white/10 bg-white/5">
                                                    <img src="{{ $nuevaImagen->temporaryUrl() }}" alt="Previa"
                                                        class="h-44 w-full object-cover">
                                                    <div
                                                        class="absolute inset-x-0 bottom-0 bg-black/40 backdrop-blur-sm px-3 py-2 text-xs text-white/85">
                                                        La imagen se reemplazará al guardar.
                                                    </div>
                                                </div>
                                            </div>
                                        @endif

                                        @error('nuevaImagen')
                                            <p class="mt-2 text-xs text-red-300">{{ $message }}</p>
                                        @enderror
                                    </div>

                                    <!-- Descripción -->
                                    <div class="rounded-2xl border border-white/10 bg-white/5 p-4">
                                        <label class="block text-xs font-semibold text-white/70">Descripción</label>
                                        <input type="text" wire:model.live="descripcionEdit"
                                            class="mt-2 w-full rounded-xl border border-white/10 bg-white/10 px-3 py-2 text-sm text-white placeholder:text-white/40
             focus:outline-none focus:ring-2 focus:ring-white/25"
                                            placeholder="Ej. Recomendado de Secundaria" />
                                        @error('descripcionEdit')
                                            <p class="mt-2 text-xs text-red-300">{{ $message }}</p>
                                        @enderror
                                    </div>
                                </div>

                                <!-- Footer (reemplaza tu footer por este) -->
                                <div class="mt-6 flex justify-end gap-2">
                                    <button type="button" @click="isOpen = false; $wire.closeModal()"
                                        class="inline-flex items-center justify-center rounded-xl px-4 py-2 text-sm font-semibold
           bg-white/10 text-white hover:bg-white/15 border border-white/10
           focus:outline-none focus:ring-2 focus:ring-white/30">
                                        Cancelar
                                    </button>

                                    <button type="button" wire:click="actualizarImagenReconocimiento"
                                        wire:loading.attr="disabled"
                                        class="inline-flex items-center justify-center rounded-xl px-4 py-2 text-sm font-semibold
           bg-gradient-to-r from-sky-500 via-blue-600 to-indigo-600 text-white
           hover:brightness-110 disabled:opacity-50
           focus:outline-none focus:ring-2 focus:ring-white/30">
                                        <span wire:loading.remove
                                            wire:target="actualizarImagenReconocimiento,nuevaImagen">Guardar
                                            cambios</span>
                                        <span wire:loading
                                            wire:target="actualizarImagenReconocimiento,nuevaImagen">Guardando…</span>
                                    </button>
                                </div>

                                <!-- Footer -->
                                <div class="mt-6 flex justify-end gap-2">
                                    <button type="button" wire:click="closeModal()"
                                        class="inline-flex items-center justify-center rounded-xl px-4 py-2 text-sm font-semibold
               bg-white/10 text-white hover:bg-white/15 border border-white/10
               focus:outline-none focus:ring-2 focus:ring-white/30">
                                        Cerrar
                                    </button>
                                </div>
                            </div>
                        </div>



                    </div>

                    <!-- Imagen (ahora clickeable para el modal) -->
                    <div @click="open = true; selectedImage = '{{ asset('storage/imagenesReconocimientos/' . $imagen->imagen) }}'"
                        class="cursor-pointer">
                        <img src="{{ asset('storage/imagenesReconocimientos/' . $imagen->imagen) }}"
                            alt="{{ $imagen->descripcion ?? 'Imagen' }}" class="w-full h-48 object-cover">
                        <div class="absolute bottom-0 left-0 right-0 px-4 py-2 bg-gray-800/75">
                            <h3 class="text-white text-sm font-medium">{{ $imagen->descripcion ?? 'Sin descripción' }}
                            </h3>
                        </div>
                    </div>
                </div>
            @empty
                <p class="col-span-full text-center text-gray-500 dark:text-gray-400">No hay imágenes de
                    reconocimientos
                    disponibles.</p>
            @endforelse
        </div>

        <!-- Modal -->
        <div x-show="open" x-transition
            class="fixed inset-0 z-50 flex items-center justify-center bg-black/50 backdrop-blur-sm supports-[backdrop-filter:blur(0)]:bg-black/40"
            @click.self="open = false" role="dialog" aria-modal="true">
            <div class="relative max-w-3xl max-h-[90vh] overflow-auto bg-white dark:bg-neutral-900 rounded-lg">
                <img :src="selectedImage" class="w-full h-auto rounded-lg">
                <button @click="open = false"
                    class="absolute top-4 right-4 text-white bg-gray-800/90 hover:bg-gray-800 rounded-full p-2 shadow ring-1 ring-white/20"
                    aria-label="Cerrar">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </button>
            </div>
        </div>
    </div>

</div>
