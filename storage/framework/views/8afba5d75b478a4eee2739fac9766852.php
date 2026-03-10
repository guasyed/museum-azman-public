<?php if (isset($component)) { $__componentOriginal23a33f287873b564aaf305a1526eada4 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal23a33f287873b564aaf305a1526eada4 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.layout','data' => ['title' => 'Settings - Museum Azman']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('layout'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['title' => 'Settings - Museum Azman']); ?>
    <section class="space-y-6">
        <div>
            <h2 class="museum-page-title">Settings</h2>
            <p class="museum-page-subtitle">System configuration and user management</p>
        </div>

        <div class="mb-8 border-b border-zinc-200">
    <nav class="flex flex-wrap gap-2 -mb-px" aria-label="Settings tabs">
        <?php
            $tabs = [
                ['key' => 'general',       'label' => 'General',        'icon' => '⚙️'],
                ['key' => 'users-roles',   'label' => 'Users & Roles',  'icon' => '👥'],
                ['key' => 'notifications', 'label' => 'Notifications',  'icon' => '🔔'],
                ['key' => 'appearance',    'label' => 'Appearance',     'icon' => '🎨'],
            ];
        ?>

        <?php $__currentLoopData = $tabs; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $tab): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
            <?php $isActive = $activeTab === $tab['key']; ?>

            <a
                href="<?php echo e(route('settings.index', ['tab' => $tab['key']])); ?>"
                class="
                    inline-flex items-center gap-2
                    px-4 py-3
                    border-b-2
                    text-sm font-semibold
                    transition-colors
                    <?php echo e($isActive
                        ? 'border-zinc-900 text-zinc-900'
                        : 'border-transparent text-zinc-500 hover:text-zinc-900'); ?>

                "
                aria-current="<?php echo e($isActive ? 'page' : 'false'); ?>"
            >
                <span class="text-base leading-none"><?php echo e($tab['icon']); ?></span>
                <span><?php echo e($tab['label']); ?></span>
            </a>
        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
    </nav>
