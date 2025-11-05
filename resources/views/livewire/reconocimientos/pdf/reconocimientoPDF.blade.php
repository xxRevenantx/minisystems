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

        @font-face {
            font-family: 'greatVibes';
            font-style: normal;
            src: url('{{ storage_path('fonts/GreatVibes-Regular.ttf') }}') format('truetype');

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
        padding: 70px 0 ;
        margin-top:370px;
        /* background: #d7d7d7; */
    }

    .alumno{
        font-size: 55px;
        text-align: center;
        color: #000;
        font-family: 'greatVibes';
    }

    .descripcion{
        font-size: 23px;
        text-align: center;
        color: #000;
        font-family: 'calibri';
        margin-top: -30px;
        line-height: 20px;
    }
    .licenciatura{
        text-align: center;
        color: #000;
        font-family: 'calibri';
        margin-top: -20px;
        text-transform: uppercase;
        font-weight: bold;
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
        font-family: 'calibri';
        margin-top: -40px;
        text-transform: uppercase;
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
    // $nombreCompleto = "{$alumno->nombre} {$alumno->apellido_paterno} {$alumno->apellido_materno}";
    // $nombreFormateado = Str::title(Str::lower($nombreCompleto)); // Por si viene con mayúsculas raras


    // $nombreRector = "{$rector->nombre} {$rector->apellido_paterno} {$rector->apellido_materno}";
    // $nombreDirectora = "{$directora->nombre} {$directora->apellido_paterno} {$directora->apellido_materno}";
@endphp

    {{$reconocimiento}}


        <div class="fondo">
            <img src="{{ public_path('storage/imagenesReconocimientos/'.$reconocimiento->imagen) }}" alt="fondo" style="width: 100%; height: 100%;">
        </div>

        <div class="contenedor">
          <p class="alumno"></p>

          <p class="descripcion">
            EN VIRTUD DE HABER CONCLUIDO SATISFACTORIAMENTE SUS <br>
            ESTUDIOS CORRESPONDIENTES A LA CARRERA DE:
          </p>



          <div class="contenedor-linea">
             <img class="linea" src="{{ public_path('storage/linea.png') }}" >
          </div>


          <p class="lugar">
            {{-- CD. ALTAMIRANO, GRO., A {{ \Carbon\Carbon::parse($fecha)->translatedFormat('d \D\E F \D\E\L Y') }} --}}
            {{-- CD. ALTAMIRANO, GRO., A 28 DE AGOSTO DEL 2024 --}}
          </p>


          <table>
                <tr>
                    {{-- <td style="text-align: center;">
                        <span style="display: block; border-top: 1px solid #000; width: 300px; margin: 0 auto;"></span>
                        <span class="rector" style="font-family: 'calibri'; font-size: 18px; bold;">{{ $rector->titulo }} {{ $nombreRector }}</span><br>
                        <span style="font-family: 'calibri'; font-size: 17px;">{{ $rector->cargo }}</span>
                    </td>
                    <td style="text-align: center;">
                        <span style="display: block; border-top: 1px solid #000; width: 300px; margin: 0 auto;"></span>
                        <span class="directora" style="font-family: 'calibri'; font-size: 18px; bold;">M.S.P.{{ $nombreDirectora }}</span><br>
                        <span style="font-family: 'calibri'; font-size: 17px;">{{ $directora->cargo }}</span>
                    </td> --}}
                </tr>
          </table>




        </div>


</body>
</html>
