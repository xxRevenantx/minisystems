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
            src: url('<?php echo e(storage_path('fonts/raleway-regular.ttf')); ?>') format('truetype');
        }

        @font-face {
            font-family: 'raleway';
            font-style: normal;
            font-weight: 700;
            src: url('<?php echo e(storage_path('fonts/raleway-bold.ttf')); ?>') format('truetype');
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
            margin-top: 130px;
        }

        .lugar {
            margin-top: 150px;
            color: #66851f;
        }

        .descripcion {
            line-height: 1;

            margin: 100px auto 0;
            width: 750px;
        }

        .fecha {
            margin-top: 50px;
            font-size: 16px;
            color: #262626;
        }

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
            margin: 80px auto 0;
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
            padding-top: 2px;
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

        .validacion {
            position: absolute;
            left: 4%;
            bottom: 3%;
            width: 205px;
            min-height: 54px;
            padding-left: 60px;
            font-size: 8px;
            line-height: 1.35;
            color: #475569;
        }

        .validacion img {
            position: absolute;
            left: 0;
            top: 0;
            width: 52px;
            height: 52px;
        }

        .validacion strong {
            color: #006492;
            font-size: 9px;
        }
    </style>
</head>

<body>
    <?php $__currentLoopData = $reconocimientos; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $reconocimiento): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
        <?php
            $img = $reconocimiento->reconocimientoImagen;
            $cfg = $img?->configuracion ?? [];

            $nombreTop = data_get($cfg, 'nombre.top', 250);
            $nombreTam = data_get($cfg, 'nombre.tamano', 55);

            $descTop = data_get($cfg, 'descripcion.top', 330);
            $descTam = data_get($cfg, 'descripcion.tamano', 16);

            $fechaTop = data_get($cfg, 'fecha.top', 470);
            $firmasTop = data_get($cfg, 'firmas.top', 800);

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
        ?>

        <div class="pagina">
            <?php if($img?->imagen): ?>
                <img class="fondo" src="<?php echo e(public_path('storage/imagenesReconocimientos/' . $img->imagen)); ?>"
                    alt="">
            <?php endif; ?>

            <?php if($reconocimiento->estado === 'cancelado'): ?>
                <div class="cancelado">
                    CANCELADO
                </div>
            <?php endif; ?>

            <div class="bloque nombre" style="top: <?php echo e($nombreTop); ?>px; font-size:50px;">
                <?php echo e($reconocimiento->reconocimiento_a); ?>

            </div>

            <div class="bloque descripcion" style="top: <?php echo e($descTop); ?>px; font-size: <?php echo e($descTam); ?>px;">
                <?php if($reconocimiento->evento): ?>
                    <div class="evento">
                        <?php echo e($reconocimiento->evento->nombre); ?>


                        <?php if($reconocimiento->evento->lugar): ?>
                            · <?php echo e($reconocimiento->evento->lugar); ?>

                        <?php endif; ?>
                    </div>
                <?php endif; ?>

                <?php echo \App\Support\ReconocimientoHtml::limpiar($reconocimiento->descripcion); ?>

            </div>

            <div class="bloque fecha" style="top: <?php echo e($fechaTop); ?>px; font-size: 15px; margin-left: 500px;">
                <?php if($reconocimiento->evento?->lugar): ?>
                    <?php echo e($reconocimiento->evento->lugar); ?>,
                <?php endif; ?><?php echo e($reconocimiento->fecha?->translatedFormat('d \d\e F \d\e Y')); ?>

            </div>

            <?php if($dirs->isNotEmpty()): ?>
                <div class="firmas" style="top: <?php echo e($inicioFirmas); ?>px;">
                    
                    <?php if($directivosPrimeraFila->isNotEmpty()): ?>
                        <table class="tabla-firmas">
                            <tr>
                                <?php $__currentLoopData = $directivosPrimeraFila; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $directivo): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                    <td class="firma">
                                        <?php if($directivo->firma): ?>
                                            <img class="firma-img"
                                                src="<?php echo e(public_path('storage/firmasDirectivos/' . $directivo->firma)); ?>"
                                                alt="">
                                        <?php else: ?>
                                            <div class="espacio-firma"></div>
                                        <?php endif; ?>

                                        <div class="linea">
                                            <div class="nombre-firmante">
                                                <strong>
                                                    <?php echo e($directivo->nombre_completo); ?>

                                                </strong>
                                            </div>

                                            <div class="cargo">
                                                <?php echo e($directivo->cargo); ?>

                                            </div>
                                        </div>
                                    </td>
                                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                            </tr>
                        </table>
                    <?php endif; ?>

                    
                    <?php if($directivosSegundaFila->isNotEmpty()): ?>
                        <table class="tabla-firmas">
                            <tr>
                                <?php $__currentLoopData = $directivosSegundaFila; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $directivo): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                    <td class="firma">
                                        <?php if($directivo->firma): ?>
                                            <img class="firma-img"
                                                src="<?php echo e(public_path('storage/firmasDirectivos/' . $directivo->firma)); ?>"
                                                alt="">
                                        <?php else: ?>
                                            <div class="espacio-firma"></div>
                                        <?php endif; ?>

                                        <div class="linea">
                                            <div class="nombre-firmante">
                                                <strong>
                                                    <?php echo e($directivo->nombre_completo); ?>

                                                </strong>
                                            </div>

                                            <div class="cargo">
                                                <?php echo e($directivo->cargo); ?>

                                            </div>
                                        </div>
                                    </td>
                                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                            </tr>
                        </table>
                    <?php endif; ?>
                </div>
            <?php endif; ?>

            <?php
                $sello = $dirs->first(fn($directivo) => !empty($directivo->sello));
            ?>

            <?php if($sello): ?>
                <img class="sello" src="<?php echo e(public_path('storage/sellosDirectivos/' . $sello->sello)); ?>"
                    alt="">
            <?php endif; ?>
            
        </div>
    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
</body>

</html>
<?php /**PATH C:\laragon\www\minisystems\resources\views/livewire/reconocimientos/pdf/documentosPDF.blade.php ENDPATH**/ ?>