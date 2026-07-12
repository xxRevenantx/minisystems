<x-layouts.app :title="__('MiniSystems - Imágenes de Reconocimientos')">
    <div class="space-y-6">
        <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
            <div>
                <flux:heading size="xl" level="1">{{ __('Diseños de reconocimientos') }}</flux:heading>
                <flux:text class="mt-1">{{ __('Sube, edita y organiza las imágenes usadas como fondo de los reconocimientos.') }}</flux:text>
            </div>

            <flux:button href="{{ route('reconocimiento') }}" variant="filled">
                {{ __('Regresar a reconocimientos') }}
            </flux:button>
        </div>

        <flux:separator />

        <livewire:reconocimientos.imagenes-reconocimientos />
    </div>
</x-layouts.app>
