<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>MiniSystems | Creative Suite</title>

    <link rel="icon" href="/favicon.ico" sizes="any">
    <link rel="icon" href="/favicon.svg" type="image/svg+xml">
    <link rel="apple-touch-icon" href="/apple-touch-icon.png">
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=instrument-sans:400,500,600,700,800" rel="stylesheet" />

    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @fluxAppearance
</head>
<body class="min-h-screen overflow-x-hidden bg-white text-slate-950 antialiased">
    <div class="relative isolate min-h-screen overflow-hidden">
        <div class="pointer-events-none absolute inset-0 -z-20 bg-white"></div>
        <div class="pointer-events-none absolute inset-0 -z-10 bg-[radial-gradient(circle_at_top_left,rgba(14,165,233,.16),transparent_34%),radial-gradient(circle_at_top_right,rgba(136,172,46,.14),transparent_30%),linear-gradient(to_bottom,#ffffff,#f8fafc)]"></div>
        <div class="pointer-events-none absolute inset-0 -z-10 bg-[linear-gradient(to_right,rgba(148,163,184,.09)_1px,transparent_1px),linear-gradient(to_bottom,rgba(148,163,184,.09)_1px,transparent_1px)] bg-[size:48px_48px] [mask-image:linear-gradient(to_bottom,black,transparent_80%)]"></div>

        <header class="mx-auto flex max-w-7xl items-center justify-between px-6 py-6 lg:px-8">
            <a href="{{ url('/') }}" class="flex items-center gap-3">
                <div class="flex size-11 items-center justify-center rounded-2xl border border-sky-200 bg-white text-[#006492] shadow-lg shadow-sky-100">
                    <svg class="size-6" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true">
                        <rect x="4" y="4" width="7" height="7" rx="2"></rect>
                        <rect x="13" y="4" width="7" height="7" rx="2"></rect>
                        <rect x="4" y="13" width="7" height="7" rx="2"></rect>
                        <path d="M13 16h7M16.5 12.5v7"></path>
                    </svg>
                </div>
                <div>
                    <div class="text-lg font-extrabold tracking-tight">MiniSystems</div>
                    <div class="text-[10px] font-bold uppercase tracking-[.28em] text-slate-500">Creative Suite</div>
                </div>
            </a>

            <nav class="flex items-center gap-2">
                @auth
                    <flux:button :href="route('dashboard')" variant="primary" icon:trailing="arrow-right">
                        Ir al panel
                    </flux:button>
                @else
                    @if (Route::has('login'))
                        <flux:button :href="route('login')" variant="ghost">Iniciar sesión</flux:button>
                    @endif
                    @if (Route::has('register'))
                        <flux:button :href="route('register')" variant="primary">Registrarse</flux:button>
                    @endif
                @endauth
            </nav>
        </header>

        <main>
            <section class="mx-auto grid max-w-7xl items-center gap-14 px-6 pb-20 pt-10 lg:grid-cols-[1.05fr_.95fr] lg:px-8 lg:pb-28 lg:pt-16">
                <div>
                    <flux:badge color="sky" size="sm" inset="top bottom">
                        Plataforma visual profesional
                    </flux:badge>

                    <flux:heading size="xl" level="1" class="mt-6 max-w-4xl text-4xl! font-extrabold! leading-[1.08]! tracking-tight! sm:text-5xl! lg:text-6xl!">
                        Diseña, genera y organiza
                        <span class="bg-gradient-to-r from-[#006492] via-sky-500 to-[#88AC2E] bg-clip-text text-transparent">
                            contenido visual
                        </span>
                        para redes, reconocimientos y credenciales.
                    </flux:heading>

                    <flux:text class="mt-6 max-w-2xl text-base! leading-8! text-slate-600! sm:text-lg!">
                        Procesa imágenes, reutiliza marcos y plantillas, genera piezas personalizadas y organiza tu producción desde un solo lugar.
                    </flux:text>

                    <div class="mt-8 flex flex-wrap gap-3">
                        @auth
                            <flux:button :href="route('dashboard')" variant="primary" size="lg" icon:trailing="arrow-right">
                                Entrar a MiniSystems
                            </flux:button>
                        @else
                            @if (Route::has('login'))
                                <flux:button :href="route('login')" variant="primary" size="lg" icon:trailing="arrow-right">
                                    Comenzar ahora
                                </flux:button>
                            @endif
                            @if (Route::has('register'))
                                <flux:button :href="route('register')" size="lg">Crear cuenta</flux:button>
                            @endif
                        @endauth
                    </div>

                    <div class="mt-10 grid gap-4 sm:grid-cols-3">
                        @foreach([
                            ['01', 'Procesamiento visual', 'Adapta imágenes a formatos horizontales, verticales y de redes sociales.'],
                            ['02', 'Diseño reutilizable', 'Centraliza marcos, marcas, personas, plantillas y recursos multimedia.'],
                            ['03', 'Entrega profesional', 'Exporta credenciales, reconocimientos, imágenes, PDF y paquetes ZIP.'],
                        ] as [$number, $label, $description])
                            <flux:card class="bg-white/90 shadow-sm backdrop-blur transition hover:-translate-y-1 hover:shadow-lg">
                                <div class="text-2xl font-extrabold text-slate-950">{{ $number }}</div>
                                <flux:heading size="sm" class="mt-3 text-[#006492]!">{{ $label }}</flux:heading>
                                <flux:text class="mt-2 text-sm! leading-6!">{{ $description }}</flux:text>
                            </flux:card>
                        @endforeach
                    </div>
                </div>

                <div class="relative">
                    <div class="absolute -inset-8 -z-10 rounded-[3rem] bg-gradient-to-br from-sky-200/70 via-blue-100/60 to-lime-100/70 blur-3xl"></div>

                    <flux:card class="overflow-hidden bg-white/95 p-4! shadow-2xl shadow-slate-300/50 backdrop-blur-xl">
                        <div class="flex items-center justify-between border-b border-slate-200 pb-4">
                            <div class="flex items-center gap-2" aria-hidden="true">
                                <span class="size-3 rounded-full bg-red-400"></span>
                                <span class="size-3 rounded-full bg-amber-400"></span>
                                <span class="size-3 rounded-full bg-emerald-400"></span>
                            </div>
                            <flux:badge color="zinc" size="sm">Dashboard creativo</flux:badge>
                        </div>

                        <div class="mt-4 grid grid-cols-3 gap-3">
                            @foreach([
                                ['Plantillas', '48', '+12 este mes', 'text-emerald-600'],
                                ['Diseños', '320', 'Listos para exportar', 'text-sky-600'],
                                ['Exportaciones', '1.2k', 'ZIP, PNG y PDF', 'text-indigo-600'],
                            ] as [$label, $value, $helper, $helperClass])
                                <flux:card class="bg-slate-50 p-4! shadow-none">
                                    <div class="text-[10px] font-bold uppercase tracking-[.18em] text-slate-400">{{ $label }}</div>
                                    <div class="mt-3 text-2xl font-extrabold">{{ $value }}</div>
                                    <div class="mt-1 text-xs font-semibold {{ $helperClass }}">{{ $helper }}</div>
                                </flux:card>
                            @endforeach
                        </div>

                        <div class="mt-4 grid gap-4 md:grid-cols-[1.08fr_.92fr]">
                            <flux:card class="border-sky-200! bg-gradient-to-br from-sky-50 to-blue-50 p-5! shadow-none">
                                <div class="flex items-start justify-between gap-3">
                                    <div>
                                        <flux:heading size="sm" class="text-[#006492]!">System Images</flux:heading>
                                        <flux:text class="mt-2 text-sm! leading-6!">
                                            Detecta orientaciones, aplica el marco correcto y prepara piezas para distintas plataformas.
                                        </flux:text>
                                    </div>
                                    <div class="flex size-11 shrink-0 items-center justify-center rounded-2xl border border-sky-200 bg-white text-[#006492]">
                                        <svg class="size-5" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24" aria-hidden="true">
                                            <rect x="3" y="4" width="18" height="14" rx="3"></rect>
                                            <path d="M8 20h8M8 9h8M8 13h5"></path>
                                        </svg>
                                    </div>
                                </div>
                                <div class="mt-5 grid grid-cols-3 gap-3">
                                    @foreach([
                                        ['Post', 'from-pink-300 to-orange-200'],
                                        ['Story', 'from-sky-300 to-blue-300'],
                                        ['Banner', 'from-emerald-300 to-lime-200'],
                                    ] as [$name, $gradient])
                                        <div class="rounded-2xl border border-white bg-white p-3 shadow-sm">
                                            <div class="h-16 rounded-xl bg-gradient-to-br {{ $gradient }}"></div>
                                            <div class="mt-2 text-xs font-semibold text-slate-500">{{ $name }}</div>
                                        </div>
                                    @endforeach
                                </div>
                            </flux:card>

                            <div class="grid gap-3">
                                @foreach([
                                    ['Reconocimientos', 'Plantillas y exportación profesional.', 'indigo'],
                                    ['Credenciales', 'Gafetes e identificaciones personalizables.', 'emerald'],
                                    ['Plantillas y medios', 'Recursos organizados para producir más rápido.', 'sky'],
                                ] as [$name, $description, $color])
                                    <flux:card class="p-4! shadow-sm">
                                        <div class="flex items-center gap-3">
                                            <div @class([
                                                'flex size-10 shrink-0 items-center justify-center rounded-xl font-black',
                                                'bg-indigo-100 text-indigo-600' => $color === 'indigo',
                                                'bg-emerald-100 text-emerald-600' => $color === 'emerald',
                                                'bg-sky-100 text-sky-600' => $color === 'sky',
                                            ])>{{ mb_substr($name, 0, 1) }}</div>
                                            <div>
                                                <flux:heading size="sm">{{ $name }}</flux:heading>
                                                <flux:text class="mt-1 text-xs! leading-5!">{{ $description }}</flux:text>
                                            </div>
                                        </div>
                                    </flux:card>
                                @endforeach
                            </div>
                        </div>

                        <flux:separator class="my-4" />

                        <div class="grid gap-4 sm:grid-cols-2">
                            <flux:card class="bg-slate-50 p-4! shadow-none">
                                <div class="text-xs font-bold uppercase tracking-[.18em] text-slate-400">Flujo de trabajo</div>
                                <div class="mt-3 flex flex-wrap gap-2">
                                    <flux:badge color="sky">Subir imágenes</flux:badge>
                                    <flux:badge color="blue">Aplicar marcos</flux:badge>
                                    <flux:badge color="emerald">Generar diseños</flux:badge>
                                    <flux:badge color="violet">Exportar</flux:badge>
                                </div>
                            </flux:card>
                            <flux:card class="bg-slate-50 p-4! shadow-none">
                                <div class="text-xs font-bold uppercase tracking-[.18em] text-slate-400">Ideal para</div>
                                <flux:text class="mt-3 text-xs! leading-6!">
                                    Redes sociales · Reconocimientos · Diplomas · Credenciales · Gafetes · Campañas visuales
                                </flux:text>
                            </flux:card>
                        </div>
                    </flux:card>
                </div>
            </section>

            <section class="border-y border-slate-200 bg-white/80 py-20 backdrop-blur">
                <div class="mx-auto max-w-7xl px-6 lg:px-8">
                    <flux:badge color="sky">Funciones principales</flux:badge>
                    <flux:heading size="xl" level="2" class="mt-4 max-w-3xl text-3xl! font-extrabold! sm:text-4xl!">
                        Una suite general para producir contenido visual con mayor orden y velocidad
                    </flux:heading>

                    <div class="mt-10 grid gap-6 md:grid-cols-2 xl:grid-cols-4">
                        @foreach([
                            ['photo', 'Imágenes y marcos', 'Procesamiento individual o masivo con presets y orientaciones automáticas.'],
                            ['document-text', 'Plantillas dinámicas', 'Diseños reutilizables con variables para personas, marcas y proyectos.'],
                            ['identification', 'Credenciales', 'Identificaciones, gafetes y reversos con validación pública opcional.'],
                            ['arrow-down-tray', 'Exportación', 'Archivos listos para compartir, descargar, publicar o imprimir.'],
                        ] as [$icon, $name, $description])
                            <flux:card class="transition hover:-translate-y-1 hover:shadow-xl">
                                <div class="flex size-12 items-center justify-center rounded-2xl bg-sky-100 text-[#006492]">
                                    <flux:icon :name="$icon" class="size-6" />
                                </div>
                                <flux:heading size="lg" class="mt-5">{{ $name }}</flux:heading>
                                <flux:text class="mt-3 text-sm! leading-6!">{{ $description }}</flux:text>
                            </flux:card>
                        @endforeach
                    </div>
                </div>
            </section>

            <section class="mx-auto max-w-7xl px-6 py-20 lg:px-8">
                <flux:card class="relative overflow-hidden border-sky-200! bg-gradient-to-r from-sky-50 via-blue-50 to-lime-50 p-8! shadow-xl sm:p-10!">
                    <div class="pointer-events-none absolute -right-16 -top-20 size-64 rounded-full bg-sky-200/50 blur-3xl"></div>
                    <div class="relative grid items-center gap-8 lg:grid-cols-[1fr_auto]">
                        <div>
                            <flux:badge color="sky">MiniSystems Creative Suite</flux:badge>
                            <flux:heading size="xl" level="2" class="mt-4 max-w-3xl text-3xl! font-extrabold!">
                                Convierte tu flujo creativo en una experiencia más rápida, visual y profesional.
                            </flux:heading>
                            <flux:text class="mt-4 max-w-2xl leading-7!">
                                Gestiona recursos, genera materiales personalizados y mantén tu producción organizada en una sola plataforma.
                            </flux:text>
                        </div>
                        @auth
                            <flux:button :href="route('dashboard')" variant="primary" size="lg" icon:trailing="arrow-right">Ir al panel</flux:button>
                        @else
                            <flux:button :href="route('login')" variant="primary" size="lg" icon:trailing="arrow-right">Iniciar sesión</flux:button>
                        @endauth
                    </div>
                </flux:card>
            </section>
        </main>

        <footer class="border-t border-slate-200 bg-white">
            <div class="mx-auto flex max-w-7xl flex-col gap-3 px-6 py-8 text-sm text-slate-500 sm:flex-row sm:items-center sm:justify-between lg:px-8">
                <strong class="text-slate-800">MiniSystems Creative Suite</strong>
                <span>© {{ now()->year }} Plataforma de producción visual.</span>
            </div>
        </footer>
    </div>

    @fluxScripts
</body>
</html>
