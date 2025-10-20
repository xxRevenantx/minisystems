<div
    x-data="{ isUploading: false, progress: 0, etaText: '' }"
    x-on:livewire-upload-start="
        isUploading = true; progress = 0;
        const count = ($wire.get('images') || []).length || 1;
        const seconds = Math.ceil(count * 0.5);
        etaText = seconds + 's aprox.';
    "
    x-on:livewire-upload-finish="isUploading = false; etaText = ''"
    x-on:livewire-upload-error="isUploading = false; etaText = ''"
    x-on:livewire-upload-progress="progress = $event.detail.progress"
    class="w-full mx-auto max-h-[100vh] overflow-auto"
>
    <div class="rounded-2xl border border-neutral-200 dark:border-neutral-800 bg-white dark:bg-neutral-900 shadow-sm overflow-hidden">
        <div class="px-6 py-4 border-b border-neutral-200 dark:border-neutral-800 bg-gradient-to-r from-sky-500 via-blue-600 to-indigo-600 text-white">
            <h2 class="text-base sm:text-lg font-semibold">Descargar imágenes en ZIP</h2>
            <p class="text-xs/6 sm:text-sm/6 opacity-90">Redimensiona a 2048×1365, aplica marco y descarga.</p>
        </div>

        <form wire:submit.prevent="submit" class="p-6 space-y-6">
            <flux:field label="Imágenes" for="images">
                <input
                    id="images"
                    type="file"
                    wire:model.live="images"
                    multiple
                    accept="image/*"
                    class="block w-full cursor-pointer rounded-xl border border-neutral-300 dark:border-neutral-700 bg-white/70 dark:bg-neutral-900/70 px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-600"
                />
                @error('images')   <div class="mt-1 text-sm text-red-500">{{ $message }}</div> @enderror
                @error('images.*') <div class="mt-1 text-sm text-red-500">{{ $message }}</div> @enderror
                <p class="mt-1 text-xs text-neutral-500">JPG, PNG o WebP. Máx. 20MB por archivo. Hasta 100 archivos.</p>
            </flux:field>

            {{-- ETA / progreso (estimación cliente) --}}
            <div x-show="isUploading" class="rounded-lg border border-neutral-200 dark:border-neutral-800 p-3">
                <div class="mb-1 text-xs text-neutral-600 dark:text-neutral-300">
                    Subiendo… <span x-text="progress + '%'"></span> <span class="ml-2">ETA: <span x-text="etaText"></span></span>
                </div>
                <div class="h-2 w-full rounded-full bg-neutral-200 dark:bg-neutral-800 overflow-hidden">
                    <div class="h-2 rounded-full bg-blue-600 transition-all" :style="`width: ${progress}%`"></div>
                </div>
            </div>

            {{-- PREVIEWS + Drag & Drop --}}
            <div
                x-data
                x-ref="grid"
                x-init="
                    new Sortable($refs.grid, {
                        animation: 150,
                        handle: '.handle',
                        ghostClass: 'opacity-50',
                        onEnd() {
                            const ids = [...$refs.grid.children].map(el => el.dataset.id);
                            $wire.reorder(ids); // ← método Livewire
                        },
                    });
                "
                class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-4 gap-4"
            >
                @forelse ($images as $index => $img)
                    @php
                        // ID estable mientras esté en livewire-tmp
                        $uid = (string) $img->getFilename();
                    @endphp

                    <div
                        class="relative rounded-xl overflow-hidden border border-neutral-200 dark:border-neutral-800"
                        wire:key="img-{{ $uid }}"
                        data-id="{{ $uid }}"
                    >
                        <div class="relative w-full h-40">
                            {{-- Asa para arrastrar --}}
                            <div class="handle absolute top-2 left-2 text-xs px-2 py-1 rounded bg-neutral-900/60 text-white cursor-grab select-none">☰</div>

                            {{-- SKELETON --}}
                            {{-- <div x-cloak x-data="{ok:false,fail:false}" class="absolute inset-0" x-show="!ok && !fail">
                                <div class="w-full h-full bg-neutral-200 dark:bg-neutral-800 animate-pulse"></div>
                                <div class="absolute inset-0 flex items-center justify-center">
                                    <div class="h-7 w-7 rounded-full border-2 border-white/60 border-t-transparent animate-spin"></div>
                                </div>
                            </div> --}}

                            {{-- IMAGEN + overlay marco --}}
                            <img
                                src="{{ $img->temporaryUrl() }}"
                                alt="preview"
                                loading="lazy"
                                class="w-full h-40 object-cover transition duration-300"
                                x-on:load="$el.previousElementSibling && ($el.previousElementSibling.__x.$data.ok = true)"
                                x-on:error="$el.previousElementSibling && ($el.previousElementSibling.__x.$data.fail = true)"
                            />
                            <div
                                class="absolute inset-0 pointer-events-none"
                                style="background: url('{{ asset('frames/marco.png') }}') center/cover no-repeat;"
                                aria-hidden="true"
                            ></div>
                        </div>

                        <div class="p-2 text-xs text-neutral-600 dark:text-neutral-300 truncate">
                            {{ $img->getClientOriginalName() }}
                        </div>

                        {{-- Eliminar (por ID estable) --}}
                        <button
                            type="button"
                            wire:click="removeByTemp('{{ $uid }}')" {{-- método Livewire --}}
                            class="absolute top-2 right-2 inline-flex h-7 w-7 items-center justify-center rounded-full
                                   bg-black/60 text-white hover:bg-black/80 focus:outline-none focus:ring-2 focus:ring-white/70"
                            title="Quitar imagen" aria-label="Quitar imagen">
                            &times;
                        </button>
                    </div>
                @empty
                    <div class="col-span-full text-sm text-neutral-500">
                        No hay imágenes seleccionadas.
                    </div>
                @endforelse
            </div>

            {{-- BOTÓN DESCARGA --}}
            <div class="flex items-center gap-3">
                <flux:button
                    type="submit"
                    variant="primary"
                    x-bind:disabled="!Array.isArray($wire.get('images')) || $wire.get('images').length === 0"
                >
                    Descargar ZIP
                </flux:button>
            </div>
        </form>
    </div>
</div>

@push('scripts')
  <script src="https://cdn.jsdelivr.net/npm/sortablejs@1.15.2/Sortable.min.js" defer></script>
@endpush

