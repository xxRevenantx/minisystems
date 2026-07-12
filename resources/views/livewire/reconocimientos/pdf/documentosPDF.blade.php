<!doctype html>
<html lang="es">

<head>
    <meta charset="utf-8">

    <style>
        @page {
            margin: 0;
        }

        @font-face {
            font-family: 'raleway';
            font-style: normal;
            src: url('{{ storage_path('fonts/raleway-regular.ttf') }}') format('truetype');
        }

        @font-face {
            font-family: 'raleway';
            font-style: normal;
            font-weight: 700;
            src: url('{{ storage_path('fonts/raleway-bold.ttf') }}') format('truetype');
        }

        body {
            margin: 0;
            font-family: 'raleway', DejaVu Sans, sans-serif;
            color: #1f2937;
        }

        .pagina {
            position: relative;
            width: 100%;
            height: 100%;
            overflow: hidden;
            page-break-after: always;
        }

        .pagina:last-child {
            page-break-after: auto;
        }

        .fondo {
            position: absolute;
            inset: 0;
            width: 100%;
            height: 100%;
            object-fit: cover;
        }

        .bloque {
            position: absolute;
            left: 8%;
            right: 8%;
            text-align: center;
        }

        .nombre {
            color: #006492;
            line-height: 1.05;

        }

        .lugar {
            margin-top: 8px;

            color: #66851f;
        }

        .descripcion {
            line-height: 1.5;
        }

        .fecha {}

        .evento {
            margin-bottom: 5px;
            font-size: 10px;
            color: #555;
        }

        /*
        |--------------------------------------------------------------------------
        | Firmas en dos filas
        |--------------------------------------------------------------------------
        */

        .firmas {
            position: absolute;
            left: 8%;
            right: 8%;
            width: 84%;
        }

        .tabla-firmas {
            width: 80%;
            border-collapse: separate;
            border-spacing: 14px 0;
            margin: 0 auto;
        }

        .tabla-firmas+.tabla-firmas {
            margin-top: 18px;
        }

        .firma {
            padding: 0 8px;
            font-size: 15px;
            text-align: center;
            vertical-align: bottom;
        }

        .espacio-firma {
            height: 38px;
        }

        .firma-img {
            display: block;
            width: auto;
            height: 38px;
            max-width: 125px;
            margin: 0 auto;
            object-fit: contain;
        }

        .linea {
            border-top: 1px solid #333;
            padding-top: 4px;
        }

        .nombre-firmante {
            font-size: 15px;
            line-height: 1.2;
        }

        .cargo {
            margin-top: 3px;
            font-size: 14px;
            line-height: 1.2;
            color: #555;
        }

        .cancelado {
            position: absolute;
            z-index: 5;
            top: 42%;
            left: 12%;
            right: 12%;
            color: rgba(190, 0, 0, .20);
            font-size: 58px;
            text-align: center;
            transform: rotate(-25deg);
        }

        .sello {
            position: absolute;
            right: 5%;
            bottom: 4%;
            width: 65px;
            opacity: .9;
        }
    </style>
</head>

