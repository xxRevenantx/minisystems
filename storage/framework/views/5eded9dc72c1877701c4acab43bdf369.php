<div
    x-data="{
        previewOpen: false,
        previewUrl: '',
        previewTitle: '',
        confirmDelete(id, name) {
            Swal.fire({
                title: '¿Enviar a la papelera?',
                text: `${name}. El marco dejará de estar disponible para procesar imágenes.`,
                icon: 'warning',
                showCancelButton: true,
                confirmButtonText: 'Sí, eliminar',
                cancelButtonText: 'Cancelar',
                confirmButtonColor: '#dc2626',
            }).then(result => {
                if (result.isConfirmed) $wire.eliminarMarco(id)
            })
        },
        confirmForceDelete(id, name) {
            Swal.fire({
                title: '¿Eliminar definitivamente?',
                text: `${name}. También se eliminarán sus archivos. Esta acción no se puede deshacer.`,
                icon: 'error',
                showCancelButton: true,
                confirmButtonText: 'Eliminar definitivamente',
                cancelButtonText: 'Cancelar',
                confirmButtonColor: '#b91c1c',
            }).then(result => {
                if (result.isConfirmed) $wire.eliminarDefinitivamente(id)
            })
        },
        confirmRestore(id, name) {
            Swal.fire({
                title: 'Restaurar marco',
                text: name,
                icon: 'question',
                showCancelButton: true,
                confirmButtonText: 'Restaurar',
                cancelButtonText: 'Cancelar',
                confirmButtonColor: '#16a34a',
            }).then(result => {
                if (result.isConfirmed) $wire.restaurarMarco(id)
            })
        },
        confirmToggle(id, active, name) {
            const action = active ? 'desactivar' : 'activar'
            Swal.fire({
                title: `¿${action.charAt(0).toUpperCase() + action.slice(1)} marco?`,
                text: name,
                icon: 'question',
                showCancelButton: true,
                confirmButtonText: `Sí, ${action}`,
                cancelButtonText: 'Cancelar',
                confirmButtonColor: active ? '#d97706' : '#16a34a',
            }).then(result => {
                if (result.isConfirmed) $wire.alternarEstado(id)
            })
        },
        confirmDuplicate(id, name) {
            Swal.fire({
                title: 'Duplicar marco',
                text: `Se creará una copia inactiva de ${name}.`,
                icon: 'info',
                showCancelButton: true,
                confirmButtonText: 'Duplicar',
                cancelButtonText: 'Cancelar',
                confirmButtonColor: '#2563eb',
            }).then(result => {
                if (result.isConfirmed) $wire.duplicarMarco(id)
            })
        },
        openPreview(url, title) {
            this.previewUrl = url
            this.previewTitle = title
            this.previewOpen = true
        }
    }"
    x-on:marco-form-focus.window="$nextTick(() => document.getElementById('marco-formulario')?.scrollIntoView({ behavior: 'smooth', block: 'start' }))"
    x-on:keydown.escape.window="previewOpen = false"
    class="space-y-6"
>
    
    <section class="grid grid-cols-2 gap-3 lg:grid-cols-4">
        <div class="rounded-2xl border border-neutral-200 bg-white p-4 shadow-sm dark:border-neutral-800 dark:bg-neutral-900">
            <p class="text-xs font-semibold uppercase tracking-wide text-neutral-500">Total</p>
            <div class="mt-2 flex items-end justify-between">
                <span class="text-3xl font-black text-neutral-900 dark:text-white"><?php echo e($estadisticas['total']); ?></span>
                <span class="rounded-xl bg-blue-50 p-2 text-blue-600 dark:bg-blue-500/10 dark:text-blue-300">▣</span>
            </div>
        </div>
        <div class="rounded-2xl border border-neutral-200 bg-white p-4 shadow-sm dark:border-neutral-800 dark:bg-neutral-900">
            <p class="text-xs font-semibold uppercase tracking-wide text-neutral-500">Activos</p>
            <div class="mt-2 flex items-end justify-between">
                <span class="text-3xl font-black text-neutral-900 dark:text-white"><?php echo e($estadisticas['activos']); ?></span>
                <span class="rounded-xl bg-emerald-50 p-2 text-emerald-600 dark:bg-emerald-500/10 dark:text-emerald-300">✓</span>
            </div>
        </div>
        <div class="rounded-2xl border border-neutral-200 bg-white p-4 shadow-sm dark:border-neutral-800 dark:bg-neutral-900">
            <p class="text-xs font-semibold uppercase tracking-wide text-neutral-500">Completos</p>
            <div class="mt-2 flex items-end justify-between">
                <span class="text-3xl font-black text-neutral-900 dark:text-white"><?php echo e($estadisticas['completos']); ?></span>
                <span class="rounded-xl bg-violet-50 p-2 text-violet-600 dark:bg-violet-500/10 dark:text-violet-300">↔</span>
            </div>
        </div>
        <div class="rounded-2xl border border-neutral-200 bg-white p-4 shadow-sm dark:border-neutral-800 dark:bg-neutral-900">
            <p class="text-xs font-semibold uppercase tracking-wide text-neutral-500">Incompletos</p>
            <div class="mt-2 flex items-end justify-between">
                <span class="text-3xl font-black text-neutral-900 dark:text-white"><?php echo e($estadisticas['incompletos']); ?></span>
                <span class="rounded-xl bg-amber-50 p-2 text-amber-600 dark:bg-amber-500/10 dark:text-amber-300">!</span>
            </div>
        </div>
    </section>

    
    <section id="marco-formulario" class="overflow-hidden rounded-2xl border border-neutral-200 bg-white shadow-sm dark:border-neutral-800 dark:bg-neutral-900">
        <div class="flex flex-col gap-3 border-b border-neutral-200 bg-gradient-to-r from-slate-950 via-blue-950 to-indigo-950 px-5 py-5 text-white sm:flex-row sm:items-center sm:justify-between dark:border-neutral-800">
            <div>
                <div class="mb-1 flex items-center gap-2">
                    <span class="rounded-full bg-white/10 px-2.5 py-1 text-[11px] font-bold uppercase tracking-wider">
                        <?php echo e($editandoId ? 'Editando' : 'Nuevo marco'); ?>

                    </span>
                    <!--[if BLOCK]><![endif]--><?php if($editandoId): ?>
                        <span class="text-xs text-blue-200">ID #<?php echo e($editandoId); ?></span>
                    <?php endif; ?><!--[if ENDBLOCK]><![endif]-->
                </div>
                <h2 class="text-xl font-black"><?php echo e($editandoId ? 'Actualizar marco adaptable' : 'Crear marco adaptable'); ?></h2>
                <p class="mt-1 max-w-2xl text-sm text-blue-100/80">
                    Sube una versión horizontal y otra vertical. Puedes guardar el marco con una sola orientación y completarlo después.
                </p>
            </div>
            <!--[if BLOCK]><![endif]--><?php if($editandoId): ?>
                <?php if (isset($component)) { $__componentOriginalc04b147acd0e65cc1a77f86fb0e81580 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginalc04b147acd0e65cc1a77f86fb0e81580 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'e60dd9d2c3a62d619c9acb38f20d5aa5::button.index','data' => ['type' => 'button','wire:click' => 'cancelarEdicion','class' => 'inline-flex items-center justify-center rounded-xl border border-white/20 bg-white/10 px-4 py-2 text-sm font-semibold text-white transition hover:bg-white/20']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('flux::button'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['type' => 'button','wire:click' => 'cancelarEdicion','class' => 'inline-flex items-center justify-center rounded-xl border border-white/20 bg-white/10 px-4 py-2 text-sm font-semibold text-white transition hover:bg-white/20']); ?>
                    Cancelar edición
                 <?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginalc04b147acd0e65cc1a77f86fb0e81580)): ?>
