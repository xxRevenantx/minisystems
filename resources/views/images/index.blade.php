<x-layouts.app :title="__('MiniSystems - System Images')">
    <div class="mx-auto w-full max-w-[1800px] space-y-5">
        <header class="flex flex-col gap-3 sm:flex-row sm:items-end sm:justify-between">
            <div>
                <div class="mb-2 flex items-center gap-2 text-xs font-bold uppercase tracking-widest text-blue-600 dark:text-blue-400">
                    <span class="h-2 w-2 rounded-full bg-blue-600"></span>
                    Imágenes múltiples
                </div>
                <h1 class="text-2xl font-black tracking-tight text-neutral-950 dark:text-white">Procesador inteligente de imágenes</h1>
                <p class="mt-1 max-w-3xl text-sm text-neutral-500 dark:text-neutral-400">
                    Procesa imágenes horizontales, verticales y cuadradas en un solo lote, con marcos adaptables y ajustes individuales.
                </p>
            </div>
            <a href="{{ route('marcos') }}" wire:navigate
                class="inline-flex items-center justify-center rounded-xl border border-neutral-200 bg-white px-4 py-2.5 text-sm font-bold text-neutral-700 shadow-sm transition hover:border-blue-300 hover:bg-blue-50 hover:text-blue-700 dark:border-neutral-700 dark:bg-neutral-900 dark:text-neutral-200 dark:hover:border-blue-700 dark:hover:bg-blue-950/30">
                Administrar marcos
            </a>
        </header>

        <livewire:images.creacion-imagenes />
    </div>
</x-layouts.app>
