<div class="space-y-6">
    <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-6">
        @foreach(['total'=>'Total','mes'=>'Este mes','revision'=>'En revisión','aprobados'=>'Aprobados','entregados'=>'Entregados','cancelados'=>'Cancelados'] as $k=>$label)
            <div class="rounded-2xl border border-neutral-200 bg-white p-4 shadow-sm dark:border-neutral-700 dark:bg-neutral-900">
                <div class="text-3xl font-black {{ $k === 'cancelados' ? 'text-red-600' : ($k === 'entregados' ? 'text-[#88AC2E]' : 'text-[#006492]') }}">{{ $stats[$k] }}</div>
                <div class="mt-1 text-sm font-semibold text-neutral-500">{{ $label }}</div>
            </div>
        @endforeach
    </div>

    <div class="grid gap-5 lg:grid-cols-2">
        <section class="rounded-2xl border border-neutral-200 bg-white p-5 shadow-sm dark:border-neutral-700 dark:bg-neutral-900">
            <div class="mb-4 flex items-center justify-between"><div><h2 class="text-lg font-bold">Eventos recientes</h2><p class="text-sm text-neutral-500">Control de lotes y avance de emisión.</p></div><a href="{{ route('reconocimiento',['tab'=>'eventos']) }}" class="text-sm font-bold text-[#006492]">Administrar</a></div>
            <div class="space-y-3">
                @forelse($eventos as $evento)
                    <div class="rounded-xl border p-3"><div class="flex justify-between gap-3"><div><strong>{{ $evento->nombre }}</strong><div class="text-xs text-neutral-500">{{ $evento->fecha?->format('d/m/Y') }} · {{ $evento->nivel ?: 'Todos los niveles' }}</div></div><span class="rounded-full bg-[#006492]/10 px-3 py-1 text-xs font-bold text-[#006492]">{{ $evento->reconocimientos_count }} documentos</span></div></div>
                @empty<p class="py-8 text-center text-neutral-500">Aún no hay eventos registrados.</p>@endforelse
            </div>
        </section>

        <section class="rounded-2xl border border-neutral-200 bg-white p-5 shadow-sm dark:border-neutral-700 dark:bg-neutral-900">
            <h2 class="text-lg font-bold">Tipos más utilizados</h2><p class="mb-4 text-sm text-neutral-500">Ayuda a estandarizar textos y diseños.</p>
            <div class="space-y-3">
                @forelse($porTipo as $fila)
                    @php $max=max(1,(int)$porTipo->max('total')); $pct=round(($fila->total/$max)*100); @endphp
                    <div><div class="mb-1 flex justify-between text-sm"><span class="font-semibold">{{ $fila->nombre }}</span><span>{{ $fila->total }}</span></div><div class="h-2 rounded-full bg-neutral-100 dark:bg-neutral-800"><div class="h-2 rounded-full bg-[#88AC2E]" style="width:{{ $pct }}%"></div></div></div>
                @empty<p class="py-8 text-center text-neutral-500">Todavía no hay datos suficientes.</p>@endforelse
            </div>
        </section>
    </div>

    <section class="rounded-2xl border border-neutral-200 bg-white p-5 shadow-sm dark:border-neutral-700 dark:bg-neutral-900">
        <div class="mb-4 flex items-center justify-between"><div><h2 class="text-lg font-bold">Actividad reciente</h2><p class="text-sm text-neutral-500">Últimos documentos creados.</p></div><a href="{{ route('reconocimiento',['tab'=>'reconocimientos']) }}" class="rounded-xl bg-[#006492] px-4 py-2 text-sm font-bold text-white">Gestionar reconocimientos</a></div>
        <div class="overflow-x-auto"><table class="min-w-full text-sm"><thead class="bg-neutral-50 text-xs uppercase text-neutral-500 dark:bg-neutral-800"><tr><th class="p-3 text-left">Destinatario</th><th class="p-3 text-left">Evento</th><th class="p-3 text-left">Tipo</th><th class="p-3 text-left">Estado</th><th class="p-3 text-left">Creado</th></tr></thead><tbody class="divide-y dark:divide-neutral-800">@forelse($recientes as $r)<tr><td class="p-3 font-bold">{{ $r->reconocimiento_a }}</td><td class="p-3">{{ $r->evento?->nombre ?: 'Sin evento' }}</td><td class="p-3">{{ $r->tipo?->nombre ?: 'Personalizado' }}</td><td class="p-3">{{ ucfirst($r->estado) }}</td><td class="p-3">{{ $r->created_at?->format('d/m/Y H:i') }}</td></tr>@empty<tr><td colspan="5" class="p-8 text-center text-neutral-500">Sin actividad.</td></tr>@endforelse</tbody></table></div>
    </section>
</div>
