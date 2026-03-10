<?php if (isset($component)) { $__componentOriginal23a33f287873b564aaf305a1526eada4 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal23a33f287873b564aaf305a1526eada4 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.layout','data' => ['title' => 'Edit User - Museum Azman']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('layout'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['title' => 'Edit User - Museum Azman']); ?>
    <section class="space-y-6">
        <div class="flex items-start justify-between gap-4">
            <div>
                <h2 class="museum-page-title">Edit User</h2>
                <p class="museum-page-subtitle">Update profile picture and account details</p>
            </div>
            <a href="<?php echo e(route('admin.users.index')); ?>" class="museum-btn-secondary">Back to Users</a>
        </div>

        <article class="museum-panel p-5">
            <form id="edit-user-form" action="<?php echo e(route('admin.users.update', $user)); ?>" method="POST" enctype="multipart/form-data" class="grid gap-4 md:grid-cols-2">
                <?php echo csrf_field(); ?>
                <?php echo method_field('PUT'); ?>

                <div class="md:col-span-2 flex items-center gap-4">
                    <?php if($user->avatar_url): ?>
                        <img src="<?php echo e($user->avatar_url); ?>" alt="<?php echo e($user->name); ?>" class="h-16 w-16 rounded-full object-cover">
                    <?php else: ?>
                        <span class="inline-flex h-16 w-16 items-center justify-center rounded-full bg-zinc-900 text-lg font-semibold text-white"><?php echo e(strtoupper(substr($user->name, 0, 2))); ?></span>
                    <?php endif; ?>
                    <label class="museum-field flex-1">
                        <span>Profile Picture</span>
                        <input id="edit-avatar-input" type="file" name="avatar" accept="image/*,.webp">
                    </label>
                </div>

                <label class="museum-field">
                    <span>Name</span>
                    <input type="text" name="name" value="<?php echo e(old('name', $user->name)); ?>" required>
                </label>

                <label class="museum-field">
                    <span>Email</span>
                    <input type="email" name="email" value="<?php echo e(old('email', $user->email)); ?>" required>
                </label>

                <label class="museum-field">
                    <span>Role</span>
                    <select name="role_id" required>
                        <?php $__currentLoopData = $roles; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $role): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                            <option value="<?php echo e($role->id); ?>" <?php if((string) old('role_id', $user->role_id) === (string) $role->id): echo 'selected'; endif; ?>><?php echo e($role->name); ?></option>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                    </select>
                </label>

                <label class="museum-field">
                    <span>New Password (optional)</span>
                    <input type="password" name="password">
                </label>

                <div class="md:col-span-2">
                    <button type="submit" class="museum-btn">Save User</button>
                </div>
            </form>
        </article>
    </section>

    <script>
document.addEventListener('DOMContentLoaded', () => {
    const form = document.getElementById('edit-user-form');
    const input = document.getElementById('edit-avatar-input');

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
<?php /**PATH /Users/syed/MEGA/WORK/RELIVA/Website/LEBTECH/museum-azman/resources/views/admin/users/edit.blade.php ENDPATH**/ ?>