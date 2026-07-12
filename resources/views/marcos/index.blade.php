<x-layouts.app :title="__('MiniSystems - Marcos adaptables')">
    <div class="mx-auto w-full max-w-[1800px] space-y-5">
        <header class="flex flex-col gap-3 sm:flex-row sm:items-end sm:justify-between">
            <div>
                <div class="mb-2 flex items-center gap-2 text-xs font-bold uppercase tracking-widest text-blue-600 dark:text-blue-400">
                    <span class="h-2 w-2 rounded-full bg-blue-600"></span>
                    Imágenes múltiples
                </div>
                <h1 class="text-2xl font-black tracking-tight text-neutral-950 dark:text-white">Marcos adaptables</h1>
                <p class="mt-1 max-w-3xl text-sm text-neutral-500 dark:text-neutral-400">
                    Administra versiones horizontales y verticales del mismo marco para que cada imagen reciba el diseño correcto sin deformaciones.
                </p>
            </div>
            <a href="{{ route('images') }}" wire:navigate
                class="inline-flex items-center justify-center rounded-xl bg-neutral-950 px-4 py-2.5 text-sm font-bold text-white shadow-sm transition hover:bg-blue-700 dark:bg-white dark:text-neutral-950 dark:hover:bg-blue-200">
                Procesar imágenes →
            </a>
        </header>

        <livewire:images.creacion-marcos />
    </div>
</x-layouts.app>