<?php $attributes = $__attributesOriginalc04b147acd0e65cc1a77f86fb0e81580; ?>
<?php unset($__attributesOriginalc04b147acd0e65cc1a77f86fb0e81580); ?>
<?php endif; ?>
<?php if (isset($__componentOriginalc04b147acd0e65cc1a77f86fb0e81580)): ?>
<?php $component = $__componentOriginalc04b147acd0e65cc1a77f86fb0e81580; ?>
<?php unset($__componentOriginalc04b147acd0e65cc1a77f86fb0e81580); ?>
<?php endif; ?>
            <?php endif; ?><!--[if ENDBLOCK]><![endif]-->
        </div>

        <form wire:submit="guardarMarco" class="space-y-6 p-5">
            <div class="grid gap-5 lg:grid-cols-2">
                
                <div class="rounded-2xl border border-dashed border-blue-300 bg-blue-50/50 p-4 dark:border-blue-800 dark:bg-blue-950/20">
                    <div class="mb-3 flex items-start justify-between gap-3">
                        <div>
                            <div class="flex items-center gap-2">
                                <h3 class="font-bold text-neutral-900 dark:text-white">Versión desktop</h3>
                                <span class="rounded-full bg-blue-100 px-2 py-0.5 text-[10px] font-bold uppercase text-blue-700 dark:bg-blue-500/15 dark:text-blue-300">Horizontal</span>
                            </div>
                            <p class="mt-1 text-xs text-neutral-500 dark:text-neutral-400">Recomendado: 2058 × 1365 px, PNG transparente.</p>
                        </div>
                    </div>

                    <label class="group flex min-h-44 cursor-pointer flex-col items-center justify-center rounded-xl border border-neutral-200 bg-white px-4 py-5 text-center transition hover:border-blue-400 hover:bg-blue-50 dark:border-neutral-700 dark:bg-neutral-950 dark:hover:border-blue-600 dark:hover:bg-blue-950/30">
                        <?php if (isset($component)) { $__componentOriginal26c546557cdc09040c8dd00b2090afd0 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal26c546557cdc09040c8dd00b2090afd0 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'e60dd9d2c3a62d619c9acb38f20d5aa5::input.index','data' => ['type' => 'file','wire:model' => 'marcoDesktop','accept' => 'image/jpeg,image/png,image/webp','class' => 'sr-only']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('flux::input'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['type' => 'file','wire:model' => 'marcoDesktop','accept' => 'image/jpeg,image/png,image/webp','class' => 'sr-only']); ?>
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
                        <!--[if BLOCK]><![endif]--><?php if($marcoDesktop): ?>
                            <?php [$dw, $dh] = @getimagesize($marcoDesktop->getRealPath()) ?: [null, null]; ?>
                            <img src="<?php echo e($marcoDesktop->temporaryUrl()); ?>" class="h-28 w-full rounded-lg object-contain" alt="Vista previa desktop">
                            <p class="mt-3 text-xs font-semibold text-blue-700 dark:text-blue-300"><?php echo e($dw); ?> × <?php echo e($dh); ?> px · <?php echo e(strtoupper($marcoDesktop->getClientOriginalExtension())); ?></p>
                        <?php elseif($marcoEditando && ($archivo = $marcoEditando->marco_desktop ?: $marcoEditando->marco)): ?>
                            <img src="<?php echo e(asset('storage/imagenesMarcos/'.$archivo)); ?>" class="h-28 w-full rounded-lg object-contain" alt="Marco desktop actual">
                            <p class="mt-3 text-xs font-semibold text-neutral-600 dark:text-neutral-300">Archivo actual · haz clic para reemplazar</p>
                        <?php else: ?>
                            <div class="flex h-12 w-12 items-center justify-center rounded-2xl bg-blue-100 text-2xl text-blue-600 transition group-hover:scale-105 dark:bg-blue-500/15 dark:text-blue-300">＋</div>
                            <p class="mt-3 text-sm font-bold text-neutral-800 dark:text-neutral-100">Seleccionar marco horizontal</p>
                            <p class="mt-1 text-xs text-neutral-500">JPG, PNG o WebP · máximo 20 MB</p>
                        <?php endif; ?><!--[if ENDBLOCK]><![endif]-->
                    </label>

                    <!--[if BLOCK]><![endif]--><?php if($marcoEditando && ($marcoEditando->marco_desktop ?: $marcoEditando->marco)): ?>
                        <label class="mt-3 flex items-center gap-2 text-xs text-red-600 dark:text-red-400">
                            <?php if (isset($component)) { $__componentOriginal9384bd05e996fcc8c16dc84e6bbc1c8f = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal9384bd05e996fcc8c16dc84e6bbc1c8f = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'e60dd9d2c3a62d619c9acb38f20d5aa5::checkbox.index','data' => ['wire:model' => 'quitarDesktop','class' => 'rounded border-neutral-300']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('flux::checkbox'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['wire:model' => 'quitarDesktop','class' => 'rounded border-neutral-300']); ?>
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
                            Quitar la versión desktop actual
                        </label>
                    <?php endif; ?><!--[if ENDBLOCK]><![endif]-->
                    <!--[if BLOCK]><![endif]--><?php $__errorArgs = ['marcoDesktop'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> <p class="mt-2 text-sm font-medium text-red-600"><?php echo e($message); ?></p> <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?><!--[if ENDBLOCK]><![endif]-->
                </div>

                
                <div class="rounded-2xl border border-dashed border-violet-300 bg-violet-50/50 p-4 dark:border-violet-800 dark:bg-violet-950/20">
                    <div class="mb-3 flex items-start justify-between gap-3">
                        <div>
                            <div class="flex items-center gap-2">
                                <h3 class="font-bold text-neutral-900 dark:text-white">Versión móvil</h3>
                                <span class="rounded-full bg-violet-100 px-2 py-0.5 text-[10px] font-bold uppercase text-violet-700 dark:bg-violet-500/15 dark:text-violet-300">Vertical</span>
                            </div>
                            <p class="mt-1 text-xs text-neutral-500 dark:text-neutral-400">Recomendado: 1365 × 2058 px, PNG transparente.</p>
                        </div>
                    </div>

                    <label class="group flex min-h-44 cursor-pointer flex-col items-center justify-center rounded-xl border border-neutral-200 bg-white px-4 py-5 text-center transition hover:border-violet-400 hover:bg-violet-50 dark:border-neutral-700 dark:bg-neutral-950 dark:hover:border-violet-600 dark:hover:bg-violet-950/30">
                        <?php if (isset($component)) { $__componentOriginal26c546557cdc09040c8dd00b2090afd0 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal26c546557cdc09040c8dd00b2090afd0 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'e60dd9d2c3a62d619c9acb38f20d5aa5::input.index','data' => ['type' => 'file','wire:model' => 'marcoMobile','accept' => 'image/jpeg,image/png,image/webp','class' => 'sr-only']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('flux::input'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['type' => 'file','wire:model' => 'marcoMobile','accept' => 'image/jpeg,image/png,image/webp','class' => 'sr-only']); ?>
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
                        <!--[if BLOCK]><![endif]--><?php if($marcoMobile): ?>
                            <?php [$mw, $mh] = @getimagesize($marcoMobile->getRealPath()) ?: [null, null]; ?>
                            <img src="<?php echo e($marcoMobile->temporaryUrl()); ?>" class="h-28 w-full rounded-lg object-contain" alt="Vista previa móvil">
                            <p class="mt-3 text-xs font-semibold text-violet-700 dark:text-violet-300"><?php echo e($mw); ?> × <?php echo e($mh); ?> px · <?php echo e(strtoupper($marcoMobile->getClientOriginalExtension())); ?></p>
                        <?php elseif($marcoEditando && $marcoEditando->marco_mobile): ?>
                            <img src="<?php echo e(asset('storage/imagenesMarcos/'.$marcoEditando->marco_mobile)); ?>" class="h-28 w-full rounded-lg object-contain" alt="Marco móvil actual">
                            <p class="mt-3 text-xs font-semibold text-neutral-600 dark:text-neutral-300">Archivo actual · haz clic para reemplazar</p>
                        <?php else: ?>
                            <div class="flex h-12 w-12 items-center justify-center rounded-2xl bg-violet-100 text-2xl text-violet-600 transition group-hover:scale-105 dark:bg-violet-500/15 dark:text-violet-300">＋</div>
                            <p class="mt-3 text-sm font-bold text-neutral-800 dark:text-neutral-100">Seleccionar marco vertical</p>
                            <p class="mt-1 text-xs text-neutral-500">JPG, PNG o WebP · máximo 20 MB</p>
                        <?php endif; ?><!--[if ENDBLOCK]><![endif]-->
                    </label>

                    <!--[if BLOCK]><![endif]--><?php if($marcoEditando && $marcoEditando->marco_mobile): ?>
                        <label class="mt-3 flex items-center gap-2 text-xs text-red-600 dark:text-red-400">
                            <?php if (isset($component)) { $__componentOriginal9384bd05e996fcc8c16dc84e6bbc1c8f = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal9384bd05e996fcc8c16dc84e6bbc1c8f = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'e60dd9d2c3a62d619c9acb38f20d5aa5::checkbox.index','data' => ['wire:model' => 'quitarMobile','class' => 'rounded border-neutral-300']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('flux::checkbox'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['wire:model' => 'quitarMobile','class' => 'rounded border-neutral-300']); ?>
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
                            Quitar la versión móvil actual
                        </label>
                    <?php endif; ?><!--[if ENDBLOCK]><![endif]-->
                    <!--[if BLOCK]><![endif]--><?php $__errorArgs = ['marcoMobile'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> <p class="mt-2 text-sm font-medium text-red-600"><?php echo e($message); ?></p> <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?><!--[if ENDBLOCK]><![endif]-->
                </div>
            </div>

            <div class="grid gap-4 md:grid-cols-2 xl:grid-cols-3">
                <?php if (isset($component)) { $__componentOriginal26c546557cdc09040c8dd00b2090afd0 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal26c546557cdc09040c8dd00b2090afd0 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'e60dd9d2c3a62d619c9acb38f20d5aa5::input.index','data' => ['wire:model' => 'nombre','label' => 'Nombre del marco','badge' => 'Obligatorio','placeholder' => 'Ej. Clausura institucional 2026']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('flux::input'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['wire:model' => 'nombre','label' => 'Nombre del marco','badge' => 'Obligatorio','placeholder' => 'Ej. Clausura institucional 2026']); ?>
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
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'e60dd9d2c3a62d619c9acb38f20d5aa5::input.index','data' => ['wire:model' => 'categoria','label' => 'Categoría','badge' => 'Obligatorio','placeholder' => 'Institucional, clausura, publicidad...','list' => 'categorias-marcos']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('flux::input'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['wire:model' => 'categoria','label' => 'Categoría','badge' => 'Obligatorio','placeholder' => 'Institucional, clausura, publicidad...','list' => 'categorias-marcos']); ?>
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
                <datalist id="categorias-marcos">
                    <!--[if BLOCK]><![endif]--><?php $__currentLoopData = $categorias; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $cat): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?><option value="<?php echo e($cat); ?>"></option><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><!--[if ENDBLOCK]><![endif]-->
                </datalist>
                <?php if (isset($component)) { $__componentOriginal26c546557cdc09040c8dd00b2090afd0 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal26c546557cdc09040c8dd00b2090afd0 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'e60dd9d2c3a62d619c9acb38f20d5aa5::input.index','data' => ['wire:model' => 'tagsTexto','label' => 'Etiquetas','placeholder' => 'graduación, azul, universidad','description' => 'Sepáralas con comas.']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('flux::input'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['wire:model' => 'tagsTexto','label' => 'Etiquetas','placeholder' => 'graduación, azul, universidad','description' => 'Sepáralas con comas.']); ?>
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
                <div class="md:col-span-2 xl:col-span-2">
                    <?php if (isset($component)) { $__componentOriginal0ee30026125d1a66523211147b00e4dc = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal0ee30026125d1a66523211147b00e4dc = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'e60dd9d2c3a62d619c9acb38f20d5aa5::textarea','data' => ['wire:model' => 'descripcion','label' => 'Descripción','placeholder' => 'Describe el uso y estilo del marco...','rows' => '3']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('flux::textarea'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['wire:model' => 'descripcion','label' => 'Descripción','placeholder' => 'Describe el uso y estilo del marco...','rows' => '3']); ?>
<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal0ee30026125d1a66523211147b00e4dc)): ?>
<?php $attributes = $__attributesOriginal0ee30026125d1a66523211147b00e4dc; ?>
<?php unset($__attributesOriginal0ee30026125d1a66523211147b00e4dc); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal0ee30026125d1a66523211147b00e4dc)): ?>
<?php $component = $__componentOriginal0ee30026125d1a66523211147b00e4dc; ?>
<?php unset($__componentOriginal0ee30026125d1a66523211147b00e4dc); ?>
<?php endif; ?>
                </div>
                <?php if (isset($component)) { $__componentOriginal0ee30026125d1a66523211147b00e4dc = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal0ee30026125d1a66523211147b00e4dc = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'e60dd9d2c3a62d619c9acb38f20d5aa5::textarea','data' => ['wire:model' => 'notas','label' => 'Notas internas','placeholder' => 'Indicaciones para administradores...','rows' => '3']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('flux::textarea'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['wire:model' => 'notas','label' => 'Notas internas','placeholder' => 'Indicaciones para administradores...','rows' => '3']); ?>
<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal0ee30026125d1a66523211147b00e4dc)): ?>
<?php $attributes = $__attributesOriginal0ee30026125d1a66523211147b00e4dc; ?>
<?php unset($__attributesOriginal0ee30026125d1a66523211147b00e4dc); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal0ee30026125d1a66523211147b00e4dc)): ?>
<?php $component = $__componentOriginal0ee30026125d1a66523211147b00e4dc; ?>
<?php unset($__componentOriginal0ee30026125d1a66523211147b00e4dc); ?>
<?php endif; ?>
            </div>

            <div class="flex flex-col gap-4 rounded-xl border border-neutral-200 bg-neutral-50 p-4 sm:flex-row sm:items-center sm:justify-between dark:border-neutral-800 dark:bg-neutral-950/60">
                <label class="flex cursor-pointer items-center gap-3">
                    <?php if (isset($component)) { $__componentOriginal9384bd05e996fcc8c16dc84e6bbc1c8f = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal9384bd05e996fcc8c16dc84e6bbc1c8f = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'e60dd9d2c3a62d619c9acb38f20d5aa5::checkbox.index','data' => ['wire:model' => 'activo','class' => 'h-5 w-5 rounded border-neutral-300 text-blue-600 focus:ring-blue-500']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('flux::checkbox'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['wire:model' => 'activo','class' => 'h-5 w-5 rounded border-neutral-300 text-blue-600 focus:ring-blue-500']); ?>
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
                        <span class="block text-sm font-bold text-neutral-800 dark:text-neutral-100">Marco activo</span>
                        <span class="block text-xs text-neutral-500">Disponible inmediatamente en System Images.</span>
                    </span>
                </label>

                <div class="flex flex-wrap items-center gap-2">
                    <!--[if BLOCK]><![endif]--><?php if($editandoId): ?>
                        <?php if (isset($component)) { $__componentOriginalc04b147acd0e65cc1a77f86fb0e81580 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginalc04b147acd0e65cc1a77f86fb0e81580 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'e60dd9d2c3a62d619c9acb38f20d5aa5::button.index','data' => ['type' => 'button','wire:click' => 'cancelarEdicion','variant' => 'ghost']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('flux::button'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['type' => 'button','wire:click' => 'cancelarEdicion','variant' => 'ghost']); ?>Cancelar <?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginalc04b147acd0e65cc1a77f86fb0e81580)): ?>
<?php $attributes = $__attributesOriginalc04b147acd0e65cc1a77f86fb0e81580; ?>
<?php unset($__attributesOriginalc04b147acd0e65cc1a77f86fb0e81580); ?>
<?php endif; ?>
<?php if (isset($__componentOriginalc04b147acd0e65cc1a77f86fb0e81580)): ?>
<?php $component = $__componentOriginalc04b147acd0e65cc1a77f86fb0e81580; ?>
<?php unset($__componentOriginalc04b147acd0e65cc1a77f86fb0e81580); ?>
<?php endif; ?>
                    <?php endif; ?><!--[if ENDBLOCK]><![endif]-->
                    <?php if (isset($component)) { $__componentOriginalc04b147acd0e65cc1a77f86fb0e81580 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginalc04b147acd0e65cc1a77f86fb0e81580 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'e60dd9d2c3a62d619c9acb38f20d5aa5::button.index','data' => ['type' => 'submit','variant' => 'primary','wire:loading.attr' => 'disabled','wire:target' => 'guardarMarco,marcoDesktop,marcoMobile']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('flux::button'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['type' => 'submit','variant' => 'primary','wire:loading.attr' => 'disabled','wire:target' => 'guardarMarco,marcoDesktop,marcoMobile']); ?>
                        <span wire:loading.remove wire:target="guardarMarco"><?php echo e($editandoId ? 'Guardar cambios' : 'Crear marco'); ?></span>
                        <span wire:loading wire:target="guardarMarco">Guardando...</span>
                     <?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginalc04b147acd0e65cc1a77f86fb0e81580)): ?>
