<?php
    $uploaderConfig = [
        'csrf' => csrf_token(),
        'maxFiles' => $maxFiles,
        'maxFileMb' => $maxFileMb,
        'maxFileBytes' => (int) config('system_images.max_file_kb', 20 * 1024) * 1024,
        'uploadConcurrency' => $uploadConcurrency,
        'urls' => [
            'active' => route('images.system.api.active'),
            'store' => route('images.system.api.store'),
            'show' => route('images.system.api.show', ['batch' => '__BATCH__']),
            'upload' => route('images.system.api.upload', [
                'batch' => '__BATCH__',
                'item' => '__ITEM__',
            ]),
            'uploadFailed' => route('images.system.api.upload-failed', [
                'batch' => '__BATCH__',
                'item' => '__ITEM__',
            ]),
            'retry' => route('images.system.api.retry', [
                'batch' => '__BATCH__',
                'item' => '__ITEM__',
            ]),
            'destroy' => route('images.system.api.destroy', ['batch' => '__BATCH__']),
        ],
    ];
?>

<div
    x-data="systemImagesUploader(<?php echo \Illuminate\Support\Js::from($uploaderConfig)->toHtml() ?>)"
    class="space-y-6 px-1 sm:px-2"
>
    <section
        class="overflow-hidden rounded-[28px] border border-slate-200/80 bg-white shadow-[0_20px_60px_-20px_rgba(15,23,42,0.18)] ring-1 ring-slate-100 dark:border-neutral-800 dark:bg-neutral-900 dark:ring-neutral-800"
    >
        <div class="relative overflow-hidden bg-gradient-to-br from-sky-500 via-blue-600 to-indigo-700 px-6 py-8 text-white sm:px-8 sm:py-10">
            <div class="pointer-events-none absolute inset-0 opacity-25">
                <div class="absolute -left-14 top-0 h-44 w-44 rounded-full bg-white/25 blur-3xl"></div>
                <div class="absolute right-0 top-5 h-56 w-56 rounded-full bg-cyan-300/25 blur-3xl"></div>
                <div class="absolute bottom-0 left-1/3 h-44 w-44 rounded-full bg-indigo-300/25 blur-3xl"></div>
            </div>

            <div class="relative flex flex-col gap-7 lg:flex-row lg:items-center lg:justify-between">
                <div class="max-w-3xl">
                    <span class="inline-flex items-center gap-2 rounded-full border border-white/15 bg-white/10 px-4 py-1.5 text-[11px] font-extrabold uppercase tracking-[0.18em] text-white/95 backdrop-blur">
                        <span class="h-2 w-2 rounded-full bg-cyan-200"></span>
                        Carga progresiva con cola
                    </span>

                    <h2 class="mt-4 text-3xl font-black tracking-tight sm:text-4xl">
                        System Images <?php echo e($maxFilesLabel); ?>

                    </h2>

                    <p class="mt-3 max-w-2xl text-sm leading-6 text-blue-50/95 sm:text-[15px]">
                        Selecciona archivos o una carpeta completa. Cada imagen se sube en una petición independiente,
                        entra a la cola <span class="font-black">system-images</span> y se procesa sin cargar todo el lote en memoria.
                    </p>
                </div>

                <div class="grid grid-cols-1 gap-3 sm:grid-cols-3">
                    <div class="rounded-2xl border border-white/10 bg-white/10 px-4 py-3 text-center backdrop-blur">
                        <p class="text-[10px] font-extrabold uppercase tracking-[0.16em] text-blue-100">Capacidad</p>
                        <p class="mt-1 text-sm font-black text-white"><?php echo e($maxFilesLabel); ?></p>
                    </div>
                    <div class="rounded-2xl border border-white/10 bg-white/10 px-4 py-3 text-center backdrop-blur">
                        <p class="text-[10px] font-extrabold uppercase tracking-[0.16em] text-blue-100">Peso</p>
                        <p class="mt-1 text-sm font-black text-white"><?php echo e($maxFileMb); ?> MB c/u</p>
                    </div>
                    <div class="rounded-2xl border border-white/10 bg-white/10 px-4 py-3 text-center backdrop-blur">
                        <p class="text-[10px] font-extrabold uppercase tracking-[0.16em] text-blue-100">Worker</p>
                        <p class="mt-1 text-sm font-black text-white">Cola automática</p>
                    </div>
                </div>
            </div>
        </div>

        <div class="space-y-6 bg-slate-50/40 p-5 dark:bg-neutral-950/20 sm:p-6">
            <div x-show="globalError" x-cloak class="rounded-2xl border border-red-200 bg-red-50 px-4 py-3 text-sm font-semibold text-red-700 dark:border-red-900/60 dark:bg-red-950/25 dark:text-red-300">
                <span x-text="globalError"></span>
            </div>

            <div x-show="loadingBatch" x-cloak class="rounded-2xl border border-sky-200 bg-sky-50 px-4 py-3 text-sm font-bold text-sky-700 dark:border-sky-900/60 dark:bg-sky-950/25 dark:text-sky-300">
                Buscando si existe un lote activo...
            </div>

            <section class="grid gap-5 xl:grid-cols-[1fr_1.1fr]">
                <div class="space-y-4 rounded-[24px] border border-slate-200 bg-white p-5 shadow-sm dark:border-neutral-800 dark:bg-neutral-950">
                    <div>
                        <h3 class="font-black text-slate-900 dark:text-white">Configuración general</h3>
                        <p class="text-xs text-slate-500 dark:text-slate-400">
                            Estos ajustes se congelan cuando inicia el lote para que todos los jobs usen la misma configuración.
                        </p>
                    </div>

                    <div x-show="batch" x-cloak class="rounded-2xl border border-amber-200 bg-amber-50 px-4 py-3 text-xs font-semibold text-amber-800 dark:border-amber-900/60 dark:bg-amber-950/25 dark:text-amber-300">
                        Hay un lote activo. Para cambiar configuración, termina o elimina el lote actual y crea uno nuevo.
                    </div>

                    <?php if (isset($component)) { $__componentOriginala467913f9ff34913553be64599ec6e92 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginala467913f9ff34913553be64599ec6e92 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'e60dd9d2c3a62d619c9acb38f20d5aa5::select.index','data' => ['wire:model.live' => 'marco','label' => 'Marco general','placeholder' => 'Procesar sin marco','xBind:disabled' => 'Boolean(batch)']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('flux::select'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['wire:model.live' => 'marco','label' => 'Marco general','placeholder' => 'Procesar sin marco','x-bind:disabled' => 'Boolean(batch)']); ?>
                        <?php if (isset($component)) { $__componentOriginalc7395a5f1f6c2e275d1dc4ea0be0c745 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginalc7395a5f1f6c2e275d1dc4ea0be0c745 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'e60dd9d2c3a62d619c9acb38f20d5aa5::select.option.index','data' => ['value' => '']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('flux::select.option'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['value' => '']); ?>Sin marco <?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginalc7395a5f1f6c2e275d1dc4ea0be0c745)): ?>
