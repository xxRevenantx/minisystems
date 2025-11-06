<x-layouts.app :title="__('MiniSystems - Reconocimientos')" >
    <div class="relative overflow-hidden bg-white rounded-xl border border-neutral-200 dark:border-neutral-700 p-5 dark:bg-neutral-800">
        <section class="space-y-4">
            <header class="mb-4">
                <h1 class="text-2xl font-semibold text-neutral-900 dark:text-neutral-100">{{ __('Modifica el Reconocimiento') }}</h1>
                <p class="mt-1 text-sm text-neutral-600 dark:text-neutral-300">{{ __('Gestione los reconocimientos aquí.') }}</p>
            </header>
            <div class="mb-4">
                <a href="{{ route('reconocimiento') }}" class="inline-flex items-center px-4 py-2 bg-gray-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-gray-700 focus:bg-gray-700 active:bg-gray-900 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 transition ease-in-out duration-150">
                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path>
                    </svg>
                    {{ __('Regresar') }}
                </a>
            </div>
                {{-- {{$reconocimiento}} --}}
               <livewire:reconocimientos.editar-reconocimiento :reconocimiento="$reconocimiento" />
        </section>
    </div>


</x-layouts.app>
