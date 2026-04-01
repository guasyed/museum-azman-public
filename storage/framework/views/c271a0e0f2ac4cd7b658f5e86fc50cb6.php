<?php if (isset($component)) { $__componentOriginal23a33f287873b564aaf305a1526eada4 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal23a33f287873b564aaf305a1526eada4 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.layout','data' => ['title' => 'Locations - Museum Azman']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('layout'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['title' => 'Locations - Museum Azman']); ?>
    <section class="space-y-6">
        <div class="flex items-start justify-between gap-4">
            <div>
                <h2 class="museum-page-title">Locations</h2>
                <p class="museum-page-subtitle">Manage storage facilities, galleries, and collection venues</p>
            </div>
            <?php if(auth()->check() && auth()->user()->isAdmin()): ?>
                <a href="<?php echo e(route('locations.create')); ?>" class="museum-btn">+ Add Location</a>
            <?php endif; ?>
        </div>

        <div class="grid gap-4 md:grid-cols-3">
            <div class="museum-stat-card">
                <p>Total Locations</p>
                <strong><?php echo e(number_format($stats['total_locations'])); ?></strong>
            </div>
            <div class="museum-stat-card">
                <p>Artworks Stored</p>
                <strong><?php echo e(number_format($stats['artworks_stored'])); ?></strong>
            </div>
            <div class="museum-stat-card">
                <p>Total Insured Value</p>
                <strong><?php echo e(\App\Support\Currency::symbol()); ?><?php echo e(number_format($stats['insured_value'] / 1000000, 2)); ?>M</strong>
            </div>
        </div>

        <form method="GET" class="flex flex-col gap-3 lg:flex-row lg:items-center lg:justify-between">
            <input type="hidden" name="view" value="<?php echo e($view); ?>">
            <input type="hidden" name="sort" value="<?php echo e($sortColumn ?? 'name'); ?>">
            <input type="hidden" name="direction" value="<?php echo e($direction ?? 'asc'); ?>">
            <div class="flex-1">
                <input
                    type="text"
                    name="q"
                    value="<?php echo e($q); ?>"
                    placeholder="Search by name or address..."
                    class="w-full rounded-xl border border-zinc-300 bg-[#f7f7f6] px-4 py-2.5"
                >
            </div>

            <div class="flex items-center gap-2">
                <select name="type" class="min-w-55 rounded-xl border border-zinc-300 bg-[#f7f7f6] px-4 py-2.5" onchange="this.form.submit()">
                    <?php $__currentLoopData = $typeOptions; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $type): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                        <option value="<?php echo e($type === 'All Types' ? '' : $type); ?>" <?php if(($selectedType === '' && $type === 'All Types') || $selectedType === $type): echo 'selected'; endif; ?>>
                            <?php echo e($type); ?>

                        </option>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                </select>
                <div class="flex gap-2">

                    
                    <a
                        href="<?php echo e(route('locations.index', [
                            'q' => $q,
                            'type' => $selectedType,
                            'view' => 'grid',
                            'sort' => $sortColumn ?? 'name',
                            'direction' => $direction ?? 'asc',
                        ])); ?>"
                        class="inline-flex items-center justify-center size-9 rounded-md transition-colors
                        <?php echo e($view === 'grid'
                            ? 'text-white border'
                            : 'border bg-background text-foreground hover:bg-accent hover:text-accent-foreground'); ?>"
                        style="<?php echo e($view === 'grid' ? 'background: var(--museum-accent); border-color: var(--museum-accent);' : ''); ?>"
                        title="Grid View"
                    >
                        <svg class="size-4"
                            fill="none" stroke="currentColor" stroke-width="2"
                            viewBox="0 0 24 24" stroke-linecap="round" stroke-linejoin="round">
                            <rect width="7" height="7" x="3" y="3" rx="1"></rect>
                            <rect width="7" height="7" x="14" y="3" rx="1"></rect>
                            <rect width="7" height="7" x="14" y="14" rx="1"></rect>
                            <rect width="7" height="7" x="3" y="14" rx="1"></rect>
                        </svg>
                    </a>

                    
                    <a
                        href="<?php echo e(route('locations.index', [
                            'q' => $q,
                            'type' => $selectedType,
                            'view' => 'list',
                            'sort' => $sortColumn ?? 'name',
                            'direction' => $direction ?? 'asc',
                        ])); ?>"
                        class="inline-flex items-center justify-center size-9 rounded-md transition-colors
                        <?php echo e($view === 'list'
                            ? 'text-white border'
                            : 'border bg-background text-foreground hover:bg-accent hover:text-accent-foreground'); ?>"
                        style="<?php echo e($view === 'list' ? 'background: var(--museum-accent); border-color: var(--museum-accent);' : ''); ?>"
                        title="List View"
                    >
                        <svg class="size-4"
                            fill="none" stroke="currentColor" stroke-width="2"
                            viewBox="0 0 24 24" stroke-linecap="round" stroke-linejoin="round">
                            <line x1="8" x2="21" y1="6" y2="6"></line>
                            <line x1="8" x2="21" y1="12" y2="12"></line>
                            <line x1="8" x2="21" y1="18" y2="18"></line>
                            <line x1="3" x2="3.01" y1="6" y2="6"></line>
                            <line x1="3" x2="3.01" y1="12" y2="12"></line>
                            <line x1="3" x2="3.01" y1="18" y2="18"></line>
                        </svg>
                    </a>

                </div>
            </div>
        </form>

        <?php if($view === 'list'): ?>
            <article class="museum-panel p-0! overflow-hidden">
                <div class="overflow-x-auto">
                    <table class="w-full min-w-295 text-sm">
                        <thead>
                            <tr class="border-b border-zinc-200 text-left text-sm font-semibold text-zinc-800">
                                <th class="px-4 py-3">
                                    <?php
                                        $isNameSort = ($sortColumn ?? 'name') === 'name';
                                        $nextNameDirection = $isNameSort && ($direction ?? 'asc') === 'asc' ? 'desc' : 'asc';
                                    ?>
                                    <a
                                        href="<?php echo e(route('locations.index', array_merge(request()->query(), ['sort' => 'name', 'direction' => $nextNameDirection]))); ?>"
                                        class="inline-flex items-center gap-1 hover:text-zinc-900"
                                    >
                                        <span>Location</span>
                                        <?php if($isNameSort): ?>
                                            <span class="text-xs"><?php echo e(($direction ?? 'asc') === 'asc' ? '▲' : '▼'); ?></span>
                                        <?php endif; ?>
                                    </a>
                                </th>
                                <th class="px-4 py-3">
                                    <?php
                                        $isTypeSort = ($sortColumn ?? 'name') === 'type';
                                        $nextTypeDirection = $isTypeSort && ($direction ?? 'asc') === 'asc' ? 'desc' : 'asc';
                                    ?>
                                    <a
                                        href="<?php echo e(route('locations.index', array_merge(request()->query(), ['sort' => 'type', 'direction' => $nextTypeDirection]))); ?>"
                                        class="inline-flex items-center gap-1 hover:text-zinc-900"
                                    >
                                        <span>Type</span>
                                        <?php if($isTypeSort): ?>
                                            <span class="text-xs"><?php echo e(($direction ?? 'asc') === 'asc' ? '▲' : '▼'); ?></span>
                                        <?php endif; ?>
                                    </a>
                                </th>
                                <th class="px-4 py-3">Address</th>
                                <th class="px-4 py-3 text-right">Artworks</th>
                                <th class="px-4 py-3 text-right">Insured Value</th>
                                <th class="px-4 py-3">Last Audit</th>
                                <th class="px-4 py-3 text-center">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                        <?php $__empty_1 = true; $__currentLoopData = $locations; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $location): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                            <tr class="border-b border-zinc-200 text-sm">
                                <td class="px-4 py-3.5">
                                    <div class="flex items-center gap-3">
                                        <div class="flex h-10 w-10 items-center justify-center rounded-xl text-white" style="background: var(--museum-accent);">
                                            <svg class="size-5" fill="none" stroke="currentColor" stroke-width="1.9" viewBox="0 0 24 24" stroke-linecap="round" stroke-linejoin="round">
                                                <path d="M3 9.5L12 3l9 6.5"></path>
                                                <path d="M5.5 10.5V20h13V10.5"></path>
                                                <path d="M9 20v-5h6v5"></path>
                                            </svg>
                                        </div>
                                        <p class="text-base font-semibold text-zinc-900"><?php echo e($location->name); ?></p>
                                    </div>
                                </td>
                                <td class="px-4 py-3.5">
                                    <span class="inline-flex rounded-xl px-3 py-1 text-xs font-semibold <?php echo e($location->type_badge_class); ?>">
                                        <?php echo e($location->display_type); ?>

                                    </span>
                                </td>
                                <td class="px-4 py-3.5 text-zinc-600"><?php echo e($location->address ?: '-'); ?></td>
                                <td class="px-4 py-3.5 text-right font-semibold"><?php echo e($location->artworks_count); ?></td>
                                <td class="px-4 py-3.5 text-right font-semibold"><?php echo e(\App\Support\Currency::symbol()); ?><?php echo e(number_format($location->insured_value, 0)); ?></td>
                                <td class="px-4 py-3.5 text-zinc-600"><?php echo e(\App\Support\DateFormat::display($location->last_audit_date)); ?></td>
                                <td class="px-4 py-3.5">
                                    <div class="flex items-center justify-center gap-3 text-zinc-700">
                                        <?php if($location->map_url): ?>
                                            <a href="<?php echo e($location->map_url); ?>" target="_blank" rel="noopener noreferrer" title="Open Map" class="rounded-md p-1.5 hover:bg-zinc-100">
                                                <svg class="size-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" stroke-linecap="round" stroke-linejoin="round">
                                                    <path d="M3 6l6-2 6 2 6-2v14l-6 2-6-2-6 2z"></path>
                                                    <path d="M9 4v14"></path>
                                                    <path d="M15 6v14"></path>
                                                </svg>
                                            </a>
                                        <?php endif; ?>
                                        <a href="<?php echo e(route('locations.show', $location)); ?>" title="View" class="rounded-md p-1.5 hover:bg-zinc-100">
                                            <svg class="size-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" stroke-linecap="round" stroke-linejoin="round">
                                                <path d="M2 12s3.8-7 10-7 10 7 10 7-3.8 7-10 7-10-7-10-7z"></path>
                                                <circle cx="12" cy="12" r="3"></circle>
                                            </svg>
                                        </a>
                                        <?php if(auth()->check() && auth()->user()->isAdmin()): ?>
                                            <a href="<?php echo e(route('locations.edit', $location)); ?>" title="Edit" class="rounded-md p-1.5 hover:bg-zinc-100">
                                                <svg class="size-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" stroke-linecap="round" stroke-linejoin="round">
                                                    <path d="M12 20h9"></path>
                                                    <path d="M16.5 3.5a2.1 2.1 0 1 1 3 3L7 19l-4 1 1-4 12.5-12.5z"></path>
                                                </svg>
                                            </a>
                                        <?php endif; ?>
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                            <tr><td colspan="7" class="px-4 py-4 text-zinc-500">No locations found for this filter.</td></tr>
                        <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </article>
        <?php else: ?>
            <div class="grid gap-4 xl:grid-cols-3 md:grid-cols-2">
                <?php $__empty_1 = true; $__currentLoopData = $locations; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $location): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                    <article class="museum-panel p-5">
                        <div class="flex items-start gap-3">
                            <div class="flex h-10 w-10 items-center justify-center rounded-xl text-white" style="background: var(--museum-accent);">⌂</div>
                            <div>
                                <h3 class="museum-card-title"><?php echo e($location->name); ?></h3>
                                <span class="mt-2 inline-flex rounded-lg px-2.5 py-1 text-xs font-semibold <?php echo e($location->type_badge_class); ?>"><?php echo e($location->display_type); ?></span>
                            </div>
                        </div>

                        <p class="mt-4 border-t border-zinc-200 pt-4 text-sm text-zinc-600"><?php echo e($location->address ?: 'Address not set'); ?></p>

                        <div class="mt-3 grid grid-cols-2 gap-3 border-t border-zinc-200 pt-3">
                            <div>
                                <p class="text-sm text-zinc-500">Artworks</p>
                                <p class="text-sm font-semibold"><?php echo e($location->artworks_count); ?></p>
                            </div>
                            <div>
                                <p class="text-sm text-zinc-500">Insured Value</p>
                                <p class="text-sm font-semibold"><?php echo e(\App\Support\Currency::symbol()); ?><?php echo e(number_format($location->insured_value / 1000, 0)); ?>K</p>
                            </div>
                        </div>

                        <div class="mt-3 border-t border-zinc-200 pt-3 text-sm text-zinc-500">
                            Last audit: <?php echo e(\App\Support\DateFormat::display($location->last_audit_date)); ?>

                        </div>

                        <div class="mt-3 flex items-center gap-2">
                            <a href="<?php echo e(route('locations.show', $location)); ?>" class="museum-btn-secondary flex-1 text-center" style="border-color: color-mix(in srgb, var(--museum-accent) 45%, white); color: var(--museum-accent);">View Details</a>
                            <?php if($location->map_url): ?>
                                <a href="<?php echo e($location->map_url); ?>" target="_blank" rel="noopener noreferrer" class="museum-btn-secondary" style="border-color: color-mix(in srgb, var(--museum-accent) 45%, white); color: var(--museum-accent);">Map</a>
                            <?php endif; ?>
                            <?php if(auth()->check() && auth()->user()->isAdmin()): ?>
                                <a href="<?php echo e(route('locations.edit', $location)); ?>" class="museum-btn-secondary" style="border-color: color-mix(in srgb, var(--museum-accent) 45%, white); color: var(--museum-accent);">✎</a>
                            <?php endif; ?>
                        </div>
                    </article>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                    <div class="xl:col-span-3 md:col-span-2 museum-panel">
                        <p class="text-zinc-500">No locations found for this filter.</p>
                    </div>
                <?php endif; ?>
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
<?php /**PATH /Users/syed/MEGA/WORK/RELIVA/Website/LEBTECH/museum-azman/resources/views/locations/index.blade.php ENDPATH**/ ?>