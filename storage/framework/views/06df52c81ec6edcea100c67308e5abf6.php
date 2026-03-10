<?php if (isset($component)) { $__componentOriginal23a33f287873b564aaf305a1526eada4 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal23a33f287873b564aaf305a1526eada4 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.layout','data' => ['title' => 'My Profile - Museum Azman']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('layout'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['title' => 'My Profile - Museum Azman']); ?>
    <section class="space-y-6">
        <div>
            <h2 class="museum-page-title">My Profile</h2>
            <p class="museum-page-subtitle">Update your account details and password.</p>
        </div>

        <article class="museum-panel p-5">
            <h3 class="museum-section-title text-base!">Profile Details</h3>
            <form action="<?php echo e(route('profile.update')); ?>" method="POST" enctype="multipart/form-data" class="mt-4 grid gap-4 md:grid-cols-2">
                <?php echo csrf_field(); ?>
                <?php echo method_field('PUT'); ?>

                <div class="md:col-span-2 flex items-center gap-4">
                    <?php if($user->avatar_url): ?>
                        <img src="<?php echo e($user->avatar_url); ?>" alt="<?php echo e($user->name); ?>" class="h-16 w-16 rounded-full object-cover border border-zinc-300">
                    <?php else: ?>
                        <span class="inline-flex h-16 w-16 items-center justify-center rounded-full text-lg font-semibold text-white" style="background: var(--museum-accent);"><?php echo e(strtoupper(substr($user->name, 0, 2))); ?></span>
                    <?php endif; ?>

                    <label class="museum-field flex-1">
                        <span>Profile Picture</span>
                        <input type="file" name="avatar" accept="image/*,.webp">
                    </label>
                </div>

                <label class="museum-field">
                    <span>Name</span>
                    <input type="text" name="name" value="<?php echo e(old('name', $user->name)); ?>" required>
                </label>

                <label class="museum-field">
                    <span>Email</span>
                    <?php if($user->isAdmin()): ?>
                        <input type="email" name="email" value="<?php echo e(old('email', $user->email)); ?>" required>
                    <?php else: ?>
                        <input type="email" value="<?php echo e($user->email); ?>" disabled>
                        <small class="text-xs text-zinc-500">Only admin users can update email addresses.</small>
                    <?php endif; ?>
                </label>

                <div class="md:col-span-2">
                    <button type="submit" class="museum-btn">Save Profile</button>
                </div>
            </form>
        </article>

        <article class="museum-panel p-5">
            <h3 class="museum-section-title text-base!">Change Password</h3>
            <form action="<?php echo e(route('profile.password.update')); ?>" method="POST" class="mt-4 grid gap-4 md:grid-cols-2">
                <?php echo csrf_field(); ?>
                <?php echo method_field('PUT'); ?>

                <label class="museum-field md:col-span-2">
                    <span>Current Password</span>
                    <input type="password" name="current_password" required>
                </label>

                <label class="museum-field">
                    <span>New Password</span>
                    <input type="password" name="password" required>
                </label>

                <label class="museum-field">
                    <span>Confirm New Password</span>
                    <input type="password" name="password_confirmation" required>
                </label>

                <div class="md:col-span-2">
                    <button type="submit" class="museum-btn">Update Password</button>
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
<?php /**PATH /Users/syed/MEGA/WORK/RELIVA/Website/LEBTECH/museum-azman/resources/views/profile/edit.blade.php ENDPATH**/ ?>