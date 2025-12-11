<div x-data ="{
        eliminarMarco(id, nombre) {
            Swal.fire({
                title: '¿Estás seguro?',
                text: `La imagen del marco se eliminará de forma permanente`,
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#2563EB',
                cancelButtonColor: '#EF4444',
                cancelButtonText: 'Cancelar',
                confirmButtonText: 'Sí, eliminar'
            }).then((result) => {
                if (result.isConfirmed) {
                    @this.call('eliminarMarco', id);
                }
            })
        }
    }"
    class="container mx-auto">
    <form wire:submit.prevent="guardarMarco">
        <div class="lg:col-span-1">
            <div
                class="rounded-xl border border-dashed border-gray-300 dark:border-neutral-700 p-4 sm:p-5 bg-white dark:bg-neutral-900">
                <flux:input wire:model.live="marco" badge="Obligatorio" :label="__('Imagen del Marco')" type="file"
                    accept="image/jpeg,image/jpg,image/png" />
                <p class="mt-2 text-xs text-gray-500 dark:text-gray-400">
                    Tamaño preferible: 21.59 cm x 27.94 cm (8.5 x 11 pulgadas) (PNG o JPG).
                </p>

                @if ($marco)
                    <div class="mt-4 flex flex-col items-center">
                        <div class="relative">
                            <img src="{{ $marco->temporaryUrl() }}" alt="{{ __('Vista previa') }}"
                                class="h-24 w-24 rounded-xl object-cover ring-1 ring-gray-200 dark:ring-neutral-700 cursor-pointer"
                                x-data="{}"
                                @click="$dispatch('open-preview', { url: '{{ $marco->temporaryUrl() }}' })">

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
                    placeholder="Nombre del marco" />
            </div>
        </div>
        <div class="mt-4">
            <button type="submit"
                class="inline-flex items-center cursor-pointer px-4 py-2 bg-blue-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 disabled:opacity-25 transition">
                {{ __('Guardar Imagen del Marco') }}
            </button>
        </div>
    </form>

    <!-- Galería + Modal con blur -->
    <div x-data="{ open: false, selectedImage: '' }" @keydown.escape.window="open = false">
        <!-- Grid de imágenes -->
        <div
            class="grid mt-3    grid-cols-1 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-4 p-4 rounded-xl border border-dashed border-gray-300 dark:border-neutral-700 p-4 sm:p-5 bg-white dark:bg-neutral-900">

            @forelse($marcos as $marco)
                <div class="relative overflow-hidden rounded-lg shadow-lg hover:shadow-xl transition-shadow duration-300"
                    wire:key="marco-{{ $marco->id }}">
                    <!-- Botón eliminar -->
                    <button
                        @click="eliminarMarco({{ $marco->id }}, '{{ $marco->descripcion ?? 'Sin descripción' }}')"
                        class="absolute cursor-pointer top-2 right-2 z-10 bg-red-500 hover:bg-red-600 text-white rounded-full p-1.5 shadow-md transition-colors duration-200">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M6 18L18 6M6 6l12 12" />
                        </svg>
                    </button>

                    <!-- Imagen (ahora clickeable para el modal) -->
                    <div @click="open = true; selectedImage = '{{ asset('storage/imagenesMarcos/' . $marco->marco) }}'"
                        class="cursor-pointer">
                        <img src="{{ asset('storage/imagenesMarcos/' . $marco->marco) }}"
                            alt="{{ $marco->descripcion ?? 'Imagen' }}" class="w-full h-48 object-cover">
                        <div class="absolute bottom-0 left-0 right-0 px-4 py-2 bg-gray-800/75">
                            <h3 class="text-white text-sm font-medium">{{ $marco->descripcion ?? 'Sin descripción' }}
                            </h3>
                        </div>
                    </div>
                </div>
            @empty
                <p class="col-span-full text-center text-gray-500 dark:text-gray-400">No hay imágenes de reconocimientos
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
