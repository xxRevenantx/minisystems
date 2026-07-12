<x-layouts.app :title="__('MiniSystems - Editar reconocimiento')">
    <div class="space-y-6">
        <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
            <div>
                <flux:heading size="xl" level="1">{{ __('Modificar reconocimiento') }}</flux:heading>
                <flux:text class="mt-1">{{ __('Actualiza el contenido, estado, firmantes y diseño del documento.') }}</flux:text>
            </div>

            <flux:button href="{{ route('reconocimiento', ['tab' => 'reconocimientos']) }}" variant="filled">
                {{ __('Regresar') }}
            </flux:button>
        </div>

        <flux:separator />

        <livewire:reconocimientos.editar-reconocimiento :reconocimiento="$reconocimiento" />
    </div>
</x-layouts.app>
