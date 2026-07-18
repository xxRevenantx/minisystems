@php($section = $section ?? 'processor')
<x-layouts.app :title="__('MiniSystems - System Images')">
    <div class="mx-auto w-full max-w-[1800px] space-y-5">
        <header class="flex flex-col gap-4 xl:flex-row xl:items-end xl:justify-between">
            <div>
                <div class="mb-2 flex items-center gap-2 text-xs font-bold uppercase tracking-widest text-blue-600 dark:text-blue-400">
                    <span class="h-2 w-2 rounded-full bg-blue-600"></span>
                    Imágenes múltiples
                </div>
                <h1 class="text-2xl font-black tracking-tight text-neutral-950 dark:text-white">System Images</h1>
                <p class="mt-1 max-w-3xl text-sm text-neutral-500 dark:text-neutral-400">
                    Procesa, optimiza y convierte tus fotografías en publicaciones listas para redes sociales.
                </p>
            </div>

            <nav class="flex flex-wrap gap-2 rounded-2xl border border-neutral-200 bg-white p-1.5 shadow-sm dark:border-neutral-800 dark:bg-neutral-900">
                <a href="{{ route('images') }}" wire:navigate
                    class="inline-flex items-center justify-center rounded-xl px-4 py-2.5 text-sm font-black transition {{ $section === 'processor' ? 'bg-blue-600 text-white shadow-md shadow-blue-500/20' : 'text-neutral-600 hover:bg-blue-50 hover:text-blue-700 dark:text-neutral-300 dark:hover:bg-blue-950/30 dark:hover:text-blue-300' }}">
                    Procesar imágenes
                </a>
                <a href="{{ route('images.optimizer') }}" wire:navigate
                    class="inline-flex items-center justify-center rounded-xl px-4 py-2.5 text-sm font-black transition {{ $section === 'optimizer' ? 'bg-emerald-600 text-white shadow-md shadow-emerald-500/20' : 'text-neutral-600 hover:bg-emerald-50 hover:text-emerald-700 dark:text-neutral-300 dark:hover:bg-emerald-950/30 dark:hover:text-emerald-300' }}">
                    Optimizar imágenes
                </a>
                <a href="{{ route('images.social-ai') }}" wire:navigate
                    class="inline-flex items-center justify-center rounded-xl px-4 py-2.5 text-sm font-black transition {{ $section === 'social-ai' ? 'bg-violet-600 text-white shadow-md shadow-violet-500/20' : 'text-neutral-600 hover:bg-violet-50 hover:text-violet-700 dark:text-neutral-300 dark:hover:bg-violet-950/30 dark:hover:text-violet-300' }}">
                    ✨ Redacción IA
                </a>
                <a href="{{ route('marcos') }}" wire:navigate
                    class="inline-flex items-center justify-center rounded-xl px-4 py-2.5 text-sm font-black text-neutral-600 transition hover:bg-fuchsia-50 hover:text-fuchsia-700 dark:text-neutral-300 dark:hover:bg-fuchsia-950/30 dark:hover:text-fuchsia-300">
                    Administrar marcos
                </a>
            </nav>
        </header>

        @if ($section === 'optimizer')
            <livewire:images.optimizar-imagenes />
        @elseif ($section === 'social-ai')
            <livewire:images.social-ai-composer />
        @else
            <livewire:images.creacion-imagenes-corregida />
        @endif
    </div>
</x-layouts.app>
