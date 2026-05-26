<div x-data="{
    modalEditar: @entangle('modalEditar').live
}" class="space-y-6">
    <div
        class="overflow-hidden rounded-3xl border border-slate-200 bg-white shadow-xl shadow-slate-200/60 dark:border-zinc-800 dark:bg-zinc-950 dark:shadow-none">

        {{-- ENCABEZADO --}}
        <div
            class="relative overflow-hidden border-b border-slate-200 bg-gradient-to-r from-slate-900 via-slate-800 to-slate-950 px-6 py-6 text-white dark:border-zinc-800">
            <div class="absolute -right-20 -top-20 h-52 w-52 rounded-full bg-sky-500/20 blur-2xl"></div>
            <div class="absolute -bottom-24 left-10 h-56 w-56 rounded-full bg-indigo-500/20 blur-2xl"></div>

            <div class="relative flex flex-col gap-4 lg:flex-row lg:items-center lg:justify-between">
                <div>
                    <p class="text-xs font-semibold uppercase tracking-[0.25em] text-sky-200">
                        Credenciales registradas
                    </p>

                    <h2 class="mt-2 text-2xl font-bold tracking-tight">
                        Listado de credenciales
                    </h2>

                    <p class="mt-2 max-w-2xl text-sm text-slate-300">
                        Consulta, edita, elimina o descarga las credenciales registradas en el sistema.
                    </p>
                </div>

                <div class="rounded-2xl border border-white/10 bg-white/10 px-4 py-3 text-sm backdrop-blur">
                    <p class="font-semibold">{{ $credenciales->total() }} credenciales</p>
                    <p class="text-xs text-slate-300">Total de registros encontrados</p>
                </div>
            </div>
        </div>

        <div class="space-y-5 p-6">

            {{-- BUSCADOR, BOTÓN PDF GENERAL Y PAGINACIÓN --}}
            <div class="flex flex-col gap-3 lg:flex-row lg:items-center lg:justify-between">
                <div class="relative w-full lg:max-w-md">
                    <div class="pointer-events-none absolute inset-y-0 left-4 flex items-center text-slate-400">
                        <svg class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor">
                            <path fill-rule="evenodd"
                                d="M9 3.5a5.5 5.5 0 104.315 8.91l2.638 2.637a.75.75 0 101.06-1.06l-2.637-2.638A5.5 5.5 0 009 3.5zM5 9a4 4 0 118 0 4 4 0 01-8 0z"
                                clip-rule="evenodd" />
                        </svg>
                    </div>

                    <input type="search" wire:model.live.debounce.400ms="buscar"
                        placeholder="Buscar por nombre, matrícula, CURP, nivel..."
                        class="w-full rounded-2xl border border-slate-200 bg-white py-3 pl-12 pr-4 text-sm text-slate-800 shadow-sm outline-none transition focus:border-sky-400 focus:ring-4 focus:ring-sky-100 dark:border-zinc-800 dark:bg-zinc-900 dark:text-zinc-100 dark:focus:border-sky-500 dark:focus:ring-sky-900/40">
                </div>

                <div class="flex flex-col gap-3 sm:flex-row sm:items-center">
                    <a href="{{ route('credenciales.pdf.todas') }}" target="_blank"
                        class="inline-flex items-center justify-center gap-2 rounded-2xl bg-gradient-to-r from-emerald-500 via-green-600 to-lime-600 px-5 py-3 text-sm font-semibold text-white shadow-lg shadow-green-500/25 transition hover:-translate-y-0.5 hover:shadow-xl hover:shadow-green-500/30">
                        <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M12 3v12m0 0l4-4m-4 4l-4-4M4 17v2a2 2 0 002 2h12a2 2 0 002-2v-2" />
                        </svg>
                        Descargar todas en PDF
                    </a>

                    <div class="flex items-center gap-3">
                        <label class="text-sm font-semibold text-slate-600 dark:text-slate-300">
                            Mostrar
                        </label>

                        <select wire:model.live="porPagina"
                            class="rounded-2xl border border-slate-200 bg-white px-4 py-3 text-sm text-slate-800 shadow-sm outline-none transition focus:border-sky-400 focus:ring-4 focus:ring-sky-100 dark:border-zinc-800 dark:bg-zinc-900 dark:text-zinc-100 dark:focus:border-sky-500 dark:focus:ring-sky-900/40">
                            <option value="5">5</option>
                            <option value="10">10</option>
                            <option value="15">15</option>
                            <option value="25">25</option>
                        </select>
                    </div>
                </div>
            </div>

            {{-- LOADER --}}
            <div wire:loading.flex wire:target="buscar,porPagina,eliminar,actualizar"
                class="items-center gap-2 rounded-2xl border border-sky-100 bg-sky-50 px-4 py-3 text-sm font-semibold text-sky-700 dark:border-sky-900/50 dark:bg-sky-950/30 dark:text-sky-200">
                <svg class="h-4 w-4 animate-spin" viewBox="0 0 24 24" fill="none">
                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor"
                        stroke-width="4"></circle>
                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v4a4 4 0 00-4 4H4z"></path>
                </svg>
                Cargando información...
            </div>

            {{-- TABLA ESCRITORIO --}}
            <div class="hidden overflow-hidden rounded-2xl border border-slate-200 dark:border-zinc-800 lg:block">
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-slate-200 dark:divide-zinc-800">
                        <thead class="bg-slate-50 dark:bg-zinc-900">
                            <tr>
                                <th
                                    class="px-4 py-4 text-left text-xs font-bold uppercase tracking-wider text-slate-500 dark:text-slate-400">
                                    Alumno
                                </th>

                                <th
                                    class="px-4 py-4 text-left text-xs font-bold uppercase tracking-wider text-slate-500 dark:text-slate-400">
                                    CURP
                                </th>

                                <th
                                    class="px-4 py-4 text-left text-xs font-bold uppercase tracking-wider text-slate-500 dark:text-slate-400">
                                    Nivel
                                </th>

                                <th
                                    class="px-4 py-4 text-left text-xs font-bold uppercase tracking-wider text-slate-500 dark:text-slate-400">
                                    Grado / Grupo
                                </th>

                                <th
                                    class="px-4 py-4 text-left text-xs font-bold uppercase tracking-wider text-slate-500 dark:text-slate-400">
                                    Licenciatura
                                </th>

                                <th
                                    class="px-4 py-4 text-left text-xs font-bold uppercase tracking-wider text-slate-500 dark:text-slate-400">
                                    Ciclo / Vigencia
                                </th>

                                <th
                                    class="px-4 py-4 text-right text-xs font-bold uppercase tracking-wider text-slate-500 dark:text-slate-400">
                                    Acciones
                                </th>
                            </tr>
                        </thead>

                        <tbody class="divide-y divide-slate-100 bg-white dark:divide-zinc-800 dark:bg-zinc-950">
                            @forelse ($credenciales as $credencial)
                                <tr class="transition hover:bg-slate-50 dark:hover:bg-zinc-900/70">
                                    <td class="px-4 py-4">
                                        <div>
                                            <p class="font-semibold text-slate-800 dark:text-slate-100">
                                                {{ $credencial->nombre }}
                                            </p>

                                            <p class="mt-1 text-xs text-slate-500 dark:text-slate-400">
                                                Matrícula: {{ $credencial->matricula }}
                                            </p>
                                        </div>
                                    </td>

                                    <td class="px-4 py-4">
                                        <span
                                            class="rounded-xl bg-slate-100 px-3 py-1 text-xs font-semibold text-slate-700 dark:bg-zinc-800 dark:text-zinc-200">
                                            {{ $credencial->curp }}
                                        </span>
                                    </td>

                                    <td class="px-4 py-4">
                                        <span
                                            class="inline-flex rounded-full border border-sky-200 bg-sky-50 px-3 py-1 text-xs font-bold text-sky-700 dark:border-sky-900/60 dark:bg-sky-950/40 dark:text-sky-200">
                                            {{ $credencial->nivel }}
                                        </span>
                                    </td>

                                    <td class="px-4 py-4 text-sm text-slate-600 dark:text-slate-300">
                                        @if ($credencial->nivel === 'Licenciatura')
                                            <span class="text-slate-400">No aplica</span>
                                        @else
                                            {{ $credencial->grado ?: 'Sin grado' }} /
                                            {{ $credencial->grupo ?: 'Sin grupo' }}
                                        @endif
                                    </td>

                                    <td class="px-4 py-4 text-sm text-slate-600 dark:text-slate-300">
                                        {{ $credencial->licenciatura ?: 'No aplica' }}
                                    </td>

                                    <td class="px-4 py-4 text-sm text-slate-600 dark:text-slate-300">
                                        <p>{{ $credencial->ciclo_escolar ?: 'Sin ciclo' }}</p>
                                        <p class="text-xs text-slate-400">
                                            Vigencia: {{ $credencial->vigencia ?: 'Sin vigencia' }}
                                        </p>
                                    </td>

                                    <td class="px-4 py-4">
                                        <div class="flex justify-end gap-2">
                                            <a href="{{ route('credenciales.pdf.individual', $credencial) }}"
                                                target="_blank"
                                                class="inline-flex items-center justify-center rounded-xl border border-sky-200 bg-sky-50 px-3 py-2 text-xs font-bold text-sky-700 transition hover:bg-sky-100 dark:border-sky-900/60 dark:bg-sky-950/40 dark:text-sky-200">
                                                PDF
                                            </a>

                                            <button type="button" wire:click="abrirEditar({{ $credencial->id }})"
                                                class="inline-flex items-center justify-center rounded-xl border border-amber-200 bg-amber-50 px-3 py-2 text-xs font-bold text-amber-700 transition hover:bg-amber-100 dark:border-amber-900/60 dark:bg-amber-950/40 dark:text-amber-200">
                                                Editar
                                            </button>

                                            <button type="button" wire:click="eliminar({{ $credencial->id }})"
                                                wire:confirm="¿Seguro que deseas eliminar esta credencial?"
                                                class="inline-flex items-center justify-center rounded-xl border border-red-200 bg-red-50 px-3 py-2 text-xs font-bold text-red-700 transition hover:bg-red-100 dark:border-red-900/60 dark:bg-red-950/40 dark:text-red-200">
                                                Eliminar
                                            </button>
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="7" class="px-4 py-12 text-center">
                                        <div class="mx-auto max-w-md">
                                            <div
                                                class="mx-auto flex h-14 w-14 items-center justify-center rounded-2xl bg-slate-100 text-slate-400 dark:bg-zinc-900">
                                                <svg class="h-7 w-7" viewBox="0 0 24 24" fill="none"
                                                    stroke="currentColor">
                                                    <path stroke-linecap="round" stroke-linejoin="round"
                                                        stroke-width="1.8" d="M15 7a3 3 0 11-6 0 3 3 0 016 0z" />
                                                    <path stroke-linecap="round" stroke-linejoin="round"
                                                        stroke-width="1.8" d="M5 21a7 7 0 0114 0" />
                                                </svg>
                                            </div>

                                            <h3 class="mt-4 text-base font-bold text-slate-800 dark:text-slate-100">
                                                No hay credenciales registradas
                                            </h3>

                                            <p class="mt-1 text-sm text-slate-500 dark:text-slate-400">
                                                Cuando registres una credencial, aparecerá en esta tabla.
                                            </p>
                                        </div>
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>

            {{-- TARJETAS MÓVILES --}}
            <div class="space-y-4 lg:hidden">
                @forelse ($credenciales as $credencial)
                    <div
                        class="rounded-2xl border border-slate-200 bg-white p-4 shadow-sm dark:border-zinc-800 dark:bg-zinc-950">
                        <div class="flex items-start justify-between gap-3">
                            <div>
                                <p class="font-bold text-slate-800 dark:text-slate-100">
                                    {{ $credencial->nombre }}
                                </p>

                                <p class="mt-1 text-xs text-slate-500 dark:text-slate-400">
                                    Matrícula: {{ $credencial->matricula }}
                                </p>
                            </div>

                            <span
                                class="rounded-full border border-sky-200 bg-sky-50 px-3 py-1 text-xs font-bold text-sky-700 dark:border-sky-900/60 dark:bg-sky-950/40 dark:text-sky-200">
                                {{ $credencial->nivel }}
                            </span>
                        </div>

                        <div class="mt-4 grid grid-cols-1 gap-3 text-sm text-slate-600 dark:text-slate-300">
                            <div>
                                <span class="font-semibold text-slate-800 dark:text-slate-100">CURP:</span>
                                {{ $credencial->curp }}
                            </div>

                            <div>
                                <span class="font-semibold text-slate-800 dark:text-slate-100">Grado / Grupo:</span>
                                @if ($credencial->nivel === 'Licenciatura')
                                    No aplica
                                @else
                                    {{ $credencial->grado ?: 'Sin grado' }} / {{ $credencial->grupo ?: 'Sin grupo' }}
                                @endif
                            </div>

                            <div>
                                <span class="font-semibold text-slate-800 dark:text-slate-100">Licenciatura:</span>
                                {{ $credencial->licenciatura ?: 'No aplica' }}
                            </div>

                            <div>
                                <span class="font-semibold text-slate-800 dark:text-slate-100">Ciclo:</span>
                                {{ $credencial->ciclo_escolar ?: 'Sin ciclo' }}
                            </div>
                        </div>

                        <div class="mt-4 grid grid-cols-3 gap-2">
                            <a href="{{ route('credenciales.pdf.individual', $credencial) }}" target="_blank"
                                class="rounded-xl border border-sky-200 bg-sky-50 px-3 py-2 text-center text-xs font-bold text-sky-700 transition hover:bg-sky-100 dark:border-sky-900/60 dark:bg-sky-950/40 dark:text-sky-200">
                                PDF
                            </a>

                            <button type="button" wire:click="abrirEditar({{ $credencial->id }})"
                                class="rounded-xl border border-amber-200 bg-amber-50 px-3 py-2 text-xs font-bold text-amber-700 transition hover:bg-amber-100 dark:border-amber-900/60 dark:bg-amber-950/40 dark:text-amber-200">
                                Editar
                            </button>

                            <button type="button" wire:click="eliminar({{ $credencial->id }})"
                                wire:confirm="¿Seguro que deseas eliminar esta credencial?"
                                class="rounded-xl border border-red-200 bg-red-50 px-3 py-2 text-xs font-bold text-red-700 transition hover:bg-red-100 dark:border-red-900/60 dark:bg-red-950/40 dark:text-red-200">
                                Eliminar
                            </button>
                        </div>
                    </div>
                @empty
                    <div
                        class="rounded-2xl border border-dashed border-slate-300 p-8 text-center dark:border-zinc-700">
                        <p class="font-bold text-slate-700 dark:text-slate-200">
                            No hay credenciales registradas.
                        </p>
                    </div>
                @endforelse
            </div>

            {{-- PAGINACIÓN --}}
            @if ($credenciales->hasPages())
                <div class="border-t border-slate-200 pt-5 dark:border-zinc-800">
                    {{ $credenciales->links() }}
                </div>
            @endif
        </div>
    </div>

    {{-- MODAL EDITAR --}}
    <div x-show="modalEditar" x-cloak x-transition.opacity.duration.200ms
        class="fixed inset-0 z-50 flex items-center justify-center bg-slate-950/60 p-4 backdrop-blur-sm"
        x-on:keydown.escape.window="$wire.cerrarModal()">
        <div x-show="modalEditar" x-transition:enter="transition ease-out duration-300"
            x-transition:enter-start="opacity-0 translate-y-6 scale-95"
            x-transition:enter-end="opacity-100 translate-y-0 scale-100"
            x-transition:leave="transition ease-in duration-200"
            x-transition:leave-start="opacity-100 translate-y-0 scale-100"
            x-transition:leave-end="opacity-0 translate-y-6 scale-95"
            class="max-h-[90vh] w-full max-w-5xl overflow-hidden rounded-3xl border border-slate-200 bg-white shadow-2xl dark:border-zinc-800 dark:bg-zinc-950"
            x-on:click.outside="$wire.cerrarModal()">
            <div
                class="flex items-center justify-between border-b border-slate-200 bg-gradient-to-r from-amber-500 via-orange-500 to-red-500 px-6 py-5 text-white dark:border-zinc-800">
                <div>
                    <p class="text-xs font-semibold uppercase tracking-[0.25em] text-orange-100">
                        Edición de credencial
                    </p>

                    <h3 class="mt-1 text-xl font-bold">
                        Actualizar información
                    </h3>
                </div>

                <button type="button" wire:click="cerrarModal"
                    class="rounded-2xl border border-white/20 bg-white/10 px-3 py-2 text-sm font-bold text-white transition hover:bg-white/20">
                    Cerrar
                </button>
            </div>

            <form wire:submit.prevent="actualizar" class="max-h-[75vh] overflow-y-auto p-6">
                <div class="grid grid-cols-1 gap-5 md:grid-cols-2 xl:grid-cols-3">

                    {{-- NOMBRE --}}
                    <div class="space-y-2 xl:col-span-2">
                        <label class="text-sm font-semibold text-slate-700 dark:text-slate-200">
                            Nombre completo <span class="text-red-500">*</span>
                        </label>

                        <input type="text" wire:model.live="nombre"
                            class="w-full rounded-2xl border border-slate-200 bg-white px-4 py-3 text-sm text-slate-800 shadow-sm outline-none transition focus:border-amber-400 focus:ring-4 focus:ring-amber-100 dark:border-zinc-800 dark:bg-zinc-900 dark:text-zinc-100 dark:focus:border-amber-500 dark:focus:ring-amber-900/40">

                        @error('nombre')
                            <p class="text-xs font-medium text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    {{-- MATRÍCULA --}}
                    <div class="space-y-2">
                        <label class="text-sm font-semibold text-slate-700 dark:text-slate-200">
                            Matrícula <span class="text-red-500">*</span>
                        </label>

                        <input type="text" wire:model.live="matricula"
                            class="w-full rounded-2xl border border-slate-200 bg-white px-4 py-3 text-sm text-slate-800 shadow-sm outline-none transition focus:border-amber-400 focus:ring-4 focus:ring-amber-100 dark:border-zinc-800 dark:bg-zinc-900 dark:text-zinc-100 dark:focus:border-amber-500 dark:focus:ring-amber-900/40">

                        @error('matricula')
                            <p class="text-xs font-medium text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    {{-- CURP --}}
                    <div class="space-y-2">
                        <label class="text-sm font-semibold text-slate-700 dark:text-slate-200">
                            CURP <span class="text-red-500">*</span>
                        </label>

                        <input type="text" maxlength="18" wire:model.live="curp"
                            class="w-full rounded-2xl border border-slate-200 bg-white px-4 py-3 text-sm uppercase text-slate-800 shadow-sm outline-none transition focus:border-amber-400 focus:ring-4 focus:ring-amber-100 dark:border-zinc-800 dark:bg-zinc-900 dark:text-zinc-100 dark:focus:border-amber-500 dark:focus:ring-amber-900/40">

                        @error('curp')
                            <p class="text-xs font-medium text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    {{-- NIVEL --}}
                    <div class="space-y-2">
                        <label class="text-sm font-semibold text-slate-700 dark:text-slate-200">
                            Nivel <span class="text-red-500">*</span>
                        </label>

                        <select wire:model.live="nivel"
                            class="w-full rounded-2xl border border-slate-200 bg-white px-4 py-3 text-sm text-slate-800 shadow-sm outline-none transition focus:border-amber-400 focus:ring-4 focus:ring-amber-100 dark:border-zinc-800 dark:bg-zinc-900 dark:text-zinc-100 dark:focus:border-amber-500 dark:focus:ring-amber-900/40">
                            <option value="">Selecciona un nivel</option>

                            @foreach ($niveles as $item)
                                <option value="{{ $item }}">{{ $item }}</option>
                            @endforeach
                        </select>

                        @error('nivel')
                            <p class="text-xs font-medium text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    {{-- GRADO --}}
                    <div class="space-y-2">
                        <label class="text-sm font-semibold text-slate-700 dark:text-slate-200">
                            Grado
                            @if ($nivel !== 'Licenciatura')
                                <span class="text-red-500">*</span>
                            @endif
                        </label>

                        <select wire:model.live="grado"
                            x-bind:disabled="$wire.nivel === 'Licenciatura' || $wire.nivel === ''"
                            class="w-full rounded-2xl border border-slate-200 bg-white px-4 py-3 text-sm text-slate-800 shadow-sm outline-none transition focus:border-amber-400 focus:ring-4 focus:ring-amber-100 disabled:cursor-not-allowed disabled:bg-slate-100 disabled:text-slate-400 dark:border-zinc-800 dark:bg-zinc-900 dark:text-zinc-100 dark:disabled:bg-zinc-900/50 dark:disabled:text-zinc-600 dark:focus:border-amber-500 dark:focus:ring-amber-900/40">
                            <option value="">
                                {{ $nivel === 'Licenciatura' ? 'No aplica para licenciatura' : 'Selecciona un grado' }}
                            </option>

                            @foreach ($grados as $item)
                                <option value="{{ $item }}">{{ $item }}</option>
                            @endforeach
                        </select>

                        @error('grado')
                            <p class="text-xs font-medium text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    {{-- GRUPO --}}
                    <div class="space-y-2">
                        <label class="text-sm font-semibold text-slate-700 dark:text-slate-200">
                            Grupo
                            @if ($nivel !== 'Licenciatura')
                                <span class="text-red-500">*</span>
                            @endif
                        </label>

                        <select wire:model.live="grupo"
                            x-bind:disabled="$wire.nivel === 'Licenciatura' || $wire.nivel === ''"
                            class="w-full rounded-2xl border border-slate-200 bg-white px-4 py-3 text-sm text-slate-800 shadow-sm outline-none transition focus:border-amber-400 focus:ring-4 focus:ring-amber-100 disabled:cursor-not-allowed disabled:bg-slate-100 disabled:text-slate-400 dark:border-zinc-800 dark:bg-zinc-900 dark:text-zinc-100 dark:disabled:bg-zinc-900/50 dark:disabled:text-zinc-600 dark:focus:border-amber-500 dark:focus:ring-amber-900/40">
                            <option value="">
                                {{ $nivel === 'Licenciatura' ? 'No aplica para licenciatura' : 'Selecciona un grupo' }}
                            </option>

                            @foreach ($grupos as $item)
                                <option value="{{ $item }}">{{ $item }}</option>
                            @endforeach
                        </select>

                        @error('grupo')
                            <p class="text-xs font-medium text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    {{-- LICENCIATURA --}}
                    <div class="space-y-2 xl:col-span-2">
                        <label class="text-sm font-semibold text-slate-700 dark:text-slate-200">
                            Licenciatura
                            @if ($nivel === 'Licenciatura')
                                <span class="text-red-500">*</span>
                            @endif
                        </label>

                        <select wire:model.live="licenciatura" x-bind:disabled="$wire.nivel !== 'Licenciatura'"
                            class="w-full rounded-2xl border border-slate-200 bg-white px-4 py-3 text-sm text-slate-800 shadow-sm outline-none transition focus:border-amber-400 focus:ring-4 focus:ring-amber-100 disabled:cursor-not-allowed disabled:bg-slate-100 disabled:text-slate-400 dark:border-zinc-800 dark:bg-zinc-900 dark:text-zinc-100 dark:disabled:bg-zinc-900/50 dark:disabled:text-zinc-600 dark:focus:border-amber-500 dark:focus:ring-amber-900/40">
                            <option value="">
                                {{ $nivel === 'Licenciatura' ? 'Selecciona una licenciatura' : 'Disponible solo para nivel licenciatura' }}
                            </option>

                            @foreach ($licenciaturas as $item)
                                <option value="{{ $item }}">{{ $item }}</option>
                            @endforeach
                        </select>

                        @error('licenciatura')
                            <p class="text-xs font-medium text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    {{-- CICLO ESCOLAR --}}
                    <div class="space-y-2">
                        <label class="text-sm font-semibold text-slate-700 dark:text-slate-200">
                            Ciclo escolar
                        </label>

                        <input type="text" wire:model.live="ciclo_escolar"
                            class="w-full rounded-2xl border border-slate-200 bg-white px-4 py-3 text-sm text-slate-800 shadow-sm outline-none transition focus:border-amber-400 focus:ring-4 focus:ring-amber-100 dark:border-zinc-800 dark:bg-zinc-900 dark:text-zinc-100 dark:focus:border-amber-500 dark:focus:ring-amber-900/40">

                        @error('ciclo_escolar')
                            <p class="text-xs font-medium text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    {{-- VIGENCIA --}}
                    <div class="space-y-2">
                        <label class="text-sm font-semibold text-slate-700 dark:text-slate-200">
                            Vigencia
                        </label>

                        <input type="text" wire:model.live="vigencia"
                            class="w-full rounded-2xl border border-slate-200 bg-white px-4 py-3 text-sm text-slate-800 shadow-sm outline-none transition focus:border-amber-400 focus:ring-4 focus:ring-amber-100 dark:border-zinc-800 dark:bg-zinc-900 dark:text-zinc-100 dark:focus:border-amber-500 dark:focus:ring-amber-900/40">

                        @error('vigencia')
                            <p class="text-xs font-medium text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    {{-- TELÉFONO --}}
                    <div class="space-y-2">
                        <label class="text-sm font-semibold text-slate-700 dark:text-slate-200">
                            Teléfono
                        </label>

                        <input type="text" wire:model.live="telefono"
                            class="w-full rounded-2xl border border-slate-200 bg-white px-4 py-3 text-sm text-slate-800 shadow-sm outline-none transition focus:border-amber-400 focus:ring-4 focus:ring-amber-100 dark:border-zinc-800 dark:bg-zinc-900 dark:text-zinc-100 dark:focus:border-amber-500 dark:focus:ring-amber-900/40">

                        @error('telefono')
                            <p class="text-xs font-medium text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    {{-- DOMICILIO --}}
                    <div class="space-y-2 md:col-span-2 xl:col-span-3">
                        <label class="text-sm font-semibold text-slate-700 dark:text-slate-200">
                            Domicilio
                        </label>

                        <textarea rows="3" wire:model.live="domicilio"
                            class="w-full resize-none rounded-2xl border border-slate-200 bg-white px-4 py-3 text-sm text-slate-800 shadow-sm outline-none transition focus:border-amber-400 focus:ring-4 focus:ring-amber-100 dark:border-zinc-800 dark:bg-zinc-900 dark:text-zinc-100 dark:focus:border-amber-500 dark:focus:ring-amber-900/40"></textarea>

                        @error('domicilio')
                            <p class="text-xs font-medium text-red-600">{{ $message }}</p>
                        @enderror
                    </div>
                </div>

                {{-- BOTONES MODAL --}}
                <div
                    class="mt-6 flex flex-col-reverse gap-3 border-t border-slate-200 pt-6 sm:flex-row sm:items-center sm:justify-end dark:border-zinc-800">
                    <button type="button" wire:click="cerrarModal"
                        class="inline-flex items-center justify-center rounded-2xl border border-slate-200 bg-white px-5 py-3 text-sm font-semibold text-slate-700 shadow-sm transition hover:bg-slate-50 disabled:cursor-not-allowed disabled:opacity-60 dark:border-zinc-800 dark:bg-zinc-900 dark:text-zinc-200 dark:hover:bg-zinc-800"
                        wire:loading.attr="disabled">
                        Cancelar
                    </button>

                    <button type="submit"
                        class="inline-flex items-center justify-center rounded-2xl bg-gradient-to-r from-amber-500 via-orange-500 to-red-500 px-6 py-3 text-sm font-semibold text-white shadow-lg shadow-orange-500/25 transition hover:-translate-y-0.5 hover:shadow-xl hover:shadow-orange-500/30 disabled:cursor-not-allowed disabled:opacity-70"
                        wire:loading.attr="disabled" wire:target="actualizar">
                        <span wire:loading.remove wire:target="actualizar">
                            Guardar cambios
                        </span>

                        <span wire:loading wire:target="actualizar" class="inline-flex items-center gap-2">
                            <svg class="h-4 w-4 animate-spin" viewBox="0 0 24 24" fill="none">
                                <circle class="opacity-25" cx="12" cy="12" r="10"
                                    stroke="currentColor" stroke-width="4"></circle>
                                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v4a4 4 0 00-4 4H4z">
                                </path>
                            </svg>
                            Actualizando...
                        </span>
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
