<div class="space-y-6">
    <section class="relative overflow-hidden rounded-3xl bg-gradient-to-r from-[#006492] via-sky-600 to-[#88AC2E] p-7 text-white shadow-xl">
        <div class="absolute -right-16 -top-20 h-56 w-56 rounded-full bg-white/10"></div>
        <div class="absolute -bottom-24 left-1/3 h-52 w-52 rounded-full bg-white/10"></div>
        <div class="relative flex flex-col gap-6 xl:flex-row xl:items-end xl:justify-between">
            <div>
                <p class="text-xs font-black uppercase tracking-[.3em] text-white/75">MiniSystems Studio</p>
                <h1 class="mt-3 text-3xl font-black">Centro creativo y de producción</h1>
                <p class="mt-2 max-w-2xl text-sm leading-6 text-white/85">Organiza clientes, personas, campañas, recursos, plantillas, solicitudes, publicaciones y validaciones desde un solo lugar.</p>
            </div>
            <div class="flex flex-wrap gap-2">
                <a href="{{ route('images') }}" wire:navigate class="rounded-xl bg-white px-4 py-3 text-xs font-black text-[#006492] shadow">Procesar imágenes</a>
                <a href="{{ route('studio.section','generador') }}" wire:navigate class="rounded-xl bg-white/15 px-4 py-3 text-xs font-black text-white backdrop-blur hover:bg-white/25">Generador masivo</a>
                <a href="{{ route('reconocimiento') }}" wire:navigate class="rounded-xl bg-white/15 px-4 py-3 text-xs font-black text-white backdrop-blur hover:bg-white/25">Reconocimientos</a>
            </div>
        </div>
    </section>

    @if(!$ready)
        <div class="rounded-3xl border border-amber-200 bg-amber-50 p-6 text-amber-900">
            <h2 class="font-black">Falta ejecutar la migración de MiniSystems Studio</h2>
            <p class="mt-1 text-sm">Ejecuta <code class="rounded bg-white px-2 py-1">php artisan migrate</code> para activar todos los módulos.</p>
        </div>
    @else
        <section class="grid gap-4 sm:grid-cols-2 xl:grid-cols-4">
            @php
                $cards = [
                    ['marcas','Clientes y marcas',$stats['marcas'],'studio.section','marcas'],
                    ['personas','Personas activas',$stats['personas'],'studio.section','personas'],
                    ['proyectos','Proyectos abiertos',$stats['proyectos'],'studio.section','proyectos'],
                    ['archivos','Recursos multimedia',$stats['archivos'],'studio.section','biblioteca'],
                    ['plantillas','Plantillas activas',$stats['plantillas'],'studio.section','plantillas'],
                    ['solicitudes','Solicitudes pendientes',$stats['solicitudes'],'studio.section','solicitudes'],
                    ['publicaciones','Publicaciones listas',$stats['publicaciones'],'studio.section','publicaciones'],
                    ['exportaciones','Piezas exportadas este mes',$stats['exportaciones'],'studio.section','exportaciones'],
                ];
            @endphp
            @foreach($cards as [$key,$label,$value,$route,$param])
                <a href="{{ route($route,$param) }}" wire:navigate class="group rounded-3xl border border-slate-200 bg-white p-5 shadow-sm transition hover:-translate-y-1 hover:shadow-lg dark:border-zinc-800 dark:bg-zinc-950">
                    <div class="flex items-center justify-between"><span class="rounded-2xl bg-[#006492]/10 px-3 py-2 text-xs font-black uppercase text-[#006492]">{{ Str::upper(Str::substr($key,0,2)) }}</span><span class="text-slate-300 transition group-hover:translate-x-1 group-hover:text-[#006492]">→</span></div>
                    <strong class="mt-5 block text-3xl font-black">{{ number_format($value) }}</strong>
                    <span class="mt-1 block text-sm text-slate-500">{{ $label }}</span>
                </a>
            @endforeach
        </section>

        <section class="grid gap-6 xl:grid-cols-2">
            <div class="overflow-hidden rounded-3xl border border-slate-200 bg-white shadow-sm dark:border-zinc-800 dark:bg-zinc-950">
                <div class="flex items-center justify-between border-b border-slate-200 p-5 dark:border-zinc-800"><div><h2 class="text-lg font-black">Solicitudes prioritarias</h2><p class="text-xs text-slate-500">Trabajos pendientes y próximas entregas.</p></div><a href="{{ route('studio.section','solicitudes') }}" wire:navigate class="text-xs font-black text-[#006492]">Ver todas</a></div>
                <div class="divide-y divide-slate-100 dark:divide-zinc-800">
                    @forelse($upcoming as $item)
                        <div class="flex items-center justify-between gap-4 p-4"><div><div class="flex gap-2"><span class="rounded-full px-2 py-1 text-[10px] font-black uppercase {{ $item->prioridad === 'urgente' ? 'bg-red-100 text-red-700' : 'bg-amber-100 text-amber-700' }}">{{ $item->prioridad }}</span><span class="rounded-full bg-slate-100 px-2 py-1 text-[10px] font-black uppercase dark:bg-zinc-900">{{ str_replace('_',' ',$item->estado) }}</span></div><strong class="mt-2 block">{{ $item->titulo }}</strong><span class="text-xs text-slate-500">{{ $item->marca?->nombre ?? 'Sin marca' }}</span></div><time class="text-right text-xs font-semibold text-slate-500">{{ $item->fecha_entrega?->format('d/m/Y H:i') ?? 'Sin fecha' }}</time></div>
                    @empty<div class="p-10 text-center text-sm text-slate-500">No hay solicitudes pendientes.</div>@endforelse
                </div>
            </div>

            <div class="overflow-hidden rounded-3xl border border-slate-200 bg-white shadow-sm dark:border-zinc-800 dark:bg-zinc-950">
                <div class="flex items-center justify-between border-b border-slate-200 p-5 dark:border-zinc-800"><div><h2 class="text-lg font-black">Calendario de publicaciones</h2><p class="text-xs text-slate-500">Piezas aprobadas o programadas.</p></div><a href="{{ route('studio.section','publicaciones') }}" wire:navigate class="text-xs font-black text-[#006492]">Ver calendario</a></div>
                <div class="divide-y divide-slate-100 dark:divide-zinc-800">
                    @forelse($scheduled as $item)
                        <div class="flex items-center justify-between gap-4 p-4"><div class="flex items-center gap-3">@if($item->archivo && str_starts_with($item->archivo->mime ?? '', 'image/'))<img src="{{ asset('storage/'.$item->archivo->archivo) }}" class="h-12 w-12 rounded-xl object-cover">@else<div class="flex h-12 w-12 items-center justify-center rounded-xl bg-slate-100 text-xs font-black dark:bg-zinc-900">{{ Str::upper(Str::substr($item->red_social,0,2)) }}</div>@endif<div><strong>{{ $item->titulo }}</strong><p class="text-xs text-slate-500">{{ $item->red_social }} · {{ $item->marca?->nombre ?? 'Sin marca' }}</p></div></div><time class="text-right text-xs font-semibold text-slate-500">{{ $item->programada_at?->format('d/m/Y H:i') ?? 'Sin fecha' }}</time></div>
                    @empty<div class="p-10 text-center text-sm text-slate-500">No hay publicaciones programadas.</div>@endforelse
                </div>
            </div>
        </section>

        <section class="rounded-3xl border border-slate-200 bg-white p-5 shadow-sm dark:border-zinc-800 dark:bg-zinc-950">
            <div class="flex items-center justify-between"><div><h2 class="text-lg font-black">Recursos recientes</h2><p class="text-xs text-slate-500">Últimos archivos agregados a la biblioteca.</p></div><a href="{{ route('studio.section','biblioteca') }}" wire:navigate class="text-xs font-black text-[#006492]">Abrir biblioteca</a></div>
            <div class="mt-5 grid grid-cols-2 gap-3 sm:grid-cols-4 xl:grid-cols-8">
                @forelse($recentAssets as $asset)
                    <a href="{{ asset('storage/'.$asset->archivo) }}" target="_blank" class="group overflow-hidden rounded-2xl border border-slate-200 dark:border-zinc-800">
                        <div class="aspect-square bg-slate-100 dark:bg-zinc-900">@if(str_starts_with($asset->mime ?? '', 'image/'))<img src="{{ asset('storage/'.$asset->archivo) }}" class="h-full w-full object-cover transition group-hover:scale-105">@else<div class="flex h-full items-center justify-center text-xs font-black">{{ strtoupper($asset->extension ?? 'FILE') }}</div>@endif</div>
                        <p class="truncate p-2 text-[11px] font-bold">{{ $asset->nombre }}</p>
                    </a>
                @empty<div class="col-span-full py-10 text-center text-sm text-slate-500">No hay recursos recientes.</div>@endforelse
            </div>
        </section>
    @endif
</div>
