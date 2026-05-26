<x-layouts.app :title="__('MiniSystems - Credenciales')">
    <div
        class="relative overflow-hidden bg-white rounded-xl border border-neutral-200 dark:border-neutral-700 p-5 dark:bg-neutral-800">
        <section class="space-y-4">

            <livewire:credencial.crear-credencial />
            <livewire:credencial.mostrar-credenciales />
        </section>
    </div>
</x-layouts.app>
