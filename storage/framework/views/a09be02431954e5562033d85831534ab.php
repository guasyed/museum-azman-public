<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Register - Museum Azman</title>
    <link rel="icon" type="image/x-icon" href="<?php echo e(asset('icons/museum-azman.ico')); ?>?v=3">
    <link rel="alternate icon" href="<?php echo e(asset('icons/museum-azman.ico')); ?>?v=3">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <?php echo app('Illuminate\Foundation\Vite')(['resources/css/app.css', 'resources/js/app.js']); ?>
</head>
<body class="museum-shell flex min-h-screen items-center justify-center bg-[#f6f5f4] p-4">
    <div class="w-full max-w-md rounded-2xl border border-zinc-200 bg-white p-6 shadow-sm">
        <h1 class="museum-page-title text-3xl!">Create Account</h1>
        <p class="museum-page-subtitle">New registrations require admin approval.</p>

        <?php if($errors->any()): ?>
            <div class="mt-4 rounded-xl border border-rose-200 bg-rose-50 px-3 py-2 text-sm text-rose-700">
                <?php echo e($errors->first()); ?>

            </div>
        <?php endif; ?>

        <form method="POST" action="<?php echo e(route('register.perform')); ?>" enctype="multipart/form-data" class="mt-6 space-y-4">
            <?php echo csrf_field(); ?>
            <label class="museum-field">
                <span>Name</span>
                <input type="text" name="name" value="<?php echo e(old('name')); ?>" required autofocus>
            </label>

            <label class="museum-field">
                <span>Email</span>
                <input type="email" name="email" value="<?php echo e(old('email')); ?>" required>
            </label>

            <label class="museum-field">
                <span>Requested Role</span>
                <select name="role_id" required>
                    <option value="" disabled <?php echo e(old('role_id') ? '' : 'selected'); ?>>Select your requested role</option>
                    <?php $__currentLoopData = ($requestableRoles ?? collect()); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $role): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                        <option value="<?php echo e($role->id); ?>" <?php if((string) old('role_id') === (string) $role->id): echo 'selected'; endif; ?>><?php echo e($role->name); ?></option>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                </select>
            </label>

            <label class="museum-field">
                <span>Profile Picture (optional)</span>
                <input type="file" name="avatar" accept="image/*,.webp">
            </label>

            <label class="museum-field">
                <span>Password</span>
                <input type="password" name="password" required>
            </label>

            <label class="museum-field">
                <span>Confirm Password</span>
                <input type="password" name="password_confirmation" required>
            </label>

            <button type="submit" class="museum-btn w-full">Submit Registration</button>
        </form>

        <p class="mt-3 text-xs text-zinc-500">Requested role access is reviewed by admin during approval.</p>

        <p class="mt-4 text-sm text-zinc-600">
            Already have an account?
            <a href="<?php echo e(route('login')); ?>" class="font-semibold" style="color: var(--museum-accent);">Sign in</a>
        </p>
    </div>
</body>
</html>
<?php /**PATH /Users/syed/MEGA/WORK/RELIVA/Website/LEBTECH/museum-azman/resources/views/auth/register.blade.php ENDPATH**/ ?>