<body>
    @foreach ($reconocimientos as $reconocimiento)
        @php
            $img = $reconocimiento->reconocimientoImagen;
            $cfg = $img?->configuracion ?? [];

            $nombreTop = data_get($cfg, 'nombre.top', 250);
            $nombreTam = data_get($cfg, 'nombre.tamano', 55);

            $descTop = data_get($cfg, 'descripcion.top', 330);
            $descTam = data_get($cfg, 'descripcion.tamano', 16);

            $fechaTop = data_get($cfg, 'fecha.top', 470);
            $firmasTop = data_get($cfg, 'firmas.top', 540);

            $dirs = $reconocimiento->directivos->sortBy('orden')->values();

            /*
             * La Directora de Primaria y Secundaria y el Subdirector
             * se enviarán a la segunda fila.
             */
            $perteneceSegundaFila = function ($directivo): bool {
                $cargo = mb_strtolower(trim((string) $directivo->cargo), 'UTF-8');

                return str_contains($cargo, 'primaria y secundaria') || str_contains($cargo, 'subdirector');
            };

            $directivosPrimeraFila = $dirs->reject($perteneceSegundaFila)->values();

            $directivosSegundaFila = $dirs->filter($perteneceSegundaFila)->values();

            /*
             * Se sube ligeramente el bloque para dar espacio
             * suficiente a las dos filas.
             */
            $inicioFirmas = $directivosSegundaFila->isNotEmpty() ? $firmasTop - 42 : $firmasTop;
        @endphp

        <div class="pagina">
            @if ($img?->imagen)
                <img class="fondo" src="{{ public_path('storage/imagenesReconocimientos/' . $img->imagen) }}"
                    alt="">
            @endif

            @if ($reconocimiento->estado === 'cancelado')
                <div class="cancelado">
                    CANCELADO
                </div>
            @endif

            <div class="bloque nombre" style="top: {{ $nombreTop }}px; font-size:50px;">
                {{ $reconocimiento->reconocimiento_a }}
            </div>

            @if ($reconocimiento->lugar_obtenido)
                <div class="bloque lugar"
                    style="
                        top: {{ $nombreTop + $nombreTam + 15 }}px;
                        font-size: {{ max(12, $nombreTam - 16) }}px;
                    ">
                    {{ $reconocimiento->lugar_obtenido }}
                </div>
            @endif

            <div class="bloque descripcion" style="top: {{ $descTop }}px; font-size: {{ $descTam }}px;">
                @if ($reconocimiento->evento)
                    <div class="evento">
                        {{ $reconocimiento->evento->nombre }}

                        @if ($reconocimiento->evento->lugar)
                            · {{ $reconocimiento->evento->lugar }}
                        @endif
                    </div>
                @endif

                {!! \App\Support\ReconocimientoHtml::limpiar($reconocimiento->descripcion) !!}
            </div>

            <div class="bloque fecha" style="top: {{ $fechaTop }}px; font-size: 12px;">
                {{ $reconocimiento->fecha?->translatedFormat('d \d\e F \d\e Y') }}
            </div>

            @if ($dirs->isNotEmpty())
                <div class="firmas" style="top: {{ $inicioFirmas }}px;">
                    {{-- Primera fila: Rector y Directora General --}}
                    @if ($directivosPrimeraFila->isNotEmpty())
                        <table class="tabla-firmas">
                            <tr>
                                @foreach ($directivosPrimeraFila as $directivo)
                                    <td class="firma">
                                        @if ($directivo->firma)
                                            <img class="firma-img"
                                                src="{{ public_path('storage/firmasDirectivos/' . $directivo->firma) }}"
                                                alt="">
                                        @else
                                            <div class="espacio-firma"></div>
                                        @endif

                                        <div class="linea">
                                            <div class="nombre-firmante">
                                                <strong>
                                                    {{ $directivo->nombre_completo }}
                                                </strong>
                                            </div>

                                            <div class="cargo">
                                                {{ $directivo->cargo }}
                                            </div>
                                        </div>
                                    </td>
                                @endforeach
                            </tr>
                        </table>
                    @endif

                    {{-- Segunda fila: Directora de Primaria y Secundaria y Subdirector --}}
                    @if ($directivosSegundaFila->isNotEmpty())
                        <table class="tabla-firmas">
                            <tr>
                                @foreach ($directivosSegundaFila as $directivo)
                                    <td class="firma">
                                        @if ($directivo->firma)
                                            <img class="firma-img"
                                                src="{{ public_path('storage/firmasDirectivos/' . $directivo->firma) }}"
                                                alt="">
                                        @else
                                            <div class="espacio-firma"></div>
                                        @endif

                                        <div class="linea">
                                            <div class="nombre-firmante">
                                                <strong>
                                                    {{ $directivo->nombre_completo }}
                                                </strong>
                                            </div>

                                            <div class="cargo">
                                                {{ $directivo->cargo }}
                                            </div>
                                        </div>
                                    </td>
                                @endforeach
                            </tr>
                        </table>
                    @endif
                </div>
            @endif

            @php
                $sello = $dirs->first(fn($directivo) => !empty($directivo->sello));
            @endphp

            @if ($sello)
                <img class="sello" src="{{ public_path('storage/sellosDirectivos/' . $sello->sello) }}"
                    alt="">
            @endif
        </div>
    @endforeach
</body>

</html>
