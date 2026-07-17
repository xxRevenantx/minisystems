<?php ($section = $section ?? 'processor'); ?>
<?php if (isset($component)) { $__componentOriginal5863877a5171c196453bfa0bd807e410 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal5863877a5171c196453bfa0bd807e410 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.layouts.app','data' => ['title' => __('MiniSystems - System Images')]] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('layouts.app'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['title' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute(__('MiniSystems - System Images'))]); ?>
    <div class="mx-auto w-full max-w-[1800px] space-y-5">
        <header class="flex flex-col gap-4 xl:flex-row xl:items-end xl:justify-between">
            <div>
                <div class="mb-2 flex items-center gap-2 text-xs font-bold uppercase tracking-widest text-blue-600 dark:text-blue-400">
                    <span class="h-2 w-2 rounded-full bg-blue-600"></span>
                    Imágenes múltiples
                </div>
                <h1 class="text-2xl font-black tracking-tight text-neutral-950 dark:text-white">System Images</h1>
                <p class="mt-1 max-w-3xl text-sm text-neutral-500 dark:text-neutral-400">
                    Procesa, adapta y optimiza imágenes desde un mismo espacio de trabajo.
                </p>
            </div>

            <nav class="flex flex-wrap gap-2 rounded-2xl border border-neutral-200 bg-white p-1.5 shadow-sm dark:border-neutral-800 dark:bg-neutral-900">
                <a href="<?php echo e(route('images')); ?>" wire:navigate
                    class="inline-flex items-center justify-center rounded-xl px-4 py-2.5 text-sm font-black transition
                        <?php echo e($section === 'processor'
                            ? 'bg-blue-600 text-white shadow-md shadow-blue-500/20'
                            : 'text-neutral-600 hover:bg-blue-50 hover:text-blue-700 dark:text-neutral-300 dark:hover:bg-blue-950/30 dark:hover:text-blue-300'); ?>">
                    Procesar imágenes
                </a>
                <a href="<?php echo e(route('images.optimizer')); ?>" wire:navigate
                    class="inline-flex items-center justify-center rounded-xl px-4 py-2.5 text-sm font-black transition
                        <?php echo e($section === 'optimizer'
                            ? 'bg-emerald-600 text-white shadow-md shadow-emerald-500/20'
                            : 'text-neutral-600 hover:bg-emerald-50 hover:text-emerald-700 dark:text-neutral-300 dark:hover:bg-emerald-950/30 dark:hover:text-emerald-300'); ?>">
                    Optimizar imágenes
                </a>
                <a href="<?php echo e(route('marcos')); ?>" wire:navigate
                    class="inline-flex items-center justify-center rounded-xl px-4 py-2.5 text-sm font-black text-neutral-600 transition hover:bg-violet-50 hover:text-violet-700 dark:text-neutral-300 dark:hover:bg-violet-950/30 dark:hover:text-violet-300">
                    Administrar marcos
                </a>
            </nav>
        </header>

        <?php if($section === 'optimizer'): ?>
            <?php
$__split = function ($name, $params = []) {
    return [$name, $params];
};
[$__name, $__params] = $__split('images.optimizar-imagenes', []);

$__html = app('livewire')->mount($__name, $__params, 'lw-3165379333-0', $__slots ?? [], get_defined_vars());

echo $__html;

unset($__html);
unset($__name);
unset($__params);
unset($__split);
if (isset($__slots)) unset($__slots);
?>
        <?php else: ?>
            <?php
$__split = function ($name, $params = []) {
    return [$name, $params];
};
[$__name, $__params] = $__split('images.creacion-imagenes', []);

$__html = app('livewire')->mount($__name, $__params, 'lw-3165379333-1', $__slots ?? [], get_defined_vars());

echo $__html;

unset($__html);
unset($__name);
unset($__params);
unset($__split);
if (isset($__slots)) unset($__slots);
?>
        <?php endif; ?>
    </div>
 <?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal5863877a5171c196453bfa0bd807e410)): ?>
<?php $attributes = $__attributesOriginal5863877a5171c196453bfa0bd807e410; ?>
<?php unset($__attributesOriginal5863877a5171c196453bfa0bd807e410); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal5863877a5171c196453bfa0bd807e410)): ?>
<?php $component = $__componentOriginal5863877a5171c196453bfa0bd807e410; ?>
<?php unset($__componentOriginal5863877a5171c196453bfa0bd807e410); ?>
<?php endif; ?>
<?php /**PATH C:\laragon\www\minisystems\resources\views/images/index.blade.php ENDPATH**/ ?>