<?php $attributes = $__attributesOriginalc7395a5f1f6c2e275d1dc4ea0be0c745; ?>
<?php unset($__attributesOriginalc7395a5f1f6c2e275d1dc4ea0be0c745); ?>
<?php endif; ?>
<?php if (isset($__componentOriginalc7395a5f1f6c2e275d1dc4ea0be0c745)): ?>
<?php $component = $__componentOriginalc7395a5f1f6c2e275d1dc4ea0be0c745; ?>
<?php unset($__componentOriginalc7395a5f1f6c2e275d1dc4ea0be0c745); ?>
<?php endif; ?>
                        <!--[if BLOCK]><![endif]--><?php $__currentLoopData = $marcos; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $item): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                            <?php if (isset($component)) { $__componentOriginalc7395a5f1f6c2e275d1dc4ea0be0c745 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginalc7395a5f1f6c2e275d1dc4ea0be0c745 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'e60dd9d2c3a62d619c9acb38f20d5aa5::select.option.index','data' => ['value' => ''.e($item->id).'']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('flux::select.option'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['value' => ''.e($item->id).'']); ?>
                                <?php echo e($item->nombre ?: $item->descripcion); ?>

                                <?php echo e($item->completo ? '· Desktop + Móvil' : '· Incompleto'); ?>

                             <?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginalc7395a5f1f6c2e275d1dc4ea0be0c745)): ?>
<?php $attributes = $__attributesOriginalc7395a5f1f6c2e275d1dc4ea0be0c745; ?>
<?php unset($__attributesOriginalc7395a5f1f6c2e275d1dc4ea0be0c745); ?>
<?php endif; ?>
<?php if (isset($__componentOriginalc7395a5f1f6c2e275d1dc4ea0be0c745)): ?>
<?php $component = $__componentOriginalc7395a5f1f6c2e275d1dc4ea0be0c745; ?>
<?php unset($__componentOriginalc7395a5f1f6c2e275d1dc4ea0be0c745); ?>
<?php endif; ?>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><!--[if ENDBLOCK]><![endif]-->
                     <?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginala467913f9ff34913553be64599ec6e92)): ?>
<?php $attributes = $__attributesOriginala467913f9ff34913553be64599ec6e92; ?>
<?php unset($__attributesOriginala467913f9ff34913553be64599ec6e92); ?>
<?php endif; ?>
<?php if (isset($__componentOriginala467913f9ff34913553be64599ec6e92)): ?>
<?php $component = $__componentOriginala467913f9ff34913553be64599ec6e92; ?>
<?php unset($__componentOriginala467913f9ff34913553be64599ec6e92); ?>
<?php endif; ?>

                    <!--[if BLOCK]><![endif]--><?php if($marco && ($selectedMarco = $marcos->firstWhere('id', (int) $marco))): ?>
                        <div class="grid grid-cols-2 gap-3">
                            <?php $desktopFile = $selectedMarco->marco_desktop ?: $selectedMarco->marco; ?>
                            <div class="rounded-xl border p-3 <?php echo e($desktopFile ? 'border-blue-200 bg-blue-50/60 dark:border-blue-900 dark:bg-blue-950/20' : 'border-amber-200 bg-amber-50 dark:border-amber-900 dark:bg-amber-950/20'); ?>">
                                <p class="text-[10px] font-black uppercase tracking-wide <?php echo e($desktopFile ? 'text-blue-700 dark:text-blue-300' : 'text-amber-700 dark:text-amber-300'); ?>">Desktop</p>
                                <p class="mt-1 text-xs font-semibold text-slate-700 dark:text-neutral-200"><?php echo e($desktopFile ? 'Disponible' : 'No disponible'); ?></p>
                            </div>
                            <div class="rounded-xl border p-3 <?php echo e($selectedMarco->marco_mobile ? 'border-violet-200 bg-violet-50/60 dark:border-violet-900 dark:bg-violet-950/20' : 'border-amber-200 bg-amber-50 dark:border-amber-900 dark:bg-amber-950/20'); ?>">
                                <p class="text-[10px] font-black uppercase tracking-wide <?php echo e($selectedMarco->marco_mobile ? 'text-violet-700 dark:text-violet-300' : 'text-amber-700 dark:text-amber-300'); ?>">Móvil</p>
                                <p class="mt-1 text-xs font-semibold text-slate-700 dark:text-neutral-200"><?php echo e($selectedMarco->marco_mobile ? 'Disponible' : 'No disponible'); ?></p>
                            </div>
                        </div>
                    <?php endif; ?><!--[if ENDBLOCK]><![endif]-->

                    <div class="grid gap-3 sm:grid-cols-2">
                        <?php if (isset($component)) { $__componentOriginala467913f9ff34913553be64599ec6e92 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginala467913f9ff34913553be64599ec6e92 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'e60dd9d2c3a62d619c9acb38f20d5aa5::select.index','data' => ['wire:model.live' => 'orientationMode','label' => 'Orientación del lote','xBind:disabled' => 'Boolean(batch)']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('flux::select'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['wire:model.live' => 'orientationMode','label' => 'Orientación del lote','x-bind:disabled' => 'Boolean(batch)']); ?>
                            <?php if (isset($component)) { $__componentOriginalc7395a5f1f6c2e275d1dc4ea0be0c745 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginalc7395a5f1f6c2e275d1dc4ea0be0c745 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'e60dd9d2c3a62d619c9acb38f20d5aa5::select.option.index','data' => ['value' => 'auto']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('flux::select.option'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['value' => 'auto']); ?>Automática por imagen <?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginalc7395a5f1f6c2e275d1dc4ea0be0c745)): ?>
<?php $attributes = $__attributesOriginalc7395a5f1f6c2e275d1dc4ea0be0c745; ?>
<?php unset($__attributesOriginalc7395a5f1f6c2e275d1dc4ea0be0c745); ?>
<?php endif; ?>
<?php if (isset($__componentOriginalc7395a5f1f6c2e275d1dc4ea0be0c745)): ?>
<?php $component = $__componentOriginalc7395a5f1f6c2e275d1dc4ea0be0c745; ?>
<?php unset($__componentOriginalc7395a5f1f6c2e275d1dc4ea0be0c745); ?>
<?php endif; ?>
                            <?php if (isset($component)) { $__componentOriginalc7395a5f1f6c2e275d1dc4ea0be0c745 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginalc7395a5f1f6c2e275d1dc4ea0be0c745 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'e60dd9d2c3a62d619c9acb38f20d5aa5::select.option.index','data' => ['value' => 'desktop']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('flux::select.option'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['value' => 'desktop']); ?>Forzar desktop <?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginalc7395a5f1f6c2e275d1dc4ea0be0c745)): ?>
<?php $attributes = $__attributesOriginalc7395a5f1f6c2e275d1dc4ea0be0c745; ?>
<?php unset($__attributesOriginalc7395a5f1f6c2e275d1dc4ea0be0c745); ?>
<?php endif; ?>
<?php if (isset($__componentOriginalc7395a5f1f6c2e275d1dc4ea0be0c745)): ?>
<?php $component = $__componentOriginalc7395a5f1f6c2e275d1dc4ea0be0c745; ?>
<?php unset($__componentOriginalc7395a5f1f6c2e275d1dc4ea0be0c745); ?>
<?php endif; ?>
                            <?php if (isset($component)) { $__componentOriginalc7395a5f1f6c2e275d1dc4ea0be0c745 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginalc7395a5f1f6c2e275d1dc4ea0be0c745 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'e60dd9d2c3a62d619c9acb38f20d5aa5::select.option.index','data' => ['value' => 'mobile']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('flux::select.option'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['value' => 'mobile']); ?>Forzar móvil <?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginalc7395a5f1f6c2e275d1dc4ea0be0c745)): ?>
<?php $attributes = $__attributesOriginalc7395a5f1f6c2e275d1dc4ea0be0c745; ?>
<?php unset($__attributesOriginalc7395a5f1f6c2e275d1dc4ea0be0c745); ?>
<?php endif; ?>
<?php if (isset($__componentOriginalc7395a5f1f6c2e275d1dc4ea0be0c745)): ?>
<?php $component = $__componentOriginalc7395a5f1f6c2e275d1dc4ea0be0c745; ?>
<?php unset($__componentOriginalc7395a5f1f6c2e275d1dc4ea0be0c745); ?>
<?php endif; ?>
                         <?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginala467913f9ff34913553be64599ec6e92)): ?>
