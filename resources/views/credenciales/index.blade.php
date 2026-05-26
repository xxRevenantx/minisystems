<x-layouts.app :title="__('MiniSystems - Credenciales')">
    <div
        class="relative overflow-hidden bg-white rounded-xl border border-neutral-200 dark:border-neutral-700 p-5 dark:bg-neutral-800">
        <section class="space-y-4">
            <header class="flex items-center justify-between">
                <div>
                    <p class="text-sm text-neutral-500 dark:text-neutral-400">
                        Aquí puedes crear tus credenciales.
                    </p>
                </div>
            </header>

            <livewire:credencial.crear-credencial />
        </section>
    </div>
</x-layouts.app>
