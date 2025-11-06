<!DOCTYPE html>
<html lang="es">
<head>
     <meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
    <meta name="viewport" content="width=device-width, user-scalable=no, initial-scale=1.0, maximum-scale=1.0, minimum-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">

    <title>RECONOCIMIENTO</title>
</head>
<style>

      @page { margin:0px 0px 0px 0px; }
        .page-break {
           page-break-after: always;
        }

    /* @font-face {
            font-family: 'greatVibes';
            font-style: normal;
            src: url('{{ storage_path('fonts/GreatVibes-Regular.ttf') }}') format('truetype');
    } */

    @font-face {
        font-family: 'greatVibes';
        font-style: normal;
        /* src: url('{{ storage_path('fonts/GreatVibes-Regular.ttf') }}') format('truetype'); */
        src: url('{{ public_path('fonts/GreatVibes-Regular.ttf') }}') format('truetype');

        /* src: url('fonts/GreatVibes-Regular.ttf') format('ttf'); */

    }

    @font-face {
        font-family: 'calibri';
        font-style: normal;
        src: url('{{ storage_path('fonts/calibri/calibri.ttf') }}') format('truetype');

     }

    @font-face {
        font-family: 'calibri';
        font-style: bold;
        font-weight: 700;
        src: url('{{ storage_path('fonts/calibri/calibri-bold.ttf') }}') format('truetype');
    }


    .fondo {
        position: absolute;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        z-index: -1;
    }
    .contenedor{
        padding: 70px 0 0 ;
        margin-top:370px;
        /* background: #d7d7d7; */
    }

    .reconocimientoa{
        font-size: 65px;
        text-align: center;
        color: #000;
        margin-top: -95px;
        /* font-family: 'greatVibes'; */
        font-family: 'greatVibes', sans-serif; /* Apply the font to your elements */

    }

    .descripcion{
        font-size: 17px;
        width: 70%;
        text-align: center;
        color: #000;
        font-family: sans-serif;
        margin: -75px auto 0 ;
        line-height: 20px;
    }


    .contenedor-linea{
        display: block;
        margin: -30px auto;
        width: 90%;
    }

    img.linea{
        display: block;
        margin: 0 auto;
        width: 100%;
    }

    .generacion{
        font-size: 45px;
        text-align: center;
        color: #000;
         font-family: 'greatVibes';
        margin-top: 15px;
    }

    .lugar{
        font-size: 17px;
        text-align: center;
        color: #000;
        font-family: sans-serif;
        margin-top: 40px;
        margin-left: 300px;
    }

    table {
        width: 100%;
        border-collapse: collapse;
        text-transform: uppercase;
        margin-top: 90px;
        line-height: 17px;

    }

    .rector{
        text-transform: uppercase;
    }

    .directora{
        text-transform: uppercase;
    }

</style>
<body>
    @php
    $nombreCompleto = "{$reconocimiento->reconocimiento_a}";
    // $nombreFormateado = Str::title(Str::lower($nombreCompleto)); // Por si viene con mayúsculas raras


    // $nombreRector = "{$rector->nombre} {$rector->apellido_paterno} {$rector->apellido_materno}";
    // $nombreDirectora = "{$directora->nombre} {$directora->apellido_paterno} {$directora->apellido_materno}";

       // 1) Trae lo almacenado
    $raw = $reconocimiento->descripcion ?? '';

    // 2) Conserva SOLO estas etiquetas (ajusta a tu gusto)
    $allowed = '<p><br><b><strong><i><em><u><ul><ol><li>';

    $desc = strip_tags($raw, $allowed);

    // 3) Quita atributos molestos (data-*, style, class, id, etc.)
    //    así mantienes <strong> y <em> pero sin "data-start" ni estilos inline
    $desc = preg_replace('/\s(data-[\w-]+|style|class|id)="[^"]*"/i', '', $desc);

    // 4) Decodifica entidades a UTF-8 (á, é, í…) por si vienen como &aacute;
    $desc = html_entity_decode($desc, ENT_QUOTES | ENT_HTML5, 'UTF-8');

    // 5) (Opcional) Normaliza dobles <br> a párrafos, etc.