<?php $attributes = $__attributesOriginalc04b147acd0e65cc1a77f86fb0e81580; ?>
<?php unset($__attributesOriginalc04b147acd0e65cc1a77f86fb0e81580); ?>
<?php endif; ?>
<?php if (isset($__componentOriginalc04b147acd0e65cc1a77f86fb0e81580)): ?>
<?php $component = $__componentOriginalc04b147acd0e65cc1a77f86fb0e81580; ?>
<?php unset($__componentOriginalc04b147acd0e65cc1a77f86fb0e81580); ?>
<?php endif; ?>
                </div>
            </div>
        </form>
    </section>

    
    <section class="rounded-2xl border border-neutral-200 bg-white p-4 shadow-sm dark:border-neutral-800 dark:bg-neutral-900">
        <div class="grid gap-3 md:grid-cols-[1fr_220px_200px_auto]">
            <?php if (isset($component)) { $__componentOriginal26c546557cdc09040c8dd00b2090afd0 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal26c546557cdc09040c8dd00b2090afd0 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'e60dd9d2c3a62d619c9acb38f20d5aa5::input.index','data' => ['wire:model.live.debounce.350ms' => 'buscar','icon' => 'magnifying-glass','placeholder' => 'Buscar por nombre, descripción o categoría...']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('flux::input'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['wire:model.live.debounce.350ms' => 'buscar','icon' => 'magnifying-glass','placeholder' => 'Buscar por nombre, descripción o categoría...']); ?>
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
            <?php if (isset($component)) { $__componentOriginala467913f9ff34913553be64599ec6e92 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginala467913f9ff34913553be64599ec6e92 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'e60dd9d2c3a62d619c9acb38f20d5aa5::select.index','data' => ['wire:model.live' => 'filtroCategoria','placeholder' => 'Todas las categorías']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('flux::select'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['wire:model.live' => 'filtroCategoria','placeholder' => 'Todas las categorías']); ?>
                <?php if (isset($component)) { $__componentOriginalc7395a5f1f6c2e275d1dc4ea0be0c745 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginalc7395a5f1f6c2e275d1dc4ea0be0c745 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'e60dd9d2c3a62d619c9acb38f20d5aa5::select.option.index','data' => ['value' => '']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('flux::select.option'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['value' => '']); ?>Todas las categorías <?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginalc7395a5f1f6c2e275d1dc4ea0be0c745)): ?>
<?php $attributes = $__attributesOriginalc7395a5f1f6c2e275d1dc4ea0be0c745; ?>
<?php unset($__attributesOriginalc7395a5f1f6c2e275d1dc4ea0be0c745); ?>
<?php endif; ?>
<?php if (isset($__componentOriginalc7395a5f1f6c2e275d1dc4ea0be0c745)): ?>
<?php $component = $__componentOriginalc7395a5f1f6c2e275d1dc4ea0be0c745; ?>
<?php unset($__componentOriginalc7395a5f1f6c2e275d1dc4ea0be0c745); ?>
<?php endif; ?>
                <!--[if BLOCK]><![endif]--><?php $__currentLoopData = $categorias; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $cat): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                    <?php if (isset($component)) { $__componentOriginalc7395a5f1f6c2e275d1dc4ea0be0c745 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginalc7395a5f1f6c2e275d1dc4ea0be0c745 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'e60dd9d2c3a62d619c9acb38f20d5aa5::select.option.index','data' => ['value' => ''.e($cat).'']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('flux::select.option'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['value' => ''.e($cat).'']); ?><?php echo e($cat); ?> <?php echo $__env->renderComponent(); ?>
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
            <?php if (isset($component)) { $__componentOriginala467913f9ff34913553be64599ec6e92 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginala467913f9ff34913553be64599ec6e92 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'e60dd9d2c3a62d619c9acb38f20d5aa5::select.index','data' => ['wire:model.live' => 'filtroEstado']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('flux::select'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['wire:model.live' => 'filtroEstado']); ?>
                <?php if (isset($component)) { $__componentOriginalc7395a5f1f6c2e275d1dc4ea0be0c745 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginalc7395a5f1f6c2e275d1dc4ea0be0c745 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'e60dd9d2c3a62d619c9acb38f20d5aa5::select.option.index','data' => ['value' => 'todos']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('flux::select.option'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['value' => 'todos']); ?>Todos los estados <?php echo $__env->renderComponent(); ?>
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
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'e60dd9d2c3a62d619c9acb38f20d5aa5::select.option.index','data' => ['value' => 'activos']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('flux::select.option'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['value' => 'activos']); ?>Activos <?php echo $__env->renderComponent(); ?>
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
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'e60dd9d2c3a62d619c9acb38f20d5aa5::select.option.index','data' => ['value' => 'inactivos']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('flux::select.option'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['value' => 'inactivos']); ?>Inactivos <?php echo $__env->renderComponent(); ?>
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
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'e60dd9d2c3a62d619c9acb38f20d5aa5::select.option.index','data' => ['value' => 'incompletos']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('flux::select.option'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['value' => 'incompletos']); ?>Incompletos <?php echo $__env->renderComponent(); ?>
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
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'e60dd9d2c3a62d619c9acb38f20d5aa5::select.option.index','data' => ['value' => 'papelera']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('flux::select.option'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['value' => 'papelera']); ?>Papelera (<?php echo e($estadisticas['papelera']); ?>) <?php echo $__env->renderComponent(); ?>
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
            <div class="flex items-center justify-end text-xs font-semibold text-neutral-500">
                <?php echo e($marcos->count()); ?> resultado(s)
            </div>
        </div>
    </section>

    
    <section>
        <div class="mb-3 flex items-center justify-between">
            <div>
                <h2 class="text-lg font-black text-neutral-900 dark:text-white">Biblioteca de marcos</h2>
                <p class="text-xs text-neutral-500">Arrastra las tarjetas para cambiar su orden.</p>
            </div>
        </div>

        <div
            x-data
            x-ref="marcosGrid"
            x-init="
                const iniciar = () => {
                    if (!window.Sortable || !$refs.marcosGrid || $refs.marcosGrid._sortable) return;
                    $refs.marcosGrid._sortable = new Sortable($refs.marcosGrid, {
                        animation: 180,
                        handle: '.marco-handle',
                        ghostClass: 'opacity-40',
                        onEnd() {
                            $wire.reordenarMarcos([...$refs.marcosGrid.children].map(el => el.dataset.id))
                        }
                    })
                };
                iniciar();
                document.addEventListener('livewire:navigated', iniciar)
            "
            class="grid gap-4 sm:grid-cols-2 xl:grid-cols-3 2xl:grid-cols-4"
        >
            <!--[if BLOCK]><![endif]--><?php $__empty_1 = true; $__currentLoopData = $marcos; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $marco): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                <?php
                    $desktop = $marco->marco_desktop ?: $marco->marco;
                    $mobile = $marco->marco_mobile;
                    $desktopUrl = $desktop ? asset('storage/imagenesMarcos/'.$desktop) : null;
                    $mobileUrl = $mobile ? asset('storage/imagenesMarcos/'.$mobile) : null;
                ?>
                <article
                    wire:key="marco-card-<?php echo e($marco->id); ?>"
                    data-id="<?php echo e($marco->id); ?>"
                    x-data="{ tab: '<?php echo e($desktop ? 'desktop' : 'mobile'); ?>' }"
                    class="group overflow-hidden rounded-2xl border border-neutral-200 bg-white shadow-sm transition hover:-translate-y-0.5 hover:shadow-lg dark:border-neutral-800 dark:bg-neutral-900"
                >
                    <div class="relative aspect-[16/10] overflow-hidden bg-[linear-gradient(45deg,#e5e7eb_25%,transparent_25%),linear-gradient(-45deg,#e5e7eb_25%,transparent_25%),linear-gradient(45deg,transparent_75%,#e5e7eb_75%),linear-gradient(-45deg,transparent_75%,#e5e7eb_75%)] bg-[length:18px_18px] bg-[position:0_0,0_9px,9px_-9px,-9px_0px] dark:bg-neutral-950">
                        <?php if (isset($component)) { $__componentOriginalc04b147acd0e65cc1a77f86fb0e81580 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginalc04b147acd0e65cc1a77f86fb0e81580 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'e60dd9d2c3a62d619c9acb38f20d5aa5::button.index','data' => ['type' => 'button','class' => 'marco-handle absolute left-2 top-2 z-20 cursor-grab rounded-lg bg-black/65 px-2 py-1 text-xs font-bold text-white backdrop-blur','title' => 'Arrastrar']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('flux::button'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['type' => 'button','class' => 'marco-handle absolute left-2 top-2 z-20 cursor-grab rounded-lg bg-black/65 px-2 py-1 text-xs font-bold text-white backdrop-blur','title' => 'Arrastrar']); ?>☰ <?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginalc04b147acd0e65cc1a77f86fb0e81580)): ?>
<?php $attributes = $__attributesOriginalc04b147acd0e65cc1a77f86fb0e81580; ?>
<?php unset($__attributesOriginalc04b147acd0e65cc1a77f86fb0e81580); ?>
<?php endif; ?>
<?php if (isset($__componentOriginalc04b147acd0e65cc1a77f86fb0e81580)): ?>
<?php $component = $__componentOriginalc04b147acd0e65cc1a77f86fb0e81580; ?>
<?php unset($__componentOriginalc04b147acd0e65cc1a77f86fb0e81580); ?>
<?php endif; ?>

                        <div class="absolute right-2 top-2 z-20 flex rounded-lg bg-black/65 p-1 text-[10px] font-bold text-white backdrop-blur">
                            <!--[if BLOCK]><![endif]--><?php if($desktop): ?>
                                <?php if (isset($component)) { $__componentOriginalc04b147acd0e65cc1a77f86fb0e81580 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginalc04b147acd0e65cc1a77f86fb0e81580 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'e60dd9d2c3a62d619c9acb38f20d5aa5::button.index','data' => ['type' => 'button','@click' => 'tab=\'desktop\'','class' => 'rounded-md px-2 py-1 transition']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('flux::button'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['type' => 'button','@click' => 'tab=\'desktop\'','class' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute('rounded-md px-2 py-1 transition')]); ?>DESKTOP <?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginalc04b147acd0e65cc1a77f86fb0e81580)): ?>