<?php $attributes = $__attributesOriginala467913f9ff34913553be64599ec6e92; ?>
<?php unset($__attributesOriginala467913f9ff34913553be64599ec6e92); ?>
<?php endif; ?>
<?php if (isset($__componentOriginala467913f9ff34913553be64599ec6e92)): ?>
<?php $component = $__componentOriginala467913f9ff34913553be64599ec6e92; ?>
<?php unset($__componentOriginala467913f9ff34913553be64599ec6e92); ?>
<?php endif; ?>
                        <?php if (isset($component)) { $__componentOriginala467913f9ff34913553be64599ec6e92 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginala467913f9ff34913553be64599ec6e92 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'e60dd9d2c3a62d619c9acb38f20d5aa5::select.index','data' => ['wire:model.live' => 'squareMode','label' => 'Imágenes cuadradas','xBind:disabled' => 'Boolean(batch)']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('flux::select'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['wire:model.live' => 'squareMode','label' => 'Imágenes cuadradas','x-bind:disabled' => 'Boolean(batch)']); ?>
                            <?php if (isset($component)) { $__componentOriginalc7395a5f1f6c2e275d1dc4ea0be0c745 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginalc7395a5f1f6c2e275d1dc4ea0be0c745 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'e60dd9d2c3a62d619c9acb38f20d5aa5::select.option.index','data' => ['value' => 'desktop']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('flux::select.option'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['value' => 'desktop']); ?>Tratarlas como desktop <?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginalc7395a5f1f6c2e275d1dc4ea0be0c745)): ?>
<?php $attributes = $__attributesOriginalc7395a5f1f6c2e275d1dc4ea0be0c745; ?>
<?php unset($__attributesOriginalc7395a5f1f6c2e275d1dc4ea0be0c745); ?>
<?php endif; ?>
<?php if (isset($__componentOriginalc7395a5f1f6c2e275d1dc4ea0be0c745)): ?>
<?php $component = $__componentOriginalc7395a5f1f6c2e275d1dc4ea0be0c745; ?>
<?php unset($__componentOriginalc7395a5f1f6c2e275d1dc4ea0be0c745); ?>
<?php endif; ?>
                            <?php if (isset($component)) { $__componentOriginalc7395a5f1f6c2e275d1dc4ea0be0c745 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginalc7395a5f1f6c2e275d1dc4ea0be0c745 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'e60dd9d2c3a62d619c9acb38f20d5aa5::select.option.index','data' => ['value' => 'mobile']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('flux::select.option'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['value' => 'mobile']); ?>Tratarlas como móvil <?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginalc7395a5f1f6c2e275d1dc4ea0be0c745)): ?>
<?php $attributes = $__attributesOriginalc7395a5f1f6c2e275d1dc4ea0be0c745; ?>
<?php unset($__attributesOriginalc7395a5f1f6c2e275d1dc4ea0be0c745); ?>
<?php endif; ?>
<?php if (isset($__componentOriginalc7395a5f1f6c2e275d1dc4ea0be0c745)): ?>
<?php $component = $__componentOriginalc7395a5f1f6c2e275d1dc4ea0be0c745; ?>
<?php unset($__componentOriginalc7395a5f1f6c2e275d1dc4ea0be0c745); ?>
<?php endif; ?>
                         <?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginala467913f9ff34913553be64599ec6e92)): ?>
<?php $attributes = $__attributesOriginala467913f9ff34913553be64599ec6e92; ?>
<?php unset($__attributesOriginala467913f9ff34913553be64599ec6e92); ?>
<?php endif; ?>
<?php if (isset($__componentOriginala467913f9ff34913553be64599ec6e92)): ?>
<?php $component = $__componentOriginala467913f9ff34913553be64599ec6e92; ?>
<?php unset($__componentOriginala467913f9ff34913553be64599ec6e92); ?>
<?php endif; ?>
                    </div>

                    <?php if (isset($component)) { $__componentOriginala467913f9ff34913553be64599ec6e92 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginala467913f9ff34913553be64599ec6e92 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'e60dd9d2c3a62d619c9acb38f20d5aa5::select.index','data' => ['wire:model.live' => 'missingFrameBehavior','label' => 'Si falta una orientación del marco','xBind:disabled' => 'Boolean(batch)']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('flux::select'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['wire:model.live' => 'missingFrameBehavior','label' => 'Si falta una orientación del marco','x-bind:disabled' => 'Boolean(batch)']); ?>
                        <?php if (isset($component)) { $__componentOriginalc7395a5f1f6c2e275d1dc4ea0be0c745 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginalc7395a5f1f6c2e275d1dc4ea0be0c745 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'e60dd9d2c3a62d619c9acb38f20d5aa5::select.option.index','data' => ['value' => 'skip']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('flux::select.option'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['value' => 'skip']); ?>Procesar esa imagen sin marco <?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginalc7395a5f1f6c2e275d1dc4ea0be0c745)): ?>
<?php $attributes = $__attributesOriginalc7395a5f1f6c2e275d1dc4ea0be0c745; ?>
<?php unset($__attributesOriginalc7395a5f1f6c2e275d1dc4ea0be0c745); ?>
<?php endif; ?>
<?php if (isset($__componentOriginalc7395a5f1f6c2e275d1dc4ea0be0c745)): ?>
<?php $component = $__componentOriginalc7395a5f1f6c2e275d1dc4ea0be0c745; ?>
<?php unset($__componentOriginalc7395a5f1f6c2e275d1dc4ea0be0c745); ?>
<?php endif; ?>
                        <?php if (isset($component)) { $__componentOriginalc7395a5f1f6c2e275d1dc4ea0be0c745 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginalc7395a5f1f6c2e275d1dc4ea0be0c745 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'e60dd9d2c3a62d619c9acb38f20d5aa5::select.option.index','data' => ['value' => 'alternate']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('flux::select.option'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['value' => 'alternate']); ?>Usar y adaptar la versión alterna <?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginalc7395a5f1f6c2e275d1dc4ea0be0c745)): ?>
<?php $attributes = $__attributesOriginalc7395a5f1f6c2e275d1dc4ea0be0c745; ?>
<?php unset($__attributesOriginalc7395a5f1f6c2e275d1dc4ea0be0c745); ?>
<?php endif; ?>
<?php if (isset($__componentOriginalc7395a5f1f6c2e275d1dc4ea0be0c745)): ?>
<?php $component = $__componentOriginalc7395a5f1f6c2e275d1dc4ea0be0c745; ?>
<?php unset($__componentOriginalc7395a5f1f6c2e275d1dc4ea0be0c745); ?>
<?php endif; ?>
                     <?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginala467913f9ff34913553be64599ec6e92)): ?>
