<div x-data="{ isUploading: false, progress: 0, etaText: '' }" x-cloak
    x-on:livewire-upload-start.window="
        isUploading = true;
        progress = 0;
        const count = ($wire.get('images') || []).length || 1;
        const seconds = Math.ceil(count * 0.5);
        etaText = seconds + 's aprox.';
    "
    x-on:livewire-upload-finish.window="
        isUploading = false;
        etaText = '';
    "
    x-on:livewire-upload-error.window="
        isUploading = false;
        etaText = '';
    "
    x-on:livewire-upload-progress.window="progress = $event.detail.progress"
    class="w-full mx-auto max-h-[100vh] overflow-auto">

    <div
        class="rounded-2xl border border-neutral-200 dark:border-neutral-800 bg-white dark:bg-neutral-900 shadow-sm overflow-hidden">
        <div
            class="px-6 py-4 border-b border-neutral-200 dark:border-neutral-800 bg-gradient-to-r from-sky-500 via-blue-600 to-indigo-600 text-white">
            <h2 class="text-base sm:text-lg font-semibold">Descargar imágenes en ZIP</h2>
            <p class="text-xs/6 sm:text-sm/6 opacity-90">
                Sube las imágenes, selecciona un marco y el tipo de dispositivo, y descarga el ZIP procesado.
            </p>
        </div>

        <form wire:submit.prevent="submit" class="p-6 space-y-6">
            {{-- CAMPOS: IMÁGENES + MARCO + DISPOSITIVO --}}
            <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                <flux:field label="Imágenes" for="images" class="md:col-span-3">
                    <input id="images" type="file" wire:model="images" multiple
                        accept="image/jpeg,image/png,image/webp"
                        class="block w-full cursor-pointer rounded-xl border border-neutral-300 dark:border-neutral-700 bg-white/70 dark:bg-neutral-900/70 px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-600" />
                    @error('images')
                        <div class="mt-1 text-sm text-red-500">{{ $message }}</div>
                    @enderror
                    @error('images.*')
                        <div class="mt-1 text-sm text-red-500">{{ $message }}</div>
                    @enderror
                    <p class="mt-1 text-xs text-neutral-500">
                        JPG, PNG o WebP. Máx. 20MB por archivo. Hasta 100 archivos.
                    </p>
                </flux:field>

                <flux:field label="Marco de imagen">
                    <flux:select wire:model="marco" placeholder="Selecciona un marco...">
                        <flux:select.option value="">--- Sin marco ---</flux:select.option>
                        @foreach ($marcos as $item)
                            <flux:select.option value="{{ $item->id }}">
                                {{ $item->descripcion }}
                            </flux:select.option>
                        @endforeach
                    </flux:select>
                    @error('marco')
                        <div class="mt-1 text-sm text-red-500">{{ $message }}</div>
                    @enderror
                </flux:field>

                <flux:field label="Tipo de dispositivo">
                    <flux:select wire:model="device">
                        <flux:select.option value="desktop">
                            Desktop (2058x1365 - horizontal)
                        </flux:select.option>
                        <flux:select.option value="mobile">
                            Móvil (1365x2058 - vertical)
                        </flux:select.option>
                    </flux:select>
                    @error('device')
                        <div class="mt-1 text-sm text-red-500">{{ $message }}</div>
                    @enderror
                </flux:field>
            </div>

            {{-- Barra de progreso de subida --}}
            <div x-show="isUploading" class="rounded-lg border border-neutral-200 dark:border-neutral-800 p-3">
                <div class="mb-1 text-xs text-neutral-600 dark:text-neutral-300" aria-live="polite">
                    Subiendo…
                    <span x-text="progress + '%'"></span>
                    <span class="ml-2">ETA: <span x-text="etaText"></span></span>
                </div>
                <div class="h-2 w-full rounded-full bg-neutral-200 dark:bg-neutral-800 overflow-hidden">
                    <div class="h-2 rounded-full bg-blue-600 transition-all" :style="`width: ${progress}%`"></div>
                </div>
            </div>

            {{-- PREVIEW GRID --}}
            <div x-data x-ref="grid" x-init="if (window.Sortable) {
                new Sortable($refs.grid, {
                    animation: 150,
                    handle: '.handle',
                    ghostClass: 'opacity-50',
                    onEnd() {
                        const ids = [...$refs.grid.children].map(el => el.dataset.id);
                        $wire.reorder(ids);
                    },
                });
            }"
                class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-4 gap-4">
                @forelse ($images as $index => $img)
                    @php $uid = (string) $img->getFilename(); @endphp

                    <div class="relative rounded-xl overflow-hidden border border-neutral-200 dark:border-neutral-800"
                        wire:key="img-{{ $uid }}" data-id="{{ $uid }}">
                        <div class="relative w-full h-40">
                            <div
                                class="handle absolute top-2 left-2 text-xs px-2 py-1 rounded bg-neutral-900/60 text-white cursor-grab select-none">
                                ☰
                            </div>

                            <img src="{{ $img->temporaryUrl() }}" alt="preview" loading="lazy"
                                class="w-full h-40 object-cover" />

                            @if ($frameName)
                                {{-- El marco está en asset('storage/' . $frameName) --}}
                                <div class="absolute inset-0 pointer-events-none"
                                    style="background: url('{{ asset('storage/' . $frameName) }}') center/cover no-repeat;"
                                    aria-hidden="true"></div>
                            @endif
                        </div>

                        <div class="p-2 text-xs text-neutral-600 dark:text-neutral-300 truncate">
                            {{ $img->getClientOriginalName() }}
                        </div>

                        <button type="button" wire:click="removeByTemp('{{ $uid }}')"
                            class="absolute top-2 right-2 inline-flex h-7 w-7 items-center justify-center rounded-full bg-black/60 text-white hover:bg-black/80 focus:outline-none focus:ring-2 focus:ring-white/70"
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

            {{-- BOTÓN SUBMIT --}}
            <div class="flex items-center gap-3">
                <flux:button type="submit" variant="primary" wire:loading.attr="disabled"
                    x-bind:disabled="!Array.isArray($wire.get('images')) || $wire.get('images').length === 0">
                    Descargar ZIP
                </flux:button>
            </div>
        </form>
    </div>
</div>

@push('scripts')
    <script src="https://cdn.jsdelivr.net/npm/sortablejs@1.15.2/Sortable.min.js" defer></script>
@endpush
