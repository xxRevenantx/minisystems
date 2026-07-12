<div class="space-y-6">
    <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-6">
        @foreach(['total'=>'Total','mes'=>'Este mes','revision'=>'En revisión','aprobados'=>'Aprobados','entregados'=>'Entregados','cancelados'=>'Cancelados'] as $k=>$label)
            <div class="rounded-2xl border border-neutral-200 bg-white p-4 shadow-sm dark:border-neutral-700 dark:bg-neutral-900">
                <div class="text-3xl font-black {{ $k === 'cancelados' ? 'text-red-600' : ($k === 'entregados' ? 'text-[#88AC2E]' : 'text-[#006492]') }}">
                    {{ $stats[$k] }}
                </div>
                <flux:text class="mt-1 font-semibold">{{ $label }}</flux:text>
            </div>
        @endforeach
    </div>

    <div class="grid gap-5 lg:grid-cols-2">
        <section class="rounded-2xl border border-neutral-200 bg-white p-5 shadow-sm dark:border-neutral-700 dark:bg-neutral-900">
            <div class="mb-4 flex items-start justify-between gap-3">
                <div>
                    <flux:heading size="lg">Eventos recientes</flux:heading>
                    <flux:text class="mt-1">Control de lotes y avance de emisión.</flux:text>
                </div>
                <flux:button href="{{ route('reconocimiento', ['tab' => 'eventos']) }}" size="sm" variant="filled">
                    Administrar
                </flux:button>
            </div>

            <div class="space-y-3">
                @forelse($eventos as $evento)
                    <div class="rounded-xl border border-neutral-200 p-3 dark:border-neutral-700">
                        <div class="flex justify-between gap-3">
                            <div>
                                <strong>{{ $evento->nombre }}</strong>
                                <div class="text-xs text-neutral-500">
                                    {{ $evento->fecha?->format('d/m/Y') }} · {{ $evento->nivel ?: 'Todos los niveles' }}
                                </div>
                            </div>
                            <flux:badge color="blue">{{ $evento->reconocimientos_count }} documentos</flux:badge>
                        </div>
                    </div>
                @empty
                    <div class="py-8 text-center"><flux:text>Aún no hay eventos registrados.</flux:text></div>
                @endforelse
            </div>
        </section>

        <section class="rounded-2xl border border-neutral-200 bg-white p-5 shadow-sm dark:border-neutral-700 dark:bg-neutral-900">
            <flux:heading size="lg">Tipos más utilizados</flux:heading>
            <flux:text class="mt-1 mb-4">Ayuda a estandarizar textos y diseños.</flux:text>

            <div class="space-y-3">
                @forelse($porTipo as $fila)
                    @php
                        $max = max(1, (int) $porTipo->max('total'));
                        $pct = round(($fila->total / $max) * 100);
                    @endphp
                    <div>
                        <div class="mb-1 flex justify-between text-sm">
                            <span class="font-semibold">{{ $fila->nombre }}</span>
                            <flux:badge color="green">{{ $fila->total }}</flux:badge>
                        </div>
                        <div class="h-2 rounded-full bg-neutral-100 dark:bg-neutral-800">
                            <div class="h-2 rounded-full bg-[#88AC2E]" style="width: {{ $pct }}%"></div>
                        </div>
                    </div>
                @empty
                    <div class="py-8 text-center"><flux:text>Todavía no hay datos suficientes.</flux:text></div>
                @endforelse
            </div>
        </section>
    </div>

    <section class="rounded-2xl border border-neutral-200 bg-white p-5 shadow-sm dark:border-neutral-700 dark:bg-neutral-900">
        <div class="mb-4 flex flex-wrap items-start justify-between gap-3">
            <div>
                <flux:heading size="lg">Actividad reciente</flux:heading>
                <flux:text class="mt-1">Últimos documentos creados.</flux:text>
            </div>
            <flux:button href="{{ route('reconocimiento', ['tab' => 'reconocimientos']) }}" variant="primary"
                class="!bg-[#006492] hover:!bg-[#00557b]">
                Gestionar reconocimientos
            </flux:button>
        </div>

        <div class="overflow-x-auto rounded-xl border border-neutral-200 dark:border-neutral-700">
            <table class="min-w-full text-sm">
                <thead class="bg-neutral-50 text-xs uppercase text-neutral-500 dark:bg-neutral-800">
                    <tr>
                        <th class="p-3 text-left">Destinatario</th>
                        <th class="p-3 text-left">Evento</th>
                        <th class="p-3 text-left">Tipo</th>
                        <th class="p-3 text-left">Estado</th>
                        <th class="p-3 text-left">Creado</th>
                    </tr>
                </thead>
                <tbody class="divide-y dark:divide-neutral-800">
                    @forelse($recientes as $r)
                        <tr>
                            <td class="p-3 font-bold">{{ $r->reconocimiento_a }}</td>
                            <td class="p-3">{{ $r->evento?->nombre ?: 'Sin evento' }}</td>
                            <td class="p-3">{{ $r->tipo?->nombre ?: 'Personalizado' }}</td>
                            <td class="p-3">
                                @php
                                    $badgeColor = match($r->estado) {
                                        'revision' => 'amber',
                                        'aprobado' => 'blue',
                                        'generado' => 'purple',
                                        'entregado' => 'green',
                                        'cancelado' => 'red',
                                        default => 'zinc',
                                    };
                                @endphp
                                <flux:badge :color="$badgeColor">{{ ucfirst($r->estado) }}</flux:badge>
                            </td>
                            <td class="p-3">{{ $r->created_at?->format('d/m/Y H:i') }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="p-8 text-center text-neutral-500">Sin actividad.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </section>
</div>
