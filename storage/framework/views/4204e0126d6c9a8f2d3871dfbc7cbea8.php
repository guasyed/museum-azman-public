<?php if (isset($component)) { $__componentOriginal23a33f287873b564aaf305a1526eada4 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal23a33f287873b564aaf305a1526eada4 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.layout','data' => ['title' => 'CSV Import - Museum Azman']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('layout'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['title' => 'CSV Import - Museum Azman']); ?>
    <section class="space-y-6">
        <div class="flex items-start justify-between gap-4">
            <div>
                <h2 class="museum-page-title">CSV Import</h2>
                <p class="museum-page-subtitle">Admin-only tool to import artworks, artists, and locations from CSV</p>
            </div>
            <a href="<?php echo e(route('settings.index', ['tab' => 'general'])); ?>" class="museum-btn-secondary">Back to Settings</a>
        </div>

        <article class="museum-panel p-5">
            <h3 class="museum-section-title text-base!">Upload CSV File</h3>
            <form action="<?php echo e(route('admin.imports.csv.store')); ?>" method="POST" enctype="multipart/form-data" class="mt-4 space-y-4">
                <?php echo csrf_field(); ?>

                <label class="museum-field block">
                    <span>CSV file</span>
                    <input type="file" name="csv_file" accept=".csv,text/csv" required>
                    <small class="text-zinc-500">Supported: .csv files up to 20MB</small>
                </label>

                <label class="museum-field block">
                    <span>Database connection (optional)</span>
                    <input type="text" name="connection" value="<?php echo e(old('connection')); ?>" placeholder="mysql">
                    <small class="text-zinc-500">Leave blank to use default connection from .env</small>
                </label>

                <label class="inline-flex items-center gap-2 text-sm text-zinc-700">
                    <input type="checkbox" name="download_images" value="1" <?php echo e(old('download_images') ? 'checked' : ''); ?>>
                    <span>Download and optimize image URLs during import</span>
                </label>

                <div>
                    <button type="submit" class="museum-btn">Start Import</button>
                </div>
            </form>
        </article>

        <?php if(session('import_output')): ?>
            <article class="museum-panel p-5">
                <h3 class="museum-section-title text-base!">Import Result</h3>
                <pre class="mt-3 overflow-x-auto rounded-xl border border-zinc-200 bg-zinc-50 p-3 text-xs text-zinc-700"><?php echo e(session('import_output')); ?></pre>
            </article>
        <?php endif; ?>
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
<?php /**PATH /Users/syed/MEGA/WORK/RELIVA/Website/LEBTECH/museum-azman/resources/views/admin/imports/index.blade.php ENDPATH**/ ?>