<?php $attributes = $__attributesOriginala467913f9ff34913553be64599ec6e92; ?>
<?php unset($__attributesOriginala467913f9ff34913553be64599ec6e92); ?>
<?php endif; ?>
<?php if (isset($__componentOriginala467913f9ff34913553be64599ec6e92)): ?>
<?php $component = $__componentOriginala467913f9ff34913553be64599ec6e92; ?>
<?php unset($__componentOriginala467913f9ff34913553be64599ec6e92); ?>
<?php endif; ?>
                </div>

                <div class="space-y-4 rounded-[24px] border border-slate-200 bg-white p-5 shadow-sm dark:border-neutral-800 dark:bg-neutral-950">
                    <div>
                        <h3 class="font-black text-slate-900 dark:text-white">Salida del lote</h3>
                        <p class="text-xs text-slate-500 dark:text-slate-400">Controla proporción, formato, calidad y nombres.</p>
                    </div>

                    <!--[if BLOCK]><![endif]--><?php if($presetsSociales->isNotEmpty()): ?>
                        <?php if (isset($component)) { $__componentOriginala467913f9ff34913553be64599ec6e92 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginala467913f9ff34913553be64599ec6e92 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'e60dd9d2c3a62d619c9acb38f20d5aa5::select.index','data' => ['wire:model.live' => 'presetSocialId','label' => 'Preset de red social','xBind:disabled' => 'Boolean(batch)']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('flux::select'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['wire:model.live' => 'presetSocialId','label' => 'Preset de red social','x-bind:disabled' => 'Boolean(batch)']); ?>
                            <?php if (isset($component)) { $__componentOriginalc7395a5f1f6c2e275d1dc4ea0be0c745 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginalc7395a5f1f6c2e275d1dc4ea0be0c745 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'e60dd9d2c3a62d619c9acb38f20d5aa5::select.option.index','data' => ['value' => '']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('flux::select.option'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['value' => '']); ?>Medidas personalizadas <?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginalc7395a5f1f6c2e275d1dc4ea0be0c745)): ?>
<?php $attributes = $__attributesOriginalc7395a5f1f6c2e275d1dc4ea0be0c745; ?>
<?php unset($__attributesOriginalc7395a5f1f6c2e275d1dc4ea0be0c745); ?>
<?php endif; ?>
<?php if (isset($__componentOriginalc7395a5f1f6c2e275d1dc4ea0be0c745)): ?>
<?php $component = $__componentOriginalc7395a5f1f6c2e275d1dc4ea0be0c745; ?>
<?php unset($__componentOriginalc7395a5f1f6c2e275d1dc4ea0be0c745); ?>
<?php endif; ?>
                            <!--[if BLOCK]><![endif]--><?php $__currentLoopData = $presetsSociales; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $preset): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                <?php if (isset($component)) { $__componentOriginalc7395a5f1f6c2e275d1dc4ea0be0c745 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginalc7395a5f1f6c2e275d1dc4ea0be0c745 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'e60dd9d2c3a62d619c9acb38f20d5aa5::select.option.index','data' => ['value' => ''.e($preset->id).'']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('flux::select.option'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['value' => ''.e($preset->id).'']); ?>
                                    <?php echo e($preset->red_social); ?> · <?php echo e($preset->nombre); ?> (<?php echo e($preset->ancho); ?> × <?php echo e($preset->alto); ?>)
                                 <?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginalc7395a5f1f6c2e275d1dc4ea0be0c745)): ?>
<?php $attributes = $__attributesOriginalc7395a5f1f6c2e275d1dc4ea0be0c745; ?>
<?php unset($__attributesOriginalc7395a5f1f6c2e275d1dc4ea0be0c745); ?>
<?php endif; ?>
<?php if (isset($__componentOriginalc7395a5f1f6c2e275d1dc4ea0be0c745)): ?>
<?php $component = $__componentOriginalc7395a5f1f6c2e275d1dc4ea0be0c745; ?>
<?php unset($__componentOriginalc7395a5f1f6c2e275d1dc4ea0be0c745); ?>
<?php endif; ?>
                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><!--[if ENDBLOCK]><![endif]-->
                         <?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginala467913f9ff34913553be64599ec6e92)): ?>
<?php $attributes = $__attributesOriginala467913f9ff34913553be64599ec6e92; ?>
<?php unset($__attributesOriginala467913f9ff34913553be64599ec6e92); ?>
<?php endif; ?>
<?php if (isset($__componentOriginala467913f9ff34913553be64599ec6e92)): ?>
<?php $component = $__componentOriginala467913f9ff34913553be64599ec6e92; ?>
<?php unset($__componentOriginala467913f9ff34913553be64599ec6e92); ?>
<?php endif; ?>
                    <?php endif; ?><!--[if ENDBLOCK]><![endif]-->

                    <div class="grid gap-3 sm:grid-cols-2">
                        <?php if (isset($component)) { $__componentOriginala467913f9ff34913553be64599ec6e92 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginala467913f9ff34913553be64599ec6e92 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'e60dd9d2c3a62d619c9acb38f20d5aa5::select.index','data' => ['wire:model.live' => 'fitMode','label' => 'Ajuste de imagen','xBind:disabled' => 'Boolean(batch)']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('flux::select'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['wire:model.live' => 'fitMode','label' => 'Ajuste de imagen','x-bind:disabled' => 'Boolean(batch)']); ?>
                            <?php if (isset($component)) { $__componentOriginalc7395a5f1f6c2e275d1dc4ea0be0c745 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginalc7395a5f1f6c2e275d1dc4ea0be0c745 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'e60dd9d2c3a62d619c9acb38f20d5aa5::select.option.index','data' => ['value' => 'cover']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('flux::select.option'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['value' => 'cover']); ?>Recortar para llenar <?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginalc7395a5f1f6c2e275d1dc4ea0be0c745)): ?>
<?php $attributes = $__attributesOriginalc7395a5f1f6c2e275d1dc4ea0be0c745; ?>
<?php unset($__attributesOriginalc7395a5f1f6c2e275d1dc4ea0be0c745); ?>
<?php endif; ?>
<?php if (isset($__componentOriginalc7395a5f1f6c2e275d1dc4ea0be0c745)): ?>
<?php $component = $__componentOriginalc7395a5f1f6c2e275d1dc4ea0be0c745; ?>
<?php unset($__componentOriginalc7395a5f1f6c2e275d1dc4ea0be0c745); ?>
<?php endif; ?>
                            <?php if (isset($component)) { $__componentOriginalc7395a5f1f6c2e275d1dc4ea0be0c745 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginalc7395a5f1f6c2e275d1dc4ea0be0c745 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'e60dd9d2c3a62d619c9acb38f20d5aa5::select.option.index','data' => ['value' => 'contain']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('flux::select.option'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['value' => 'contain']); ?>Mostrar completa con fondo blanco <?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginalc7395a5f1f6c2e275d1dc4ea0be0c745)): ?>
<?php $attributes = $__attributesOriginalc7395a5f1f6c2e275d1dc4ea0be0c745; ?>
<?php unset($__attributesOriginalc7395a5f1f6c2e275d1dc4ea0be0c745); ?>
<?php endif; ?>
<?php if (isset($__componentOriginalc7395a5f1f6c2e275d1dc4ea0be0c745)): ?>
<?php $component = $__componentOriginalc7395a5f1f6c2e275d1dc4ea0be0c745; ?>
<?php unset($__componentOriginalc7395a5f1f6c2e275d1dc4ea0be0c745); ?>
<?php endif; ?>
                            <?php if (isset($component)) { $__componentOriginalc7395a5f1f6c2e275d1dc4ea0be0c745 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginalc7395a5f1f6c2e275d1dc4ea0be0c745 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'e60dd9d2c3a62d619c9acb38f20d5aa5::select.option.index','data' => ['value' => 'blur']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('flux::select.option'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['value' => 'blur']); ?>Mostrar completa con fondo desenfocado <?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginalc7395a5f1f6c2e275d1dc4ea0be0c745)): ?>