<?php $attributes = $__attributesOriginalc04b147acd0e65cc1a77f86fb0e81580; ?>
<?php unset($__attributesOriginalc04b147acd0e65cc1a77f86fb0e81580); ?>
<?php endif; ?>
<?php if (isset($__componentOriginalc04b147acd0e65cc1a77f86fb0e81580)): ?>
<?php $component = $__componentOriginalc04b147acd0e65cc1a77f86fb0e81580; ?>
<?php unset($__componentOriginalc04b147acd0e65cc1a77f86fb0e81580); ?>
<?php endif; ?>
                            <?php endif; ?><!--[if ENDBLOCK]><![endif]-->
                            <!--[if BLOCK]><![endif]--><?php if($mobile): ?>
                                <?php if (isset($component)) { $__componentOriginalc04b147acd0e65cc1a77f86fb0e81580 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginalc04b147acd0e65cc1a77f86fb0e81580 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'e60dd9d2c3a62d619c9acb38f20d5aa5::button.index','data' => ['type' => 'button','@click' => 'tab=\'mobile\'','class' => 'rounded-md px-2 py-1 transition']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('flux::button'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['type' => 'button','@click' => 'tab=\'mobile\'','class' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute('rounded-md px-2 py-1 transition')]); ?>MÓVIL <?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginalc04b147acd0e65cc1a77f86fb0e81580)): ?>
