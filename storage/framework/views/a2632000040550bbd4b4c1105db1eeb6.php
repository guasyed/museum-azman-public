<?php if (isset($component)) { $__componentOriginal23a33f287873b564aaf305a1526eada4 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal23a33f287873b564aaf305a1526eada4 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.layout','data' => ['title' => ''.e($location->name).' - Location']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('layout'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['title' => ''.e($location->name).' - Location']); ?>
    <section class="space-y-6">
        <div class="flex items-start justify-between gap-4">
            <div>
                <h2 class="museum-page-title"><?php echo e($location->name); ?></h2>
                <p class="museum-page-subtitle">Location details and stored artworks</p>
            </div>
            <div class="flex items-center gap-2">
                <a href="<?php echo e(route('locations.index')); ?>" class="museum-btn-secondary">Back to Locations</a>
                <?php if(auth()->check() && auth()->user()->isAdmin()): ?>
                    <a href="<?php echo e(route('locations.edit', $location)); ?>" class="museum-btn">Edit Location</a>
                <?php endif; ?>
            </div>
        </div>

        <div class="grid gap-4 md:grid-cols-3">
            <div class="museum-stat-card">
                <p>Type</p>
                <strong><?php echo e($location->display_type); ?></strong>
            </div>
            <div class="museum-stat-card">
                <p>Artworks Stored</p>
                <strong><?php echo e(number_format($location->artworks_count ?? $location->artworks->count())); ?></strong>
            </div>
            <div class="museum-stat-card">
                <p>Insured Value</p>
                <strong><?php echo e(\App\Support\Currency::symbol()); ?><?php echo e(number_format($location->insured_value, 0)); ?></strong>
            </div>
        </div>

        <article class="museum-panel">
            <h3 class="museum-section-title">Location Info</h3>
            <div class="mt-4 grid gap-3 md:grid-cols-2">
                <div class="museum-detail">
                    <span>Address</span>
                    <strong><?php echo e($location->address ?: 'Address not set'); ?></strong>
                </div>
                <div class="museum-detail">
                    <span>Last Audit</span>
                    <strong><?php echo e(optional($location->last_audit_date)->format('d/m/Y') ?: '-'); ?></strong>
                </div>
            </div>

            <?php if($location->map_embed_url): ?>
                <div class="mt-4">
                    <div class="mb-2 flex items-center justify-between gap-2">
                        <p class="text-sm font-semibold text-zinc-700">Map Location</p>
                        <a href="<?php echo e($location->map_url); ?>" target="_blank" rel="noopener noreferrer" class="museum-btn-secondary">Open in Maps</a>
                    </div>
                    <iframe
                        src="<?php echo e($location->map_embed_url); ?>"
                        title="Map of <?php echo e($location->name); ?>"
                        class="h-72 w-full rounded-xl border border-zinc-200"
                        loading="lazy"
                        referrerpolicy="no-referrer-when-downgrade"
                    ></iframe>
                </div>
            <?php endif; ?>
        </article>

        <article class="museum-panel">
            <h3 class="museum-section-title">Stored Artworks</h3>

            <?php if($location->artworks->isEmpty()): ?>
                <p class="mt-3 text-zinc-500">No artworks currently linked to this location.</p>
            <?php else: ?>
                <div class="mt-4 overflow-x-auto">
                    <table class="w-full min-w-220 text-sm">
                        <thead>
                            <tr class="border-b border-zinc-200 text-left font-semibold text-zinc-800">
                                <th class="px-3 py-2.5">Title</th>
                                <th class="px-3 py-2.5">Artist</th>
                                <th class="px-3 py-2.5 text-right">Current Valuation</th>
                                <th class="px-3 py-2.5 text-center">Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php $__currentLoopData = $location->artworks; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $artwork): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                <tr class="border-b border-zinc-200">
                                    <td class="px-3 py-2.5"><?php echo e($artwork->title); ?></td>
                                    <td class="px-3 py-2.5 text-zinc-600"><?php echo e($artwork->artist?->name ?? 'Unknown Artist'); ?></td>
                                    <td class="px-3 py-2.5 text-right font-semibold"><?php echo e(\App\Support\Currency::symbol()); ?><?php echo e(number_format((float) $artwork->current_valuation, 0)); ?></td>
                                    <td class="px-3 py-2.5 text-center">
                                        <a href="<?php echo e(route('artworks.show', $artwork)); ?>" class="museum-btn-secondary">Open</a>
                                    </td>
                                </tr>
                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                        </tbody>
                    </table>
                </div>
            <?php endif; ?>
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
<?php /**PATH /Users/syed/MEGA/WORK/RELIVA/Website/LEBTECH/museum-azman/resources/views/locations/show.blade.php ENDPATH**/ ?>