<?php $attributes = $__attributesOriginalc7395a5f1f6c2e275d1dc4ea0be0c745; ?>
<?php unset($__attributesOriginalc7395a5f1f6c2e275d1dc4ea0be0c745); ?>
<?php endif; ?>
<?php if (isset($__componentOriginalc7395a5f1f6c2e275d1dc4ea0be0c745)): ?>
<?php $component = $__componentOriginalc7395a5f1f6c2e275d1dc4ea0be0c745; ?>
<?php unset($__componentOriginalc7395a5f1f6c2e275d1dc4ea0be0c745); ?>
<?php endif; ?>
                         <?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginala467913f9ff34913553be64599ec6e92)): ?>
<?php $attributes = $__attributesOriginala467913f9ff34913553be64599ec6e92; ?>
<?php unset($__attributesOriginala467913f9ff34913553be64599ec6e92); ?>
<?php endif; ?>
<?php if (isset($__componentOriginala467913f9ff34913553be64599ec6e92)): ?>
<?php $component = $__componentOriginala467913f9ff34913553be64599ec6e92; ?>
<?php unset($__componentOriginala467913f9ff34913553be64599ec6e92); ?>
<?php endif; ?>
                        <?php if (isset($component)) { $__componentOriginala467913f9ff34913553be64599ec6e92 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginala467913f9ff34913553be64599ec6e92 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'e60dd9d2c3a62d619c9acb38f20d5aa5::select.index','data' => ['wire:model.live' => 'format','label' => 'Formato de salida','xBind:disabled' => 'Boolean(batch)']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('flux::select'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['wire:model.live' => 'format','label' => 'Formato de salida','x-bind:disabled' => 'Boolean(batch)']); ?>
                            <?php if (isset($component)) { $__componentOriginalc7395a5f1f6c2e275d1dc4ea0be0c745 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginalc7395a5f1f6c2e275d1dc4ea0be0c745 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'e60dd9d2c3a62d619c9acb38f20d5aa5::select.option.index','data' => ['value' => 'jpg']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('flux::select.option'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['value' => 'jpg']); ?>JPG <?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginalc7395a5f1f6c2e275d1dc4ea0be0c745)): ?>
<?php $attributes = $__attributesOriginalc7395a5f1f6c2e275d1dc4ea0be0c745; ?>
<?php unset($__attributesOriginalc7395a5f1f6c2e275d1dc4ea0be0c745); ?>
<?php endif; ?>
<?php if (isset($__componentOriginalc7395a5f1f6c2e275d1dc4ea0be0c745)): ?>
<?php $component = $__componentOriginalc7395a5f1f6c2e275d1dc4ea0be0c745; ?>
<?php unset($__componentOriginalc7395a5f1f6c2e275d1dc4ea0be0c745); ?>
<?php endif; ?>
                            <?php if (isset($component)) { $__componentOriginalc7395a5f1f6c2e275d1dc4ea0be0c745 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginalc7395a5f1f6c2e275d1dc4ea0be0c745 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'e60dd9d2c3a62d619c9acb38f20d5aa5::select.option.index','data' => ['value' => 'png']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('flux::select.option'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['value' => 'png']); ?>PNG <?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginalc7395a5f1f6c2e275d1dc4ea0be0c745)): ?>
<?php $attributes = $__attributesOriginalc7395a5f1f6c2e275d1dc4ea0be0c745; ?>
<?php unset($__attributesOriginalc7395a5f1f6c2e275d1dc4ea0be0c745); ?>
<?php endif; ?>
<?php if (isset($__componentOriginalc7395a5f1f6c2e275d1dc4ea0be0c745)): ?>
<?php $component = $__componentOriginalc7395a5f1f6c2e275d1dc4ea0be0c745; ?>
<?php unset($__componentOriginalc7395a5f1f6c2e275d1dc4ea0be0c745); ?>
<?php endif; ?>
                            <?php if (isset($component)) { $__componentOriginalc7395a5f1f6c2e275d1dc4ea0be0c745 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginalc7395a5f1f6c2e275d1dc4ea0be0c745 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'e60dd9d2c3a62d619c9acb38f20d5aa5::select.option.index','data' => ['value' => 'webp']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('flux::select.option'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['value' => 'webp']); ?>WebP <?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginalc7395a5f1f6c2e275d1dc4ea0be0c745)): ?>
<?php $attributes = $__attributesOriginalc7395a5f1f6c2e275d1dc4ea0be0c745; ?>
<?php unset($__attributesOriginalc7395a5f1f6c2e275d1dc4ea0be0c745); ?>
<?php endif; ?>
<?php if (isset($__componentOriginalc7395a5f1f6c2e275d1dc4ea0be0c745)): ?>
<?php $component = $__componentOriginalc7395a5f1f6c2e275d1dc4ea0be0c745; ?>
<?php unset($__componentOriginalc7395a5f1f6c2e275d1dc4ea0be0c745); ?>
<?php endif; ?>
                            <?php if (isset($component)) { $__componentOriginalc7395a5f1f6c2e275d1dc4ea0be0c745 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginalc7395a5f1f6c2e275d1dc4ea0be0c745 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'e60dd9d2c3a62d619c9acb38f20d5aa5::select.option.index','data' => ['value' => 'original']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('flux::select.option'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['value' => 'original']); ?>Conservar formato compatible <?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginalc7395a5f1f6c2e275d1dc4ea0be0c745)): ?>
<?php $attributes = $__attributesOriginalc7395a5f1f6c2e275d1dc4ea0be0c745; ?>
<?php unset($__attributesOriginalc7395a5f1f6c2e275d1dc4ea0be0c745); ?>
<?php endif; ?>
<?php if (isset($__componentOriginalc7395a5f1f6c2e275d1dc4ea0be0c745)): ?>
<?php $component = $__componentOriginalc7395a5f1f6c2e275d1dc4ea0be0c745; ?>
<?php unset($__componentOriginalc7395a5f1f6c2e275d1dc4ea0be0c745); ?>
<?php endif; ?>
                         <?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginala467913f9ff34913553be64599ec6e92)): ?>
