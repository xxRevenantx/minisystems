<!doctype html>
<html lang="es">

<head>
    <meta charset="utf-8">
    <title>Etiquetas</title>

    <style>
        @page {
            size: letter portrait;
            margin: 0;
        }

        @font-face {
            font-family: 'calibri';
            font-style: normal;
            font-weight: 400;
            src: url('{{ storage_path('fonts/calibri-regular.ttf') }}') format('truetype');
        }

        @font-face {
            font-family: 'calibri';
            font-style: normal;
            font-weight: 700;
            src: url('{{ storage_path('fonts/calibri-bold.ttf') }}') format('truetype');
        }

        * {
            box-sizing: border-box;
        }

        html,
        body {
            margin: 0;
            padding: 0;
            font-family: 'calibri', sans-serif;
            color: #111827;
        }

        .pagina {
            position: relative;
            width: 21.59cm;
            height: 27.94cm;
            overflow: hidden;
            page-break-after: always;
        }

        .pagina:last-child {
            page-break-after: auto;
        }

        .fondo {
            position: absolute;
            top: 0;
            left: 0;
            width: 21.59cm;
            height: 27.94cm;
        }

        .bloque {
            position: absolute;
            left: {{ (100 - (float) $configuracion['ancho_bloque']) / 2 }}%;
            right: {{ (100 - (float) $configuracion['ancho_bloque']) / 2 }}%;
            text-align: {{ $configuracion['alineacion'] }};
        }

        .bloque.superior {
            top: {{ (float) $configuracion['superior_top'] }}cm;
        }

        .bloque.inferior {
            top: {{ (float) $configuracion['inferior_top'] }}cm;
        }

        .contenido {
            width: 100%;
        }

        .contenido.rotado-180 {
            transform: rotate(180deg);
            transform-origin: center center;
        }

        .nombre {
            width: 100%;
            line-height: 1;
            color: {{ $configuracion['nombre_color'] }};
            word-wrap: break-word;
            overflow-wrap: break-word;
            font-weight: 700;
        }

        .datos {
            font-size: {{ (int) $configuracion['datos_tamano'] }}px;
            line-height: 1;
            color: {{ $configuracion['datos_color'] }};
            font-weight: 700;
        }

        .datos.abajo-del-nombre {
            margin-top: .18cm;
        }

        .nombre.abajo-de-datos {
            margin-top: .18cm;
        }
    </style>
</head>

<body>
    @php
        $repetirMismoAlumno = ($modoImpresion ?? 'diferentes') === 'repetir';
    @endphp

    @foreach ($paginas as $pagina)
        <section class="pagina">
            <img class="fondo" src="{{ $fondoBase64 }}" alt="">

            @foreach ($pagina as $indice => $alumno)
                @php
                    $nombreOriginal = trim((string) $alumno->nombre_completo);

                    $nombre = $configuracion['mayusculas'] ? mb_strtoupper($nombreOriginal, 'UTF-8') : $nombreOriginal;

                    $longitud = mb_strlen($nombre, 'UTF-8');

                    $tamanoNombre =
                        $longitud <= 25
                            ? (int) $configuracion['nombre_tamano']
                            : ($longitud <= 34
                                ? (int) $configuracion['nombre_tamano_medio']
                                : (int) $configuracion['nombre_tamano_largo']);

                    $detalle = collect([
                        // filled($alumno->nivel) ? mb_strtoupper($alumno->nivel, 'UTF-8') : null,

                        $configuracion['mostrar_grado'] && filled($alumno->grado)
                            ? mb_strtoupper($alumno->grado, 'UTF-8')
                            : null,

                        $configuracion['mostrar_grupo'] && filled($alumno->grupo)
                            ? 'GRUPO ' . mb_strtoupper($alumno->grupo, 'UTF-8')
                            : null,
                    ])
                        ->filter()
                        ->implode(' · ');

                    $esPrimerAlumno = $indice === 0;
                    $debeRotarBloque = $esPrimerAlumno && $repetirMismoAlumno;
                @endphp

                <div class="bloque {{ $esPrimerAlumno ? 'superior' : 'inferior' }}">
                    <div class="contenido {{ $debeRotarBloque ? 'rotado-180' : '' }}">
                        <div class="nombre" style="font-size: {{ $tamanoNombre }}px;">
                            {{ $nombre }}
                        </div>

                        <div class="datos abajo-del-nombre">
                            @if (filled($detalle))
                                {{ $detalle }}
                            @endif

                            @if ($configuracion['mostrar_generacion'] && filled($alumno->generacion))
                                @if (filled($detalle))
                                    <br>
                                @endif
                                GENERACIÓN: {{ mb_strtoupper($alumno->generacion, 'UTF-8') }}
                            @endif
                        </div>
                    </div>
                </div>
            @endforeach
        </section>
    @endforeach
</body>

</html>
