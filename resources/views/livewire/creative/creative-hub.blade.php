@php
    $titles = [
        'marcas' => ['Clientes y marcas', 'Centraliza logotipos, colores, identidad y datos de cada cliente.'],
        'personas' => ['Personas y contactos', 'Catálogo general reutilizable para credenciales, reconocimientos, eventos y campañas.'],
        'proyectos' => ['Proyectos y campañas', 'Agrupa personas, archivos, plantillas, solicitudes y publicaciones por trabajo.'],
        'biblioteca' => ['Biblioteca multimedia', 'Administra imágenes, logos, fondos, firmas, sellos, marcos y documentos.'],
        'plantillas' => ['Plantillas dinámicas', 'Diseña composiciones reutilizables con bloques variables y control de versiones.'],
        'generador' => ['Generador masivo', 'Crea lotes de documentos PDF desde CSV o desde el catálogo de personas.'],
        'solicitudes' => ['Solicitudes de trabajo', 'Controla pedidos, prioridades, responsables, fechas y estados.'],
        'publicaciones' => ['Calendario de publicaciones', 'Organiza copy, hashtags, piezas y fechas para redes sociales.'],
        'presets' => ['Formatos para redes', 'Administra medidas predefinidas para posts, historias, portadas y miniaturas.'],
        'exportaciones' => ['Historial de exportaciones', 'Consulta lotes, formatos, plantillas y configuraciones utilizadas.'],
        'validaciones' => ['Folios y validación pública', 'Crea códigos verificables para reconocimientos, credenciales y documentos.'],
        'actividad' => ['Actividad del sistema', 'Auditoría general de altas, cambios, exportaciones y eliminaciones.'],
    ];
    [$title, $subtitle] = $titles[$section] ?? ['MiniSystems Studio', ''];
@endphp

<div
    x-data="{
        confirmDelete(message, action) {
            Swal.fire({
                title: '¿Confirmar acción?',
                text: message,
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#006492',
                cancelButtonColor: '#64748b',
                confirmButtonText: 'Sí, continuar',
                cancelButtonText: 'Cancelar'
            }).then(result => {
                if (result.isConfirmed) $wire.call(action.method, action.id)
            })
        }
    }"
    x-on:scroll-form.window="$nextTick(() => document.getElementById('studio-form')?.scrollIntoView({ behavior: 'smooth', block: 'start' }))"
    class="space-y-6"
