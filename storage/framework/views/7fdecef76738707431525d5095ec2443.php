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
            src: url('<?php echo e(storage_path('fonts/calibri-regular.ttf')); ?>') format('truetype');
        }

        @font-face {
            font-family: 'calibri';
            font-style: normal;
            font-weight: 700;
            src: url('<?php echo e(storage_path('fonts/calibri-bold.ttf')); ?>') format('truetype');
        }

        @font-face {
            font-family: 'greatVibes';
            font-style: normal;
            src: url('<?php echo e(storage_path('fonts/greatVibes-Regular.ttf')); ?>') format('truetype');
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
            left: <?php echo e((100 - (float) $configuracion['ancho_bloque']) / 2); ?>%;
            right: <?php echo e((100 - (float) $configuracion['ancho_bloque']) / 2); ?>%;
            text-align: <?php echo e($configuracion['alineacion']); ?>;
        }

        .bloque.superior {
            top: <?php echo e((float) $configuracion['superior_top']); ?>cm;
        }

        .bloque.inferior {
            top: <?php echo e((float) $configuracion['inferior_top']); ?>cm;
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
            color: <?php echo e($configuracion['nombre_color']); ?>;
            word-wrap: break-word;
            overflow-wrap: break-word;
            font-weight: 700;
        }

        .datos {
            font-size: <?php echo e((int) $configuracion['datos_tamano']); ?>px;
            line-height: 1;
            color: <?php echo e($configuracion['datos_color']); ?>;
            font-weight: 700;
        }

        .datos.abajo-del-nombre {
            margin-top: .18cm;
        }

        .nombre.abajo-de-datos {
            margin-top: .18cm;
        }

        .mesa {
            font-size: 150px;
            font-family: 'greatVibes', cursive;
        }
    </style>
</head>

<body>
    <?php
        $repetirMismoAlumno = ($modoImpresion ?? 'diferentes') === 'repetir';
    ?>

    <?php $__currentLoopData = $paginas; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $pagina): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
        <section class="pagina">
            <img class="fondo" src="<?php echo e($fondoBase64); ?>" alt="">

            <?php $__currentLoopData = $pagina; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $indice => $alumno): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                <?php
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
                ?>

                <div class="bloque <?php echo e($esPrimerAlumno ? 'superior' : 'inferior'); ?>">
                    <div class="contenido <?php echo e($debeRotarBloque ? 'rotado-180' : ''); ?>">
                        <div class="mesa">
                            Mesa
                        </div>
                        <div class="nombre" style="font-size: <?php echo e($tamanoNombre); ?>px;">
                            <?php echo e($nombre); ?>

                        </div>

                        <div class="datos abajo-del-nombre">
                            <?php if(filled($detalle)): ?>
                                <?php echo e($detalle); ?>

                            <?php endif; ?>

                            <?php if($configuracion['mostrar_generacion'] && filled($alumno->generacion)): ?>
                                <?php if(filled($detalle)): ?>
                                    <br>
                                <?php endif; ?>
                                GENERACIÓN: <?php echo e(mb_strtoupper($alumno->generacion, 'UTF-8')); ?>

                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
        </section>
    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
</body>

</html>
<?php /**PATH C:\laragon\www\minisystems\resources\views/pdf/etiquetas/hoja.blade.php ENDPATH**/ ?>