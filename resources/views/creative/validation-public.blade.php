<!doctype html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Validación de documento</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="min-h-screen bg-slate-100 px-4 py-10 text-slate-900">
    <main class="mx-auto max-w-2xl">
        <div class="overflow-hidden rounded-3xl bg-white shadow-2xl">
            <header class="bg-gradient-to-r from-[#006492] to-[#88AC2E] p-8 text-white">
                <p class="text-xs font-black uppercase tracking-[.28em]">MiniSystems</p>
                <h1 class="mt-3 text-3xl font-black">Validación pública</h1>
                <p class="mt-2 text-sm text-white/80">Consulta de autenticidad por código.</p>
            </header>

            <section class="p-8">
                @if(!$registro)
                    <div class="rounded-2xl border border-red-200 bg-red-50 p-5 text-red-800">
                        <h2 class="text-lg font-black">Código no encontrado</h2>
                        <p class="mt-1 text-sm">No existe un registro asociado al código <strong>{{ $codigo }}</strong>.</p>
                    </div>
                @else
                    @php
                        $isExpired = $registro->vence_at && $registro->vence_at->isPast();
                        $status = $isExpired ? 'vencido' : $registro->estado;
                        $ok = $status === 'valido';
                    @endphp
                    <div class="rounded-2xl border p-5 {{ $ok ? 'border-emerald-200 bg-emerald-50 text-emerald-900' : 'border-amber-200 bg-amber-50 text-amber-900' }}">
                        <p class="text-xs font-black uppercase tracking-widest">{{ $ok ? 'Documento válido' : 'Documento '.$status }}</p>
                        <h2 class="mt-2 text-2xl font-black">{{ $registro->titulo }}</h2>
                    </div>

                    <dl class="mt-6 grid gap-4 sm:grid-cols-2">
                        <div class="rounded-2xl bg-slate-50 p-4"><dt class="text-xs font-bold uppercase text-slate-500">Código</dt><dd class="mt-1 font-black">{{ $registro->codigo }}</dd></div>
                        <div class="rounded-2xl bg-slate-50 p-4"><dt class="text-xs font-bold uppercase text-slate-500">Tipo</dt><dd class="mt-1 font-semibold">{{ ucfirst($registro->tipo) }}</dd></div>
                        <div class="rounded-2xl bg-slate-50 p-4"><dt class="text-xs font-bold uppercase text-slate-500">Persona</dt><dd class="mt-1 font-semibold">{{ $registro->persona?->nombre ?? 'No especificada' }}</dd></div>
                        <div class="rounded-2xl bg-slate-50 p-4"><dt class="text-xs font-bold uppercase text-slate-500">Proyecto</dt><dd class="mt-1 font-semibold">{{ $registro->proyecto?->nombre ?? 'No especificado' }}</dd></div>
                        <div class="rounded-2xl bg-slate-50 p-4"><dt class="text-xs font-bold uppercase text-slate-500">Emisión</dt><dd class="mt-1 font-semibold">{{ $registro->emitido_at?->format('d/m/Y') ?? '—' }}</dd></div>
                        <div class="rounded-2xl bg-slate-50 p-4"><dt class="text-xs font-bold uppercase text-slate-500">Vigencia</dt><dd class="mt-1 font-semibold">{{ $registro->vence_at?->format('d/m/Y') ?? 'Sin vencimiento' }}</dd></div>
                    </dl>

                    @if(data_get($registro->datos_publicos, 'descripcion'))
                        <div class="mt-6 rounded-2xl border border-slate-200 p-5 text-sm leading-7 text-slate-700">
                            {{ data_get($registro->datos_publicos, 'descripcion') }}
                        </div>
                    @endif
                @endif
            </section>
        </div>
    </main>
</body>
</html>
