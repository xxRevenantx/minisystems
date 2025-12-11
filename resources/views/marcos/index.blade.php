<x-layouts.app :title="__('MiniSystems - Marcos de imágenes')">
    <div
        class="relative overflow-hidden bg-white rounded-xl border border-neutral-200 dark:border-neutral-700 p-5 dark:bg-neutral-800">
        <section class="space-y-4">
            <header class="flex items-center justify-between">
                <div>
                    <h2 class="text-lg font-semibold">Procesar marcos de imágenes con banner</h2>
                    <p class="text-sm text-neutral-500 dark:text-neutral-400">
                        Aquí puedes crear marcos personalizados para tus imágenes del sistema.
                    </p>
                </div>
            </header>

            <livewire:images.creacion-marcos />
        </section>
    </div>
</x-layouts.app>