<?php $attributes = $__attributesOriginalc04b147acd0e65cc1a77f86fb0e81580; ?>
<?php unset($__attributesOriginalc04b147acd0e65cc1a77f86fb0e81580); ?>
<?php endif; ?>
<?php if (isset($__componentOriginalc04b147acd0e65cc1a77f86fb0e81580)): ?>
<?php $component = $__componentOriginalc04b147acd0e65cc1a77f86fb0e81580; ?>
<?php unset($__componentOriginalc04b147acd0e65cc1a77f86fb0e81580); ?>
<?php endif; ?>
                            <?php endif; ?><!--[if ENDBLOCK]><![endif]-->
                        </div>

                        <!--[if BLOCK]><![endif]--><?php if($desktop): ?>
                            <img x-show="tab==='desktop'" x-transition.opacity src="<?php echo e($desktopUrl); ?>" alt="<?php echo e($marco->nombre); ?> desktop"
                                @click="openPreview(<?php echo \Illuminate\Support\Js::from($desktopUrl)->toHtml() ?>, <?php echo \Illuminate\Support\Js::from(($marco->nombre ?: $marco->descripcion).' · Desktop')->toHtml() ?>)"
                                class="h-full w-full cursor-zoom-in object-contain p-3">
                        <?php endif; ?><!--[if ENDBLOCK]><![endif]-->
                        <!--[if BLOCK]><![endif]--><?php if($mobile): ?>
                            <img x-show="tab==='mobile'" x-transition.opacity src="<?php echo e($mobileUrl); ?>" alt="<?php echo e($marco->nombre); ?> móvil"
                                @click="openPreview(<?php echo \Illuminate\Support\Js::from($mobileUrl)->toHtml() ?>, <?php echo \Illuminate\Support\Js::from(($marco->nombre ?: $marco->descripcion).' · Móvil')->toHtml() ?>)"
                                class="h-full w-full cursor-zoom-in object-contain p-3">
                        <?php endif; ?><!--[if ENDBLOCK]><![endif]-->

                        <!--[if BLOCK]><![endif]--><?php if(!$desktop || !$mobile): ?>
                            <span class="absolute bottom-2 left-2 rounded-full bg-amber-500 px-2.5 py-1 text-[10px] font-black uppercase tracking-wide text-white shadow">Incompleto</span>
                        <?php else: ?>
                            <span class="absolute bottom-2 left-2 rounded-full bg-emerald-500 px-2.5 py-1 text-[10px] font-black uppercase tracking-wide text-white shadow">Adaptable</span>
                        <?php endif; ?><!--[if ENDBLOCK]><![endif]-->
                    </div>

                    <div class="space-y-3 p-4">
                        <div class="flex items-start justify-between gap-3">
                            <div class="min-w-0">
                                <h3 class="truncate font-black text-neutral-900 dark:text-white"><?php echo e($marco->nombre ?: $marco->descripcion); ?></h3>
                                <p class="mt-1 line-clamp-2 text-xs text-neutral-500 dark:text-neutral-400"><?php echo e($marco->descripcion ?: 'Sin descripción adicional.'); ?></p>
                            </div>
                            <!--[if BLOCK]><![endif]--><?php if($marco->trashed()): ?>
                                <span class="shrink-0 rounded-full bg-red-100 px-2 py-1 text-[10px] font-black uppercase text-red-700 dark:bg-red-500/15 dark:text-red-300">Papelera</span>
                            <?php else: ?>
                                <span class="shrink-0 rounded-full px-2 py-1 text-[10px] font-black uppercase <?php echo e($marco->activo ? 'bg-emerald-100 text-emerald-700 dark:bg-emerald-500/15 dark:text-emerald-300' : 'bg-neutral-100 text-neutral-600 dark:bg-neutral-800 dark:text-neutral-300'); ?>">
                                    <?php echo e($marco->activo ? 'Activo' : 'Inactivo'); ?>

                                </span>
                            <?php endif; ?><!--[if ENDBLOCK]><![endif]-->
                        </div>

                        <div class="flex flex-wrap gap-1.5">
                            <span class="rounded-lg bg-blue-50 px-2 py-1 text-[10px] font-bold text-blue-700 dark:bg-blue-500/10 dark:text-blue-300"><?php echo e($marco->categoria); ?></span>
                            <!--[if BLOCK]><![endif]--><?php $__currentLoopData = array_slice($marco->tags ?? [], 0, 3); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $tag): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                <span class="rounded-lg bg-neutral-100 px-2 py-1 text-[10px] font-semibold text-neutral-600 dark:bg-neutral-800 dark:text-neutral-300">#<?php echo e($tag); ?></span>
                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><!--[if ENDBLOCK]><![endif]-->
                        </div>

                        <div class="grid grid-cols-2 gap-2 text-[11px] text-neutral-500">
                            <div class="rounded-lg bg-neutral-50 p-2 dark:bg-neutral-950/60">
                                <span class="block font-bold text-neutral-700 dark:text-neutral-200">Desktop</span>
                                <span><?php echo e($desktop ? (($marco->ancho_desktop ?: '—').' × '.($marco->alto_desktop ?: '—')) : 'No disponible'); ?></span>
                                <!--[if BLOCK]><![endif]--><?php if($desktop): ?>
                                    <span class="mt-1 block text-[9px] font-semibold uppercase tracking-wide <?php echo e($marco->transparencia_desktop ? 'text-emerald-600 dark:text-emerald-400' : 'text-neutral-400'); ?>">
                                        <?php echo e(strtoupper($marco->formato_desktop ?: pathinfo($desktop, PATHINFO_EXTENSION))); ?> · <?php echo e($marco->transparencia_desktop ? 'Con transparencia' : 'Sin transparencia'); ?>

                                    </span>
                                <?php endif; ?><!--[if ENDBLOCK]><![endif]-->
                            </div>
                            <div class="rounded-lg bg-neutral-50 p-2 dark:bg-neutral-950/60">
                                <span class="block font-bold text-neutral-700 dark:text-neutral-200">Móvil</span>
                                <span><?php echo e($mobile ? (($marco->ancho_mobile ?: '—').' × '.($marco->alto_mobile ?: '—')) : 'No disponible'); ?></span>
                                <!--[if BLOCK]><![endif]--><?php if($mobile): ?>
                                    <span class="mt-1 block text-[9px] font-semibold uppercase tracking-wide <?php echo e($marco->transparencia_mobile ? 'text-emerald-600 dark:text-emerald-400' : 'text-neutral-400'); ?>">
                                        <?php echo e(strtoupper($marco->formato_mobile ?: pathinfo($mobile, PATHINFO_EXTENSION))); ?> · <?php echo e($marco->transparencia_mobile ? 'Con transparencia' : 'Sin transparencia'); ?>

                                    </span>
                                <?php endif; ?><!--[if ENDBLOCK]><![endif]-->
                            </div>
                        </div>

                        <div class="flex items-center justify-between border-t border-neutral-100 pt-3 text-[11px] text-neutral-500 dark:border-neutral-800">
                            <span><?php echo e(number_format($marco->usos)); ?> uso(s)</span>
                            <span><?php echo e($marco->ultimo_uso_at?->diffForHumans() ?: 'Sin uso todavía'); ?></span>
                        </div>

                        <!--[if BLOCK]><![endif]--><?php if($marco->trashed()): ?>
                            <div class="grid grid-cols-2 gap-2">
                                <?php if (isset($component)) { $__componentOriginalc04b147acd0e65cc1a77f86fb0e81580 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginalc04b147acd0e65cc1a77f86fb0e81580 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'e60dd9d2c3a62d619c9acb38f20d5aa5::button.index','data' => ['type' => 'button','@click' => 'confirmRestore('.e($marco->id).', @js($marco->nombre ?: $marco->descripcion))','class' => 'rounded-lg border border-emerald-200 px-3 py-2 text-xs font-bold text-emerald-700 transition hover:bg-emerald-50 dark:border-emerald-900/60 dark:text-emerald-400 dark:hover:bg-emerald-950/30']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('flux::button'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['type' => 'button','@click' => 'confirmRestore('.e($marco->id).', @js($marco->nombre ?: $marco->descripcion))','class' => 'rounded-lg border border-emerald-200 px-3 py-2 text-xs font-bold text-emerald-700 transition hover:bg-emerald-50 dark:border-emerald-900/60 dark:text-emerald-400 dark:hover:bg-emerald-950/30']); ?>Restaurar <?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginalc04b147acd0e65cc1a77f86fb0e81580)): ?>
