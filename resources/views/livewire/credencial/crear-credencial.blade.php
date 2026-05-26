<div x-data="{
    abierto: JSON.parse(localStorage.getItem('collapse_crear_credencial') ?? 'false'),
    guardado: @entangle('guardado'),

    alternarCollapse() {
        this.abierto = !this.abierto;
        localStorage.setItem('collapse_crear_credencial', JSON.stringify(this.abierto));
    },

    cerrarAlerta() {
        setTimeout(() => {
            this.guardado = false;
        }, 2500);
    }
}" x-init="$watch('guardado', value => {
    if (value) cerrarAlerta();
});" class="space-y-6">
    <div
        class="overflow-hidden rounded-3xl border border-slate-200 bg-white shadow-xl shadow-slate-200/60 dark:border-zinc-800 dark:bg-zinc-950 dark:shadow-none">

        {{-- ENCABEZADO DEL COLLAPSE --}}
        <button type="button" x-on:click="alternarCollapse()"
            class="group relative flex w-full items-center justify-between overflow-hidden border-b border-slate-200 bg-gradient-to-r from-sky-500 via-blue-600 to-indigo-700 px-6 py-6 text-left text-white transition dark:border-zinc-800">
            <div class="absolute -right-16 -top-16 h-40 w-40 rounded-full bg-white/10"></div>
            <div class="absolute -bottom-20 left-10 h-44 w-44 rounded-full bg-white/10"></div>

            <div class="relative flex items-center gap-4">
                <div
                    class="flex h-12 w-12 items-center justify-center rounded-2xl border border-white/20 bg-white/15 text-xl font-black shadow-lg backdrop-blur">
                    ID
                </div>

                <div>
                    <p class="text-xs font-semibold uppercase tracking-[0.25em] text-sky-100">
                        Módulo de credenciales
                    </p>

                    <h2 class="mt-1 text-2xl font-bold tracking-tight">
                        Crear nueva credencial
                    </h2>

                    <p class="mt-1 text-sm text-sky-50">
                        Da clic para abrir o cerrar el formulario de captura.
                    </p>
                </div>
            </div>

            <div class="relative flex items-center gap-3">
                <span
                    class="hidden rounded-2xl border border-white/20 bg-white/10 px-4 py-2 text-xs font-semibold text-sky-50 backdrop-blur sm:inline-flex"
                    x-text="abierto ? 'Formulario abierto' : 'Formulario cerrado'"></span>

                <div class="flex h-10 w-10 items-center justify-center rounded-2xl border border-white/20 bg-white/15 text-white shadow-lg transition duration-300 group-hover:scale-105"
                    :class="{ 'rotate-180': abierto }">
                    <svg class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor">
                        <path fill-rule="evenodd"
                            d="M5.23 7.21a.75.75 0 011.06.02L10 11.17l3.71-3.94a.75.75 0 111.08 1.04l-4.25 4.5a.75.75 0 01-1.08 0l-4.25-4.5a.75.75 0 01.02-1.06z"
                            clip-rule="evenodd" />
                    </svg>
                </div>
            </div>
        </button>

        {{-- CONTENIDO COLLAPSABLE --}}
        <div x-show="abierto" x-cloak x-transition:enter="transition ease-out duration-300"
            x-transition:enter-start="opacity-0 -translate-y-3" x-transition:enter-end="opacity-100 translate-y-0"
            x-transition:leave="transition ease-in duration-200" x-transition:leave-start="opacity-100 translate-y-0"
            x-transition:leave-end="opacity-0 -translate-y-3" class="p-6">
            <template x-if="guardado">
                <div x-transition.opacity.duration.300ms
                    class="mb-6 rounded-2xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm text-emerald-800 dark:border-emerald-900/60 dark:bg-emerald-950/40 dark:text-emerald-200">
                    <div class="flex items-start gap-3">
                        <div
                            class="mt-0.5 flex h-6 w-6 items-center justify-center rounded-full bg-emerald-500 text-xs font-bold text-white">
                            ✓
                        </div>

                        <div>
                            <p class="font-semibold">Credencial registrada correctamente.</p>
                            <p class="text-xs text-emerald-700 dark:text-emerald-300">
                                Los datos fueron guardados en el sistema.
                            </p>
                        </div>
                    </div>
                </div>
            </template>

            <form wire:submit.prevent="guardar" class="space-y-6">
                <div class="grid grid-cols-1 gap-5 md:grid-cols-2 xl:grid-cols-3">

                    {{-- NOMBRE --}}
                    <div class="space-y-2 xl:col-span-2">
                        <label for="nombre" class="text-sm font-semibold text-slate-700 dark:text-slate-200">
                            Nombre completo <span class="text-red-500">*</span>
                        </label>

                        <input id="nombre" type="text" wire:model.live="nombre"
                            placeholder="Ej. Carlos Alberto Núñez Pérez"
                            class="w-full rounded-2xl border border-slate-200 bg-white px-4 py-3 text-sm text-slate-800 shadow-sm outline-none transition focus:border-sky-400 focus:ring-4 focus:ring-sky-100 dark:border-zinc-800 dark:bg-zinc-900 dark:text-zinc-100 dark:focus:border-sky-500 dark:focus:ring-sky-900/40">

                        @error('nombre')
                            <p class="text-xs font-medium text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    {{-- MATRÍCULA --}}
                    <div class="space-y-2">
                        <label for="matricula" class="text-sm font-semibold text-slate-700 dark:text-slate-200">
                            Matrícula <span class="text-red-500">*</span>
                        </label>

                        <input id="matricula" type="text" wire:model.live="matricula" placeholder="Ej. CUM-2026-001"
                            class="w-full rounded-2xl border border-slate-200 bg-white px-4 py-3 text-sm text-slate-800 shadow-sm outline-none transition focus:border-sky-400 focus:ring-4 focus:ring-sky-100 dark:border-zinc-800 dark:bg-zinc-900 dark:text-zinc-100 dark:focus:border-sky-500 dark:focus:ring-sky-900/40">

                        @error('matricula')
                            <p class="text-xs font-medium text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    {{-- CURP --}}
                    <div class="space-y-2">
                        <label for="curp" class="text-sm font-semibold text-slate-700 dark:text-slate-200">
                            CURP <span class="text-red-500">*</span>
                        </label>

                        <input id="curp" type="text" maxlength="18" wire:model.live="curp"
                            placeholder="18 caracteres"
                            class="w-full rounded-2xl border border-slate-200 bg-white px-4 py-3 text-sm uppercase text-slate-800 shadow-sm outline-none transition focus:border-sky-400 focus:ring-4 focus:ring-sky-100 dark:border-zinc-800 dark:bg-zinc-900 dark:text-zinc-100 dark:focus:border-sky-500 dark:focus:ring-sky-900/40">

                        @error('curp')
                            <p class="text-xs font-medium text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    {{-- NIVEL --}}
                    <div class="space-y-2">
                        <label for="nivel" class="text-sm font-semibold text-slate-700 dark:text-slate-200">
                            Nivel <span class="text-red-500">*</span>
                        </label>

                        <select id="nivel" wire:model.live="nivel"
                            class="w-full rounded-2xl border border-slate-200 bg-white px-4 py-3 text-sm text-slate-800 shadow-sm outline-none transition focus:border-sky-400 focus:ring-4 focus:ring-sky-100 dark:border-zinc-800 dark:bg-zinc-900 dark:text-zinc-100 dark:focus:border-sky-500 dark:focus:ring-sky-900/40">
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
                        <label for="grado" class="text-sm font-semibold text-slate-700 dark:text-slate-200">
                            Grado
                            @if ($nivel !== 'Licenciatura')
                                <span class="text-red-500">*</span>
                            @endif
                        </label>

                        <select id="grado" wire:model.live="grado"
                            x-bind:disabled="$wire.nivel === 'Licenciatura' || $wire.nivel === ''"
                            class="w-full rounded-2xl border border-slate-200 bg-white px-4 py-3 text-sm text-slate-800 shadow-sm outline-none transition focus:border-sky-400 focus:ring-4 focus:ring-sky-100 disabled:cursor-not-allowed disabled:bg-slate-100 disabled:text-slate-400 dark:border-zinc-800 dark:bg-zinc-900 dark:text-zinc-100 dark:disabled:bg-zinc-900/50 dark:disabled:text-zinc-600 dark:focus:border-sky-500 dark:focus:ring-sky-900/40">
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
                        <label for="grupo" class="text-sm font-semibold text-slate-700 dark:text-slate-200">
                            Grupo
                            @if ($nivel !== 'Licenciatura')
                                <span class="text-red-500">*</span>
                            @endif
                        </label>

                        <select id="grupo" wire:model.live="grupo"
                            x-bind:disabled="$wire.nivel === 'Licenciatura' || $wire.nivel === ''"
                            class="w-full rounded-2xl border border-slate-200 bg-white px-4 py-3 text-sm text-slate-800 shadow-sm outline-none transition focus:border-sky-400 focus:ring-4 focus:ring-sky-100 disabled:cursor-not-allowed disabled:bg-slate-100 disabled:text-slate-400 dark:border-zinc-800 dark:bg-zinc-900 dark:text-zinc-100 dark:disabled:bg-zinc-900/50 dark:disabled:text-zinc-600 dark:focus:border-sky-500 dark:focus:ring-sky-900/40">
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
                        <label for="licenciatura" class="text-sm font-semibold text-slate-700 dark:text-slate-200">
                            Licenciatura
                            @if ($nivel === 'Licenciatura')
                                <span class="text-red-500">*</span>
                            @endif
                        </label>

                        <select id="licenciatura" wire:model.live="licenciatura"
                            x-bind:disabled="$wire.nivel !== 'Licenciatura'"
                            class="w-full rounded-2xl border border-slate-200 bg-white px-4 py-3 text-sm text-slate-800 shadow-sm outline-none transition focus:border-sky-400 focus:ring-4 focus:ring-sky-100 disabled:cursor-not-allowed disabled:bg-slate-100 disabled:text-slate-400 dark:border-zinc-800 dark:bg-zinc-900 dark:text-zinc-100 dark:disabled:bg-zinc-900/50 dark:disabled:text-zinc-600 dark:focus:border-sky-500 dark:focus:ring-sky-900/40">
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
                        <label for="ciclo_escolar" class="text-sm font-semibold text-slate-700 dark:text-slate-200">
                            Ciclo escolar
                        </label>

                        <input id="ciclo_escolar" type="text" wire:model.live="ciclo_escolar"
                            placeholder="Ej. 2025 - 2026"
                            class="w-full rounded-2xl border border-slate-200 bg-white px-4 py-3 text-sm text-slate-800 shadow-sm outline-none transition focus:border-sky-400 focus:ring-4 focus:ring-sky-100 dark:border-zinc-800 dark:bg-zinc-900 dark:text-zinc-100 dark:focus:border-sky-500 dark:focus:ring-sky-900/40">

                        @error('ciclo_escolar')
                            <p class="text-xs font-medium text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    {{-- VIGENCIA --}}
                    <div class="space-y-2">
                        <label for="vigencia" class="text-sm font-semibold text-slate-700 dark:text-slate-200">
                            Vigencia
                        </label>

                        <input id="vigencia" type="text" wire:model.live="vigencia"
                            placeholder="Ej. Agosto 2026"
                            class="w-full rounded-2xl border border-slate-200 bg-white px-4 py-3 text-sm text-slate-800 shadow-sm outline-none transition focus:border-sky-400 focus:ring-4 focus:ring-sky-100 dark:border-zinc-800 dark:bg-zinc-900 dark:text-zinc-100 dark:focus:border-sky-500 dark:focus:ring-sky-900/40">

                        @error('vigencia')
                            <p class="text-xs font-medium text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    {{-- TELÉFONO --}}
                    <div class="space-y-2">
                        <label for="telefono" class="text-sm font-semibold text-slate-700 dark:text-slate-200">
                            Teléfono
                        </label>

                        <input id="telefono" type="text" wire:model.live="telefono"
                            placeholder="Ej. 767 000 0000"
                            class="w-full rounded-2xl border border-slate-200 bg-white px-4 py-3 text-sm text-slate-800 shadow-sm outline-none transition focus:border-sky-400 focus:ring-4 focus:ring-sky-100 dark:border-zinc-800 dark:bg-zinc-900 dark:text-zinc-100 dark:focus:border-sky-500 dark:focus:ring-sky-900/40">

                        @error('telefono')
                            <p class="text-xs font-medium text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    {{-- DOMICILIO --}}
                    <div class="space-y-2 md:col-span-2 xl:col-span-3">
                        <label for="domicilio" class="text-sm font-semibold text-slate-700 dark:text-slate-200">
                            Domicilio
                        </label>

                        <textarea id="domicilio" rows="3" wire:model.live="domicilio"
                            placeholder="Calle, colonia, localidad o referencia del domicilio"
                            class="w-full resize-none rounded-2xl border border-slate-200 bg-white px-4 py-3 text-sm text-slate-800 shadow-sm outline-none transition focus:border-sky-400 focus:ring-4 focus:ring-sky-100 dark:border-zinc-800 dark:bg-zinc-900 dark:text-zinc-100 dark:focus:border-sky-500 dark:focus:ring-sky-900/40"></textarea>

                        @error('domicilio')
                            <p class="text-xs font-medium text-red-600">{{ $message }}</p>
                        @enderror
                    </div>
                </div>

                {{-- NOTA --}}
                <div
                    class="rounded-2xl border border-sky-100 bg-sky-50/80 px-4 py-4 text-sm text-sky-800 dark:border-sky-900/50 dark:bg-sky-950/30 dark:text-sky-200">
                    <p class="font-semibold">Nota importante</p>
                    <p class="mt-1 text-xs leading-5">
                        Si seleccionas el nivel <strong>Licenciatura</strong>, los campos de grado y grupo se desactivan
                        automáticamente.
                        Si seleccionas preescolar, primaria, secundaria o bachillerato, la licenciatura queda
                        deshabilitada.
                    </p>
                </div>

                {{-- BOTONES --}}
                <div
                    class="flex flex-col-reverse gap-3 border-t border-slate-200 pt-6 sm:flex-row sm:items-center sm:justify-end dark:border-zinc-800">
                    <button type="button" wire:click="limpiarFormulario"
                        class="inline-flex items-center justify-center rounded-2xl border border-slate-200 bg-white px-5 py-3 text-sm font-semibold text-slate-700 shadow-sm transition hover:bg-slate-50 disabled:cursor-not-allowed disabled:opacity-60 dark:border-zinc-800 dark:bg-zinc-900 dark:text-zinc-200 dark:hover:bg-zinc-800"
                        wire:loading.attr="disabled">
                        Limpiar
                    </button>

                    <button type="submit"
                        class="inline-flex items-center justify-center rounded-2xl bg-gradient-to-r from-sky-500 via-blue-600 to-indigo-700 px-6 py-3 text-sm font-semibold text-white shadow-lg shadow-blue-500/25 transition hover:-translate-y-0.5 hover:shadow-xl hover:shadow-blue-500/30 disabled:cursor-not-allowed disabled:opacity-70"
                        wire:loading.attr="disabled" wire:target="guardar">
                        <span wire:loading.remove wire:target="guardar">
                            Guardar credencial
                        </span>

                        <span wire:loading wire:target="guardar" class="inline-flex items-center gap-2">
                            <svg class="h-4 w-4 animate-spin" viewBox="0 0 24 24" fill="none">
                                <circle class="opacity-25" cx="12" cy="12" r="10"
                                    stroke="currentColor" stroke-width="4"></circle>
                                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v4a4 4 0 00-4 4H4z">
                                </path>
                            </svg>
                            Guardando...
                        </span>
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