</div>

        <?php if($activeTab === 'general'): ?>
            <article class="museum-panel p-4 sm:p-5">
                <h3 class="museum-section-title text-base!">Organization Information</h3>
                <p class="mt-1 text-sm text-zinc-600">Basic information about your collection</p>

                <form method="POST" action="<?php echo e(route('settings.update', ['section' => 'general'])); ?>" enctype="multipart/form-data" class="mt-5">
                    <?php echo csrf_field(); ?>
                    <div class="grid gap-4 md:grid-cols-2">
                        <div class="space-y-4">
                            <label class="museum-field">
                                <span>Organization Logo</span>
                                <input type="file" name="organization_logo" accept="image/*,.webp" class="museum-input">
                                <?php if(!empty($generalSettings['organization_logo_url'])): ?>
                                    <div class="mt-2">
                                        <img src="<?php echo e($generalSettings['organization_logo_url']); ?>" alt="Organization Logo" class="h-16 w-16 rounded-lg border border-zinc-300 object-cover">
                                    </div>
                                <?php endif; ?>
                            </label>

                            <label class="museum-field">
                                <span>Organization Name</span>
                                <input name="organization_name" value="<?php echo e(old('organization_name', $generalSettings['organization_name'])); ?>">
                            </label>
                            <label class="museum-field">
                                <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/intl-tel-input@19.5.7/build/css/intlTelInput.css">
                                <span>Phone Number</span>
                                <input
                                    id="phone_number"
                                    name="phone_number"
                                    type="tel"
                                    value="<?php echo e(old('phone_number', $generalSettings['phone_number'] ?? '')); ?>"
                                    class="museum-input"
                                >
                            </label>
                            <label class="museum-field">
                                <span>Address</span>
                                <input name="address" value="<?php echo e(old('address', $generalSettings['address'])); ?>">
                            </label>
                        </div>

                        <div class="space-y-4">
                            <label class="museum-field">
                                <span>Contact Email</span>
                                <input type="email" name="contact_email" value="<?php echo e(old('contact_email', $generalSettings['contact_email'])); ?>">
                            </label>
                            <label class="museum-field">
                                <span>Timezone</span>
                               <?php
                                $currentTz = old('timezone', $generalSettings['timezone'] ?? config('app.timezone'));
                                $regions = [
                                    'Asia' => DateTimeZone::ASIA,
                                    'Europe' => DateTimeZone::EUROPE,
                                    'America' => DateTimeZone::AMERICA,
                                    'Africa' => DateTimeZone::AFRICA,
                                    'Australia' => DateTimeZone::AUSTRALIA,
                                    'Pacific' => DateTimeZone::PACIFIC,
                                    'UTC' => DateTimeZone::UTC,
                                ];
                                ?>

                                <select name="timezone" class="museum-input">

                                <?php $__currentLoopData = $regions; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $region => $mask): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                    <optgroup label="<?php echo e($region); ?>">
                                        <?php $__currentLoopData = DateTimeZone::listIdentifiers($mask); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $tz): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>

                                            <?php
                                                $dt = new DateTime("now", new DateTimeZone($tz));
                                                $offset = $dt->getOffset();
                                                $hours = floor($offset / 3600);
                                                $minutes = abs(($offset % 3600) / 60);
                                                $gmt = sprintf('GMT%+d:%02d', $hours, $minutes);

                                                $city = str_replace('_', ' ', explode('/', $tz)[1] ?? $tz);
                                            ?>

                                            <option value="<?php echo e($tz); ?>" <?php if($currentTz === $tz): echo 'selected'; endif; ?>>
                                                <?php echo e($gmt); ?> — <?php echo e($city); ?>

                                            </option>

                                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                                    </optgroup>
                                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>

                                </select>
                            </label>
                        </div>
                    </div>

                    <button type="submit" class="museum-btn mt-5">Save Changes</button>
                </form>
            </article>

            <article class="museum-panel p-4 sm:p-5">
                <h3 class="museum-section-title text-base!">$ Currency & Regional Settings</h3>
                <p class="mt-1 text-sm text-zinc-600">Configure currency and regional preferences</p>

                <form method="POST" action="<?php echo e(route('settings.update', ['section' => 'regional'])); ?>" class="mt-5">
                    <?php echo csrf_field(); ?>
                    <div class="grid gap-4 md:grid-cols-2">
                        <label class="museum-field">
                            <span>Default Currency</span>
                            <select name="currency">
                                <option value="USD" <?php if(old('currency', $regionalSettings['currency']) === 'USD'): echo 'selected'; endif; ?>>USD ($)</option>
                                <option value="MYR" <?php if(old('currency', $regionalSettings['currency']) === 'MYR'): echo 'selected'; endif; ?>>MYR (RM)</option>
                                <option value="EUR" <?php if(old('currency', $regionalSettings['currency']) === 'EUR'): echo 'selected'; endif; ?>>EUR (€)</option>
                                <option value="GBP" <?php if(old('currency', $regionalSettings['currency']) === 'GBP'): echo 'selected'; endif; ?>>GBP (£)</option>
                                <option value="SGD" <?php if(old('currency', $regionalSettings['currency']) === 'SGD'): echo 'selected'; endif; ?>>SGD (S$)</option>
                            </select>
                        </label>

                        <label class="museum-field">
                            <span>Date Format</span>
                            <select name="date_format">
                                <option value="Y-m-d" <?php if(old('date_format', $regionalSettings['date_format']) === 'Y-m-d'): echo 'selected'; endif; ?>>YYYY-MM-DD</option>
                                <option value="d/m/Y" <?php if(old('date_format', $regionalSettings['date_format']) === 'd/m/Y'): echo 'selected'; endif; ?>>DD/MM/YYYY</option>
                                <option value="m/d/Y" <?php if(old('date_format', $regionalSettings['date_format']) === 'm/d/Y'): echo 'selected'; endif; ?>>MM/DD/YYYY</option>
                                <option value="d M Y" <?php if(old('date_format', $regionalSettings['date_format']) === 'd M Y'): echo 'selected'; endif; ?>>DD MMM YYYY</option>
                            </select>
                        </label>
                    </div>

                    <button type="submit" class="museum-btn mt-5">Save Changes</button>
                </form>
            </article>

            <article class="museum-panel p-4 sm:p-5">
                <h3 class="museum-section-title text-base!">⛁ Data & Backup</h3>
                <p class="mt-1 text-sm text-zinc-600">Manage your data and backup settings</p>

                <div class="mt-5 rounded-xl border border-zinc-200 bg-zinc-50 px-4 py-3">
                    <div class="flex items-center justify-between gap-3">
                        <div>
                            <p class="font-semibold text-zinc-900">Automatic Backups</p>
                            <p class="mt-1 text-sm text-zinc-600">
                                Last backup:
                                <?php if(!empty($backupMeta['last_generated_at'])): ?>
                                    <?php echo e(\Illuminate\Support\Carbon::parse($backupMeta['last_generated_at'])->timezone($backupMeta['timezone'] ?? config('app.timezone'))->format('M j, Y \a\t g:i A')); ?>

                                <?php else: ?>
                                    Not generated yet
                                <?php endif; ?>
                            </p>
                            <?php if(!empty($backupMeta['last_file'])): ?>
                                <p class="mt-1 text-xs text-zinc-500">File: <?php echo e(basename((string) $backupMeta['last_file'])); ?></p>
                            <?php endif; ?>
                            <p class="mt-1 text-xs text-zinc-500">Timezone: <?php echo e($backupMeta['timezone'] ?? config('app.timezone')); ?></p>
                        </div>
                        <span class="rounded-md px-2 py-0.5 text-xs font-semibold <?php echo e(($backupSettings['enabled'] ?? true) ? 'bg-zinc-900 text-white' : 'bg-zinc-200 text-zinc-700'); ?>">
                            <?php echo e(($backupSettings['enabled'] ?? true) ? 'Enabled' : 'Disabled'); ?>

                        </span>
                    </div>
                </div>

                <form method="POST" action="<?php echo e(route('settings.update', ['section' => 'backup'])); ?>" class="mt-4 grid gap-4 rounded-xl border border-zinc-200 bg-white p-4 md:grid-cols-3 md:items-end">
                    <?php echo csrf_field(); ?>
                    <label class="museum-field md:col-span-1">
                        <span>Auto Backup</span>
                        <input type="hidden" name="backup_auto_enabled" value="0">
                        <select name="backup_auto_enabled">
                            <option value="1" <?php if(old('backup_auto_enabled', ($backupSettings['enabled'] ?? true) ? '1' : '0') === '1'): echo 'selected'; endif; ?>>Enabled</option>
                            <option value="0" <?php if(old('backup_auto_enabled', ($backupSettings['enabled'] ?? true) ? '1' : '0') === '0'): echo 'selected'; endif; ?>>Disabled</option>
                        </select>
                    </label>

                    <label class="museum-field md:col-span-1">
                        <span>Run Time (Daily)</span>
                        <input type="time" name="backup_auto_time" value="<?php echo e(old('backup_auto_time', $backupSettings['time'] ?? '03:00')); ?>" required>
                    </label>

                    <div class="md:col-span-1">
                        <button type="submit" class="museum-btn w-full md:w-auto">Save Auto Backup</button>
                    </div>
                </form>

                <div class="mt-4 flex flex-wrap items-center gap-2">
                    <form method="POST" action="<?php echo e(route('settings.backup.generate')); ?>">
                        <?php echo csrf_field(); ?>
                        <button type="submit" class="museum-btn">Generate Backup</button>
                    </form>

                    <?php if(!empty($backupMeta['has_file'])): ?>
                        <a href="<?php echo e(route('settings.backup.download')); ?>" class="museum-btn-secondary">Download Backup</a>
                    <?php else: ?>
                        <button type="button" class="museum-btn-secondary opacity-60" disabled>Download Backup</button>
                    <?php endif; ?>

                    <button type="button" class="museum-btn-secondary">Restore from Backup</button>
                </div>

                <div class="mt-4 overflow-x-auto rounded-xl border border-zinc-200 bg-white">
                    <table class="w-full min-w-180 text-sm">
                        <thead>
                            <tr class="border-b border-zinc-200 text-left text-xs font-semibold uppercase tracking-wide text-zinc-500">
                                <th class="px-4 py-3">File</th>
                                <th class="px-4 py-3">Generated At</th>
                                <th class="px-4 py-3 text-right">Size</th>
                                <th class="px-4 py-3 text-right">Actions</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-zinc-100">
                            <?php $__empty_1 = true; $__currentLoopData = $backupList; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $backup): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                                <tr>
                                    <td class="px-4 py-3 font-medium text-zinc-900"><?php echo e($backup['file_name']); ?></td>
                                    <td class="px-4 py-3 text-zinc-600"><?php echo e($backup['generated_at_display']); ?></td>
                                    <td class="px-4 py-3 text-right text-zinc-600"><?php echo e(number_format((int) $backup['size_kb'])); ?> KB</td>
                                    <td class="px-4 py-3">
                                        <div class="flex items-center justify-end gap-2">
                                            <a href="<?php echo e(route('settings.backup.download', ['file' => $backup['file_name']])); ?>" class="museum-btn-secondary">Download</a>
                                            <form method="POST" action="<?php echo e(route('settings.backup.delete')); ?>" onsubmit="return confirm('Delete this backup file?');">
                                                <?php echo csrf_field(); ?>
                                                <input type="hidden" name="file" value="<?php echo e($backup['file_name']); ?>">
                                                <button type="submit" class="museum-btn-secondary text-rose-600">Delete</button>
                                            </form>
                                        </div>
                                    </td>
                                </tr>
                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                                <tr>
                                    <td colspan="4" class="px-4 py-4 text-zinc-500">No backup files yet. Generate your first backup above.</td>
                                </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </article>
        <?php elseif($activeTab === 'users-roles'): ?>
            <article class="museum-panel p-4 sm:p-5">
                <div class="flex items-start justify-between gap-3">
                    <div>
                        <h3 class="museum-section-title text-base!">◉ User Management</h3>
                        <p class="mt-1 text-sm text-zinc-600">Manage users and their access permissions</p>
                    </div>
                    <?php if(auth()->check() && auth()->user()->isAdmin()): ?>
                        <a href="<?php echo e(route('admin.users.index')); ?>" class="museum-btn text-xs">+ Add User</a>
                    <?php endif; ?>
                </div>

                <div class="mt-4 overflow-x-auto">
                    <table class="w-full min-w-245 text-sm">
                        <thead>
                            <tr class="border-b border-zinc-200 text-left text-xs font-semibold uppercase tracking-wide text-zinc-500">
                                <th class="py-2">Name</th>
                                <th class="py-2">Email</th>
                                <th class="py-2">Role</th>
                                <th class="py-2">Status</th>
                                <th class="py-2">Last Login</th>
                                <th class="py-2 text-right">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php $__empty_1 = true; $__currentLoopData = $users; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $user): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                                <?php
                                    $nameParts = preg_split('/\s+/', trim((string) $user->name)) ?: [];
                                    $initials = collect($nameParts)
                                        ->take(2)
                                        ->map(fn ($part) => mb_strtoupper(mb_substr($part, 0, 1)))
                                        ->implode('');
                                    $initials = $initials !== '' ? $initials : 'U';

                                    $rawRole = strtolower((string) optional($user->roleRelation)->slug);
                                    $roleLabel = $user->role_label;
                                    $roleClass = in_array($rawRole, ['admin', 'owner'], true)
                                        ? 'bg-zinc-900 text-white'
                                        : 'bg-zinc-100 text-zinc-700';

                                    $lastLogin = $user->updated_at
                                        ? $user->updated_at->format('Y-m-d h:i A')
                                        : '-';
                                ?>
                                <tr class="border-b border-zinc-200 last:border-b-0">
                                    <td class="py-3">
                                        <div class="flex items-center gap-2">
                                            <span class="inline-flex h-6 w-6 items-center justify-center rounded-full bg-zinc-900 text-[10px] font-semibold text-white"><?php echo e($initials); ?></span>
                                            <span class="font-medium text-zinc-900"><?php echo e($user->name); ?></span>
                                        </div>
                                    </td>
                                    <td class="py-3 text-zinc-600"><?php echo e($user->email); ?></td>
                                    <td class="py-3"><span class="rounded-md px-2 py-0.5 text-[11px] font-semibold <?php echo e($roleClass); ?>"><?php echo e($roleLabel); ?></span></td>
                                    <td class="py-3"><span class="rounded-md bg-emerald-100 px-2 py-0.5 text-[11px] font-semibold text-emerald-700">Active</span></td>
                                    <td class="py-3 text-zinc-600"><?php echo e($lastLogin); ?></td>
                                    <td class="py-3 text-right">
                                        <?php if(auth()->check() && auth()->user()->isAdmin()): ?>
                                            <a href="<?php echo e(route('admin.users.index')); ?>" class="text-zinc-700 hover:text-zinc-900">Manage</a>
                                        <?php else: ?>
                                            <span class="text-zinc-400">View</span>
                                        <?php endif; ?>
                                    </td>
                                </tr>
                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                                <tr>
                                    <td colspan="6" class="py-4 text-zinc-500">No users found.</td>
                                </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </article>

            <article class="museum-panel p-4 sm:p-5">
                <h3 class="museum-section-title text-base!">◌ Role Permissions</h3>
                <p class="mt-1 text-sm text-zinc-600">Overview of permissions by user role</p>

                <div class="mt-4 space-y-3">
                    <?php $__empty_1 = true; $__currentLoopData = $roles; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $role): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                        <?php
                            $permissions = is_array($role->permissions) ? $role->permissions : [];
                            $firstColumn = array_slice($permissions, 0, (int) ceil(max(count($permissions), 1) / 2));
                            $secondColumn = array_slice($permissions, count($firstColumn));
                            $isDarkBadge = in_array($role->slug, ['owner', 'admin'], true);
                        ?>
                        <div class="rounded-xl border border-zinc-200 bg-zinc-50 p-3">
                            <div class="mb-2 flex items-center gap-2">
                                <span class="rounded-md px-2 py-0.5 text-xs font-semibold <?php echo e($isDarkBadge ? 'bg-zinc-900 text-white' : 'bg-zinc-100 text-zinc-700'); ?>"><?php echo e($role->name); ?></span>
                                <p class="text-xs text-zinc-500"><?php echo e($role->description); ?></p>
                            </div>
                            <div class="grid gap-2 text-xs text-zinc-700 md:grid-cols-2">
                                <ul class="list-disc space-y-1 pl-4">
                                    <?php $__currentLoopData = $firstColumn; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $item): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                        <li><?php echo e($item); ?></li>
                                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                                </ul>
                                <ul class="list-disc space-y-1 pl-4">
                                    <?php $__currentLoopData = $secondColumn; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $item): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                        <li><?php echo e($item); ?></li>
                                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                                </ul>
                            </div>
                        </div>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                        <div class="rounded-xl border border-zinc-200 bg-zinc-50 p-3 text-sm text-zinc-500">No role definitions found.</div>
                    <?php endif; ?>
                </div>
            </article>
        <?php elseif($activeTab === 'notifications'): ?>
            <article class="museum-panel p-4 sm:p-5">
                <h3 class="museum-section-title text-base!">◌ Notification Preferences</h3>
                <p class="mt-1 text-sm text-zinc-600">Configure when and how you receive notifications</p>

                <form method="POST" action="<?php echo e(route('settings.update', ['section' => 'notifications'])); ?>" class="mt-5">
                    <?php echo csrf_field(); ?>
                    <div class="space-y-3">
                        <label class="flex items-center justify-between rounded-xl border border-zinc-200 bg-zinc-50 px-4 py-3 cursor-pointer">
                            <div>
                                <p class="font-semibold text-zinc-900">Movement Alerts</p>
                                <p class="text-sm text-zinc-600">Get notified when artworks are moved</p>
                            </div>
                            <input type="hidden" name="movement_alerts" value="0">
                            <input type="checkbox" name="movement_alerts" value="1" <?php if(old('movement_alerts', $notificationSettings['movement_alerts'] ? '1' : '0') === '1'): echo 'checked'; endif; ?> class="h-4 w-4 rounded border-zinc-300">
                        </label>

                        <label class="flex items-center justify-between rounded-xl border border-zinc-200 bg-zinc-50 px-4 py-3 cursor-pointer">
                            <div>
                                <p class="font-semibold text-zinc-900">Insurance Expiry Warnings</p>
                                <p class="text-sm text-zinc-600">Alert 30 days before insurance expiration</p>
                            </div>
                            <input type="hidden" name="insurance_expiry" value="0">
                            <input type="checkbox" name="insurance_expiry" value="1" <?php if(old('insurance_expiry', $notificationSettings['insurance_expiry'] ? '1' : '0') === '1'): echo 'checked'; endif; ?> class="h-4 w-4 rounded border-zinc-300">
                        </label>

                        <label class="flex items-center justify-between rounded-xl border border-zinc-200 bg-zinc-50 px-4 py-3 cursor-pointer">
                            <div>
                                <p class="font-semibold text-zinc-900">Loan Return Due</p>
                                <p class="text-sm text-zinc-600">Reminder when loaned artworks are due back</p>
                            </div>
                            <input type="hidden" name="loan_return_due" value="0">
                            <input type="checkbox" name="loan_return_due" value="1" <?php if(old('loan_return_due', $notificationSettings['loan_return_due'] ? '1' : '0') === '1'): echo 'checked'; endif; ?> class="h-4 w-4 rounded border-zinc-300">
                        </label>

                        <label class="flex items-center justify-between rounded-xl border border-zinc-200 bg-zinc-50 px-4 py-3 cursor-pointer">
                            <div>
                                <p class="font-semibold text-zinc-900">Restoration Due</p>
                                <p class="text-sm text-zinc-600">Notifications for scheduled restoration work</p>
                            </div>
                            <input type="hidden" name="restoration_due" value="0">
                            <input type="checkbox" name="restoration_due" value="1" <?php if(old('restoration_due', $notificationSettings['restoration_due'] ? '1' : '0') === '1'): echo 'checked'; endif; ?> class="h-4 w-4 rounded border-zinc-300">
                        </label>

                        <label class="flex items-center justify-between rounded-xl border border-zinc-200 bg-zinc-50 px-4 py-3 cursor-pointer">
                            <div>
                                <p class="font-semibold text-zinc-900">Valuation Updates</p>
                                <p class="text-sm text-zinc-600">Alert when artwork valuations change significantly</p>
                            </div>
                            <input type="hidden" name="valuation_updates" value="0">
                            <input type="checkbox" name="valuation_updates" value="1" <?php if(old('valuation_updates', $notificationSettings['valuation_updates'] ? '1' : '0') === '1'): echo 'checked'; endif; ?> class="h-4 w-4 rounded border-zinc-300">
                        </label>
                    </div>

                    <div class="mt-4 border-t border-zinc-200 pt-4">
                        <p class="font-medium text-zinc-800">Notification Delivery</p>
                        <div class="mt-3 space-y-2 text-sm">
                            <label class="inline-flex items-center gap-2 text-zinc-700">
                                <input type="hidden" name="delivery_email" value="0">
                                <input type="checkbox" name="delivery_email" value="1" <?php if(old('delivery_email', $notificationSettings['delivery_email'] ? '1' : '0') === '1'): echo 'checked'; endif; ?> class="rounded border-zinc-300">
                                <span>Email Notifications</span>
                            </label>
                            <label class="inline-flex items-center gap-2 text-zinc-700">
                                <input type="hidden" name="delivery_browser" value="0">
                                <input type="checkbox" name="delivery_browser" value="1" <?php if(old('delivery_browser', $notificationSettings['delivery_browser'] ? '1' : '0') === '1'): echo 'checked'; endif; ?> class="rounded border-zinc-300">
                                <span>Browser Notifications</span>
                            </label>
                        </div>
                        <button type="submit" class="mt-4 museum-btn">Save Preferences</button>
                    </div>
                </form>
            </article>
        <?php elseif($activeTab === 'appearance'): ?>
            <article class="museum-panel p-4 sm:p-5">
                <h3 class="museum-section-title text-base!">◔ Display Preferences</h3>
                <p class="mt-1 text-sm text-zinc-600">Customize the look and feel of the application</p>

                <form method="POST" action="<?php echo e(route('settings.update', ['section' => 'appearance'])); ?>" class="mt-5 space-y-4">
                    <?php echo csrf_field(); ?>
                    <div>
                        <p class="font-semibold text-zinc-800">Theme</p>
                        <select name="theme" class="mt-2 w-full rounded-xl border border-zinc-300 bg-white px-4 py-2.5">
                            <option value="light" <?php if(old('theme', $appearanceSettings['theme']) === 'light'): echo 'selected'; endif; ?>>Light Mode</option>
                            <option value="dark" <?php if(old('theme', $appearanceSettings['theme']) === 'dark'): echo 'selected'; endif; ?>>Dark Mode</option>
                        </select>
                        <p class="mt-2 text-sm text-zinc-500">Dark mode variant available for reduced eye strain</p>
                    </div>

                    <div>
                        <p class="font-semibold text-zinc-800">Display Density</p>
                        <select name="density" class="mt-2 w-full rounded-xl border border-zinc-300 bg-white px-4 py-2.5">
                            <option value="comfortable" <?php if(old('density', $appearanceSettings['density']) === 'comfortable'): echo 'selected'; endif; ?>>Comfortable</option>
                            <option value="compact" <?php if(old('density', $appearanceSettings['density']) === 'compact'): echo 'selected'; endif; ?>>Compact</option>
                            <option value="spacious" <?php if(old('density', $appearanceSettings['density']) === 'spacious'): echo 'selected'; endif; ?>>Spacious</option>
                        </select>
                    </div>

                    <div>
                        <p class="font-semibold text-zinc-800">Typography</p>
                        <div class="mt-2 space-y-2">
                            <div class="flex items-center justify-between rounded-lg bg-zinc-100 px-3 py-2 text-sm">
                                <span class="text-zinc-600">Headings & Titles</span>
                                <span class="text-zinc-500">Cormorant Garamond</span>
                            </div>
                            <div class="flex items-center justify-between rounded-lg bg-zinc-100 px-3 py-2 text-sm">
                                <span class="text-zinc-600">Body Text</span>
                                <span class="text-zinc-500">Inter</span>
                            </div>
                        </div>
                    </div>

                    <div class="border-t border-zinc-200 pt-4">
                        <p class="font-semibold text-zinc-800">Accent Color</p>
                        <div class="mt-3 inline-flex items-center gap-3 rounded-xl border border-zinc-200 bg-zinc-50 px-3 py-2">
                            <span class="h-9 w-9 rounded-lg bg-[#1c1917]"></span>
                            <div>
                                <p class="font-semibold text-zinc-900">Dark Luxury</p>
                                <p class="text-xs text-zinc-500">#1c1917</p>
                            </div>
                        </div>
                    </div>

                    <button type="submit" class="mt-2 museum-btn">Save Preferences</button>
                </form>
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
<?php /**PATH /Users/syed/MEGA/WORK/RELIVA/Website/LEBTECH/museum-azman/resources/views/settings/index.blade.php ENDPATH**/ ?>