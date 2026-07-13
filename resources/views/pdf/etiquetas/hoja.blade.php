<!doctype html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <title>Etiquetas</title>
    <style>
        @page { size: letter portrait; margin: 0; }
        * { box-sizing: border-box; }
        html, body { margin: 0; padding: 0; font-family: DejaVu Sans, sans-serif; color: #111827; }
        .pagina { position: relative; width: 21.59cm; height: 27.94cm; overflow: hidden; page-break-after: always; }
        .pagina:last-child { page-break-after: auto; }
        .fondo { position: absolute; inset: 0; width: 21.59cm; height: 27.94cm; }
        .bloque { position: absolute; left: {{ (100 - (float) $configuracion['ancho_bloque']) / 2 }}%; right: {{ (100 - (float) $configuracion['ancho_bloque']) / 2 }}%; text-align: {{ $configuracion['alineacion'] }}; }
        .bloque.superior { top: {{ (float) $configuracion['superior_top'] }}cm; }
        .bloque.inferior { top: {{ (float) $configuracion['inferior_top'] }}cm; }
        .nombre { font-weight: 800; line-height: 1.03; color: {{ $configuracion['nombre_color'] }}; word-wrap: break-word; }
        .datos { margin-top: .18cm; font-size: {{ (int) $configuracion['datos_tamano'] }}px; line-height: 1.25; font-weight: 700; color: {{ $configuracion['datos_color'] }}; }
    </style>
</head>
<body>
@foreach($paginas as $pagina)
    <section class="pagina">
        <img class="fondo" src="{{ $fondoBase64 }}" alt="">
        @foreach($pagina as $indice => $alumno)
            @php
                $nombre = $configuracion['mayusculas'] ? mb_strtoupper($alumno->nombre, 'UTF-8') : $alumno->nombre;
                $longitud = mb_strlen($nombre, 'UTF-8');
                $tamanoNombre = $longitud <= 25
                    ? (int) $configuracion['nombre_tamano']
                    : ($longitud <= 34 ? (int) $configuracion['nombre_tamano_medio'] : (int) $configuracion['nombre_tamano_largo']);
                $detalle = collect([
                    mb_strtoupper($alumno->nivel, 'UTF-8'),
                    $configuracion['mostrar_grado'] && $alumno->grado ? mb_strtoupper($alumno->grado, 'UTF-8') : null,
                    $configuracion['mostrar_grupo'] && $alumno->grupo ? 'GRUPO '.mb_strtoupper($alumno->grupo, 'UTF-8') : null,
                ])->filter()->implode(' · ');
            @endphp
            <div class="bloque {{ $indice === 0 ? 'superior' : 'inferior' }}">
                <div class="nombre" style="font-size: {{ $tamanoNombre }}px;">{{ $nombre }}</div>
                <div class="datos">
                    {{ $detalle }}
                    @if($configuracion['mostrar_generacion'] && $alumno->generacion)
                        <br>GENERACIÓN {{ mb_strtoupper($alumno->generacion, 'UTF-8') }}
                    @endif
                </div>
            </div>
        @endforeach
    </section>
@endforeach
</body>
</html>
