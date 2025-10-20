<x-layouts.app :title="__('MiniSystems - Imagenes de Reconocimientos')" >
    <div class="relative overflow-hidden bg-white rounded-xl border border-neutral-200 dark:border-neutral-700 p-5 dark:bg-neutral-800">
        <section class="space-y-4">
            <header class="mb-4">
                <h1 class="text-2xl font-semibold text-neutral-900 dark:text-neutral-100">{{ __('Imagenes de Reconocimientos') }}</h1>
                <p class="mt-1 text-sm text-neutral-600 dark:text-neutral-300">{{ __('Gestione las imagenes del Reconocimiento aquí') }}</p>
            </header>

            <livewire:reconocimientos.imagenes-reconocimientos />
        </section>
    </div>
</x-layouts.app>