@endphp




        <div class="fondo">
            <img src="{{ public_path('storage/imagenesReconocimientos/'.$reconocimiento->reconocimientoImagen->imagen) }}" alt="fondo" style="width: 100%; height: 100%;">
        </div>

        <div class="contenedor">
          <p class="reconocimientoa">A: {{ $nombreCompleto }}</p>

          <div class="descripcion">

                {!! $desc !!}

          </div>






          <p class="lugar">
            Cd. Altamirano, Gro., a {{ \Carbon\Carbon::parse($reconocimiento->fecha)->translatedFormat('d \d\e F \d\e\l Y') }}
            {{-- CD. ALTAMIRANO, GRO., A 28 DE AGOSTO DEL 2024 --}}
          </p>

          {{-- {{ $reconocimiento->directivos }} --}}
@php
    $dirs   = $reconocimiento->directivos->sortBy('id')->values();
    $count  = $dirs->count();
@endphp

<style>
  /* Estilos seguros para DomPDF */
  table.firmas { width: 70%; border-collapse: collapse; margin: 0 auto; }
  table.firmas td { width: 50%; text-align: center; vertical-align: bottom; padding: 45px 0 0 0; }
  .firma-linea  { width: 300px; margin: 0 auto 6px auto; display:block; }
  .firma-nombre { font-family: 'calibri','Carlito',Arial,sans-serif; font-size: 13px; font-weight: bold; text-transform: uppercase; line-height: 1.2; display:block; }
  .firma-cargo  { font-family: 'calibri','Carlito',Arial,sans-serif; font-size: 13px; line-height: 1.2; display:block; }
</style>

<table class="firmas">
  @if ($count === 1)
      {{-- 1 firmante: centrado --}}
      <tr>
        <td colspan="2">
          <span class="firma-linea">___________________________________</span>
          <span class="firma-nombre">
            {{ $dirs[0]->titulo }} {{ $dirs[0]->nombre }} {{ $dirs[0]->apellido_paterno }} {{ $dirs[0]->apellido_materno }}
          </span>
          <span class="firma-cargo">{{ $dirs[0]->cargo }}</span>
        </td>
      </tr>

  @elseif ($count === 3)
      {{-- 3 firmantes: 2 arriba + 1 centrado abajo --}}
      <tr>
        @foreach($dirs->take(2) as $d)
          <td>
            <span class="firma-linea">___________________________________</span>
            <span class="firma-nombre">
              {{ $d->titulo }} {{ $d->nombre }} {{ $d->apellido_paterno }} {{ $d->apellido_materno }}
            </span>
            <span class="firma-cargo">{{ $d->cargo }}</span>
          </td>
        @endforeach
      </tr>
      <tr>
        <td colspan="2">
          @php $d = $dirs[2]; @endphp
          <span class="firma-linea">___________________________________</span>
          <span class="firma-nombre">
            {{ $d->titulo }} {{ $d->nombre }} {{ $d->apellido_paterno }} {{ $d->apellido_materno }}
          </span>
          <span class="firma-cargo">{{ $d->cargo }}</span>
        </td>
      </tr>

  @else
      {{-- Caso general (2, 4, 5…): filas de 2; si queda 1, se agrega celda vacía --}}
      @foreach($dirs->chunk(2) as $fila)
        <tr>
          @foreach($fila as $d)
            <td>
              <span class="firma-linea">___________________________________</span>
              <span class="firma-nombre">
                {{ $d->titulo }} {{ $d->nombre }} {{ $d->apellido_paterno }} {{ $d->apellido_materno }}
              </span>
              <span class="firma-cargo">{{ $d->cargo }}</span>
            </td>
          @endforeach
          @if($fila->count() < 2)
            <td></td>
          @endif
        </tr>
      @endforeach
  @endif
</table>






        </div>


</body>
</html>
