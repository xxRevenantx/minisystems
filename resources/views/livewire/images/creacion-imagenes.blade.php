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
            <p class="text-xs/6 sm:text-sm/6 opacity-90">Redimensiona, aplica marco/watermark, renombra y descarga (full + thumbs).</p>
        </div>

        <form wire:submit.prevent="submit" class="p-6 space-y-6">
            <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                <flux:field label="Imágenes" for="images" class="md:col-span-3">
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

                <flux:field label="Preset (ancho x alto)">
                    <select wire:model.live="preset" class="w-full rounded-lg border border-neutral-300 dark:border-neutral-700 bg-transparent px-3 py-2 text-sm">
                        <option value="2048x1365">3:2 — 2048×1365 (recomendado)</option>
                        <option value="1080x1080">1:1 — 1080×1080</option>
                        <option value="1350x1080">4:5 — 1350×1080</option>
                        <option value="1920x1080">16:9 — 1920×1080</option>
                        <option value="2048x2048">1:1 — 2048×2048</option>
                    </select>
                    @error('preset') <div class="mt-1 text-sm text-red-500">{{ $message }}</div> @enderror
                </flux:field>

                <flux:field label="Formato de salida">
                    <select wire:model.live="format" class="w-full rounded-lg border border-neutral-300 dark:border-neutral-700 bg-transparent px-3 py-2 text-sm">
                        <option value="jpg">JPG</option>
                        <option value="webp">WebP</option>
                        <option value="avif">AVIF</option>
                    </select>
                    @error('format') <div class="mt-1 text-sm text-red-500">{{ $message }}</div> @enderror
                </flux:field>

                <flux:field label="Calidad (60–95)">
                    <input type="range" min="60" max="95" step="1" wire:model.live="quality" class="w-full" />
                    <div class="text-xs mt-1">Calidad: <b>{{ $quality }}</b></div>
                    @error('quality') <div class="mt-1 text-sm text-red-500">{{ $message }}</div> @enderror
                </flux:field>

                <flux:field label="Marco (opcional)">
                    <select wire:model.live="frameName" class="w-full rounded-lg border border-neutral-300 dark:border-neutral-700 bg-transparent px-3 py-2 text-sm">
                        <option value="">Sin marco</option>
                        <option value="marco.png">marco.png</option>
                        <option value="marco_b.png">marco_b.png</option>
                        <option value="marco_c.png">marco_c.png</option>
                    </select>
                </flux:field>

                <flux:field label="Watermark (PNG)">
                    <label class="inline-flex items-center gap-2 text-sm">
                        <input type="checkbox" wire:model.live="addWatermark" class="rounded" /> Activar
                    </label>
                    <input type="text" placeholder="Ruta absoluta opcional (e.g., {{ public_path('watermarks/logo.png') }})" wire:model.live="watermarkPath" class="mt-2 w-full rounded-lg border border-neutral-300 dark:border-neutral-700 bg-transparent px-3 py-2 text-sm"/>
                    <div class="mt-2 text-xs">Margen</div>
                    <input type="range" min="0" max="128" step="2" wire:model.live="watermarkMargin" class="w-full" />
                </flux:field>

                <flux:field label="Patrón de nombre de archivo" class="md:col-span-2">
                    <input type="text" wire:model.live="renamePattern" class="w-full rounded-lg border border-neutral-300 dark:border-neutral-700 bg-transparent px-3 py-2 text-sm"/>
                    <div class="text-xs mt-1 text-neutral-500">Placeholders: <code>{index}</code>, <code>{date}</code>, <code>{orig}</code> — Ej: <code>Evento_{date}_{index}</code></div>
                    @error('renamePattern') <div class="mt-1 text-sm text-red-500">{{ $message }}</div> @enderror
                </flux:field>
            </div>

            <div x-show="isUploading" class="rounded-lg border border-neutral-200 dark:border-neutral-800 p-3">
                <div class="mb-1 text-xs text-neutral-600 dark:text-neutral-300" aria-live="polite">
                    Subiendo… <span x-text="progress + '%' "></span> <span class="ml-2">ETA: <span x-text="etaText"></span></span>
                </div>
                <div class="h-2 w-full rounded-full bg-neutral-200 dark:bg-neutral-800 overflow-hidden">
                    <div class="h-2 rounded-full bg-blue-600 transition-all" :style="`width: ${progress}%`"></div>
                </div>
            </div>

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
                            $wire.reorder(ids);
                        },
                    });
                "
                class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-4 gap-4"
            >
                @forelse ($images as $index => $img)
                    @php $uid = (string) $img->getFilename(); @endphp
                    <div
                        class="relative rounded-xl overflow-hidden border border-neutral-200 dark:border-neutral-800"
                        wire:key="img-{{ $uid }}"
                        data-id="{{ $uid }}"
                    >
                        <div class="relative w-full h-40">
                            <div class="handle absolute top-2 left-2 text-xs px-2 py-1 rounded bg-neutral-900/60 text-white cursor-grab select-none">☰</div>
                            <img src="{{ $img->temporaryUrl() }}" alt="preview" loading="lazy" class="w-full h-40 object-cover" />
                            @if ($frameName)
                                <div class="absolute inset-0 pointer-events-none" style="background: url('{{ asset('frames/' . $frameName) }}') center/cover no-repeat;" aria-hidden="true"></div>
                            @endif
                        </div>
                        <div class="p-2 text-xs text-neutral-600 dark:text-neutral-300 truncate">{{ $img->getClientOriginalName() }}</div>
                        <button type="button" wire:click="removeByTemp('{{ $uid }}')" class="absolute top-2 right-2 inline-flex h-7 w-7 items-center justify-center rounded-full bg-black/60 text-white hover:bg-black/80 focus:outline-none focus:ring-2 focus:ring-white/70" title="Quitar imagen" aria-label="Quitar imagen">&times;</button>
                    </div>
                @empty
                    <div class="col-span-full text-sm text-neutral-500">No hay imágenes seleccionadas.</div>
                @endforelse
            </div>

            <div class="flex items-center gap-3">
                <flux:button type="submit" variant="primary" x-bind:disabled="!Array.isArray($wire.get('images')) || $wire.get('images').length === 0">
                    Descargar ZIP
                </flux:button>
            </div>
        </form>
    </div>
</div>

@push('scripts')
  <script src="https://cdn.jsdelivr.net/npm/sortablejs@1.15.2/Sortable.min.js" defer></script>
@endpush