<?php $attributes = $__attributesOriginalc04b147acd0e65cc1a77f86fb0e81580; ?>
<?php unset($__attributesOriginalc04b147acd0e65cc1a77f86fb0e81580); ?>
<?php endif; ?>
<?php if (isset($__componentOriginalc04b147acd0e65cc1a77f86fb0e81580)): ?>
<?php $component = $__componentOriginalc04b147acd0e65cc1a77f86fb0e81580; ?>
<?php unset($__componentOriginalc04b147acd0e65cc1a77f86fb0e81580); ?>
<?php endif; ?>
                                <?php if (isset($component)) { $__componentOriginalc04b147acd0e65cc1a77f86fb0e81580 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginalc04b147acd0e65cc1a77f86fb0e81580 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'e60dd9d2c3a62d619c9acb38f20d5aa5::button.index','data' => ['type' => 'button','@click' => 'confirmForceDelete('.e($marco->id).', @js($marco->nombre ?: $marco->descripcion))','class' => 'rounded-lg border border-red-200 px-3 py-2 text-xs font-bold text-red-600 transition hover:bg-red-50 dark:border-red-900/60 dark:text-red-400 dark:hover:bg-red-950/30']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('flux::button'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['type' => 'button','@click' => 'confirmForceDelete('.e($marco->id).', @js($marco->nombre ?: $marco->descripcion))','class' => 'rounded-lg border border-red-200 px-3 py-2 text-xs font-bold text-red-600 transition hover:bg-red-50 dark:border-red-900/60 dark:text-red-400 dark:hover:bg-red-950/30']); ?>Eliminar definitivamente <?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginalc04b147acd0e65cc1a77f86fb0e81580)): ?>
