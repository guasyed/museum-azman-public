<?php if (isset($component)) { $__componentOriginal23a33f287873b564aaf305a1526eada4 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal23a33f287873b564aaf305a1526eada4 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.layout','data' => ['title' => 'Edit Artwork - Museum Azman']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('layout'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['title' => 'Edit Artwork - Museum Azman']); ?>
    <?php
        $returnUrl = request()->query('return');
        if (!is_string($returnUrl) || trim($returnUrl) === '') {
            $returnUrl = url()->previous();
        }

        $returnHost = is_string($returnUrl) ? parse_url($returnUrl, PHP_URL_HOST) : null;
        $returnPath = is_string($returnUrl) ? parse_url($returnUrl, PHP_URL_PATH) : null;
        $isSafeReturnUrl = is_string($returnUrl)
            && $returnUrl !== ''
            && is_string($returnPath)
            && str_starts_with($returnPath, '/')
            && ($returnHost === null || $returnHost === request()->getHost());

        $backUrl = $isSafeReturnUrl
            ? $returnUrl
            : route('artworks.show', [
                'artwork' => $artwork,
                'from' => request()->string('from')->toString() === 'dashboard' ? 'dashboard' : 'collection',
            ]);
    ?>

    <section class="space-y-6">
        <div class="flex items-start justify-between gap-3">
            <div>
                <h2 class="museum-page-title">Edit Artwork</h2>
                <p class="museum-page-subtitle">Update artwork details and images.</p>
            </div>
            <a href="<?php echo e($backUrl); ?>" class="museum-btn-secondary">Back</a>
        </div>

        <form method="POST" action="<?php echo e(route('artworks.update', ['artwork' => $artwork, 'from' => request()->string('from')->toString(), 'return' => $returnUrl])); ?>" enctype="multipart/form-data" class="museum-panel space-y-6">
            <?php echo csrf_field(); ?>
            <?php echo method_field('PUT'); ?>
            <?php echo $__env->make('artworks.partials.form', ['artwork' => $artwork], array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
            <div class="flex justify-end">
                <a href="<?php echo e($backUrl); ?>" class="museum-btn-secondary" style='margin-right: 10px;'>Back</a>
                <button type="submit" class="museum-btn">Update Artwork</button>
            </div>
        </form>
    </section>
 <?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal23a33f287873b564aaf305a1526eada4)): ?>
<?php $attributes = $__attributesOriginal23a33f287873b564aaf305a1526eada4; ?>
<?php unset($__attributesOriginal23a33f287873b564aaf305a1526eada4); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal23a33f287873b564aaf305a1526eada4)): ?>
<?php $component = $__componentOriginal23a33f287873b564aaf305a1526eada4; ?>
<?php unset($__componentOriginal23a33f287873b564aaf305a1526eada4); ?>
<?php endif; ?>
<?php /**PATH /Users/syed/MEGA/WORK/RELIVA/Website/LEBTECH/museum-azman/resources/views/artworks/edit.blade.php ENDPATH**/ ?>