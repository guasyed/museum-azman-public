<?php if (isset($component)) { $__componentOriginal23a33f287873b564aaf305a1526eada4 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal23a33f287873b564aaf305a1526eada4 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.layout','data' => ['title' => 'Manage Users - Museum Azman']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('layout'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['title' => 'Manage Users - Museum Azman']); ?>
    <section class="space-y-6">
        <div class="flex items-start justify-between gap-4">
            <div>
                <h2 class="museum-page-title">User Management</h2>
                <p class="museum-page-subtitle">Admin can manage profile picture and account data</p>
            </div>
            <a href="<?php echo e(route('settings.index', ['tab' => 'users-roles'])); ?>" class="museum-btn-secondary">Back to Settings</a>
        </div>

        <article class="museum-panel p-5">
            <h3 class="museum-section-title text-base!">Create User</h3>
            <form id="create-user-form" action="<?php echo e(route('admin.users.store')); ?>" method="POST" enctype="multipart/form-data" class="mt-4 grid gap-4 md:grid-cols-2">
                <?php echo csrf_field(); ?>
                <label class="museum-field">
                    <span>Name</span>
                    <input type="text" name="name" required>
                </label>
                <label class="museum-field">
                    <span>Email</span>
                    <input type="email" name="email" required>
                </label>
                <label class="museum-field">
                    <span>Role</span>
                    <select name="role_id" required>
                        <?php $__currentLoopData = $roles; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $role): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                            <option value="<?php echo e($role->id); ?>"><?php echo e($role->name); ?></option>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                    </select>
                </label>
                <label class="museum-field">
                    <span>Password</span>
                    <input type="password" name="password" required>
                </label>
                <label class="museum-field md:col-span-2">
                    <span>Profile Picture</span>
                    <input id="create-avatar-input" type="file" name="avatar" accept="image/*,.webp">
                </label>
                <div class="md:col-span-2">
                    <button type="submit" class="museum-btn">Create User</button>
                </div>
            </form>
        </article>

        <article class="museum-panel p-0! overflow-hidden">
            <div class="overflow-x-auto">
                <table class="w-full min-w-245 text-sm">
                    <thead>
                        <tr class="border-b border-zinc-200 text-left text-zinc-600">
                            <th class="px-4 py-3">User</th>
                            <th class="px-4 py-3">Email</th>
                            <th class="px-4 py-3">Role</th>
                            <th class="px-4 py-3">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                    <?php $__empty_1 = true; $__currentLoopData = $users; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $user): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                        <tr class="border-b border-zinc-200">
                            <td class="px-4 py-3">
                                <div class="flex items-center gap-3">
                                    <?php if($user->avatar_url): ?>
                                        <img src="<?php echo e($user->avatar_url); ?>" alt="<?php echo e($user->name); ?>" class="h-9 w-9 rounded-full object-cover">
                                    <?php else: ?>
                                        <span class="inline-flex h-9 w-9 items-center justify-center rounded-full bg-zinc-900 text-xs font-semibold text-white"><?php echo e(strtoupper(substr($user->name, 0, 2))); ?></span>
                                    <?php endif; ?>
                                    <span class="font-semibold text-zinc-900"><?php echo e($user->name); ?></span>
                                </div>
                            </td>
                            <td class="px-4 py-3 text-zinc-600"><?php echo e($user->email); ?></td>
                            <td class="px-4 py-3">
                                <?php
                                    $userRoleSlug = optional($user->roleRelation)->slug;
                                    $userRoleLabel = $user->role_label;
                                    $userRoleClass = in_array($userRoleSlug, ['owner', 'admin'], true)
                                        ? 'bg-zinc-900 text-white'
                                        : 'bg-zinc-100 text-zinc-700';
                                ?>
                                <span class="rounded-md px-2 py-0.5 text-xs font-semibold <?php echo e($userRoleClass); ?>"><?php echo e($userRoleLabel); ?></span>
                            </td>
                            <td class="px-4 py-3">
                                <a href="<?php echo e(route('admin.users.edit', $user)); ?>" class="museum-btn-secondary">Edit</a>
                            </td>
                        </tr>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                        <tr><td colspan="4" class="px-4 py-4 text-zinc-500">No users found.</td></tr>
                    <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </article>
    </section>

    <script>
document.addEventListener('DOMContentLoaded', () => {
    const form = document.getElementById('create-user-form');
    const input = document.getElementById('create-avatar-input');

    if (!form || !input) {
        return;
    }

    const toFile = (blob, originalName) => {
        const extension = blob.type === 'image/png' ? 'png' : 'jpg';
        const base = (originalName || 'avatar').replace(/\.[^/.]+$/, '');
        return new File([blob], `${base}.${extension}`, { type: blob.type, lastModified: Date.now() });
    };

    const loadImage = (file) => new Promise((resolve, reject) => {
        const url = URL.createObjectURL(file);
        const img = new Image();
        img.onload = () => {
            URL.revokeObjectURL(url);
            resolve(img);
        };
        img.onerror = (error) => {
            URL.revokeObjectURL(url);
            reject(error);
        };
        img.src = url;
    });

    const canvasToBlob = (canvas, mime, quality) => new Promise((resolve) => {
        canvas.toBlob((blob) => resolve(blob), mime, quality);
    });

    const compressAvatar = async (file) => {
        if (!file || !file.type.startsWith('image/')) {
            return file;
        }

        if (file.size <= 2 * 1024 * 1024) {
            return file;
        }

        const image = await loadImage(file);
        const maxSide = 1400;
        const ratio = Math.min(maxSide / Math.max(image.width, 1), maxSide / Math.max(image.height, 1), 1);
        const width = Math.max(1, Math.round(image.width * ratio));
        const height = Math.max(1, Math.round(image.height * ratio));

        const canvas = document.createElement('canvas');
        canvas.width = width;
        canvas.height = height;

        const context = canvas.getContext('2d');
        if (!context) {
            return file;
        }

        context.drawImage(image, 0, 0, width, height);

        const png = await canvasToBlob(canvas, 'image/png', 0.9);
        const jpeg = await canvasToBlob(canvas, 'image/jpeg', 0.82);

        const candidates = [png, jpeg].filter((blob) => blob instanceof Blob);
        const best = candidates.sort((a, b) => a.size - b.size)[0];

        if (!best || best.size >= file.size) {
            return file;
        }

        return toFile(best, file.name);
    };

    form.addEventListener('submit', async (event) => {
        const currentFile = input.files && input.files[0] ? input.files[0] : null;
        if (!currentFile) {
            return;
        }

        event.preventDefault();

        try {
            const compressed = await compressAvatar(currentFile);
            const dataTransfer = new DataTransfer();
            dataTransfer.items.add(compressed);
            input.files = dataTransfer.files;
        } catch (error) {
            console.error(error);
        }

        form.submit();
    });
});
    </script>
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
<?php /**PATH /Users/syed/MEGA/WORK/RELIVA/Website/LEBTECH/museum-azman/resources/views/admin/users/index.blade.php ENDPATH**/ ?>