<?php $attributes = $__attributesOriginalc04b147acd0e65cc1a77f86fb0e81580; ?>
<?php unset($__attributesOriginalc04b147acd0e65cc1a77f86fb0e81580); ?>
<?php endif; ?>
<?php if (isset($__componentOriginalc04b147acd0e65cc1a77f86fb0e81580)): ?>
<?php $component = $__componentOriginalc04b147acd0e65cc1a77f86fb0e81580; ?>
<?php unset($__componentOriginalc04b147acd0e65cc1a77f86fb0e81580); ?>
<?php endif; ?>
                            </div>
                        <?php else: ?>
                            <div class="grid grid-cols-4 gap-2">
                                <?php if (isset($component)) { $__componentOriginalc04b147acd0e65cc1a77f86fb0e81580 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginalc04b147acd0e65cc1a77f86fb0e81580 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'e60dd9d2c3a62d619c9acb38f20d5aa5::button.index','data' => ['type' => 'button','wire:click' => 'editarMarco('.e($marco->id).')','class' => 'rounded-lg border border-neutral-200 px-2 py-2 text-xs font-bold text-neutral-700 transition hover:border-blue-300 hover:bg-blue-50 hover:text-blue-700 dark:border-neutral-700 dark:text-neutral-200 dark:hover:border-blue-700 dark:hover:bg-blue-950/30','title' => 'Editar']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('flux::button'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['type' => 'button','wire:click' => 'editarMarco('.e($marco->id).')','class' => 'rounded-lg border border-neutral-200 px-2 py-2 text-xs font-bold text-neutral-700 transition hover:border-blue-300 hover:bg-blue-50 hover:text-blue-700 dark:border-neutral-700 dark:text-neutral-200 dark:hover:border-blue-700 dark:hover:bg-blue-950/30','title' => 'Editar']); ?>Editar <?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginalc04b147acd0e65cc1a77f86fb0e81580)): ?>
<?php $attributes = $__attributesOriginalc04b147acd0e65cc1a77f86fb0e81580; ?>
<?php unset($__attributesOriginalc04b147acd0e65cc1a77f86fb0e81580); ?>
<?php endif; ?>
<?php if (isset($__componentOriginalc04b147acd0e65cc1a77f86fb0e81580)): ?>
<?php $component = $__componentOriginalc04b147acd0e65cc1a77f86fb0e81580; ?>
<?php unset($__componentOriginalc04b147acd0e65cc1a77f86fb0e81580); ?>
<?php endif; ?>
                                <?php if (isset($component)) { $__componentOriginalc04b147acd0e65cc1a77f86fb0e81580 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginalc04b147acd0e65cc1a77f86fb0e81580 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'e60dd9d2c3a62d619c9acb38f20d5aa5::button.index','data' => ['type' => 'button','@click' => 'confirmDuplicate('.e($marco->id).', @js($marco->nombre ?: $marco->descripcion))','class' => 'rounded-lg border border-neutral-200 px-2 py-2 text-xs font-bold text-neutral-700 transition hover:border-violet-300 hover:bg-violet-50 hover:text-violet-700 dark:border-neutral-700 dark:text-neutral-200 dark:hover:border-violet-700 dark:hover:bg-violet-950/30','title' => 'Duplicar']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('flux::button'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['type' => 'button','@click' => 'confirmDuplicate('.e($marco->id).', @js($marco->nombre ?: $marco->descripcion))','class' => 'rounded-lg border border-neutral-200 px-2 py-2 text-xs font-bold text-neutral-700 transition hover:border-violet-300 hover:bg-violet-50 hover:text-violet-700 dark:border-neutral-700 dark:text-neutral-200 dark:hover:border-violet-700 dark:hover:bg-violet-950/30','title' => 'Duplicar']); ?>Copiar <?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginalc04b147acd0e65cc1a77f86fb0e81580)): ?>
