<x-layouts.app :title="__('MiniSystems - Reconocimientos')">
    <div class="space-y-6">
        <div class="flex flex-col gap-2 sm:flex-row sm:items-center sm:justify-between">
            <div>
                <flux:heading size="xl" level="1">{{ __('Reconocimientos') }}</flux:heading>
                <flux:text class="mt-1">{{ __('Crea, administra y descarga reconocimientos institucionales.') }}</flux:text>
            </div>

            @if(auth()->user()?->puedeReconocimientos('administrar'))
                <flux:button href="{{ route('reconocimiento.imagenes') }}" variant="filled">
                    {{ __('Diseños de reconocimientos') }}
                </flux:button>
            @endif
        </div>

        <flux:separator />

        <livewire:reconocimientos.creacion-reconocimientos />
    </div>
</x-layouts.app>
