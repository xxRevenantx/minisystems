<x-layouts.app :title="__('MiniSystems - Imagenes')">
    <div class="relative overflow-hidden bg-white rounded-xl border border-neutral-200 dark:border-neutral-700 p-5 dark:bg-neutral-800">
        <section class="space-y-4">
            <header class="flex items-center justify-between">
                <div>
                    <h2 class="text-lg font-semibold">Procesar imágenes con banner</h2>
                    <p class="text-sm text-neutral-500 dark:text-neutral-400">
                        Sube varias imágenes, aplica un banner (imagen y/o texto) y descarga el ZIP con los resultados.
                    </p>
                </div>
            </header>

            <livewire:images.creacion-imagenes />
        </section>
    </div>
</x-layouts.app>