<?php $attributes = $__attributesOriginalc04b147acd0e65cc1a77f86fb0e81580; ?>
<?php unset($__attributesOriginalc04b147acd0e65cc1a77f86fb0e81580); ?>
<?php endif; ?>
<?php if (isset($__componentOriginalc04b147acd0e65cc1a77f86fb0e81580)): ?>
<?php $component = $__componentOriginalc04b147acd0e65cc1a77f86fb0e81580; ?>
<?php unset($__componentOriginalc04b147acd0e65cc1a77f86fb0e81580); ?>
<?php endif; ?>
                                <?php if (isset($component)) { $__componentOriginalc04b147acd0e65cc1a77f86fb0e81580 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginalc04b147acd0e65cc1a77f86fb0e81580 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'e60dd9d2c3a62d619c9acb38f20d5aa5::button.index','data' => ['type' => 'button','@click' => 'confirmToggle('.e($marco->id).', @js((bool)$marco->activo), @js($marco->nombre ?: $marco->descripcion))','class' => 'rounded-lg border border-neutral-200 px-2 py-2 text-xs font-bold text-neutral-700 transition hover:border-amber-300 hover:bg-amber-50 hover:text-amber-700 dark:border-neutral-700 dark:text-neutral-200 dark:hover:border-amber-700 dark:hover:bg-amber-950/30','title' => ''.e($marco->activo ? 'Desactivar' : 'Activar').'']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('flux::button'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['type' => 'button','@click' => 'confirmToggle('.e($marco->id).', @js((bool)$marco->activo), @js($marco->nombre ?: $marco->descripcion))','class' => 'rounded-lg border border-neutral-200 px-2 py-2 text-xs font-bold text-neutral-700 transition hover:border-amber-300 hover:bg-amber-50 hover:text-amber-700 dark:border-neutral-700 dark:text-neutral-200 dark:hover:border-amber-700 dark:hover:bg-amber-950/30','title' => ''.e($marco->activo ? 'Desactivar' : 'Activar').'']); ?><?php echo e($marco->activo ? 'Pausar' : 'Activar'); ?> <?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginalc04b147acd0e65cc1a77f86fb0e81580)): ?>
<?php $attributes = $__attributesOriginalc04b147acd0e65cc1a77f86fb0e81580; ?>
<?php unset($__attributesOriginalc04b147acd0e65cc1a77f86fb0e81580); ?>
<?php endif; ?>
<?php if (isset($__componentOriginalc04b147acd0e65cc1a77f86fb0e81580)): ?>
<?php $component = $__componentOriginalc04b147acd0e65cc1a77f86fb0e81580; ?>
<?php unset($__componentOriginalc04b147acd0e65cc1a77f86fb0e81580); ?>
<?php endif; ?>
                                <?php if (isset($component)) { $__componentOriginalc04b147acd0e65cc1a77f86fb0e81580 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginalc04b147acd0e65cc1a77f86fb0e81580 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'e60dd9d2c3a62d619c9acb38f20d5aa5::button.index','data' => ['type' => 'button','@click' => 'confirmDelete('.e($marco->id).', @js($marco->nombre ?: $marco->descripcion))','class' => 'rounded-lg border border-red-200 px-2 py-2 text-xs font-bold text-red-600 transition hover:bg-red-50 dark:border-red-900/60 dark:text-red-400 dark:hover:bg-red-950/30','title' => 'Eliminar']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('flux::button'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['type' => 'button','@click' => 'confirmDelete('.e($marco->id).', @js($marco->nombre ?: $marco->descripcion))','class' => 'rounded-lg border border-red-200 px-2 py-2 text-xs font-bold text-red-600 transition hover:bg-red-50 dark:border-red-900/60 dark:text-red-400 dark:hover:bg-red-950/30','title' => 'Eliminar']); ?>Eliminar <?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginalc04b147acd0e65cc1a77f86fb0e81580)): ?>
<?php $attributes = $__attributesOriginalc04b147acd0e65cc1a77f86fb0e81580; ?>
<?php unset($__attributesOriginalc04b147acd0e65cc1a77f86fb0e81580); ?>
<?php endif; ?>
<?php if (isset($__componentOriginalc04b147acd0e65cc1a77f86fb0e81580)): ?>
<?php $component = $__componentOriginalc04b147acd0e65cc1a77f86fb0e81580; ?>
<?php unset($__componentOriginalc04b147acd0e65cc1a77f86fb0e81580); ?>
<?php endif; ?>
                            </div>
                        <?php endif; ?><!--[if ENDBLOCK]><![endif]-->
                    </div>
                </article>
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                <div class="col-span-full rounded-2xl border border-dashed border-neutral-300 bg-white px-6 py-16 text-center dark:border-neutral-700 dark:bg-neutral-900">
                    <div class="mx-auto flex h-14 w-14 items-center justify-center rounded-2xl bg-neutral-100 text-2xl dark:bg-neutral-800">▣</div>
                    <h3 class="mt-4 font-black text-neutral-900 dark:text-white">No se encontraron marcos</h3>
                    <p class="mt-1 text-sm text-neutral-500">Cambia los filtros o crea el primer marco adaptable.</p>
                </div>
            <?php endif; ?><!--[if ENDBLOCK]><![endif]-->
        </div>
    </section>

    
    <div x-show="previewOpen" x-transition.opacity x-cloak
        class="fixed inset-0 z-[100] flex items-center justify-center bg-black/75 p-4 backdrop-blur-sm"
        @click.self="previewOpen=false">
        <div class="relative max-h-[92vh] w-full max-w-6xl overflow-hidden rounded-2xl bg-white shadow-2xl dark:bg-neutral-900">
            <div class="flex items-center justify-between border-b border-neutral-200 px-4 py-3 dark:border-neutral-800">
                <h3 class="font-bold text-neutral-900 dark:text-white" x-text="previewTitle"></h3>
                <?php if (isset($component)) { $__componentOriginalc04b147acd0e65cc1a77f86fb0e81580 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginalc04b147acd0e65cc1a77f86fb0e81580 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'e60dd9d2c3a62d619c9acb38f20d5aa5::button.index','data' => ['type' => 'button','@click' => 'previewOpen=false','class' => 'flex h-9 w-9 items-center justify-center rounded-full bg-neutral-100 text-xl hover:bg-neutral-200 dark:bg-neutral-800 dark:hover:bg-neutral-700']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('flux::button'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['type' => 'button','@click' => 'previewOpen=false','class' => 'flex h-9 w-9 items-center justify-center rounded-full bg-neutral-100 text-xl hover:bg-neutral-200 dark:bg-neutral-800 dark:hover:bg-neutral-700']); ?>× <?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginalc04b147acd0e65cc1a77f86fb0e81580)): ?>
<?php $attributes = $__attributesOriginalc04b147acd0e65cc1a77f86fb0e81580; ?>
<?php unset($__attributesOriginalc04b147acd0e65cc1a77f86fb0e81580); ?>
<?php endif; ?>
<?php if (isset($__componentOriginalc04b147acd0e65cc1a77f86fb0e81580)): ?>
<?php $component = $__componentOriginalc04b147acd0e65cc1a77f86fb0e81580; ?>
<?php unset($__componentOriginalc04b147acd0e65cc1a77f86fb0e81580); ?>
<?php endif; ?>
            </div>
            <div class="max-h-[82vh] overflow-auto bg-[linear-gradient(45deg,#e5e7eb_25%,transparent_25%),linear-gradient(-45deg,#e5e7eb_25%,transparent_25%),linear-gradient(45deg,transparent_75%,#e5e7eb_75%),linear-gradient(-45deg,transparent_75%,#e5e7eb_75%)] bg-[length:24px_24px] bg-[position:0_0,0_12px,12px_-12px,-12px_0px] p-6 dark:bg-neutral-950">
                <img :src="previewUrl" :alt="previewTitle" class="mx-auto max-h-[76vh] max-w-full object-contain">
            </div>
        </div>
    </div>
</div>

<?php $__env->startPush('scripts'); ?>
    <script src="https://cdn.jsdelivr.net/npm/sortablejs@1.15.2/Sortable.min.js" defer></script>
<?php $__env->stopPush(); ?>
<?php /**PATH C:\laragon\www\minisystems\resources\views/livewire/images/creacion-marcos.blade.php ENDPATH**/ ?>