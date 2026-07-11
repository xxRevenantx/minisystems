<div class="space-y-5">
    <div class="grid gap-5 xl:grid-cols-[1.25fr_.75fr]">
        <form wire:submit="guardarReconocimiento" class="overflow-hidden rounded-2xl border border-neutral-200 bg-white shadow-sm dark:border-neutral-700 dark:bg-neutral-900">
            <div class="flex flex-wrap items-center justify-between gap-3 border-b border-neutral-200 bg-gradient-to-r from-[#006492]/10 to-[#88AC2E]/10 px-5 py-4 dark:border-neutral-700">
                <div>
                    <h2 class="text-lg font-bold text-neutral-900 dark:text-white">Nuevo reconocimiento</h2>
                    <p class="text-sm text-neutral-500">Creación individual o masiva desde el padrón de credenciales.</p>
                </div>
                <div class="inline-flex rounded-xl border border-neutral-200 bg-white p-1 dark:border-neutral-700 dark:bg-neutral-800">
                    <button type="button" wire:click="$set('modo','individual')" class="rounded-lg px-3 py-2 text-sm font-semibold {{ $modo === 'individual' ? 'bg-[#006492] text-white' : 'text-neutral-600 dark:text-neutral-300' }}">Individual</button>
                    <button type="button" wire:click="$set('modo','masivo')" class="rounded-lg px-3 py-2 text-sm font-semibold {{ $modo === 'masivo' ? 'bg-[#88AC2E] text-white' : 'text-neutral-600 dark:text-neutral-300' }}">Masivo</button>
                </div>
            </div>

            <div class="space-y-5 p-5">
                <div class="grid gap-4 md:grid-cols-3">
                    <label class="space-y-1 text-sm font-medium">Evento o lote
                        <select wire:model.live="reconocimiento_evento_id" class="w-full rounded-xl border-neutral-300 dark:border-neutral-700 dark:bg-neutral-800">
                            <option value="">Sin evento</option>
                            @foreach($eventos as $evento)<option value="{{ $evento->id }}">{{ $evento->nombre }}</option>@endforeach
                        </select>
                    </label>
                    <label class="space-y-1 text-sm font-medium">Tipo reutilizable
                        <select wire:model.live="reconocimiento_tipo_id" class="w-full rounded-xl border-neutral-300 dark:border-neutral-700 dark:bg-neutral-800">
                            <option value="">Personalizado</option>
                            @foreach($tipos as $tipo)<option value="{{ $tipo->id }}">{{ $tipo->nombre }}</option>@endforeach
                        </select>
                    </label>
                    <label class="space-y-1 text-sm font-medium">Estado inicial
                        <select wire:model="estado" class="w-full rounded-xl border-neutral-300 dark:border-neutral-700 dark:bg-neutral-800">
                            <option value="borrador">Borrador</option><option value="revision">Pendiente de revisión</option>
                        </select>
                    </label>
                </div>

                @if($modo === 'individual')
                    <div class="grid gap-4 md:grid-cols-2">
                        <label class="space-y-1 text-sm font-medium">Destinatario <span class="text-red-500">*</span>
                            <input wire:model.live.debounce.300ms="reconocimiento" type="text" maxlength="255" placeholder="Nombre completo, institución o invitado" class="w-full rounded-xl border-neutral-300 dark:border-neutral-700 dark:bg-neutral-800">
                            @error('reconocimiento')<span class="text-xs text-red-600">{{ $message }}</span>@enderror
                        </label>
                        <label class="space-y-1 text-sm font-medium">Lugar obtenido
                            <input wire:model.live.debounce.300ms="lugar_obtenido" type="text" maxlength="255" placeholder="Ej. Primer lugar" class="w-full rounded-xl border-neutral-300 dark:border-neutral-700 dark:bg-neutral-800">
                        </label>
                    </div>
                @else
                    <section class="rounded-2xl border border-[#006492]/20 bg-[#006492]/5 p-4">
                        <div class="mb-4 flex flex-wrap items-center justify-between gap-3">
                            <div><h3 class="font-bold text-[#006492]">Seleccionar destinatarios</h3><p class="text-xs text-neutral-500">{{ count($credencialesSeleccionadas) }} seleccionado(s). Se mostrarán como máximo 100 resultados.</p></div>
                            <button type="button" wire:click="limpiarAlumnos" class="rounded-lg border px-3 py-2 text-xs font-semibold">Limpiar selección</button>
                        </div>
                        <div class="grid gap-3 md:grid-cols-5">
                            <input wire:model.live.debounce.300ms="buscarAlumno" placeholder="Nombre o matrícula" class="rounded-xl border-neutral-300 dark:border-neutral-700 dark:bg-neutral-800">
                            <select wire:model.live="nivelFiltro" class="rounded-xl border-neutral-300 dark:border-neutral-700 dark:bg-neutral-800"><option value="">Nivel</option>@foreach($niveles as $v)<option>{{ $v }}</option>@endforeach</select>
                            <select wire:model.live="gradoFiltro" class="rounded-xl border-neutral-300 dark:border-neutral-700 dark:bg-neutral-800"><option value="">Grado</option>@foreach($grados as $v)<option>{{ $v }}</option>@endforeach</select>
                            <select wire:model.live="grupoFiltro" class="rounded-xl border-neutral-300 dark:border-neutral-700 dark:bg-neutral-800"><option value="">Grupo</option>@foreach($grupos as $v)<option>{{ $v }}</option>@endforeach</select>
                            <select wire:model.live="licenciaturaFiltro" class="rounded-xl border-neutral-300 dark:border-neutral-700 dark:bg-neutral-800"><option value="">Licenciatura</option>@foreach($licenciaturas as $v)<option>{{ $v }}</option>@endforeach</select>
                        </div>
                        <div class="mt-3 flex flex-wrap gap-2">
                            <button type="button" wire:click="seleccionarPagina({{ $credenciales->pluck('id')->values()->toJson() }})" class="rounded-lg bg-[#006492] px-3 py-2 text-xs font-bold text-white">Seleccionar resultados visibles</button>
                            <a href="{{ route('reconocimientos.plantilla.csv') }}" class="rounded-lg border bg-white px-3 py-2 text-xs font-bold dark:bg-neutral-900">Descargar plantilla CSV</a><label class="flex items-center gap-2 rounded-lg border bg-white px-3 py-2 text-xs dark:bg-neutral-900">Importar CSV de Excel <input type="file" wire:model="archivoCsv" accept=".csv,.txt" class="max-w-44 text-xs"></label>
                            @if($archivoCsv)<button type="button" wire:click="importarCsv" class="rounded-lg bg-[#88AC2E] px-3 py-2 text-xs font-bold text-white">Procesar archivo</button>@endif
                        </div>
                        @error('credencialesSeleccionadas')<p class="mt-2 text-xs text-red-600">{{ $message }}</p>@enderror
                        <div class="mt-4 max-h-64 overflow-auto rounded-xl border bg-white dark:bg-neutral-900">
                            <table class="min-w-full text-sm">
                                <thead class="sticky top-0 bg-neutral-100 dark:bg-neutral-800"><tr><th class="p-2"></th><th class="p-2 text-left">Destinatario</th><th class="p-2 text-left">Nivel / grupo</th><th class="p-2 text-left">Matrícula</th></tr></thead>
                                <tbody class="divide-y dark:divide-neutral-800">
                                @forelse($credenciales as $alumno)
                                    <tr><td class="p-2"><input type="checkbox" wire:model="credencialesSeleccionadas" value="{{ $alumno->id }}" class="rounded border-neutral-300 text-[#006492]"></td><td class="p-2 font-semibold">{{ $alumno->nombre }}</td><td class="p-2 text-neutral-500">{{ $alumno->nivel }} {{ $alumno->grado }} {{ $alumno->grupo }} {{ $alumno->licenciatura }}</td><td class="p-2">{{ $alumno->matricula }}</td></tr>
                                @empty<tr><td colspan="4" class="p-6 text-center text-neutral-500">No hay coincidencias.</td></tr>@endforelse
                                </tbody>
                            </table>
                        </div>
                    </section>
                @endif

                <div class="grid gap-4 md:grid-cols-[1fr_220px]">
                    <label class="space-y-1 text-sm font-medium">Descripción <span class="text-red-500">*</span>
                        <textarea wire:model.live.debounce.400ms="descripcion" rows="6" maxlength="5000" class="w-full rounded-xl border-neutral-300 dark:border-neutral-700 dark:bg-neutral-800" placeholder="Motivo del reconocimiento"></textarea>
                        <span class="text-xs text-neutral-500">Puedes usar negritas y listas mediante un tipo reutilizable; el contenido se sanitiza antes de guardarse.</span>
                        @error('descripcion')<span class="block text-xs text-red-600">{{ $message }}</span>@enderror
                    </label>
                    <label class="space-y-1 text-sm font-medium">Fecha <span class="text-red-500">*</span>
                        <input wire:model.live="fecha" type="date" class="w-full rounded-xl border-neutral-300 dark:border-neutral-700 dark:bg-neutral-800">
                        @error('fecha')<span class="text-xs text-red-600">{{ $message }}</span>@enderror
                    </label>
                </div>

                <section>
                    <div class="mb-2 flex items-center justify-between"><h3 class="text-sm font-bold">Firmantes</h3><span class="text-xs text-neutral-500">Máximo 5</span></div>
                    <div class="grid gap-2 md:grid-cols-2">
                        @forelse($directivosLista as $d)
                            <label class="flex items-start gap-3 rounded-xl border p-3 hover:border-[#88AC2E]"><input type="checkbox" wire:model="directivos" value="{{ $d->id }}" class="mt-1 rounded border-neutral-300 text-[#88AC2E]"><span><strong>{{ $d->nombre_completo }}</strong><small class="block text-neutral-500">{{ $d->cargo }}</small></span></label>
                        @empty<p class="text-sm text-neutral-500">No hay firmantes activos.</p>@endforelse
                    </div>
                    @error('directivos')<p class="mt-2 text-xs text-red-600">{{ $message }}</p>@enderror
                </section>

                <section>
                    <div class="mb-2 flex items-center justify-between"><h3 class="text-sm font-bold">Plantilla <span class="text-red-500">*</span></h3><button type="button" wire:click="limpiarSeleccion" class="text-xs font-semibold text-[#006492]">Limpiar selección</button></div>
                    <div class="grid gap-3 sm:grid-cols-2 lg:grid-cols-3">
                        @forelse($reconocimientosImagenes as $plantilla)
                            <label class="relative cursor-pointer overflow-hidden rounded-xl border-2 {{ (int)$reconocimiento_imagen_id === $plantilla->id ? 'border-[#88AC2E] ring-2 ring-[#88AC2E]/20' : 'border-neutral-200 dark:border-neutral-700' }}">
                                <input type="radio" wire:model.live="reconocimiento_imagen_id" value="{{ $plantilla->id }}" class="absolute left-3 top-3 z-10">
                                <img src="{{ asset('storage/imagenesReconocimientos/'.$plantilla->imagen) }}" class="h-32 w-full object-cover" alt="Plantilla">
                                <div class="p-2 text-xs font-semibold">{{ $plantilla->nombre ?: ($plantilla->descripcion ?: 'Plantilla '.$plantilla->id) }}</div>
                            </label>
                        @empty<p class="text-sm text-neutral-500">No hay plantillas activas.</p>@endforelse
                    </div>
                    @error('reconocimiento_imagen_id')<p class="mt-2 text-xs text-red-600">{{ $message }}</p>@enderror
                </section>
            </div>

            <div class="flex flex-wrap justify-end gap-3 border-t border-neutral-200 bg-neutral-50 px-5 py-4 dark:border-neutral-700 dark:bg-neutral-800/50">
                <button type="button" wire:click="resetFormulario" class="rounded-xl border px-4 py-2 text-sm font-bold">Limpiar</button>
                <button type="submit" wire:loading.attr="disabled" class="rounded-xl bg-[#006492] px-5 py-2.5 text-sm font-bold text-white hover:bg-[#00557b] disabled:opacity-50">
                    <span wire:loading.remove wire:target="guardarReconocimiento">{{ $modo === 'masivo' ? 'Generar reconocimientos' : 'Guardar reconocimiento' }}</span>
                    <span wire:loading wire:target="guardarReconocimiento">Guardando…</span>
                </button>
            </div>
        </form>

        <aside class="xl:sticky xl:top-4 xl:self-start">
            <div class="overflow-hidden rounded-2xl border border-neutral-200 bg-white shadow-sm dark:border-neutral-700 dark:bg-neutral-900">
                <div class="border-b border-neutral-200 px-4 py-3 dark:border-neutral-700"><h3 class="font-bold">Vista previa</h3><p class="text-xs text-neutral-500">Comprueba nombre, texto, fecha y firmantes antes de guardar.</p></div>
                <div class="relative aspect-[11/8.5] overflow-hidden bg-neutral-100">
                    @php $preview = $reconocimientosImagenes->firstWhere('id', (int)$reconocimiento_imagen_id); @endphp
                    @if($preview)<img src="{{ asset('storage/imagenesReconocimientos/'.$preview->imagen) }}" class="absolute inset-0 h-full w-full object-cover">@endif
                    <div class="absolute inset-0 flex flex-col items-center px-8 text-center">
                        <div class="mt-[25%] max-w-full truncate font-serif text-2xl font-bold text-[#006492]">{{ $modo === 'masivo' ? (count($credencialesSeleccionadas).' destinatarios seleccionados') : ($reconocimiento ?: 'Nombre del destinatario') }}</div>
                        @if($lugar_obtenido)<div class="mt-2 text-sm font-bold text-[#88AC2E]">{{ $lugar_obtenido }}</div>@endif
                        <div class="mt-3 max-h-24 overflow-hidden text-xs leading-relaxed text-neutral-700">{{ \Illuminate\Support\Str::limit(strip_tags($descripcion ?: 'Aquí aparecerá la descripción del reconocimiento.'), 340) }}</div>
                        <div class="mt-4 text-xs font-semibold">{{ $fecha ? \Carbon\Carbon::parse($fecha)->format('d/m/Y') : 'Fecha' }}</div>
                        <div class="mt-auto mb-6 grid w-full grid-cols-2 gap-3 text-[9px]">
                            @foreach($directivosLista->whereIn('id', $directivos)->take(4) as $d)<div class="border-t border-neutral-500 pt-1"><strong>{{ $d->nombre_completo }}</strong><br>{{ $d->cargo }}</div>@endforeach
                        </div>
                    </div>
                </div>
                @if(!$preview)<div class="p-3 text-center text-xs text-amber-700">Selecciona una plantilla para completar la vista previa.</div>@endif
                @php $textoPlano = trim(strip_tags($descripcion)); @endphp
                @if(($modo === 'individual' && mb_strlen($reconocimiento) > 48) || mb_strlen($textoPlano) > 520 || count($directivos) > 3)
                    <div class="border-t border-amber-200 bg-amber-50 p-3 text-xs text-amber-800">
                        <strong>Revisa la distribución:</strong>
                        @if($modo === 'individual' && mb_strlen($reconocimiento) > 48) el nombre es largo; @endif
                        @if(mb_strlen($textoPlano) > 520) la descripción podría desbordarse; @endif
                        @if(count($directivos) > 3) hay varios firmantes y las firmas serán más compactas; @endif
                    </div>
                @endif
            </div>
        </aside>
    </div>

    <div class="rounded-2xl border border-neutral-200 bg-white p-5 shadow-sm dark:border-neutral-700 dark:bg-neutral-900">
        <livewire:reconocimientos.mostrar-reconocimientos />
    </div>
</div>