>
    <section class="overflow-hidden rounded-3xl border border-slate-200 bg-white shadow-xl shadow-slate-200/50 dark:border-zinc-800 dark:bg-zinc-950 dark:shadow-none">
        <div class="relative overflow-hidden bg-gradient-to-r from-[#006492] via-sky-600 to-[#88AC2E] px-6 py-7 text-white">
            <div class="absolute -right-20 -top-20 h-56 w-56 rounded-full bg-white/10"></div>
            <div class="absolute -bottom-24 left-1/3 h-52 w-52 rounded-full bg-white/10"></div>
            <div class="relative flex flex-col gap-5 xl:flex-row xl:items-end xl:justify-between">
                <div>
                    <p class="text-xs font-black uppercase tracking-[.28em] text-white/75">MiniSystems Studio</p>
                    <h1 class="mt-2 text-3xl font-black tracking-tight">{{ $title }}</h1>
                    <p class="mt-2 max-w-3xl text-sm leading-6 text-white/85">{{ $subtitle }}</p>
                </div>
                <div class="flex flex-wrap gap-2 text-xs font-bold">
                    <a href="{{ route('studio.section', 'marcas') }}" wire:navigate class="rounded-full px-3 py-2 {{ $section === 'marcas' ? 'bg-white text-[#006492]' : 'bg-white/15 text-white hover:bg-white/25' }}">Marcas</a>
                    <a href="{{ route('studio.section', 'personas') }}" wire:navigate class="rounded-full px-3 py-2 {{ $section === 'personas' ? 'bg-white text-[#006492]' : 'bg-white/15 text-white hover:bg-white/25' }}">Personas</a>
                    <a href="{{ route('studio.section', 'proyectos') }}" wire:navigate class="rounded-full px-3 py-2 {{ $section === 'proyectos' ? 'bg-white text-[#006492]' : 'bg-white/15 text-white hover:bg-white/25' }}">Proyectos</a>
                    <a href="{{ route('studio.section', 'biblioteca') }}" wire:navigate class="rounded-full px-3 py-2 {{ $section === 'biblioteca' ? 'bg-white text-[#006492]' : 'bg-white/15 text-white hover:bg-white/25' }}">Biblioteca</a>
                    <a href="{{ route('studio.section', 'plantillas') }}" wire:navigate class="rounded-full px-3 py-2 {{ $section === 'plantillas' ? 'bg-white text-[#006492]' : 'bg-white/15 text-white hover:bg-white/25' }}">Plantillas</a>
                    <a href="{{ route('studio.section', 'generador') }}" wire:navigate class="rounded-full px-3 py-2 {{ $section === 'generador' ? 'bg-white text-[#006492]' : 'bg-white/15 text-white hover:bg-white/25' }}">Generador</a>
                </div>
            </div>
        </div>

        @if(!in_array($section, ['generador'], true))
            <div class="grid gap-3 border-t border-slate-200 bg-slate-50 p-4 dark:border-zinc-800 dark:bg-zinc-900/50 md:grid-cols-[1fr_180px_180px]">
                <label class="relative">
                    <span class="sr-only">Buscar</span>
                    <flux:input wire:model.live.debounce.300ms="buscar" type="search" placeholder="Buscar en este módulo..."
                        class="w-full rounded-2xl border border-slate-300 bg-white px-4 py-3 text-sm outline-none ring-0 transition focus:border-[#006492] dark:border-zinc-700 dark:bg-zinc-950" />
                </label>
                <flux:select wire:model.live="filtroEstado" class="rounded-2xl border border-slate-300 bg-white px-4 py-3 text-sm dark:border-zinc-700 dark:bg-zinc-950">
                    <flux:select.option value="todos">Todos los estados</flux:select.option>
                    @if(in_array($section, ['marcas','personas','biblioteca'], true))
                        <flux:select.option value="activos">Activos</flux:select.option>
                        <flux:select.option value="inactivos">Inactivos</flux:select.option>
                    @elseif($section === 'proyectos')
                        <flux:select.option value="borrador">Borrador</flux:select.option>
                        <flux:select.option value="en_proceso">En proceso</flux:select.option>
                        <flux:select.option value="revision">Revisión</flux:select.option>
                        <flux:select.option value="aprobado">Aprobado</flux:select.option>
                        <flux:select.option value="entregado">Entregado</flux:select.option>
                    @elseif($section === 'plantillas')
                        <flux:select.option value="borrador">Borrador</flux:select.option>
                        <flux:select.option value="revision">Revisión</flux:select.option>
                        <flux:select.option value="aprobado">Aprobado</flux:select.option>
                        <flux:select.option value="archivado">Archivado</flux:select.option>
                    @elseif($section === 'solicitudes')
                        <flux:select.option value="pendiente">Pendiente</flux:select.option>
                        <flux:select.option value="en_proceso">En proceso</flux:select.option>
                        <flux:select.option value="revision">Revisión</flux:select.option>
                        <flux:select.option value="aprobada">Aprobada</flux:select.option>
                        <flux:select.option value="entregada">Entregada</flux:select.option>
                        <flux:select.option value="cancelada">Cancelada</flux:select.option>
                    @elseif($section === 'publicaciones')
                        <flux:select.option value="borrador">Borrador</flux:select.option>
                        <flux:select.option value="revision">Revisión</flux:select.option>
                        <flux:select.option value="aprobada">Aprobada</flux:select.option>
                        <flux:select.option value="programada">Programada</flux:select.option>
                        <flux:select.option value="publicada">Publicada</flux:select.option>
                    @elseif($section === 'validaciones')
                        <flux:select.option value="valido">Válido</flux:select.option>
                        <flux:select.option value="cancelado">Cancelado</flux:select.option>
                        <flux:select.option value="vencido">Vencido</flux:select.option>
                    @endif
                </flux:select>
                <flux:select wire:model.live="filtroTipo" class="rounded-2xl border border-slate-300 bg-white px-4 py-3 text-sm dark:border-zinc-700 dark:bg-zinc-950">
                    <flux:select.option value="todos">Todos los tipos</flux:select.option>
                    @if($section === 'personas')
                        @foreach(['contacto','participante','ponente','empleado','proveedor','cliente','otro'] as $type)<flux:select.option value="{{ $type }}">{{ ucfirst($type) }}</flux:select.option>@endforeach
                    @elseif($section === 'biblioteca')
                        @foreach(['imagen','logo','fondo','marco','firma','sello','icono','documento','otro'] as $type)<flux:select.option value="{{ $type }}">{{ ucfirst($type) }}</flux:select.option>@endforeach
                    @else
                        <flux:select.option value="todos">Sin filtro adicional</flux:select.option>
                    @endif
                </flux:select>
            </div>
        @endif
    </section>

    @if($errors->any())
        <div class="rounded-2xl border border-red-200 bg-red-50 p-4 text-sm text-red-800 dark:border-red-900 dark:bg-red-950/30 dark:text-red-200">
            <p class="font-black">Revisa los siguientes campos:</p>
            <ul class="mt-2 list-disc space-y-1 pl-5">
                @foreach($errors->all() as $error)<li>{{ $error }}</li>@endforeach
            </ul>
        </div>
    @endif

    @switch($section)
        @case('marcas')
            <div class="grid gap-6 xl:grid-cols-[420px_1fr]">
                <form id="studio-form" wire:submit="guardarMarca" class="self-start rounded-3xl border border-slate-200 bg-white p-5 shadow-sm dark:border-zinc-800 dark:bg-zinc-950 xl:sticky xl:top-4">
                    <div class="flex items-center justify-between">
                        <div><p class="text-xs font-bold uppercase tracking-widest text-[#006492]">Identidad</p><h2 class="text-xl font-black">{{ $marcaId ? 'Editar marca' : 'Nueva marca' }}</h2></div>
                        @if($marcaId)<flux:button type="button" wire:click="limpiarMarca" class="text-xs font-bold text-slate-500 hover:text-red-600">Cancelar</flux:button>@endif
                    </div>
                    <div class="mt-5 space-y-4">
                        <label class="block text-sm font-bold">Nombre<flux:input wire:model="marcaNombre" class="mt-1 w-full rounded-xl border-slate-300 dark:border-zinc-700 dark:bg-zinc-900" placeholder="Nombre del cliente o marca" /></label>
                        <div class="grid grid-cols-2 gap-3">
                            <label class="text-sm font-bold">Tipo<flux:select wire:model="marcaTipo" class="mt-1 w-full rounded-xl border-slate-300 dark:border-zinc-700 dark:bg-zinc-900">@foreach(['cliente','institucion','evento','personal','otro'] as $v)<flux:select.option value="{{ $v }}">{{ ucfirst($v) }}</flux:select.option>@endforeach</flux:select></label>
                            <label class="text-sm font-bold">Contacto<flux:input wire:model="marcaContacto" class="mt-1 w-full rounded-xl border-slate-300 dark:border-zinc-700 dark:bg-zinc-900" /></label>
                        </div>
                        <div class="grid grid-cols-2 gap-3">
                            <label class="text-sm font-bold">Correo<flux:input wire:model="marcaEmail" type="email" class="mt-1 w-full rounded-xl border-slate-300 dark:border-zinc-700 dark:bg-zinc-900" /></label>
                            <label class="text-sm font-bold">Teléfono<flux:input wire:model="marcaTelefono" class="mt-1 w-full rounded-xl border-slate-300 dark:border-zinc-700 dark:bg-zinc-900" /></label>
                        </div>
                        <label class="block text-sm font-bold">Sitio web<flux:input wire:model="marcaSitio" class="mt-1 w-full rounded-xl border-slate-300 dark:border-zinc-700 dark:bg-zinc-900" placeholder="https://..." /></label>
                        <div class="grid grid-cols-2 gap-3">
                            <label class="text-sm font-bold">Color principal<flux:input wire:model.live="marcaColorPrimario" type="color" class="mt-1 h-11 w-full rounded-xl border-slate-300 p-1 dark:border-zinc-700 dark:bg-zinc-900" /></label>
                            <label class="text-sm font-bold">Color secundario<flux:input wire:model.live="marcaColorSecundario" type="color" class="mt-1 h-11 w-full rounded-xl border-slate-300 p-1 dark:border-zinc-700 dark:bg-zinc-900" /></label>
                        </div>
                        <div class="grid grid-cols-2 gap-3">
                            <label class="text-sm font-bold">Logo principal<flux:input wire:model="marcaLogo" type="file" accept="image/*" class="mt-1 block w-full text-xs" /></label>
                            <label class="text-sm font-bold">Logo alterno<flux:input wire:model="marcaLogoSecundario" type="file" accept="image/*" class="mt-1 block w-full text-xs" /></label>
                        </div>
                        <label class="block text-sm font-bold">Notas<flux:textarea wire:model="marcaNotas" rows="3" class="mt-1 w-full rounded-xl border-slate-300 dark:border-zinc-700 dark:bg-zinc-900"></flux:textarea></label>
                        <label class="flex items-center gap-3 rounded-xl bg-slate-50 p-3 text-sm font-bold dark:bg-zinc-900"><flux:checkbox wire:model="marcaActivo" class="rounded" /> Marca activa</label>
                        <flux:button variant="primary" type="submit" class="w-full rounded-xl bg-[#006492] px-4 py-3 text-sm font-black text-white hover:bg-[#005477]" wire:loading.attr="disabled">{{ $marcaId ? 'Guardar cambios' : 'Crear marca' }}</flux:button>
                    </div>
                </form>

                <div class="grid auto-rows-min gap-4 md:grid-cols-2 2xl:grid-cols-3">
                    @forelse($items as $item)
                        <article class="overflow-hidden rounded-3xl border border-slate-200 bg-white shadow-sm dark:border-zinc-800 dark:bg-zinc-950">
                            <div class="h-3" style="background: linear-gradient(90deg, {{ $item->color_primario }}, {{ $item->color_secundario }});"></div>
                            <div class="p-5">
                                <div class="flex items-start gap-4">
                                    <div class="flex h-16 w-16 shrink-0 items-center justify-center overflow-hidden rounded-2xl bg-slate-100 text-xl font-black dark:bg-zinc-900">
                                        @if($item->logo)<img src="{{ asset('storage/'.$item->logo) }}" class="h-full w-full object-contain p-2">@else{{ Str::upper(Str::substr($item->nombre,0,2)) }}@endif
                                    </div>
                                    <div class="min-w-0 flex-1">
                                        <div class="flex flex-wrap items-center gap-2"><h3 class="truncate text-lg font-black">{{ $item->nombre }}</h3><span class="rounded-full px-2 py-1 text-[10px] font-black {{ $item->activo ? 'bg-emerald-100 text-emerald-700' : 'bg-slate-200 text-slate-600' }}">{{ $item->activo ? 'ACTIVA' : 'INACTIVA' }}</span></div>
                                        <p class="mt-1 text-xs uppercase tracking-wider text-slate-500">{{ $item->tipo }}</p>
                                        <p class="mt-3 text-sm text-slate-600 dark:text-zinc-300">{{ $item->contacto ?: 'Sin contacto' }}</p>
                                        <p class="text-xs text-slate-500">{{ $item->email ?: 'Sin correo' }}</p>
                                    </div>
                                </div>
                                <div class="mt-5 grid grid-cols-3 gap-2 text-center text-xs">
                                    <div class="rounded-xl bg-slate-50 p-2 dark:bg-zinc-900"><strong class="block text-base">{{ $item->personas_count ?? $item->personas()->count() }}</strong>Personas</div>
                                    <div class="rounded-xl bg-slate-50 p-2 dark:bg-zinc-900"><strong class="block text-base">{{ $item->proyectos()->count() }}</strong>Proyectos</div>
                                    <div class="rounded-xl bg-slate-50 p-2 dark:bg-zinc-900"><strong class="block text-base">{{ $item->archivos()->count() }}</strong>Archivos</div>
                                </div>
                                <div class="mt-4 flex gap-2">
                                    <flux:button type="button" wire:click="editarMarca({{ $item->id }})" class="flex-1 rounded-xl border border-slate-200 px-3 py-2 text-xs font-black hover:border-[#006492] dark:border-zinc-700">Editar</flux:button>
                                    <flux:button type="button" wire:click="alternarMarca({{ $item->id }})" class="rounded-xl border border-slate-200 px-3 py-2 text-xs font-black dark:border-zinc-700">{{ $item->activo ? 'Desactivar' : 'Activar' }}</flux:button>
                                    <flux:button type="button" @click="confirmDelete('La marca se archivará, sin borrar sus datos relacionados.', {method:'eliminarMarca', id:{{ $item->id }}})" class="rounded-xl border border-red-200 px-3 py-2 text-xs font-black text-red-600">Archivar</flux:button>
                                </div>
                            </div>
                        </article>
                    @empty
                        <div class="col-span-full rounded-3xl border border-dashed border-slate-300 p-12 text-center text-slate-500 dark:border-zinc-700">No hay marcas para mostrar.</div>
                    @endforelse
                </div>
            </div>
        @break

        @case('personas')
            <div class="grid gap-6 xl:grid-cols-[430px_1fr]">
                <div id="studio-form" class="self-start space-y-4 xl:sticky xl:top-4">
                    <form wire:submit="guardarPersona" class="rounded-3xl border border-slate-200 bg-white p-5 shadow-sm dark:border-zinc-800 dark:bg-zinc-950">
                        <div class="flex items-center justify-between"><div><p class="text-xs font-bold uppercase tracking-widest text-[#006492]">Directorio</p><h2 class="text-xl font-black">{{ $personaId ? 'Editar persona' : 'Nueva persona' }}</h2></div>@if($personaId)<flux:button type="button" wire:click="limpiarPersona" class="text-xs font-bold text-slate-500">Cancelar</flux:button>@endif</div>
                        <div class="mt-5 space-y-4">
                            <label class="block text-sm font-bold">Nombre completo<flux:input wire:model="personaNombre" class="mt-1 w-full rounded-xl border-slate-300 dark:border-zinc-700 dark:bg-zinc-900" /></label>
                            <div class="grid grid-cols-2 gap-3">
                                <label class="text-sm font-bold">Tipo<flux:select wire:model="personaTipo" class="mt-1 w-full rounded-xl border-slate-300 dark:border-zinc-700 dark:bg-zinc-900">@foreach(['contacto','participante','ponente','empleado','proveedor','cliente','otro'] as $v)<flux:select.option value="{{ $v }}">{{ ucfirst($v) }}</flux:select.option>@endforeach</flux:select></label>
                                <label class="text-sm font-bold">Marca<flux:select wire:model="personaMarcaId" class="mt-1 w-full rounded-xl border-slate-300 dark:border-zinc-700 dark:bg-zinc-900"><flux:select.option value="">Sin marca</flux:select.option>@foreach($brands as $brand)<flux:select.option value="{{ $brand->id }}">{{ $brand->nombre }}</flux:select.option>@endforeach</flux:select></label>
                            </div>
                            <div class="grid grid-cols-2 gap-3">
                                <label class="text-sm font-bold">Cargo<flux:input wire:model="personaCargo" class="mt-1 w-full rounded-xl border-slate-300 dark:border-zinc-700 dark:bg-zinc-900" /></label>
                                <label class="text-sm font-bold">Organización<flux:input wire:model="personaOrganizacion" class="mt-1 w-full rounded-xl border-slate-300 dark:border-zinc-700 dark:bg-zinc-900" /></label>
                            </div>
                            <div class="grid grid-cols-2 gap-3">
                                <label class="text-sm font-bold">Correo<flux:input wire:model="personaEmail" type="email" class="mt-1 w-full rounded-xl border-slate-300 dark:border-zinc-700 dark:bg-zinc-900" /></label>
                                <label class="text-sm font-bold">Teléfono<flux:input wire:model="personaTelefono" class="mt-1 w-full rounded-xl border-slate-300 dark:border-zinc-700 dark:bg-zinc-900" /></label>
                            </div>
                            <label class="block text-sm font-bold">Identificador o folio<flux:input wire:model="personaIdentificador" class="mt-1 w-full rounded-xl border-slate-300 dark:border-zinc-700 dark:bg-zinc-900" /></label>
                            <label class="block text-sm font-bold">Etiquetas<flux:input wire:model="personaTags" class="mt-1 w-full rounded-xl border-slate-300 dark:border-zinc-700 dark:bg-zinc-900" placeholder="ponente, cliente, evento" /></label>
                            <label class="block text-sm font-bold">Fotografía<flux:input wire:model="personaFoto" type="file" accept="image/*" class="mt-1 block w-full text-xs" /></label>
                            <label class="block text-sm font-bold">Notas<flux:textarea wire:model="personaNotas" rows="3" class="mt-1 w-full rounded-xl border-slate-300 dark:border-zinc-700 dark:bg-zinc-900"></flux:textarea></label>
                            <label class="flex items-center gap-3 rounded-xl bg-slate-50 p-3 text-sm font-bold dark:bg-zinc-900"><flux:checkbox wire:model="personaActivo" class="rounded" /> Persona activa</label>
                            <flux:button variant="primary" type="submit" class="w-full rounded-xl bg-[#006492] px-4 py-3 text-sm font-black text-white">{{ $personaId ? 'Guardar cambios' : 'Agregar persona' }}</flux:button>
                        </div>
                    </form>
                    <form wire:submit="importarPersonasCsv" class="rounded-3xl border border-dashed border-[#88AC2E] bg-[#88AC2E]/5 p-5">
                        <h3 class="font-black">Importación masiva CSV</h3>
                        <p class="mt-1 text-xs text-slate-500">Importa nombre, tipo, cargo, organización, contacto y etiquetas.</p>
                        <flux:input wire:model="personasCsv" type="file" accept=".csv,text/csv" class="mt-4 block w-full text-xs" />
                        <div class="mt-4 flex gap-2">
                            <flux:button type="submit" class="rounded-xl bg-[#88AC2E] px-4 py-2 text-xs font-black text-white">Importar</flux:button>
                            <a href="{{ route('studio.personas.plantilla') }}" class="rounded-xl border border-slate-300 px-4 py-2 text-xs font-black">Plantilla</a>
                            <a href="{{ route('studio.personas.exportar') }}" class="rounded-xl border border-slate-300 px-4 py-2 text-xs font-black">Exportar</a>
                        </div>
                    </form>
                </div>
                <div class="overflow-hidden rounded-3xl border border-slate-200 bg-white shadow-sm dark:border-zinc-800 dark:bg-zinc-950">
                    <div class="overflow-x-auto">
                        <table class="min-w-full text-sm">
                            <thead class="bg-slate-50 text-left text-xs uppercase tracking-wider text-slate-500 dark:bg-zinc-900"><tr><th class="p-4">Persona</th><th class="p-4">Contacto</th><th class="p-4">Marca</th><th class="p-4">Etiquetas</th><th class="p-4 text-right">Acciones</th></tr></thead>
                            <tbody class="divide-y divide-slate-100 dark:divide-zinc-800">
                                @forelse($items as $item)
                                    <tr class="hover:bg-slate-50/70 dark:hover:bg-zinc-900/50">
                                        <td class="p-4"><div class="flex items-center gap-3"><div class="h-12 w-12 overflow-hidden rounded-2xl bg-slate-100 dark:bg-zinc-900">@if($item->foto)<img src="{{ asset('storage/'.$item->foto) }}" class="h-full w-full object-cover">@else<div class="flex h-full items-center justify-center font-black">{{ Str::upper(Str::substr($item->nombre,0,2)) }}</div>@endif</div><div><strong>{{ $item->nombre }}</strong><p class="text-xs text-slate-500">{{ $item->cargo ?: ucfirst($item->tipo) }}{{ $item->organizacion ? ' · '.$item->organizacion : '' }}</p></div></div></td>
                                        <td class="p-4 text-xs"><p>{{ $item->email ?: '—' }}</p><p class="text-slate-500">{{ $item->telefono ?: '—' }}</p></td>
                                        <td class="p-4 text-xs">{{ $item->marca?->nombre ?? 'Sin marca' }}</td>
                                        <td class="p-4"><div class="flex flex-wrap gap-1">@foreach($item->tags ?? [] as $tag)<span class="rounded-full bg-slate-100 px-2 py-1 text-[10px] font-bold dark:bg-zinc-900">{{ $tag }}</span>@endforeach</div></td>
                                        <td class="p-4"><div class="flex justify-end gap-2"><flux:button type="button" wire:click="editarPersona({{ $item->id }})" class="rounded-lg border px-3 py-2 text-xs font-bold dark:border-zinc-700">Editar</flux:button><flux:button type="button" wire:click="alternarPersona({{ $item->id }})" class="rounded-lg border px-3 py-2 text-xs font-bold dark:border-zinc-700">{{ $item->activo ? 'Desactivar' : 'Activar' }}</flux:button><flux:button type="button" @click="confirmDelete('La persona se archivará.', {method:'eliminarPersona', id:{{ $item->id }}})" class="rounded-lg border border-red-200 px-3 py-2 text-xs font-bold text-red-600">Archivar</flux:button></div></td>
                                    </tr>
                                @empty<tr><td colspan="5" class="p-12 text-center text-slate-500">No hay personas registradas.</td></tr>@endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        @break

        @case('proyectos')
            <div class="grid gap-6 xl:grid-cols-[430px_1fr]">
                <form id="studio-form" wire:submit="guardarProyecto" class="self-start rounded-3xl border border-slate-200 bg-white p-5 shadow-sm dark:border-zinc-800 dark:bg-zinc-950 xl:sticky xl:top-4">
                    <div class="flex items-center justify-between"><div><p class="text-xs font-bold uppercase tracking-widest text-[#006492]">Campañas</p><h2 class="text-xl font-black">{{ $proyectoId ? 'Editar proyecto' : 'Nuevo proyecto' }}</h2></div>@if($proyectoId)<flux:button type="button" wire:click="limpiarProyecto" class="text-xs font-bold text-slate-500">Cancelar</flux:button>@endif</div>
                    <div class="mt-5 space-y-4">
                        <label class="block text-sm font-bold">Nombre<flux:input wire:model="proyectoNombre" class="mt-1 w-full rounded-xl border-slate-300 dark:border-zinc-700 dark:bg-zinc-900" /></label>
                        <label class="block text-sm font-bold">Marca<flux:select wire:model="proyectoMarcaId" class="mt-1 w-full rounded-xl border-slate-300 dark:border-zinc-700 dark:bg-zinc-900"><flux:select.option value="">Sin marca</flux:select.option>@foreach($brands as $brand)<flux:select.option value="{{ $brand->id }}">{{ $brand->nombre }}</flux:select.option>@endforeach</flux:select></label>
                        <div class="grid grid-cols-2 gap-3">
                            <label class="text-sm font-bold">Tipo<flux:select wire:model="proyectoTipo" class="mt-1 w-full rounded-xl border-slate-300 dark:border-zinc-700 dark:bg-zinc-900">@foreach(['campaña','evento','redes','reconocimientos','credenciales','impresión','otro'] as $v)<flux:select.option value="{{ $v }}">{{ ucfirst($v) }}</flux:select.option>@endforeach</flux:select></label>
                            <label class="text-sm font-bold">Prioridad<flux:select wire:model="proyectoPrioridad" class="mt-1 w-full rounded-xl border-slate-300 dark:border-zinc-700 dark:bg-zinc-900">@foreach(['baja','media','alta','urgente'] as $v)<flux:select.option value="{{ $v }}">{{ ucfirst($v) }}</flux:select.option>@endforeach</flux:select></label>
                        </div>
                        <label class="block text-sm font-bold">Estado<flux:select wire:model="proyectoEstado" class="mt-1 w-full rounded-xl border-slate-300 dark:border-zinc-700 dark:bg-zinc-900">@foreach(['borrador','en_proceso','revision','aprobado','entregado','archivado'] as $v)<flux:select.option value="{{ $v }}">{{ ucfirst(str_replace('_',' ',$v)) }}</flux:select.option>@endforeach</flux:select></label>
                        <div class="grid grid-cols-2 gap-3"><label class="text-sm font-bold">Inicio<flux:input wire:model="proyectoInicio" type="date" class="mt-1 w-full rounded-xl border-slate-300 dark:border-zinc-700 dark:bg-zinc-900" /></label><label class="text-sm font-bold">Entrega<flux:input wire:model="proyectoEntrega" type="date" class="mt-1 w-full rounded-xl border-slate-300 dark:border-zinc-700 dark:bg-zinc-900" /></label></div>
                        <label class="block text-sm font-bold">Descripción<flux:textarea wire:model="proyectoDescripcion" rows="4" class="mt-1 w-full rounded-xl border-slate-300 dark:border-zinc-700 dark:bg-zinc-900"></flux:textarea></label>
                        <label class="block text-sm font-bold">Etiquetas<flux:input wire:model="proyectoTags" class="mt-1 w-full rounded-xl border-slate-300 dark:border-zinc-700 dark:bg-zinc-900" /></label>
                        <fieldset class="rounded-2xl border border-slate-200 p-3 dark:border-zinc-700"><legend class="px-2 text-sm font-black">Personas vinculadas</legend><div class="max-h-40 space-y-2 overflow-auto">@foreach($people as $person)<label class="flex items-center gap-2 text-xs"><flux:checkbox wire:model="proyectoPersonas" value="{{ $person->id }}" class="rounded" />{{ $person->nombre }} <span class="text-slate-400">{{ $person->cargo }}</span></label>@endforeach</div></fieldset>
                        <flux:button variant="primary" type="submit" class="w-full rounded-xl bg-[#006492] px-4 py-3 text-sm font-black text-white">{{ $proyectoId ? 'Guardar cambios' : 'Crear proyecto' }}</flux:button>
                    </div>
                </form>
                <div class="grid gap-4 lg:grid-cols-2">
                    @forelse($items as $item)
                        @php $priority = ['urgente'=>'bg-red-100 text-red-700','alta'=>'bg-orange-100 text-orange-700','media'=>'bg-blue-100 text-blue-700','baja'=>'bg-slate-100 text-slate-600'][$item->prioridad] ?? 'bg-slate-100'; @endphp
                        <article class="rounded-3xl border border-slate-200 bg-white p-5 shadow-sm dark:border-zinc-800 dark:bg-zinc-950">
                            <div class="flex items-start justify-between gap-3"><div><span class="rounded-full px-2 py-1 text-[10px] font-black {{ $priority }}">{{ strtoupper($item->prioridad) }}</span><h3 class="mt-3 text-xl font-black">{{ $item->nombre }}</h3><p class="text-xs text-slate-500">{{ $item->marca?->nombre ?? 'Sin marca' }} · {{ ucfirst($item->tipo) }}</p></div><span class="rounded-full bg-slate-100 px-3 py-1 text-[10px] font-black uppercase dark:bg-zinc-900">{{ str_replace('_',' ',$item->estado) }}</span></div>
                            <p class="mt-4 line-clamp-3 text-sm leading-6 text-slate-600 dark:text-zinc-300">{{ $item->descripcion ?: 'Sin descripción.' }}</p>
                            <div class="mt-4 grid grid-cols-3 gap-2 text-center text-xs"><div class="rounded-xl bg-slate-50 p-2 dark:bg-zinc-900"><strong class="block">{{ $item->personas->count() }}</strong>Personas</div><div class="rounded-xl bg-slate-50 p-2 dark:bg-zinc-900"><strong class="block">{{ $item->fecha_inicio?->format('d/m') ?? '—' }}</strong>Inicio</div><div class="rounded-xl bg-slate-50 p-2 dark:bg-zinc-900"><strong class="block">{{ $item->fecha_entrega?->format('d/m') ?? '—' }}</strong>Entrega</div></div>
                            <div class="mt-4 flex gap-2"><flux:button type="button" wire:click="editarProyecto({{ $item->id }})" class="flex-1 rounded-xl border px-3 py-2 text-xs font-black dark:border-zinc-700">Editar</flux:button><flux:button type="button" @click="confirmDelete('El proyecto se archivará.', {method:'eliminarProyecto', id:{{ $item->id }}})" class="rounded-xl border border-red-200 px-3 py-2 text-xs font-black text-red-600">Archivar</flux:button></div>
                        </article>
                    @empty<div class="col-span-full rounded-3xl border border-dashed p-12 text-center text-slate-500">No hay proyectos.</div>@endforelse
                </div>
            </div>
        @break

        @case('biblioteca')
            <div class="grid gap-6 xl:grid-cols-[420px_1fr]">
                <form id="studio-form" wire:submit="guardarArchivo" class="self-start rounded-3xl border border-slate-200 bg-white p-5 shadow-sm dark:border-zinc-800 dark:bg-zinc-950 xl:sticky xl:top-4">
                    <div class="flex items-center justify-between"><div><p class="text-xs font-bold uppercase tracking-widest text-[#006492]">Recursos</p><h2 class="text-xl font-black">{{ $archivoId ? 'Editar archivo' : 'Agregar archivo' }}</h2></div>@if($archivoId)<flux:button type="button" wire:click="limpiarArchivo" class="text-xs font-bold text-slate-500">Cancelar</flux:button>@endif</div>
                    <div class="mt-5 space-y-4">
                        <label class="block text-sm font-bold">Nombre<flux:input wire:model="archivoNombre" class="mt-1 w-full rounded-xl border-slate-300 dark:border-zinc-700 dark:bg-zinc-900" /></label>
                        <div class="grid grid-cols-2 gap-3"><label class="text-sm font-bold">Categoría<flux:select wire:model="archivoCategoria" class="mt-1 w-full rounded-xl border-slate-300 dark:border-zinc-700 dark:bg-zinc-900">@foreach(['imagen','logo','fondo','marco','firma','sello','icono','documento','otro'] as $v)<flux:select.option value="{{ $v }}">{{ ucfirst($v) }}</flux:select.option>@endforeach</flux:select></label><label class="text-sm font-bold">Marca<flux:select wire:model="archivoMarcaId" class="mt-1 w-full rounded-xl border-slate-300 dark:border-zinc-700 dark:bg-zinc-900"><flux:select.option value="">Sin marca</flux:select.option>@foreach($brands as $brand)<flux:select.option value="{{ $brand->id }}">{{ $brand->nombre }}</flux:select.option>@endforeach</flux:select></label></div>
                        <label class="block text-sm font-bold">Proyecto<flux:select wire:model="archivoProyectoId" class="mt-1 w-full rounded-xl border-slate-300 dark:border-zinc-700 dark:bg-zinc-900"><flux:select.option value="">Sin proyecto</flux:select.option>@foreach($projects as $project)<flux:select.option value="{{ $project->id }}">{{ $project->nombre }}</flux:select.option>@endforeach</flux:select></label>
                        <label class="block rounded-2xl border-2 border-dashed border-slate-300 p-4 text-sm font-bold dark:border-zinc-700">Archivo {{ $archivoId ? '(opcional para reemplazar)' : '' }}<flux:input wire:model="archivoUpload" type="file" accept=".jpg,.jpeg,.png,.webp,.pdf,.svg" class="mt-3 block w-full text-xs" /><span class="mt-2 block text-xs font-normal text-slate-500">Imágenes, PDF o SVG. Máximo 30 MB.</span></label>
                        <label class="block text-sm font-bold">Etiquetas<flux:input wire:model="archivoTags" class="mt-1 w-full rounded-xl border-slate-300 dark:border-zinc-700 dark:bg-zinc-900" /></label>
                        <label class="block text-sm font-bold">Descripción<flux:textarea wire:model="archivoDescripcion" rows="3" class="mt-1 w-full rounded-xl border-slate-300 dark:border-zinc-700 dark:bg-zinc-900"></flux:textarea></label>
                        <label class="flex items-center gap-3 rounded-xl bg-slate-50 p-3 text-sm font-bold dark:bg-zinc-900"><flux:checkbox wire:model="archivoActivo" class="rounded" /> Disponible para usar</label>
                        <flux:button variant="primary" type="submit" class="w-full rounded-xl bg-[#006492] px-4 py-3 text-sm font-black text-white">{{ $archivoId ? 'Guardar cambios' : 'Subir a biblioteca' }}</flux:button>
                    </div>
                </form>
                <div class="grid auto-rows-min gap-4 sm:grid-cols-2 lg:grid-cols-3 2xl:grid-cols-4">
                    @forelse($items as $item)
                        <article class="group overflow-hidden rounded-3xl border border-slate-200 bg-white shadow-sm dark:border-zinc-800 dark:bg-zinc-950">
                            <div class="relative aspect-[4/3] overflow-hidden bg-slate-100 dark:bg-zinc-900">
                                @if(str_starts_with($item->mime ?? '', 'image/'))
                                    <img src="{{ asset('storage/'.$item->archivo) }}" class="h-full w-full object-cover transition group-hover:scale-105">
                                @else
                                    <div class="flex h-full flex-col items-center justify-center text-slate-500"><span class="text-4xl">PDF</span><span class="mt-2 text-xs font-black">{{ strtoupper($item->extension ?? 'FILE') }}</span></div>
                                @endif
                                <span class="absolute left-3 top-3 rounded-full bg-black/65 px-2 py-1 text-[10px] font-black uppercase text-white">{{ $item->categoria }}</span>
                                @if(!$item->activo)<span class="absolute right-3 top-3 rounded-full bg-red-600 px-2 py-1 text-[10px] font-black text-white">INACTIVO</span>@endif
                            </div>
                            <div class="p-4">
                                <h3 class="truncate font-black">{{ $item->nombre }}</h3>
                                <p class="mt-1 text-xs text-slate-500">{{ $item->ancho && $item->alto ? $item->ancho.' × '.$item->alto.' px · ' : '' }}{{ number_format(($item->peso ?? 0)/1024, 0) }} KB</p>
                                <p class="mt-2 truncate text-xs text-slate-500">{{ $item->marca?->nombre ?? 'Sin marca' }}{{ $item->proyecto ? ' · '.$item->proyecto->nombre : '' }}</p>
                                <div class="mt-4 flex gap-2"><a href="{{ asset('storage/'.$item->archivo) }}" target="_blank" class="flex-1 rounded-xl border px-3 py-2 text-center text-xs font-black dark:border-zinc-700">Ver</a><flux:button type="button" wire:click="editarArchivo({{ $item->id }})" class="rounded-xl border px-3 py-2 text-xs font-black dark:border-zinc-700">Editar</flux:button><flux:button type="button" wire:click="alternarArchivo({{ $item->id }})" class="rounded-xl border px-3 py-2 text-xs font-black dark:border-zinc-700">{{ $item->activo ? 'Ocultar' : 'Activar' }}</flux:button><flux:button type="button" @click="confirmDelete('El archivo se enviará a la papelera.', {method:'eliminarArchivo', id:{{ $item->id }}})" class="rounded-xl border border-red-200 px-3 py-2 text-xs font-black text-red-600">×</flux:button></div>
                            </div>
                        </article>
                    @empty<div class="col-span-full rounded-3xl border border-dashed p-12 text-center text-slate-500">La biblioteca está vacía.</div>@endforelse
                </div>
            </div>
        @break

        @case('plantillas')
            <div class="grid gap-6 2xl:grid-cols-[560px_1fr]">
                <form id="studio-form" wire:submit="guardarPlantilla" class="self-start rounded-3xl border border-slate-200 bg-white p-5 shadow-sm dark:border-zinc-800 dark:bg-zinc-950 2xl:sticky 2xl:top-4">
                    <div class="flex items-center justify-between"><div><p class="text-xs font-bold uppercase tracking-widest text-[#006492]">Editor visual</p><h2 class="text-xl font-black">{{ $plantillaId ? 'Editar plantilla' : 'Nueva plantilla' }}</h2></div>@if($plantillaId)<flux:button type="button" wire:click="limpiarPlantilla" class="text-xs font-bold text-slate-500">Cancelar</flux:button>@endif</div>
                    <div class="mt-5 grid gap-4 sm:grid-cols-2">
                        <label class="sm:col-span-2 text-sm font-bold">Nombre<flux:input wire:model="plantillaNombre" class="mt-1 w-full rounded-xl border-slate-300 dark:border-zinc-700 dark:bg-zinc-900" /></label>
                        <label class="text-sm font-bold">Tipo<flux:select wire:model="plantillaTipo" class="mt-1 w-full rounded-xl border-slate-300 dark:border-zinc-700 dark:bg-zinc-900">@foreach(['general','reconocimiento','credencial','red_social','invitacion','diploma','flyer','portada'] as $v)<flux:select.option value="{{ $v }}">{{ ucfirst(str_replace('_',' ',$v)) }}</flux:select.option>@endforeach</flux:select></label>
                        <label class="text-sm font-bold">Marca<flux:select wire:model="plantillaMarcaId" class="mt-1 w-full rounded-xl border-slate-300 dark:border-zinc-700 dark:bg-zinc-900"><flux:select.option value="">Sin marca</flux:select.option>@foreach($brands as $brand)<flux:select.option value="{{ $brand->id }}">{{ $brand->nombre }}</flux:select.option>@endforeach</flux:select></label>
                        <label class="text-sm font-bold">Ancho<flux:input wire:model.live="plantillaAncho" type="number" class="mt-1 w-full rounded-xl border-slate-300 dark:border-zinc-700 dark:bg-zinc-900" /></label>
                        <label class="text-sm font-bold">Alto<flux:input wire:model.live="plantillaAlto" type="number" class="mt-1 w-full rounded-xl border-slate-300 dark:border-zinc-700 dark:bg-zinc-900" /></label>
                        <label class="sm:col-span-2 text-sm font-bold">Aplicar preset<flux:select wire:change="aplicarPresetPlantilla($event.target.value)" class="mt-1 w-full rounded-xl border-slate-300 dark:border-zinc-700 dark:bg-zinc-900"><flux:select.option value="">Seleccionar formato...</flux:select.option>@foreach($presets as $preset)<flux:select.option value="{{ $preset->id }}">{{ $preset->red_social }} · {{ $preset->nombre }} ({{ $preset->ancho }}×{{ $preset->alto }})</flux:select.option>@endforeach</flux:select></label>
                        <label class="sm:col-span-2 text-sm font-bold">Imagen de fondo<flux:select wire:model.live="plantillaFondoId" class="mt-1 w-full rounded-xl border-slate-300 dark:border-zinc-700 dark:bg-zinc-900"><flux:select.option value="">Fondo blanco</flux:select.option>@foreach($assets->filter(fn($a)=>str_starts_with($a->mime ?? '', 'image/')) as $asset)<flux:select.option value="{{ $asset->id }}">{{ $asset->nombre }}</flux:select.option>@endforeach</flux:select></label>
                        <label class="text-sm font-bold">Estado<flux:select wire:model="plantillaEstado" class="mt-1 w-full rounded-xl border-slate-300 dark:border-zinc-700 dark:bg-zinc-900">@foreach(['borrador','revision','aprobado','archivado'] as $v)<flux:select.option value="{{ $v }}">{{ ucfirst($v) }}</flux:select.option>@endforeach</flux:select></label>
                        <label class="flex items-end gap-3 rounded-xl bg-slate-50 p-3 text-sm font-bold dark:bg-zinc-900"><flux:checkbox wire:model="plantillaActivo" class="rounded" /> Plantilla activa</label>
                        <label class="sm:col-span-2 text-sm font-bold">Descripción<flux:textarea wire:model="plantillaDescripcion" rows="2" class="mt-1 w-full rounded-xl border-slate-300 dark:border-zinc-700 dark:bg-zinc-900"></flux:textarea></label>
                        <div class="sm:col-span-2 rounded-2xl border border-amber-200 bg-amber-50 p-4 dark:border-amber-900/70 dark:bg-amber-950/20">
                            <label class="flex items-center gap-3 text-sm font-black text-amber-900 dark:text-amber-200"><flux:checkbox wire:model.live="plantillaParaImpresion" class="rounded" /> Preparar también para impresión</label>
                            @if($plantillaParaImpresion)
                                <div class="mt-3 grid gap-3 sm:grid-cols-4">
                                    <label class="text-xs font-bold">Sangrado (mm)<flux:input wire:model="plantillaSangradoMm" type="number" min="0" max="20" step="0.5" class="mt-1 w-full rounded-xl border-amber-200 dark:border-amber-900 dark:bg-zinc-900" /></label>
                                    <label class="text-xs font-bold">Margen seguro (mm)<flux:input wire:model="plantillaMargenSeguroMm" type="number" min="0" max="50" step="0.5" class="mt-1 w-full rounded-xl border-amber-200 dark:border-amber-900 dark:bg-zinc-900" /></label>
                                    <label class="text-xs font-bold">Modo de color<flux:select wire:model="plantillaModoColor" class="mt-1 w-full rounded-xl border-amber-200 dark:border-amber-900 dark:bg-zinc-900"><flux:select.option value="rgb">RGB / digital</flux:select.option><flux:select.option value="cmyk">Preparación CMYK</flux:select.option></flux:select></label>
                                    <label class="flex items-end gap-2 rounded-xl bg-white/70 p-3 text-xs font-bold dark:bg-zinc-900"><flux:checkbox wire:model="plantillaMarcasCorte" class="rounded" /> Marcas de corte</label>
                                </div>
                                <p class="mt-2 text-[11px] text-amber-800 dark:text-amber-300">La vista muestra el área segura. Dompdf genera el archivo en RGB; la opción CMYK sirve como indicación para el flujo de imprenta.</p>
                            @endif
                        </div>
                    </div>

                    <div class="mt-6 rounded-2xl border border-slate-200 p-4 dark:border-zinc-700">
                        <div class="flex flex-wrap items-center justify-between gap-2">
                            <div>
                                <h3 class="font-black">Bloques dinámicos</h3>
                                <p class="text-xs text-slate-500">
                                    Usa variables como
                                    <code class="font-semibold text-[#006492]">@{{nombre}}</code>,
                                    <code class="font-semibold text-[#006492]">@{{cargo}}</code> y
                                    <code class="font-semibold text-[#006492]">@{{fecha}}</code>.
                                </p>
                            </div>

                            <div class="flex flex-wrap gap-1">
                                @foreach(['texto', 'imagen', 'qr', 'linea', 'caja'] as $type)
                                    <flux:button type="button"
                                        wire:click="agregarBloque('{{ $type }}')"
                                        class="rounded-lg bg-slate-100 px-2 py-1 text-[10px] font-black uppercase dark:bg-zinc-900">
                                        + {{ $type }}
                                    </flux:button>
                                @endforeach
                            </div>
                        </div>
                        <div class="mt-4 max-h-[520px] space-y-3 overflow-auto pr-1">
                            @foreach($plantillaBloques as $i => $block)
                                <div wire:key="block-{{ $block['uid'] ?? $i }}" class="rounded-2xl bg-slate-50 p-3 dark:bg-zinc-900">
                                    <div class="flex items-center justify-between gap-2"><strong class="text-xs uppercase tracking-wider">{{ $i+1 }}. {{ $block['tipo'] }}</strong><div class="flex gap-1"><flux:button type="button" wire:click="moverBloque({{ $i }},'arriba')" class="rounded bg-white px-2 py-1 text-xs dark:bg-zinc-800">↑</flux:button><flux:button type="button" wire:click="moverBloque({{ $i }},'abajo')" class="rounded bg-white px-2 py-1 text-xs dark:bg-zinc-800">↓</flux:button><flux:button type="button" wire:click="eliminarBloque({{ $i }})" class="rounded bg-red-100 px-2 py-1 text-xs text-red-700">×</flux:button></div></div>
                                    <div class="mt-3 grid grid-cols-2 gap-2">
                                        <label class="text-[11px] font-bold">Nombre<flux:input wire:model="plantillaBloques.{{ $i }}.nombre" class="mt-1 w-full rounded-lg border-slate-300 text-xs dark:border-zinc-700 dark:bg-zinc-950" /></label>
                                        <label class="text-[11px] font-bold">Contenido / variable<flux:input wire:model.live="plantillaBloques.{{ $i }}.contenido" class="mt-1 w-full rounded-lg border-slate-300 text-xs dark:border-zinc-700 dark:bg-zinc-950" /></label>
                                        @foreach(['x'=>'X %','y'=>'Y %','w'=>'Ancho %','h'=>'Alto %'] as $key=>$label)<label class="text-[11px] font-bold">{{ $label }}<flux:input wire:model.live="plantillaBloques.{{ $i }}.{{ $key }}" type="number" step="0.5" class="mt-1 w-full rounded-lg border-slate-300 text-xs dark:border-zinc-700 dark:bg-zinc-950" /></label>@endforeach
                                        <label class="text-[11px] font-bold">Tamaño fuente<flux:input wire:model.live="plantillaBloques.{{ $i }}.font_size" type="number" class="mt-1 w-full rounded-lg border-slate-300 text-xs dark:border-zinc-700 dark:bg-zinc-950" /></label>
                                        <label class="text-[11px] font-bold">Color<flux:input wire:model.live="plantillaBloques.{{ $i }}.color" type="color" class="mt-1 h-9 w-full rounded-lg border-slate-300 p-1 dark:border-zinc-700 dark:bg-zinc-950" /></label>
                                        <label class="text-[11px] font-bold">Alineación<flux:select wire:model.live="plantillaBloques.{{ $i }}.align" class="mt-1 w-full rounded-lg border-slate-300 text-xs dark:border-zinc-700 dark:bg-zinc-950"><flux:select.option value="left">Izquierda</flux:select.option><flux:select.option value="center">Centro</flux:select.option><flux:select.option value="right">Derecha</flux:select.option></flux:select></label>
                                        <label class="text-[11px] font-bold">Peso<flux:select wire:model.live="plantillaBloques.{{ $i }}.font_weight" class="mt-1 w-full rounded-lg border-slate-300 text-xs dark:border-zinc-700 dark:bg-zinc-950"><flux:select.option value="400">Normal</flux:select.option><flux:select.option value="600">Semibold</flux:select.option><flux:select.option value="700">Bold</flux:select.option><flux:select.option value="900">Black</flux:select.option></flux:select></label>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    </div>
                    <flux:button variant="primary" type="submit" class="mt-5 w-full rounded-xl bg-[#006492] px-4 py-3 text-sm font-black text-white">{{ $plantillaId ? 'Guardar nueva versión' : 'Crear plantilla' }}</flux:button>
                </form>

                <div class="space-y-6">
                    <div class="rounded-3xl border border-slate-200 bg-slate-100 p-5 dark:border-zinc-800 dark:bg-zinc-900">
                        <div class="mb-4 flex items-center justify-between"><div><h3 class="font-black">Vista previa en vivo</h3><p class="text-xs text-slate-500">{{ $plantillaAncho }} × {{ $plantillaAlto }} px</p></div><span class="rounded-full bg-white px-3 py-1 text-xs font-black text-slate-600 dark:bg-zinc-950">Escala automática</span></div>
                        <div class="relative mx-auto overflow-hidden bg-white shadow-xl touch-none" style="aspect-ratio: {{ max(1,$plantillaAncho) }}/{{ max(1,$plantillaAlto) }}; max-height: 620px;">
                            @php $bg = $assets->firstWhere('id', (int)$plantillaFondoId); @endphp
                            @if($bg)<img src="{{ asset('storage/'.$bg->archivo) }}" class="absolute inset-0 h-full w-full object-cover" draggable="false">@endif
                            @if($plantillaParaImpresion)
                                @php $safeInset = min(18, max(1.5, ((float)$plantillaMargenSeguroMm / 210) * 100)); @endphp
                                <div class="pointer-events-none absolute border-2 border-dashed border-amber-500/80" style="inset: {{ $safeInset }}%;" title="Área segura de impresión"></div>
                                @if($plantillaMarcasCorte)
                                    <div class="pointer-events-none absolute inset-1 border border-slate-900/50"></div>
                                @endif
                            @endif
                            @foreach($plantillaBloques as $block)
                                @php
                                    $uid = $block['uid'] ?? (string) Str::uuid();
                                    $blockW = max(1, min(100, (float)($block['w'] ?? 10)));
                                    $blockH = max(1, min(100, (float)($block['h'] ?? 10)));
                                    $visualStyle = "width:{$blockW}%;height:{$blockH}%;color:".($block['color'] ?? '#111827').";text-align:".($block['align'] ?? 'center').";font-weight:".($block['font_weight'] ?? '700').";font-size:clamp(10px,".(($block['font_size'] ?? 42)/15)."vw,46px);";

                                    // Se construyen los tokens sin escribir llaves dobles dentro de una
                                    // expresión Blade. Esto evita que el compilador cierre el echo antes
                                    // de tiempo y produzca errores ParseError en cualquier sección Studio.
                                    $openToken = '{' . '{';
                                    $closeToken = '}' . '}';
                                    $previewContent = strtr($block['contenido'] ?? '', [
                                        $openToken . 'nombre' . $closeToken => 'María López',
                                        $openToken . 'cargo' . $closeToken => 'Ponente',
                                        $openToken . 'organizacion' . $closeToken => 'Empresa Ejemplo',
                                        $openToken . 'fecha' . $closeToken => now()->format('d/m/Y'),
                                        $openToken . 'motivo' . $closeToken => 'Por su destacada participación',
                                    ]);
                                @endphp
                                <div
                                    wire:key="preview-block-{{ $uid }}"
                                    x-data="{
                                        left: {{ (float)($block['x'] ?? 0) }}, top: {{ (float)($block['y'] ?? 0) }},
                                        dragging: false, startX: 0, startY: 0, startLeft: 0, startTop: 0,
                                        start(e) { this.dragging = true; this.startX = e.clientX; this.startY = e.clientY; this.startLeft = this.left; this.startTop = this.top; e.currentTarget.setPointerCapture?.(e.pointerId); },
                                        move(e) { if (!this.dragging) return; const r = this.$el.parentElement.getBoundingClientRect(); this.left = Math.max(0, Math.min({{ 100 - $blockW }}, this.startLeft + ((e.clientX-this.startX)/r.width*100))); this.top = Math.max(0, Math.min({{ 100 - $blockH }}, this.startTop + ((e.clientY-this.startY)/r.height*100))); },
                                        stop() { if (!this.dragging) return; this.dragging = false; $wire.actualizarPosicionBloque('{{ $uid }}', this.left, this.top); }
                                    }"
                                    x-on:pointerdown.prevent="start($event)"
                                    x-on:pointermove.window="move($event)"
                                    x-on:pointerup.window="stop()"
                                    x-bind:style="`left:${left}%;top:${top}%;{{ $visualStyle }}`"
                                    class="absolute flex cursor-move select-none items-center overflow-hidden border border-dashed border-sky-500/70 bg-white/5 px-1 ring-0 transition-shadow hover:ring-2 hover:ring-sky-400/40"
                                    title="Arrastra para cambiar la posición">
                                    @if(($block['tipo'] ?? 'texto') === 'texto')
                                        <span class="pointer-events-none w-full">{{ $previewContent }}</span>
                                    @elseif(($block['tipo'] ?? '') === 'imagen')
                                        <div class="pointer-events-none flex h-full w-full items-center justify-center bg-slate-100 text-xs font-black text-slate-400">IMAGEN</div>
                                    @elseif(($block['tipo'] ?? '') === 'qr')
                                        <div class="pointer-events-none flex h-full aspect-square items-center justify-center bg-slate-900 text-[10px] font-black text-white">QR</div>
                                    @elseif(($block['tipo'] ?? '') === 'linea')
                                        <div class="pointer-events-none h-0.5 w-full bg-current"></div>
                                    @else
                                        <div class="pointer-events-none h-full w-full rounded-xl border-2 border-current"></div>
                                    @endif
                                </div>
                            @endforeach
                        </div>
                        <p class="mt-3 text-center text-xs font-semibold text-slate-500">Arrastra cualquier bloque directamente sobre la vista previa; X e Y se actualizan al soltar.</p>
                    </div>

                    <div class="grid gap-4 lg:grid-cols-2">
                        @forelse($items as $item)
                            <article class="rounded-3xl border border-slate-200 bg-white p-5 shadow-sm dark:border-zinc-800 dark:bg-zinc-950">
                                <div class="flex items-start justify-between gap-3"><div><span class="rounded-full bg-slate-100 px-2 py-1 text-[10px] font-black uppercase dark:bg-zinc-900">{{ str_replace('_',' ',$item->tipo) }}</span><h3 class="mt-3 text-lg font-black">{{ $item->nombre }}</h3><p class="text-xs text-slate-500">{{ $item->ancho }}×{{ $item->alto }} · v{{ $item->version }} · {{ $item->marca?->nombre ?? 'Sin marca' }}</p></div><span class="rounded-full px-2 py-1 text-[10px] font-black {{ $item->estado === 'aprobado' ? 'bg-emerald-100 text-emerald-700' : 'bg-amber-100 text-amber-700' }}">{{ strtoupper($item->estado) }}</span></div>
                                <p class="mt-3 line-clamp-2 text-sm text-slate-600 dark:text-zinc-300">{{ $item->descripcion ?: 'Sin descripción.' }}</p>
                                <div class="mt-4 flex gap-2"><flux:button type="button" wire:click="editarPlantilla({{ $item->id }})" class="flex-1 rounded-xl border px-3 py-2 text-xs font-black dark:border-zinc-700">Editar</flux:button><flux:button type="button" wire:click="duplicarPlantilla({{ $item->id }})" class="rounded-xl border px-3 py-2 text-xs font-black dark:border-zinc-700">Duplicar</flux:button><flux:button type="button" @click="confirmDelete('La plantilla se archivará.', {method:'eliminarPlantilla', id:{{ $item->id }}})" class="rounded-xl border border-red-200 px-3 py-2 text-xs font-black text-red-600">Archivar</flux:button></div>
                                @if($item->versiones->isNotEmpty())
                                    <details class="mt-4 rounded-xl bg-slate-50 p-3 text-xs dark:bg-zinc-900"><summary class="cursor-pointer font-black">Versiones anteriores ({{ $item->versiones->count() }})</summary><div class="mt-2 space-y-2">@foreach($item->versiones->take(5) as $version)<div class="flex items-center justify-between"><span>Versión {{ $version->version }} · {{ $version->created_at->format('d/m/Y H:i') }}</span><flux:button type="button" wire:click="restaurarVersion({{ $version->id }})" class="font-black text-[#006492]">Restaurar</flux:button></div>@endforeach</div></details>
                                @endif
                            </article>
                        @empty<div class="col-span-full rounded-3xl border border-dashed p-12 text-center text-slate-500">No hay plantillas.</div>@endforelse
                    </div>
                </div>
            </div>
        @break

        @case('generador')
            <div class="grid gap-6 xl:grid-cols-[440px_1fr]">
                <div class="self-start space-y-4 xl:sticky xl:top-4">
                    <section class="rounded-3xl border border-slate-200 bg-white p-5 shadow-sm dark:border-zinc-800 dark:bg-zinc-950">
                        <p class="text-xs font-bold uppercase tracking-widest text-[#006492]">Configuración</p>
                        <h2 class="text-xl font-black">Generar lote de documentos</h2>
                        <div class="mt-5 space-y-4">
                            <label class="block text-sm font-bold">Plantilla<flux:select wire:model="generadorPlantillaId" class="mt-1 w-full rounded-xl border-slate-300 dark:border-zinc-700 dark:bg-zinc-900"><flux:select.option value="">Selecciona una plantilla</flux:select.option>@foreach($templates as $template)<flux:select.option value="{{ $template->id }}">{{ $template->nombre }} · v{{ $template->version }}</flux:select.option>@endforeach</flux:select></label>
                            <label class="block text-sm font-bold">Marca<flux:select wire:model="generadorMarcaId" class="mt-1 w-full rounded-xl border-slate-300 dark:border-zinc-700 dark:bg-zinc-900"><flux:select.option value="">Sin marca</flux:select.option>@foreach($brands as $brand)<flux:select.option value="{{ $brand->id }}">{{ $brand->nombre }}</flux:select.option>@endforeach</flux:select></label>
                            <label class="block text-sm font-bold">Proyecto<flux:select wire:model="generadorProyectoId" class="mt-1 w-full rounded-xl border-slate-300 dark:border-zinc-700 dark:bg-zinc-900"><flux:select.option value="">Sin proyecto</flux:select.option>@foreach($projects as $project)<flux:select.option value="{{ $project->id }}">{{ $project->nombre }}</flux:select.option>@endforeach</flux:select></label>
                            <label class="block text-sm font-bold">Patrón de nombre<flux:input wire:model="generadorNombrePatron" class="mt-1 w-full rounded-xl border-slate-300 dark:border-zinc-700 dark:bg-zinc-900" /><span class="mt-1 block text-xs font-normal text-slate-500">Variables: {nombre}, {index}, {date}</span></label>
                        </div>
                    </section>
                    <form wire:submit="cargarCsvGenerador" class="rounded-3xl border border-dashed border-[#88AC2E] bg-[#88AC2E]/5 p-5">
                        <h3 class="font-black">Cargar datos desde CSV</h3>
                        <p class="mt-1 text-xs text-slate-500">La primera fila debe contener los nombres de las variables.</p>
                        <flux:input wire:model="generadorCsv" type="file" accept=".csv,text/csv" class="mt-4 block w-full text-xs" />
                        <div class="mt-4 grid grid-cols-2 gap-2"><flux:button type="submit" class="rounded-xl bg-[#88AC2E] px-4 py-2 text-xs font-black text-white">Leer CSV</flux:button><a href="{{ route('studio.generador.plantilla') }}" class="rounded-xl border border-slate-300 px-4 py-2 text-center text-xs font-black">Descargar ejemplo</a></div>
                        <flux:button type="button" wire:click="cargarPersonasGenerador" class="mt-2 w-full rounded-xl border border-slate-300 px-4 py-2 text-xs font-black">Usar catálogo de personas</flux:button>
                    </form>
                    @if($generadorFilas)
                        <div class="rounded-3xl border border-slate-200 bg-white p-5 shadow-sm dark:border-zinc-800 dark:bg-zinc-950">
                            <div class="flex items-center justify-between"><div><p class="text-xs font-bold uppercase text-slate-500">Listo para generar</p><strong class="text-2xl">{{ count($generadorFilas) }} documentos</strong></div><flux:button type="button" wire:click="limpiarGenerador" class="text-xs font-black text-red-600">Limpiar</flux:button></div>
                            <flux:button variant="primary" type="button" wire:click="descargarLote" wire:loading.attr="disabled" class="mt-5 w-full rounded-xl bg-[#006492] px-4 py-3 text-sm font-black text-white"><span wire:loading.remove wire:target="descargarLote">Descargar ZIP con PDFs</span><span wire:loading wire:target="descargarLote">Generando lote...</span></flux:button>
                        </div>
                    @endif
                </div>
                <section class="overflow-hidden rounded-3xl border border-slate-200 bg-white shadow-sm dark:border-zinc-800 dark:bg-zinc-950">
                    <div class="border-b border-slate-200 p-5 dark:border-zinc-800"><h3 class="text-lg font-black">Vista previa de datos</h3><p class="text-xs text-slate-500">Se muestran las primeras 100 filas. El lote admite hasta 1,000.</p></div>
                    @if($generadorFilas)
                        <div class="overflow-auto"><table class="min-w-full text-xs"><thead class="sticky top-0 bg-slate-50 dark:bg-zinc-900"><tr><th class="p-3 text-left">#</th>@foreach($generadorCabeceras as $header)<th class="p-3 text-left uppercase">{{ $header }}</th>@endforeach</tr></thead><tbody class="divide-y dark:divide-zinc-800">@foreach(array_slice($generadorFilas,0,100) as $i=>$row)<tr><td class="p-3 font-black">{{ $i+1 }}</td>@foreach($generadorCabeceras as $header)<td class="max-w-[240px] truncate p-3">{{ $row[$header] ?? '' }}</td>@endforeach</tr>@endforeach</tbody></table></div>
                    @else
                        <div class="p-16 text-center text-slate-500"><div class="text-5xl">CSV</div><h3 class="mt-4 font-black">Aún no hay datos</h3><p class="mt-1 text-sm">Carga un CSV o utiliza el catálogo de personas.</p></div>
                    @endif
                </section>
            </div>
        @break

        @case('solicitudes')
            <div class="grid gap-6 xl:grid-cols-[430px_1fr]">
                <form id="studio-form" wire:submit="guardarSolicitud" class="self-start rounded-3xl border border-slate-200 bg-white p-5 shadow-sm dark:border-zinc-800 dark:bg-zinc-950 xl:sticky xl:top-4">
                    <div class="flex items-center justify-between"><div><p class="text-xs font-bold uppercase tracking-widest text-[#006492]">Flujo de trabajo</p><h2 class="text-xl font-black">{{ $solicitudId ? 'Editar solicitud' : 'Nueva solicitud' }}</h2></div>@if($solicitudId)<flux:button type="button" wire:click="limpiarSolicitud" class="text-xs font-bold text-slate-500">Cancelar</flux:button>@endif</div>
                    <div class="mt-5 space-y-4">
                        <label class="block text-sm font-bold">Título<flux:input wire:model="solicitudTitulo" class="mt-1 w-full rounded-xl border-slate-300 dark:border-zinc-700 dark:bg-zinc-900" /></label>
                        <div class="grid grid-cols-2 gap-3"><label class="text-sm font-bold">Tipo<flux:select wire:model="solicitudTipo" class="mt-1 w-full rounded-xl border-slate-300 dark:border-zinc-700 dark:bg-zinc-900">@foreach(['diseño','reconocimiento','credencial','redes','impresión','edición','otro'] as $v)<flux:select.option value="{{ $v }}">{{ ucfirst($v) }}</flux:select.option>@endforeach</flux:select></label><label class="text-sm font-bold">Prioridad<flux:select wire:model="solicitudPrioridad" class="mt-1 w-full rounded-xl border-slate-300 dark:border-zinc-700 dark:bg-zinc-900">@foreach(['baja','media','alta','urgente'] as $v)<flux:select.option value="{{ $v }}">{{ ucfirst($v) }}</flux:select.option>@endforeach</flux:select></label></div>
                        <label class="block text-sm font-bold">Estado<flux:select wire:model="solicitudEstado" class="mt-1 w-full rounded-xl border-slate-300 dark:border-zinc-700 dark:bg-zinc-900">@foreach(['pendiente','en_proceso','revision','aprobada','entregada','cancelada'] as $v)<flux:select.option value="{{ $v }}">{{ ucfirst(str_replace('_',' ',$v)) }}</flux:select.option>@endforeach</flux:select></label>
                        <div class="grid grid-cols-2 gap-3"><label class="text-sm font-bold">Marca<flux:select wire:model="solicitudMarcaId" class="mt-1 w-full rounded-xl border-slate-300 dark:border-zinc-700 dark:bg-zinc-900"><flux:select.option value="">Sin marca</flux:select.option>@foreach($brands as $brand)<flux:select.option value="{{ $brand->id }}">{{ $brand->nombre }}</flux:select.option>@endforeach</flux:select></label><label class="text-sm font-bold">Proyecto<flux:select wire:model="solicitudProyectoId" class="mt-1 w-full rounded-xl border-slate-300 dark:border-zinc-700 dark:bg-zinc-900"><flux:select.option value="">Sin proyecto</flux:select.option>@foreach($projects as $project)<flux:select.option value="{{ $project->id }}">{{ $project->nombre }}</flux:select.option>@endforeach</flux:select></label></div>
                        <div class="grid grid-cols-2 gap-3"><label class="text-sm font-bold">Solicitante<flux:input wire:model="solicitudSolicitante" class="mt-1 w-full rounded-xl border-slate-300 dark:border-zinc-700 dark:bg-zinc-900" /></label><label class="text-sm font-bold">Contacto<flux:input wire:model="solicitudContacto" class="mt-1 w-full rounded-xl border-slate-300 dark:border-zinc-700 dark:bg-zinc-900" /></label></div>
                        <label class="block text-sm font-bold">Fecha de entrega<flux:input wire:model="solicitudEntrega" type="datetime-local" class="mt-1 w-full rounded-xl border-slate-300 dark:border-zinc-700 dark:bg-zinc-900" /></label>
                        <label class="block text-sm font-bold">Descripción<flux:textarea wire:model="solicitudDescripcion" rows="4" class="mt-1 w-full rounded-xl border-slate-300 dark:border-zinc-700 dark:bg-zinc-900"></flux:textarea></label>
                        <label class="block text-sm font-bold">Notas internas<flux:textarea wire:model="solicitudNotas" rows="2" class="mt-1 w-full rounded-xl border-slate-300 dark:border-zinc-700 dark:bg-zinc-900"></flux:textarea></label>
                        <flux:button variant="primary" type="submit" class="w-full rounded-xl bg-[#006492] px-4 py-3 text-sm font-black text-white">{{ $solicitudId ? 'Guardar cambios' : 'Registrar solicitud' }}</flux:button>
                    </div>
                </form>
                <div class="space-y-3">
                    @forelse($items as $item)
                        @php $priority = ['urgente'=>'border-red-400','alta'=>'border-orange-400','media'=>'border-blue-400','baja'=>'border-slate-300'][$item->prioridad] ?? 'border-slate-300'; @endphp
                        <article class="rounded-3xl border-l-4 {{ $priority }} border-y border-r border-slate-200 bg-white p-5 shadow-sm dark:border-y-zinc-800 dark:border-r-zinc-800 dark:bg-zinc-950">
                            <div class="flex flex-col gap-3 md:flex-row md:items-start md:justify-between"><div><div class="flex flex-wrap gap-2"><span class="rounded-full bg-slate-100 px-2 py-1 text-[10px] font-black uppercase dark:bg-zinc-900">{{ $item->tipo }}</span><span class="rounded-full bg-slate-100 px-2 py-1 text-[10px] font-black uppercase dark:bg-zinc-900">{{ str_replace('_',' ',$item->estado) }}</span><span class="rounded-full px-2 py-1 text-[10px] font-black uppercase {{ $item->prioridad === 'urgente' ? 'bg-red-100 text-red-700' : 'bg-amber-100 text-amber-700' }}">{{ $item->prioridad }}</span></div><h3 class="mt-3 text-lg font-black">{{ $item->titulo }}</h3><p class="text-xs text-slate-500">{{ $item->solicitante ?: 'Sin solicitante' }} · {{ $item->marca?->nombre ?? 'Sin marca' }}</p></div><div class="text-right text-xs"><span class="block font-black">{{ $item->fecha_entrega?->format('d/m/Y H:i') ?? 'Sin fecha' }}</span><span class="text-slate-500">Entrega</span></div></div>
                            <p class="mt-3 line-clamp-2 text-sm text-slate-600 dark:text-zinc-300">{{ $item->descripcion ?: 'Sin descripción.' }}</p>
                            <div class="mt-4 flex justify-end gap-2"><flux:button type="button" wire:click="editarSolicitud({{ $item->id }})" class="rounded-xl border px-3 py-2 text-xs font-black dark:border-zinc-700">Editar</flux:button><flux:button type="button" @click="confirmDelete('La solicitud se archivará.', {method:'eliminarSolicitud', id:{{ $item->id }}})" class="rounded-xl border border-red-200 px-3 py-2 text-xs font-black text-red-600">Archivar</flux:button></div>
                        </article>
                    @empty<div class="rounded-3xl border border-dashed p-12 text-center text-slate-500">No hay solicitudes.</div>@endforelse
                </div>
            </div>
        @break

        @case('publicaciones')
            <div class="grid gap-6 xl:grid-cols-[440px_1fr]">
                <form id="studio-form" wire:submit="guardarPublicacion" class="self-start rounded-3xl border border-slate-200 bg-white p-5 shadow-sm dark:border-zinc-800 dark:bg-zinc-950 xl:sticky xl:top-4">
                    <div class="flex items-center justify-between"><div><p class="text-xs font-bold uppercase tracking-widest text-[#006492]">Redes sociales</p><h2 class="text-xl font-black">{{ $publicacionId ? 'Editar publicación' : 'Nueva publicación' }}</h2></div>@if($publicacionId)<flux:button type="button" wire:click="limpiarPublicacion" class="text-xs font-bold text-slate-500">Cancelar</flux:button>@endif</div>
                    <div class="mt-5 space-y-4">
                        <label class="block text-sm font-bold">Título interno<flux:input wire:model="publicacionTitulo" class="mt-1 w-full rounded-xl border-slate-300 dark:border-zinc-700 dark:bg-zinc-900" /></label>
                        <div class="grid grid-cols-2 gap-3"><label class="text-sm font-bold">Red<flux:select wire:model="publicacionRed" class="mt-1 w-full rounded-xl border-slate-300 dark:border-zinc-700 dark:bg-zinc-900">@foreach(['Instagram','Facebook','WhatsApp','TikTok','YouTube','LinkedIn','X','Otra'] as $v)<flux:select.option value="{{ $v }}">{{ $v }}</flux:select.option>@endforeach</flux:select></label><label class="text-sm font-bold">Estado<flux:select wire:model="publicacionEstado" class="mt-1 w-full rounded-xl border-slate-300 dark:border-zinc-700 dark:bg-zinc-900">@foreach(['borrador','revision','aprobada','programada','publicada','cancelada'] as $v)<flux:select.option value="{{ $v }}">{{ ucfirst($v) }}</flux:select.option>@endforeach</flux:select></label></div>
                        <div class="grid grid-cols-2 gap-3"><label class="text-sm font-bold">Marca<flux:select wire:model="publicacionMarcaId" class="mt-1 w-full rounded-xl border-slate-300 dark:border-zinc-700 dark:bg-zinc-900"><flux:select.option value="">Sin marca</flux:select.option>@foreach($brands as $brand)<flux:select.option value="{{ $brand->id }}">{{ $brand->nombre }}</flux:select.option>@endforeach</flux:select></label><label class="text-sm font-bold">Proyecto<flux:select wire:model="publicacionProyectoId" class="mt-1 w-full rounded-xl border-slate-300 dark:border-zinc-700 dark:bg-zinc-900"><flux:select.option value="">Sin proyecto</flux:select.option>@foreach($projects as $project)<flux:select.option value="{{ $project->id }}">{{ $project->nombre }}</flux:select.option>@endforeach</flux:select></label></div>
                        <label class="block text-sm font-bold">Pieza multimedia<flux:select wire:model="publicacionArchivoId" class="mt-1 w-full rounded-xl border-slate-300 dark:border-zinc-700 dark:bg-zinc-900"><flux:select.option value="">Sin archivo</flux:select.option>@foreach($assets as $asset)<flux:select.option value="{{ $asset->id }}">{{ $asset->nombre }}</flux:select.option>@endforeach</flux:select></label>
                        <label class="block text-sm font-bold">Programación<flux:input wire:model="publicacionProgramada" type="datetime-local" class="mt-1 w-full rounded-xl border-slate-300 dark:border-zinc-700 dark:bg-zinc-900" /></label>
                        <label class="block text-sm font-bold">Copy<flux:textarea wire:model="publicacionCopy" rows="5" class="mt-1 w-full rounded-xl border-slate-300 dark:border-zinc-700 dark:bg-zinc-900"></flux:textarea></label>
                        <label class="block text-sm font-bold">Hashtags<flux:textarea wire:model="publicacionHashtags" rows="2" class="mt-1 w-full rounded-xl border-slate-300 dark:border-zinc-700 dark:bg-zinc-900" placeholder="#evento #reconocimiento"></flux:textarea></label>
                        <label class="block text-sm font-bold">URL publicada<flux:input wire:model="publicacionUrl" class="mt-1 w-full rounded-xl border-slate-300 dark:border-zinc-700 dark:bg-zinc-900" /></label>
                        <label class="block text-sm font-bold">Notas<flux:textarea wire:model="publicacionNotas" rows="2" class="mt-1 w-full rounded-xl border-slate-300 dark:border-zinc-700 dark:bg-zinc-900"></flux:textarea></label>
                        <flux:button variant="primary" type="submit" class="w-full rounded-xl bg-[#006492] px-4 py-3 text-sm font-black text-white">{{ $publicacionId ? 'Guardar cambios' : 'Guardar publicación' }}</flux:button>
                    </div>
                </form>
                <div class="grid gap-4 lg:grid-cols-2">
                    @forelse($items as $item)
                        <article class="overflow-hidden rounded-3xl border border-slate-200 bg-white shadow-sm dark:border-zinc-800 dark:bg-zinc-950">
                            @if($item->archivo && str_starts_with($item->archivo->mime ?? '', 'image/'))<img src="{{ asset('storage/'.$item->archivo->archivo) }}" class="aspect-video w-full object-cover">@endif
                            <div class="p-5"><div class="flex items-start justify-between gap-3"><div><span class="rounded-full bg-slate-100 px-2 py-1 text-[10px] font-black uppercase dark:bg-zinc-900">{{ $item->red_social }}</span><h3 class="mt-3 text-lg font-black">{{ $item->titulo }}</h3></div><span class="rounded-full bg-blue-100 px-2 py-1 text-[10px] font-black uppercase text-blue-700">{{ $item->estado }}</span></div><p class="mt-3 line-clamp-4 whitespace-pre-line text-sm leading-6 text-slate-600 dark:text-zinc-300">{{ $item->copy ?: 'Sin copy.' }}</p><p class="mt-3 line-clamp-2 text-xs font-semibold text-[#006492]">{{ $item->hashtags }}</p><div class="mt-4 flex items-center justify-between text-xs text-slate-500"><span>{{ $item->marca?->nombre ?? 'Sin marca' }}</span><span>{{ $item->programada_at?->format('d/m/Y H:i') ?? 'Sin programación' }}</span></div><div class="mt-4 flex justify-end gap-2">@if($item->url_publicacion)<a href="{{ $item->url_publicacion }}" target="_blank" class="rounded-xl border px-3 py-2 text-xs font-black dark:border-zinc-700">Abrir</a>@endif<flux:button type="button" wire:click="editarPublicacion({{ $item->id }})" class="rounded-xl border px-3 py-2 text-xs font-black dark:border-zinc-700">Editar</flux:button><flux:button type="button" @click="confirmDelete('La publicación se archivará.', {method:'eliminarPublicacion', id:{{ $item->id }}})" class="rounded-xl border border-red-200 px-3 py-2 text-xs font-black text-red-600">Archivar</flux:button></div></div>
                        </article>
                    @empty<div class="col-span-full rounded-3xl border border-dashed p-12 text-center text-slate-500">No hay publicaciones.</div>@endforelse
                </div>
            </div>
        @break

        @case('presets')
            <div class="grid gap-6 xl:grid-cols-[400px_1fr]">
                <form id="studio-form" wire:submit="guardarPreset" class="self-start rounded-3xl border border-slate-200 bg-white p-5 shadow-sm dark:border-zinc-800 dark:bg-zinc-950 xl:sticky xl:top-4">
                    <div class="flex items-center justify-between"><div><p class="text-xs font-bold uppercase tracking-widest text-[#006492]">Medidas</p><h2 class="text-xl font-black">{{ $presetId ? 'Editar preset' : 'Nuevo preset' }}</h2></div>@if($presetId)<flux:button type="button" wire:click="limpiarPreset" class="text-xs font-bold text-slate-500">Cancelar</flux:button>@endif</div>
                    <div class="mt-5 space-y-4">
                        <label class="block text-sm font-bold">Nombre<flux:input wire:model="presetNombre" class="mt-1 w-full rounded-xl border-slate-300 dark:border-zinc-700 dark:bg-zinc-900" /></label>
                        <label class="block text-sm font-bold">Red social<flux:input wire:model="presetRed" class="mt-1 w-full rounded-xl border-slate-300 dark:border-zinc-700 dark:bg-zinc-900" /></label>
                        <div class="grid grid-cols-2 gap-3"><label class="text-sm font-bold">Ancho<flux:input wire:model="presetAncho" type="number" class="mt-1 w-full rounded-xl border-slate-300 dark:border-zinc-700 dark:bg-zinc-900" /></label><label class="text-sm font-bold">Alto<flux:input wire:model="presetAlto" type="number" class="mt-1 w-full rounded-xl border-slate-300 dark:border-zinc-700 dark:bg-zinc-900" /></label></div>
                        <label class="block text-sm font-bold">Descripción<flux:textarea wire:model="presetDescripcion" rows="3" class="mt-1 w-full rounded-xl border-slate-300 dark:border-zinc-700 dark:bg-zinc-900"></flux:textarea></label>
                        <label class="flex items-center gap-3 rounded-xl bg-slate-50 p-3 text-sm font-bold dark:bg-zinc-900"><flux:checkbox wire:model="presetActivo" class="rounded" /> Preset activo</label>
                        <flux:button variant="primary" type="submit" class="w-full rounded-xl bg-[#006492] px-4 py-3 text-sm font-black text-white">{{ $presetId ? 'Guardar cambios' : 'Crear preset' }}</flux:button>
                    </div>
                </form>
                <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-3 2xl:grid-cols-4">
                    @forelse($items as $item)
                        <article class="rounded-3xl border border-slate-200 bg-white p-5 shadow-sm dark:border-zinc-800 dark:bg-zinc-950">
                            <div class="mx-auto flex max-h-40 min-h-24 items-center justify-center rounded-2xl bg-gradient-to-br from-[#006492]/15 to-[#88AC2E]/20" style="aspect-ratio: {{ $item->ancho }}/{{ $item->alto }};"><span class="text-sm font-black">{{ $item->ancho }} × {{ $item->alto }}</span></div>
                            <p class="mt-4 text-xs font-bold uppercase tracking-wider text-[#006492]">{{ $item->red_social }}</p><h3 class="mt-1 font-black">{{ $item->nombre }}</h3><p class="mt-2 line-clamp-2 text-xs text-slate-500">{{ $item->descripcion }}</p>
                            <div class="mt-4 flex gap-2"><flux:button type="button" wire:click="editarPreset({{ $item->id }})" class="flex-1 rounded-xl border px-3 py-2 text-xs font-black dark:border-zinc-700">Editar</flux:button><flux:button type="button" @click="confirmDelete('El preset se eliminará.', {method:'eliminarPreset', id:{{ $item->id }}})" class="rounded-xl border border-red-200 px-3 py-2 text-xs font-black text-red-600">Eliminar</flux:button></div>
                        </article>
                    @empty<div class="col-span-full rounded-3xl border border-dashed p-12 text-center text-slate-500">No hay presets.</div>@endforelse
                </div>
            </div>
        @break

        @case('exportaciones')
            <div class="overflow-hidden rounded-3xl border border-slate-200 bg-white shadow-sm dark:border-zinc-800 dark:bg-zinc-950">
                <div class="overflow-x-auto"><table class="min-w-full text-sm"><thead class="bg-slate-50 text-left text-xs uppercase tracking-wider text-slate-500 dark:bg-zinc-900"><tr><th class="p-4">Fecha</th><th class="p-4">Tipo</th><th class="p-4">Formato</th><th class="p-4">Cantidad</th><th class="p-4">Contexto</th><th class="p-4">Configuración</th><th class="p-4"></th></tr></thead><tbody class="divide-y dark:divide-zinc-800">@forelse($items as $item)<tr><td class="p-4 font-semibold">{{ $item->created_at->format('d/m/Y H:i') }}</td><td class="p-4">{{ str_replace('_',' ',$item->tipo) }}</td><td class="p-4 uppercase">{{ $item->formato ?: '—' }}</td><td class="p-4">{{ $item->cantidad }}</td><td class="p-4 text-xs">{{ $item->marca?->nombre ?? 'Sin marca' }}<br>{{ $item->proyecto?->nombre ?? '' }}<br>{{ $item->plantilla?->nombre ?? '' }}</td><td class="p-4"><code class="block max-w-md whitespace-normal rounded-lg bg-slate-100 p-2 text-[10px] dark:bg-zinc-900">{{ json_encode($item->configuracion, JSON_UNESCAPED_UNICODE) }}</code></td><td class="p-4 text-right"><flux:button type="button" @click="confirmDelete('Se eliminará solo el registro del historial.', {method:'eliminarExportacion', id:{{ $item->id }}})" class="text-xs font-black text-red-600">Eliminar</flux:button></td></tr>@empty<tr><td colspan="7" class="p-12 text-center text-slate-500">Todavía no hay exportaciones registradas.</td></tr>@endforelse</tbody></table></div>
            </div>
        @break

        @case('validaciones')
            <div class="grid gap-6 xl:grid-cols-[430px_1fr]">
                <form id="studio-form" wire:submit="guardarValidacion" class="self-start rounded-3xl border border-slate-200 bg-white p-5 shadow-sm dark:border-zinc-800 dark:bg-zinc-950 xl:sticky xl:top-4">
                    <div class="flex items-center justify-between"><div><p class="text-xs font-bold uppercase tracking-widest text-[#006492]">Autenticidad</p><h2 class="text-xl font-black">{{ $validacionId ? 'Editar validación' : 'Nuevo folio' }}</h2></div>@if($validacionId)<flux:button type="button" wire:click="limpiarValidacion" class="text-xs font-bold text-slate-500">Cancelar</flux:button>@endif</div>
                    <div class="mt-5 space-y-4">
                        <label class="block text-sm font-bold">Título<flux:input wire:model="validacionTitulo" class="mt-1 w-full rounded-xl border-slate-300 dark:border-zinc-700 dark:bg-zinc-900" /></label>
                        <div class="grid grid-cols-[1fr_auto] gap-2"><label class="text-sm font-bold">Código<flux:input wire:model="validacionCodigo" class="mt-1 w-full rounded-xl border-slate-300 font-mono uppercase dark:border-zinc-700 dark:bg-zinc-900" /></label><flux:button type="button" wire:click="generarCodigoValidacion" class="self-end rounded-xl border px-3 py-2.5 text-xs font-black dark:border-zinc-700">Generar</flux:button></div>
                        <div class="grid grid-cols-2 gap-3"><label class="text-sm font-bold">Tipo<flux:select wire:model="validacionTipo" class="mt-1 w-full rounded-xl border-slate-300 dark:border-zinc-700 dark:bg-zinc-900">@foreach(['documento','reconocimiento','credencial','constancia','diploma','otro'] as $v)<flux:select.option value="{{ $v }}">{{ ucfirst($v) }}</flux:select.option>@endforeach</flux:select></label><label class="text-sm font-bold">Estado<flux:select wire:model="validacionEstado" class="mt-1 w-full rounded-xl border-slate-300 dark:border-zinc-700 dark:bg-zinc-900">@foreach(['valido','cancelado','vencido'] as $v)<flux:select.option value="{{ $v }}">{{ ucfirst($v) }}</flux:select.option>@endforeach</flux:select></label></div>
                        <div class="grid grid-cols-2 gap-3"><label class="text-sm font-bold">Persona<flux:select wire:model="validacionPersonaId" class="mt-1 w-full rounded-xl border-slate-300 dark:border-zinc-700 dark:bg-zinc-900"><flux:select.option value="">Sin persona</flux:select.option>@foreach($people as $person)<flux:select.option value="{{ $person->id }}">{{ $person->nombre }}</flux:select.option>@endforeach</flux:select></label><label class="text-sm font-bold">Proyecto<flux:select wire:model="validacionProyectoId" class="mt-1 w-full rounded-xl border-slate-300 dark:border-zinc-700 dark:bg-zinc-900"><flux:select.option value="">Sin proyecto</flux:select.option>@foreach($projects as $project)<flux:select.option value="{{ $project->id }}">{{ $project->nombre }}</flux:select.option>@endforeach</flux:select></label></div>
                        <div class="grid grid-cols-2 gap-3"><label class="text-sm font-bold">Emisión<flux:input wire:model="validacionEmitido" type="date" class="mt-1 w-full rounded-xl border-slate-300 dark:border-zinc-700 dark:bg-zinc-900" /></label><label class="text-sm font-bold">Vencimiento<flux:input wire:model="validacionVence" type="date" class="mt-1 w-full rounded-xl border-slate-300 dark:border-zinc-700 dark:bg-zinc-900" /></label></div>
                        <label class="block text-sm font-bold">Descripción pública<flux:textarea wire:model="validacionDatos" rows="4" class="mt-1 w-full rounded-xl border-slate-300 dark:border-zinc-700 dark:bg-zinc-900"></flux:textarea></label>
                        <label class="block text-sm font-bold">Notas internas<flux:textarea wire:model="validacionNotas" rows="2" class="mt-1 w-full rounded-xl border-slate-300 dark:border-zinc-700 dark:bg-zinc-900"></flux:textarea></label>
                        <flux:button variant="primary" type="submit" class="w-full rounded-xl bg-[#006492] px-4 py-3 text-sm font-black text-white">{{ $validacionId ? 'Guardar cambios' : 'Crear validación' }}</flux:button>
                    </div>
                </form>
                <div class="space-y-3">
                    @forelse($items as $item)
                        @php $expired = $item->vence_at && $item->vence_at->isPast(); $status = $expired ? 'vencido' : $item->estado; @endphp
                        <article class="rounded-3xl border border-slate-200 bg-white p-5 shadow-sm dark:border-zinc-800 dark:bg-zinc-950">
                            <div class="flex flex-col gap-3 md:flex-row md:items-start md:justify-between"><div><div class="flex gap-2"><span class="rounded-full bg-slate-100 px-2 py-1 text-[10px] font-black uppercase dark:bg-zinc-900">{{ $item->tipo }}</span><span class="rounded-full px-2 py-1 text-[10px] font-black uppercase {{ $status === 'valido' ? 'bg-emerald-100 text-emerald-700' : 'bg-amber-100 text-amber-700' }}">{{ $status }}</span></div><h3 class="mt-3 text-lg font-black">{{ $item->titulo }}</h3><p class="mt-1 font-mono text-sm font-black text-[#006492]">{{ $item->codigo }}</p><p class="mt-2 text-xs text-slate-500">{{ $item->persona?->nombre ?? 'Sin persona' }}{{ $item->proyecto ? ' · '.$item->proyecto->nombre : '' }}</p></div><div class="text-right text-xs"><p>Emisión: <strong>{{ $item->emitido_at?->format('d/m/Y') ?? '—' }}</strong></p><p>Vence: <strong>{{ $item->vence_at?->format('d/m/Y') ?? 'Sin vencimiento' }}</strong></p></div></div>
                            <div class="mt-4 flex flex-wrap justify-end gap-2"><a href="{{ route('validacion.publica', $item->codigo) }}" target="_blank" class="rounded-xl border px-3 py-2 text-xs font-black dark:border-zinc-700">Abrir validación</a><flux:button type="button" wire:click="editarValidacion({{ $item->id }})" class="rounded-xl border px-3 py-2 text-xs font-black dark:border-zinc-700">Editar</flux:button><flux:button type="button" @click="confirmDelete('El registro se eliminará.', {method:'eliminarValidacion', id:{{ $item->id }}})" class="rounded-xl border border-red-200 px-3 py-2 text-xs font-black text-red-600">Eliminar</flux:button></div>
                        </article>
                    @empty<div class="rounded-3xl border border-dashed p-12 text-center text-slate-500">No hay registros de validación.</div>@endforelse
                </div>
            </div>
        @break

        @case('actividad')
            <div class="overflow-hidden rounded-3xl border border-slate-200 bg-white shadow-sm dark:border-zinc-800 dark:bg-zinc-950">
                <div class="divide-y divide-slate-100 dark:divide-zinc-800">
                    @forelse($items as $item)
                        <div class="flex flex-col gap-3 p-5 md:flex-row md:items-center md:justify-between"><div class="flex items-start gap-4"><div class="mt-1 flex h-10 w-10 items-center justify-center rounded-2xl bg-[#006492]/10 text-xs font-black text-[#006492]">{{ Str::upper(Str::substr($item->modulo,0,2)) }}</div><div><div class="flex flex-wrap items-center gap-2"><strong>{{ ucfirst($item->modulo) }}</strong><span class="rounded-full bg-slate-100 px-2 py-1 text-[10px] font-black uppercase dark:bg-zinc-900">{{ str_replace('_',' ',$item->accion) }}</span></div><p class="mt-1 text-sm text-slate-600 dark:text-zinc-300">{{ $item->descripcion ?: 'Sin descripción' }}</p>@if($item->datos)<code class="mt-2 block max-w-3xl whitespace-normal text-[10px] text-slate-500">{{ json_encode($item->datos, JSON_UNESCAPED_UNICODE) }}</code>@endif</div></div><time class="shrink-0 text-xs font-semibold text-slate-500">{{ $item->created_at->format('d/m/Y H:i') }}</time></div>
                    @empty<div class="p-12 text-center text-slate-500">Todavía no hay actividad registrada.</div>@endforelse
                </div>
            </div>
        @break
    @endswitch
</div>
