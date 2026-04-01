<?php if (isset($component)) { $__componentOriginal23a33f287873b564aaf305a1526eada4 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal23a33f287873b564aaf305a1526eada4 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.layout','data' => ['title' => 'Activity Logs - Museum Azman']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('layout'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['title' => 'Activity Logs - Museum Azman']); ?>
    <section class="space-y-6">
        <div class="flex items-start justify-between gap-4">
            <div>
                <h2 class="museum-page-title">Activity Logs</h2>
                <p class="museum-page-subtitle">All user and admin actions, newest first</p>
            </div>
        </div>

        
        <form method="GET" action="<?php echo e(route('admin.activity-logs.index')); ?>" class="museum-panel p-4 flex flex-wrap items-end gap-3">
            <label class="museum-field flex-1 min-w-48">
                <span>Search</span>
                <input type="text" name="q" value="<?php echo e($search); ?>" placeholder="User, action, description…">
            </label>
            <label class="museum-field min-w-48">
                <span>Action</span>
                <select name="action">
                    <option value="">All actions</option>
                    <?php $__currentLoopData = $actionOptions; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $opt): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                        <option value="<?php echo e($opt); ?>" <?php if($action === $opt): echo 'selected'; endif; ?>><?php echo e($opt); ?></option>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                </select>
            </label>
            <div class="flex gap-2">
                <button type="submit" class="museum-btn">Filter</button>
                <?php if($search || $action || $userId): ?>
                    <a href="<?php echo e(route('admin.activity-logs.index')); ?>" class="museum-btn-secondary">Clear</a>
                <?php endif; ?>
            </div>
        </form>

        <?php if($logs->isEmpty()): ?>
            <div class="museum-panel p-8 text-center text-zinc-500">No activity logs found.</div>
        <?php else: ?>
            <article class="museum-panel p-0! overflow-hidden">
                <div class="overflow-x-auto">
                    <table class="w-full min-w-200 text-sm">
                        <thead>
                            <tr class="border-b border-zinc-200 text-left text-zinc-500 text-xs uppercase tracking-wide">
                                <th class="px-4 py-3 w-40">When</th>
                                <th class="px-4 py-3 w-36">User</th>
                                <th class="px-4 py-3 w-24">Role</th>
                                <th class="px-4 py-3 w-44">Action</th>
                                <th class="px-4 py-3">Description</th>
                                <th class="px-4 py-3 w-32">IP</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-zinc-100">
                            <?php $__currentLoopData = $logs; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $log): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                <tr class="hover:bg-zinc-50 transition-colors">
                                    <td class="px-4 py-3 text-zinc-500 whitespace-nowrap" title="<?php echo e($log->created_at->toDateTimeString()); ?>">
                                        <?php echo e($log->created_at->diffForHumans()); ?>

                                    </td>
                                    <td class="px-4 py-3 font-medium text-zinc-800">
                                        <?php echo e($log->user_name ?? '—'); ?>

                                        <?php if($log->user_id): ?>
                                            <a href="<?php echo e(route('admin.users.edit', $log->user_id)); ?>" class="ml-1 text-xs text-zinc-400 hover:underline">#<?php echo e($log->user_id); ?></a>
                                        <?php endif; ?>
                                    </td>
                                    <td class="px-4 py-3">
                                        <?php if($log->user_role): ?>
                                            <span class="inline-block rounded-full px-2 py-0.5 text-xs font-medium
                                                <?php echo e($log->user_role === 'admin' ? 'bg-violet-100 text-violet-700' : 'bg-zinc-100 text-zinc-600'); ?>">
                                                <?php echo e($log->user_role); ?>

                                            </span>
                                        <?php else: ?>
                                            <span class="text-zinc-400">—</span>
                                        <?php endif; ?>
                                    </td>
                                    <td class="px-4 py-3">
                                        <?php
                                            $actionBadge = match(true) {
                                                str_starts_with($log->action, 'auth.') => 'bg-blue-50 text-blue-700',
                                                str_starts_with($log->action, 'artwork.') => 'bg-emerald-50 text-emerald-700',
                                                str_starts_with($log->action, 'movement.') => 'bg-amber-50 text-amber-700',
                                                str_starts_with($log->action, 'location.') => 'bg-rose-50 text-rose-700',
                                                str_starts_with($log->action, 'profile.') => 'bg-cyan-50 text-cyan-700',
                                                str_starts_with($log->action, 'admin.') => 'bg-violet-50 text-violet-700',
                                                default => 'bg-zinc-100 text-zinc-600',
                                            };
                                        ?>
                                        <span class="inline-block rounded-full px-2 py-0.5 text-xs font-mono font-medium <?php echo e($actionBadge); ?>">
                                            <?php echo e($log->action); ?>

                                        </span>
                                    </td>
                                    <td class="px-4 py-3 text-zinc-700">
                                        <?php echo e($log->description); ?>

                                        <?php if($log->subject_label && $log->subject_type): ?>
                                            <span class="ml-1 text-xs text-zinc-400">[<?php echo e($log->subject_type); ?>]</span>
                                        <?php endif; ?>
                                    </td>
                                    <td class="px-4 py-3 text-zinc-400 font-mono text-xs">
                                        <?php echo e($log->ip_address ?? '—'); ?>

                                    </td>
                                </tr>
                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                        </tbody>
                    </table>
                </div>
            </article>

            <div class="flex justify-between items-center text-sm text-zinc-500">
                <span><?php echo e(number_format($logs->total())); ?> total entries</span>
                <?php echo e($logs->links()); ?>

            </div>
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
<?php /**PATH /Users/syed/MEGA/WORK/RELIVA/Website/LEBTECH/museum-azman/resources/views/admin/activity-logs/index.blade.php ENDPATH**/ ?>