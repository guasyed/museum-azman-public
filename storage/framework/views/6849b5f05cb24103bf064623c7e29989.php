<?php if (isset($component)) { $__componentOriginal23a33f287873b564aaf305a1526eada4 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal23a33f287873b564aaf305a1526eada4 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.layout','data' => ['title' => 'Edit '.e($location->name).' - Location']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('layout'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['title' => 'Edit '.e($location->name).' - Location']); ?>
    <section class="space-y-6 max-w-3xl">
        <div class="flex items-start justify-between gap-4">
            <div>
                <h2 class="museum-page-title">Edit Location</h2>
                <p class="museum-page-subtitle">Update location details</p>
            </div>
            <a href="<?php echo e(route('locations.show', $location)); ?>" class="museum-btn-secondary">Cancel</a>
        </div>

        <article class="museum-panel">
            <form method="POST" action="<?php echo e(route('locations.update', $location)); ?>" class="space-y-4">
                <?php echo csrf_field(); ?>
                <?php echo method_field('PUT'); ?>

                <label class="museum-field">
                    <span>Location Name</span>
                    <input type="text" name="name" value="<?php echo e(old('name', $location->name)); ?>" required maxlength="255">
                </label>

                <label class="museum-field">
                    <span>Type</span>
                    <select name="type">
                        <option value="">Select type</option>
                        <?php $__currentLoopData = $typeOptions; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $type): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                            <option value="<?php echo e($type); ?>" <?php if(old('type', $location->type) === $type): echo 'selected'; endif; ?>><?php echo e($type); ?></option>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                    </select>
                </label>

                <label class="museum-field">
                    <span>Address</span>
                    <input type="text" name="address" value="<?php echo e(old('address', $location->address)); ?>" maxlength="255">
                </label>

                <label class="museum-field">
                    <span>Last Audit Date</span>
                    <input type="date" name="last_audit_date" value="<?php echo e(old('last_audit_date', optional($location->last_audit_date)->format('Y-m-d'))); ?>">
                </label>

                <div class="pt-2">
                    <button type="submit" class="museum-btn">Save Changes</button>
                </div>
            </form>
        </article>
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
<?php /**PATH /Users/syed/MEGA/WORK/RELIVA/Website/LEBTECH/museum-azman/resources/views/locations/edit.blade.php ENDPATH**/ ?>