<?php $attributes = $__attributesOriginala467913f9ff34913553be64599ec6e92; ?>
<?php unset($__attributesOriginala467913f9ff34913553be64599ec6e92); ?>
<?php endif; ?>
<?php if (isset($__componentOriginala467913f9ff34913553be64599ec6e92)): ?>
<?php $component = $__componentOriginala467913f9ff34913553be64599ec6e92; ?>
<?php unset($__componentOriginala467913f9ff34913553be64599ec6e92); ?>
<?php endif; ?>
                    </div>

                    <div>
                        <div class="mb-2 flex items-center justify-between text-xs font-semibold text-slate-600 dark:text-neutral-300">
                            <span>Calidad de salida</span><span><?php echo e($quality); ?>%</span>
                        </div>
                        <?php if (isset($component)) { $__componentOriginal26c546557cdc09040c8dd00b2090afd0 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal26c546557cdc09040c8dd00b2090afd0 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'e60dd9d2c3a62d619c9acb38f20d5aa5::input.index','data' => ['type' => 'range','wire:model.live' => 'quality','min' => '60','max' => '100','step' => '1','class' => 'w-full accent-blue-600','xBind:disabled' => 'Boolean(batch)']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('flux::input'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['type' => 'range','wire:model.live' => 'quality','min' => '60','max' => '100','step' => '1','class' => 'w-full accent-blue-600','x-bind:disabled' => 'Boolean(batch)']); ?>
<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal26c546557cdc09040c8dd00b2090afd0)): ?>
<?php $attributes = $__attributesOriginal26c546557cdc09040c8dd00b2090afd0; ?>
<?php unset($__attributesOriginal26c546557cdc09040c8dd00b2090afd0); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal26c546557cdc09040c8dd00b2090afd0)): ?>
<?php $component = $__componentOriginal26c546557cdc09040c8dd00b2090afd0; ?>
<?php unset($__componentOriginal26c546557cdc09040c8dd00b2090afd0); ?>
<?php endif; ?>
                    </div>

                    <?php if (isset($component)) { $__componentOriginal26c546557cdc09040c8dd00b2090afd0 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal26c546557cdc09040c8dd00b2090afd0 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'e60dd9d2c3a62d619c9acb38f20d5aa5::input.index','data' => ['wire:model' => 'renamePattern','label' => 'Patrón de nombre','placeholder' => '{orig}_{index}','description' => 'Variables: {orig}, {index}, {date}, {orientation}.','xBind:disabled' => 'Boolean(batch)']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('flux::input'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['wire:model' => 'renamePattern','label' => 'Patrón de nombre','placeholder' => '{orig}_{index}','description' => 'Variables: {orig}, {index}, {date}, {orientation}.','x-bind:disabled' => 'Boolean(batch)']); ?>
<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal26c546557cdc09040c8dd00b2090afd0)): ?>
<?php $attributes = $__attributesOriginal26c546557cdc09040c8dd00b2090afd0; ?>
<?php unset($__attributesOriginal26c546557cdc09040c8dd00b2090afd0); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal26c546557cdc09040c8dd00b2090afd0)): ?>
<?php $component = $__componentOriginal26c546557cdc09040c8dd00b2090afd0; ?>
<?php unset($__componentOriginal26c546557cdc09040c8dd00b2090afd0); ?>
<?php endif; ?>

                    <div class="grid grid-cols-2 gap-3">
                        <?php if (isset($component)) { $__componentOriginal26c546557cdc09040c8dd00b2090afd0 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal26c546557cdc09040c8dd00b2090afd0 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'e60dd9d2c3a62d619c9acb38f20d5aa5::input.index','data' => ['wire:model' => 'desktopWidth','type' => 'number','label' => 'Desktop ancho','xBind:disabled' => 'Boolean(batch)']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('flux::input'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['wire:model' => 'desktopWidth','type' => 'number','label' => 'Desktop ancho','x-bind:disabled' => 'Boolean(batch)']); ?>
<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal26c546557cdc09040c8dd00b2090afd0)): ?>
<?php $attributes = $__attributesOriginal26c546557cdc09040c8dd00b2090afd0; ?>
<?php unset($__attributesOriginal26c546557cdc09040c8dd00b2090afd0); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal26c546557cdc09040c8dd00b2090afd0)): ?>
<?php $component = $__componentOriginal26c546557cdc09040c8dd00b2090afd0; ?>
<?php unset($__componentOriginal26c546557cdc09040c8dd00b2090afd0); ?>
<?php endif; ?>
                        <?php if (isset($component)) { $__componentOriginal26c546557cdc09040c8dd00b2090afd0 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal26c546557cdc09040c8dd00b2090afd0 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'e60dd9d2c3a62d619c9acb38f20d5aa5::input.index','data' => ['wire:model' => 'desktopHeight','type' => 'number','label' => 'Desktop alto','xBind:disabled' => 'Boolean(batch)']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('flux::input'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['wire:model' => 'desktopHeight','type' => 'number','label' => 'Desktop alto','x-bind:disabled' => 'Boolean(batch)']); ?>
<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal26c546557cdc09040c8dd00b2090afd0)): ?>
<?php $attributes = $__attributesOriginal26c546557cdc09040c8dd00b2090afd0; ?>
<?php unset($__attributesOriginal26c546557cdc09040c8dd00b2090afd0); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal26c546557cdc09040c8dd00b2090afd0)): ?>
<?php $component = $__componentOriginal26c546557cdc09040c8dd00b2090afd0; ?>
<?php unset($__componentOriginal26c546557cdc09040c8dd00b2090afd0); ?>
<?php endif; ?>
                        <?php if (isset($component)) { $__componentOriginal26c546557cdc09040c8dd00b2090afd0 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal26c546557cdc09040c8dd00b2090afd0 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'e60dd9d2c3a62d619c9acb38f20d5aa5::input.index','data' => ['wire:model' => 'mobileWidth','type' => 'number','label' => 'Móvil ancho','xBind:disabled' => 'Boolean(batch)']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('flux::input'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['wire:model' => 'mobileWidth','type' => 'number','label' => 'Móvil ancho','x-bind:disabled' => 'Boolean(batch)']); ?>
<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal26c546557cdc09040c8dd00b2090afd0)): ?>
<?php $attributes = $__attributesOriginal26c546557cdc09040c8dd00b2090afd0; ?>
<?php unset($__attributesOriginal26c546557cdc09040c8dd00b2090afd0); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal26c546557cdc09040c8dd00b2090afd0)): ?>
<?php $component = $__componentOriginal26c546557cdc09040c8dd00b2090afd0; ?>
<?php unset($__componentOriginal26c546557cdc09040c8dd00b2090afd0); ?>
<?php endif; ?>
                        <?php if (isset($component)) { $__componentOriginal26c546557cdc09040c8dd00b2090afd0 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal26c546557cdc09040c8dd00b2090afd0 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'e60dd9d2c3a62d619c9acb38f20d5aa5::input.index','data' => ['wire:model' => 'mobileHeight','type' => 'number','label' => 'Móvil alto','xBind:disabled' => 'Boolean(batch)']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('flux::input'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['wire:model' => 'mobileHeight','type' => 'number','label' => 'Móvil alto','x-bind:disabled' => 'Boolean(batch)']); ?>
<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal26c546557cdc09040c8dd00b2090afd0)): ?>
<?php $attributes = $__attributesOriginal26c546557cdc09040c8dd00b2090afd0; ?>
<?php unset($__attributesOriginal26c546557cdc09040c8dd00b2090afd0); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal26c546557cdc09040c8dd00b2090afd0)): ?>
<?php $component = $__componentOriginal26c546557cdc09040c8dd00b2090afd0; ?>
<?php unset($__componentOriginal26c546557cdc09040c8dd00b2090afd0); ?>
<?php endif; ?>
                    </div>

                    <label class="flex cursor-pointer items-center gap-3 rounded-xl border border-slate-200 p-3 dark:border-neutral-800">
                        <?php if (isset($component)) { $__componentOriginal9384bd05e996fcc8c16dc84e6bbc1c8f = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal9384bd05e996fcc8c16dc84e6bbc1c8f = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'e60dd9d2c3a62d619c9acb38f20d5aa5::checkbox.index','data' => ['wire:model' => 'organizeFolders','class' => 'h-5 w-5 rounded border-neutral-300 text-blue-600','xBind:disabled' => 'Boolean(batch)']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('flux::checkbox'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['wire:model' => 'organizeFolders','class' => 'h-5 w-5 rounded border-neutral-300 text-blue-600','x-bind:disabled' => 'Boolean(batch)']); ?>
<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal9384bd05e996fcc8c16dc84e6bbc1c8f)): ?>
<?php $attributes = $__attributesOriginal9384bd05e996fcc8c16dc84e6bbc1c8f; ?>
<?php unset($__attributesOriginal9384bd05e996fcc8c16dc84e6bbc1c8f); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal9384bd05e996fcc8c16dc84e6bbc1c8f)): ?>
<?php $component = $__componentOriginal9384bd05e996fcc8c16dc84e6bbc1c8f; ?>
<?php unset($__componentOriginal9384bd05e996fcc8c16dc84e6bbc1c8f); ?>
<?php endif; ?>
                        <span>
                            <span class="block text-sm font-bold text-slate-800 dark:text-white">Organizar el ZIP por orientación</span>
                            <span class="block text-xs text-slate-500">Crea carpetas desktop/ y mobile/ dentro del ZIP.</span>
                        </span>
                    </label>
                </div>
            </section>

            <section class="rounded-[26px] border border-slate-200 bg-white p-5 shadow-sm dark:border-neutral-800 dark:bg-neutral-950">
                <div class="grid gap-5 xl:grid-cols-[1.1fr_.9fr]">
                    <div
                        @dragenter.prevent="dragActive=true"
                        @dragover.prevent="dragActive=true"
                        @dragleave.prevent="dragActive=false"
                        @drop.prevent="dropFiles($event)"
                        :class="dragActive
                            ? 'border-blue-500 bg-gradient-to-br from-blue-50 to-indigo-50 shadow-lg shadow-blue-100/60 dark:from-blue-950/30 dark:to-indigo-950/20'
                            : 'border-slate-300 bg-gradient-to-br from-slate-50 to-white dark:border-neutral-700 dark:from-neutral-950/50 dark:to-neutral-900/40'"
                        class="relative overflow-hidden rounded-[26px] border-2 border-dashed px-6 py-12 text-center transition-all duration-200"
                    >
                        <input x-ref="fileInput" id="system-images-upload" type="file" multiple accept="image/jpeg,image/png,image/webp" class="sr-only" @change="selectFiles($event.target.files)" />
                        <input x-ref="folderInput" id="system-images-folder-upload" type="file" multiple webkitdirectory directory accept="image/jpeg,image/png,image/webp" class="sr-only" @change="selectFiles($event.target.files)" />

                        <div class="relative mx-auto flex h-20 w-20 items-center justify-center rounded-3xl bg-white shadow-[0_12px_30px_-12px_rgba(37,99,235,0.45)] ring-1 ring-blue-100 dark:bg-neutral-900 dark:ring-neutral-800">
                            <span class="text-4xl font-black text-blue-600">⇧</span>
                        </div>

                        <h3 class="relative mt-6 text-2xl font-black tracking-tight text-slate-900 dark:text-white">Arrastra aquí tus imágenes</h3>
                        <p class="relative mx-auto mt-3 max-w-2xl text-sm leading-6 text-slate-500 dark:text-slate-400">
                            Puedes mezclar archivos horizontales, verticales y cuadrados. Para lotes grandes, selecciona una carpeta completa; el sistema descargará por partes.
                        </p>

                        <div class="relative mt-6 flex flex-col items-center gap-3 sm:flex-row sm:justify-center">
                            <button type="button" @click="$refs.fileInput.click()" class="inline-flex items-center justify-center rounded-2xl bg-gradient-to-r from-blue-600 to-indigo-600 px-6 py-3 text-sm font-black text-white shadow-[0_12px_30px_-12px_rgba(37,99,235,0.7)] transition hover:scale-[1.02] hover:from-blue-700 hover:to-indigo-700">
                                Elegir imágenes
                            </button>
                            <button type="button" @click="$refs.folderInput.click()" class="inline-flex items-center justify-center rounded-2xl border border-blue-200 bg-white px-6 py-3 text-sm font-black text-blue-700 shadow-sm transition hover:bg-blue-50 dark:border-blue-900/60 dark:bg-neutral-900 dark:text-blue-300 dark:hover:bg-blue-950/30">
                                Elegir carpeta
                            </button>
                            <span class="rounded-2xl border border-slate-200 bg-white px-4 py-3 text-xs font-semibold text-slate-500 shadow-sm dark:border-neutral-800 dark:bg-neutral-900 dark:text-slate-400">
                                <?php echo e($maxFileMb); ?> MB por archivo
                            </span>
                        </div>

                        <p class="relative mt-4 text-xs text-slate-400 dark:text-slate-500">Lote <?php echo e($maxFilesLabel); ?> · <?php echo e($uploadConcurrency); ?> subida(s) simultánea(s) · ZIPs de hasta <?php echo e($zipPartMaxFiles); ?> imágenes o <?php echo e($zipPartMaxMb); ?> MB</p>
                    </div>

                    <div class="rounded-[24px] border border-slate-200 bg-slate-50/70 p-5 dark:border-neutral-800 dark:bg-neutral-900/60">
                        <div class="flex items-center justify-between gap-3">
                            <div>
                                <h3 class="font-black text-slate-900 dark:text-white">Avance del lote</h3>
                                <p class="text-xs text-slate-500 dark:text-slate-400" x-text="batch ? batchStatusText(batch.status) : 'Sin lote activo'"></p>
                            </div>
                            <span x-show="batch" x-cloak class="rounded-full border px-3 py-1 text-[11px] font-black" :class="statusClasses(batch?.status)">
                                <span x-text="batchStatusText(batch?.status)"></span>
                            </span>
                        </div>

                        <div class="mt-5 grid grid-cols-2 gap-3">
                            <div class="rounded-2xl border border-white bg-white p-4 shadow-sm dark:border-neutral-800 dark:bg-neutral-950">
                                <p class="text-[10px] font-black uppercase tracking-wide text-slate-400">Archivos</p>
                                <p class="mt-1 text-2xl font-black text-slate-900 dark:text-white"><span x-text="batch?.total_files ?? 0"></span></p>
                            </div>
                            <div class="rounded-2xl border border-white bg-white p-4 shadow-sm dark:border-neutral-800 dark:bg-neutral-950">
                                <p class="text-[10px] font-black uppercase tracking-wide text-slate-400">Completadas</p>
                                <p class="mt-1 text-2xl font-black text-emerald-600"><span x-text="batch?.completed_files ?? 0"></span></p>
                            </div>
                            <div class="rounded-2xl border border-white bg-white p-4 shadow-sm dark:border-neutral-800 dark:bg-neutral-950">
                                <p class="text-[10px] font-black uppercase tracking-wide text-slate-400">Con error</p>
                                <p class="mt-1 text-2xl font-black text-red-600"><span x-text="batch?.failed_files ?? 0"></span></p>
                            </div>
                            <div class="rounded-2xl border border-white bg-white p-4 shadow-sm dark:border-neutral-800 dark:bg-neutral-950">
                                <p class="text-[10px] font-black uppercase tracking-wide text-slate-400">Reducción aprox.</p>
                                <p class="mt-1 text-2xl font-black text-blue-600"><span x-text="`${batch?.total_reduction ?? 0}%`"></span></p>
                            </div>
                        </div>

                        <div class="mt-5 space-y-4">
                            <div>
                                <div class="mb-2 flex justify-between text-xs font-bold text-slate-600 dark:text-neutral-300">
                                    <span>Subida</span><span x-text="`${uploadProgress}%`"></span>
                                </div>
                                <div class="h-3 overflow-hidden rounded-full bg-slate-200 dark:bg-neutral-800">
                                    <div class="h-full rounded-full bg-gradient-to-r from-sky-500 via-blue-600 to-indigo-600 transition-all duration-300" :style="`width:${uploadProgress}%`"></div>
                                </div>
                            </div>

                            <div>
                                <div class="mb-2 flex justify-between text-xs font-bold text-slate-600 dark:text-neutral-300">
                                    <span>Procesamiento en cola</span><span x-text="`${processingProgress}%`"></span>
                                </div>
                                <div class="h-3 overflow-hidden rounded-full bg-slate-200 dark:bg-neutral-800">
                                    <div class="h-full rounded-full bg-gradient-to-r from-emerald-500 via-teal-500 to-cyan-500 transition-all duration-300" :style="`width:${processingProgress}%`"></div>
                                </div>
                            </div>
                        </div>

                        <div class="mt-5 flex flex-col gap-2 sm:flex-row">
                            <template x-if="(batch?.download_parts ?? []).length === 1">
                                <a :href="batch.download_parts[0].url" class="inline-flex flex-1 items-center justify-center rounded-2xl bg-emerald-600 px-4 py-3 text-sm font-black text-white shadow-sm transition hover:bg-emerald-700">
                                    Descargar ZIP
                                </a>
                            </template>
                            <button type="button" @click="newBatch()" class="inline-flex flex-1 items-center justify-center rounded-2xl border border-slate-200 bg-white px-4 py-3 text-sm font-black text-slate-700 shadow-sm transition hover:bg-slate-50 dark:border-neutral-800 dark:bg-neutral-950 dark:text-neutral-200 dark:hover:bg-neutral-900">
                                Nuevo lote
                            </button>
                        </div>

                        <div
                            x-show="(batch?.download_parts ?? []).length > 1"
                            x-cloak
                            class="mt-4 rounded-2xl border border-emerald-200 bg-emerald-50 p-3 dark:border-emerald-900/60 dark:bg-emerald-950/25"
                        >
                            <p class="text-xs font-black text-emerald-900 dark:text-emerald-200">Descargas por partes</p>
                            <p class="mt-1 text-[11px] leading-5 text-emerald-700 dark:text-emerald-300">
                                Se generan enlaces pequeños para evitar un ZIP demasiado pesado.
                            </p>
                            <div class="mt-3 flex flex-wrap gap-2">
                                <template x-for="part in (batch?.download_parts ?? [])" :key="part.number">
                                    <a :href="part.url" class="inline-flex items-center justify-center rounded-xl bg-emerald-600 px-3 py-2 text-[11px] font-black text-white transition hover:bg-emerald-700">
                                        <span x-text="`${part.label} · ${part.file_count} img · ${formatBytes(part.bytes)}`"></span>
                                    </a>
                                </template>
                            </div>
                        </div>

                        <p class="mt-4 text-[11px] leading-5 text-slate-500 dark:text-slate-400">
                            Los archivos temporales se conservan <?php echo e($retentionHours); ?> horas. Mantén abierto el worker de cola para procesar lotes grandes.
                        </p>
                    </div>
                </div>
            </section>

            <section x-show="batch" x-cloak class="rounded-[26px] border border-slate-200 bg-white p-5 shadow-sm dark:border-neutral-800 dark:bg-neutral-950">
                <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
                    <div>
                        <h3 class="font-black text-slate-900 dark:text-white">Archivos del lote</h3>
                        <p class="text-xs text-slate-500 dark:text-slate-400">
                            Puedes reintentar subidas fallidas o reprocesar imágenes que hayan fallado en la cola.
                        </p>
                    </div>
                    <div class="text-xs font-bold text-slate-500 dark:text-slate-400">
                        <span x-text="formatBytes(batch?.bytes_uploaded ?? 0)"></span> / <span x-text="formatBytes(batch?.bytes_total ?? 0)"></span>
                    </div>
                </div>

                <template x-if="pendingUploadCount > 0 && !busy">
                    <div class="mt-4 rounded-2xl border border-sky-200 bg-sky-50 px-4 py-3 text-sm font-semibold text-sky-700 dark:border-sky-900/60 dark:bg-sky-950/25 dark:text-sky-300">
                        Hay <span x-text="pendingUploadCount"></span> archivo(s) pendientes de subir. Vuelve a seleccionar los mismos archivos o la misma carpeta para continuar el lote.
                    </div>
                </template>

                <div class="mt-5 max-h-[680px] space-y-3 overflow-y-auto pr-1">
                    <template x-for="item in (batch?.items ?? [])" :key="item.uuid">
                        <article class="grid gap-4 rounded-[22px] border border-slate-200 bg-slate-50/70 p-4 dark:border-neutral-800 dark:bg-neutral-900/60 lg:grid-cols-[96px_1fr_auto]">
                            <div class="relative h-24 overflow-hidden rounded-2xl bg-slate-200 dark:bg-neutral-800">
                                <template x-if="item.preview_output_url || item.preview_original_url">
                                    <img :src="item.preview_output_url || item.preview_original_url" class="h-full w-full object-cover" :alt="item.original_name">
                                </template>
                                <span class="absolute bottom-2 left-2 rounded-full bg-black/65 px-2 py-1 text-[10px] font-black text-white" x-text="`#${item.position}`"></span>
                            </div>

                            <div class="min-w-0 space-y-2">
                                <div class="flex flex-col gap-1 sm:flex-row sm:items-start sm:justify-between">
                                    <div class="min-w-0">
                                        <p class="truncate text-sm font-black text-slate-900 dark:text-white" x-text="item.original_name"></p>
                                        <p class="text-[11px] text-slate-500 dark:text-slate-400">
                                            <span x-text="item.original_width && item.original_height ? `${item.original_width} × ${item.original_height}px` : 'Dimensiones pendientes'"></span>
                                            · <span x-text="formatBytes(item.original_size)"></span>
                                            <template x-if="item.width && item.height">
                                                <span> → <span x-text="`${item.width} × ${item.height}px`"></span></span>
                                            </template>
                                        </p>
                                    </div>
                                    <span class="inline-flex w-max rounded-full border px-3 py-1 text-[11px] font-black" :class="statusClasses(visibleStatus(item))" x-text="statusText(visibleStatus(item))"></span>
                                </div>

                                <div class="h-2 overflow-hidden rounded-full bg-slate-200 dark:bg-neutral-800">
                                    <div class="h-full rounded-full bg-gradient-to-r from-sky-500 to-blue-600 transition-all duration-300" :style="`width:${visibleProgress(item)}%`"></div>
                                </div>

                                <template x-if="item.error">
                                    <p class="rounded-xl border border-red-200 bg-red-50 px-3 py-2 text-xs font-semibold text-red-700 dark:border-red-900/60 dark:bg-red-950/25 dark:text-red-300" x-text="item.error"></p>
                                </template>

                                <template x-if="(item.warnings ?? []).length">
                                    <p class="rounded-xl border border-amber-200 bg-amber-50 px-3 py-2 text-xs font-semibold text-amber-800 dark:border-amber-900/60 dark:bg-amber-950/25 dark:text-amber-300" x-text="item.warnings.join(' ')"></p>
                                </template>
                            </div>

                            <div class="flex flex-row flex-wrap items-center gap-2 lg:flex-col lg:items-stretch">
                                <a x-show="item.download_url" x-cloak :href="item.download_url" class="inline-flex items-center justify-center rounded-xl bg-blue-600 px-3 py-2 text-xs font-black text-white transition hover:bg-blue-700">
                                    Descargar
                                </a>
                                <button x-show="['upload_failed', 'upload_error'].includes(visibleStatus(item))" x-cloak type="button" @click="retryLocalUpload(item)" class="inline-flex items-center justify-center rounded-xl border border-sky-200 bg-white px-3 py-2 text-xs font-black text-sky-700 transition hover:bg-sky-50 dark:border-sky-900/60 dark:bg-neutral-950 dark:text-sky-300">
                                    Reintentar subida
                                </button>
                                <button x-show="visibleStatus(item) === 'failed'" x-cloak type="button" @click="retryProcessing(item)" class="inline-flex items-center justify-center rounded-xl border border-violet-200 bg-white px-3 py-2 text-xs font-black text-violet-700 transition hover:bg-violet-50 dark:border-violet-900/60 dark:bg-neutral-950 dark:text-violet-300">
                                    Reprocesar
                                </button>
                            </div>
                        </article>
                    </template>
                </div>
            </section>
        </div>
    </section>
</div>
<?php /**PATH C:\laragon\www\minisystems\resources\views/livewire/images/creacion-imagenes.blade.php ENDPATH**/ ?>