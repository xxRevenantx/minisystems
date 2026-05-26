<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <title>CREDENCIALES</title>

    <style>
        @page {
            margin: 60px 30px 30px 30px;
        }

        @font-face {
            font-family: 'calibri';
            font-style: normal;
            font-weight: 400;
            src: url('{{ storage_path('fonts/calibri/calibri.ttf') }}') format('truetype');
        }

        @font-face {
            font-family: 'calibri';
            font-style: normal;
            font-weight: 700;
            src: url('{{ storage_path('fonts/calibri/calibri-bold.ttf') }}') format('truetype');
        }

        * {
            box-sizing: border-box;
        }

        body {
            margin: 0;
            font-family: 'calibri', DejaVu Sans, sans-serif;
        }

        .contenedorCredenciales {
            width: 100%;
            margin: auto;
        }

        .bloqueCredencial {
            position: relative;
            width: 18cm;
            height: 5.5cm;
            margin: auto;
            padding: 5px 0;
        }

        .credenciales {
            border: 1px solid #000;
            width: 18cm;
            height: 5.5cm;
            display: block;
        }

        .sinFoto {
            position: absolute;
            top: 30px;
            left: 15px;
            width: 1.8cm;
            height: 1.3cm;
            border: 1px solid #b8b8b8;
            color: #bebebe;
            text-align: center;
            font-size: 9px;
            font-weight: 700;
            padding-top: 42px;
        }

        .info {
            position: absolute;
            top: 38px;
            left: 140px;
            width: 325px;
            font-size: 9px;
            line-height: 9px;
            color: #111;
        }

        .info b {
            font-weight: 700;
        }

        .nombreAlumno {
            text-transform: uppercase;
            font-weight: 700;
            font-size: 8px;
        }

        .page-break {
            page-break-after: always;
        }

        .titulo {
            font-size: 11px;
            margin-left: 35px;
            font-weight: 700;
            color: rgb(255, 255, 255);
        }

        .cct {
            position: absolute;
            top: -30px;
            left: 126px;
            font-size: 10px;
            font-weight: 700;
            color: rgb(255, 255, 255);
        }

        .director {
            position: absolute;
            text-align: center;
            line-height: 9px;
            top: 90px;
            bottom: 10px;
            left: 470px;
            width: 160px;
            font-size: 10px;
            color: rgb(0, 0, 0);
        }

        .logo {
            position: absolute;
            top: -30px;
            left: 270px;
            width: 40px;
        }

        .logo2 {
            position: absolute;
            top: -30px;
            right: 20px;
            width: 40px;
        }

        .domicilio {
            display: inline-block;
            max-width: 260px;
            line-height: 9px;
        }
    </style>
</head>

<body>
    <div class="contenedorCredenciales">

        @foreach ($credenciales as $index => $credencial)
            @php
                /*
                 * Fondo principal de la credencial.
                 * Cambia el nombre si tu imagen se llama diferente.
                 */
                $rutaFondo = public_path('credencial.jpg');

                /*
                 * Logo general opcional.
                 * Puedes cambiarlo por el logo real que tengas en public o storage.
                 */
                $rutaLogo = public_path('logo.png');

                $existeFondo = file_exists($rutaFondo);
                $existeLogo = file_exists($rutaLogo);

                $nombreCompleto = mb_strtoupper(trim($credencial->nombre ?? ''), 'UTF-8');

                $nivel = $credencial->nivel ?: 'No especificado';

                $esLicenciatura = $credencial->nivel === 'Licenciatura';

                $gradoGrupo = trim(($credencial->grado ?? '') . ' ' . ($credencial->grupo ?? ''));

                $programa = $esLicenciatura
                    ? ($credencial->licenciatura ?:
                    'No especificada')
                    : ($gradoGrupo ?:
                    'No especificado');

                $tipoPrograma = $esLicenciatura ? 'Licenciatura' : 'Grado / Grupo';

                $cctCredencial = match ($credencial->nivel) {
                    'Preescolar' => 'No especificado',
                    'Primaria' => 'No especificado',
                    'Secundaria' => 'No especificado',
                    'Bachillerato' => 'No especificado',
                    'Licenciatura' => 'No especificado',
                    default => 'No especificado',
                };

                /*
                 * Ajusta estos datos si quieres que salgan con el nombre real del director.
                 */
                $nombreDirector = 'DIRECTOR(A)';
                $cargoDirector = 'FIRMA Y SELLO';
            @endphp

            <div class="bloqueCredencial">

                {{-- Fondo de la credencial --}}
                @if ($existeFondo)
                    <img class="credenciales" src="{{ $rutaFondo }}" alt="Credencial">
                @else
                    <div class="credenciales"></div>
                @endif

                {{-- Logos --}}
                @if ($existeLogo)
                    <img class="logo" src="{{ $rutaLogo }}" alt="Logo">

                    <img class="logo2" src="{{ $rutaLogo }}" alt="Logo">
                @endif

                {{-- CCT --}}
                {{-- <span class="cct">
                    C.C.T. {{ $cctCredencial }}
                </span> --}}

                {{-- Espacio para foto --}}
                <div class="sinFoto">
                    FOTO + SELLO
                </div>

                {{-- Información --}}
                <div class="info">
                    {{-- <span class="titulo">
                        CREDENCIAL DEL ALUMNO
                    </span>
                    <br> --}}

                    <b>Nombre:</b>
                    <span class="nombreAlumno">
                        {{ $nombreCompleto ?: 'No especificado' }}
                    </span>
                    <br>

                    <b>Matrícula:</b>
                    {{ $credencial->matricula ?: 'No especificada' }}
                    <br>

                    <b>CURP:</b>
                    {{ $credencial->curp ?: 'No especificada' }}
                    <br>

                    <b>Nivel:</b>
                    {{ $nivel }}
                    <br>

                    <b>{{ $tipoPrograma }}:</b>
                    {{ $programa }}
                    <br>

                    <b>Ciclo escolar:</b>
                    {{ $credencial->ciclo_escolar ?: 'No especificado' }}
                    <br>

                    <b>Vigencia:</b>
                    {{ $credencial->vigencia ?: 'No especificada' }}
                    <br>

                    @if ($credencial->telefono)
                        <b>Teléfono:</b>
                        {{ $credencial->telefono }}
                        <br>
                    @endif

                    @if ($credencial->domicilio)
                        <b>Domicilio:</b>
                        <span class="domicilio">
                            {{ $credencial->domicilio }}
                        </span>
                        <br>
                    @endif
                </div>


            </div>

            @if (($index + 1) % 4 === 0 && !$loop->last)
                <div class="page-break"></div>
            @endif
        @endforeach

    </div>
</body>

</html>
