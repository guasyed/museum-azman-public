<?php if (isset($component)) { $__componentOriginal23a33f287873b564aaf305a1526eada4 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal23a33f287873b564aaf305a1526eada4 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.layout','data' => ['title' => $artwork->title.' - Museum Azman']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('layout'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['title' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($artwork->title.' - Museum Azman')]); ?>
    <?php
        $statusClass = match (strtolower((string) ($artwork->status ?? ''))) {
            'on display' => 'bg-emerald-100 text-emerald-700',
            'in stage', 'in storage' => 'bg-blue-100 text-blue-700',
            'on loan' => 'bg-violet-100 text-violet-700',
            'under restoration', 'in transit' => 'bg-amber-100 text-amber-700',
            default => 'bg-zinc-100 text-zinc-700',
        };

        $artworkUrl = route('artworks.show', $artwork);
        $qrCodeUrl = 'https://api.qrserver.com/v1/create-qr-code/?size=160x160&data='.rawurlencode($artworkUrl);
        $origin = request()->string('from')->toString() === 'dashboard' ? 'dashboard' : 'collection';
        $returnUrl = request()->query('return');
        $returnPath = is_string($returnUrl) ? parse_url($returnUrl, PHP_URL_PATH) : null;
        $isSafeReturnUrl = is_string($returnUrl)
            && $returnUrl !== ''
            && parse_url($returnUrl, PHP_URL_HOST) === request()->getHost()
            && is_string($returnPath)
            && str_starts_with($returnPath, '/');
        $backRoute = $isSafeReturnUrl ? $returnUrl : ($origin === 'dashboard' ? route('dashboard') : route('artworks.index'));
        $backLabel = $origin === 'dashboard' ? 'Back to Dashboard' : 'Back to Collection';
        $selfRoute = route('artworks.show', [
            'artwork' => $artwork,
            'from' => $origin,
            'return' => $isSafeReturnUrl ? $returnUrl : null,
        ]);
    ?>

    <section class="space-y-4">
        <div class="flex items-center justify-between gap-3">
            <a href="<?php echo e($backRoute); ?>" class="inline-flex items-center gap-2 text-sm font-semibold text-zinc-700 hover:text-zinc-900">
                <span>←</span>
                <span><?php echo e($backLabel); ?></span>
            </a>

            <a href="#record-movement" class="museum-btn text-xs">+ Record Movement</a>
        </div>

        <div class="grid gap-4 xl:grid-cols-[1.55fr_0.78fr]">
            <div class="space-y-4">
                <article class="museum-panel overflow-hidden p-0">
                    <?php if($artwork->primary_image_url): ?>
                        <img
                            src="<?php echo e($artwork->primary_image_url); ?>"
                            alt="<?php echo e($artwork->title); ?>"
                            class="h-130 w-full object-cover"
                            onerror="this.classList.add('hidden'); this.nextElementSibling.classList.remove('hidden');"
                        >
                        <div class="hidden h-130 flex items-center justify-center bg-zinc-100 text-zinc-500">No Image</div>
                    <?php else: ?>
                        <div class="flex h-130 items-center justify-center bg-zinc-100 text-zinc-500">No Image</div>
                    <?php endif; ?>

                    <div class="space-y-5 p-5">
                        <div class="flex items-end justify-between gap-3 border-b border-zinc-200 pb-3">
                            <div>
                                <h2 class="museum-page-title text-[2rem]!"><?php echo e($artwork->title); ?></h2>
                                <p class="mt-1 text-zinc-600"><?php echo e($artwork->artist?->name ?? 'Unknown Artist'); ?></p>
                            </div>
                            <span class="inline-flex rounded-lg px-2.5 py-1 text-xs font-semibold <?php echo e($statusClass); ?>"><?php echo e($artwork->status ?: 'Unknown'); ?></span>
                        </div>

                        <div class="grid gap-x-8 gap-y-3 border-b border-zinc-200 pb-4 text-sm md:grid-cols-2">
                            <div><p class="text-zinc-500">Year</p><p class="font-medium"><?php echo e($artwork->year ?: '-'); ?></p></div>
                            <div><p class="text-zinc-500">Medium</p><p class="font-medium"><?php echo e($artwork->medium ?: '-'); ?></p></div>
                            <div><p class="text-zinc-500">Dimensions</p><p class="font-medium"><?php echo e($artwork->size_from_cm ?: '-'); ?> × <?php echo e($artwork->size_to_cm ?: '-'); ?> cm</p></div>
                            <div><p class="text-zinc-500">Country of Origin</p><p class="font-medium"><?php echo e($artwork->artist?->country ?: 'Malaysia'); ?></p></div>
                            <div><p class="text-zinc-500">Region</p><p class="font-medium"><?php echo e($artwork->artist?->country ?: '-'); ?></p></div>
                            <div><p class="text-zinc-500">Acquisition Date</p><p class="font-medium"><?php echo e(\App\Support\DateFormat::display($artwork->acquisition_date)); ?></p></div>
                        </div>

                        <div class="space-y-2 border-b border-zinc-200 pb-4 text-sm">
                            <p class="text-zinc-500">Description</p>
                            <p class="text-zinc-700"><?php echo e($artwork->description ?: '-'); ?></p>
                        </div>

                        <div class="rounded-xl bg-zinc-100 px-4 py-3 text-sm">
                            <p class="mb-1 text-zinc-500">Current Location</p>
                            <p class="font-semibold text-zinc-900"><?php echo e($artwork->location?->name ?: 'Unknown Location'); ?></p>
                        </div>

                        <div class="space-y-2 text-sm">
                            <p class="text-zinc-500">Provenance</p>
                            <p class="text-zinc-700"><?php echo e($artwork->provenance ?: '-'); ?></p>
                        </div>
                    </div>
                </article>

                <article class="museum-panel p-0 overflow-hidden">
                    <div class="grid grid-cols-2 border-b border-zinc-200 bg-zinc-50 text-center text-xs font-semibold text-zinc-700">
                        <div class="px-4 py-2">Movement History</div>
                        <div class="px-4 py-2">Documentation</div>
                    </div>

                    <div class="p-4 text-sm">
                        <?php $__empty_1 = true; $__currentLoopData = $artwork->movements->take(6); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $movement): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                            <div class="border-b border-zinc-200 py-3.5 last:border-b-0">
                                <div class="mb-2 flex items-center justify-between gap-2">
                                    <p class="text-[0.8rem] font-semibold leading-none text-zinc-900"><?php echo e(\App\Support\DateFormat::display($movement->date_out)); ?></p>
                                    <span class="inline-flex rounded-xl bg-zinc-900 px-3 py-1 text-[0.8rem] font-semibold text-white"><?php echo e($movement->status ?: 'Completed'); ?></span>
                                </div>

                                <div class="grid gap-4 md:grid-cols-2">
                                    <div class="space-y-1.5 text-[0.8rem] text-zinc-700">
                                        <p><span class="text-zinc-500">From:</span> <?php echo e($movement->from_location ?: '-'); ?></p>
                                        <p><span class="text-zinc-500">To:</span> <?php echo e($movement->to_location ?: '-'); ?></p>
                                        <p><span class="text-zinc-500">Responsible Handler:</span> <?php echo e($movement->responsible_handler ?: '-'); ?></p>
                                        <p><span class="text-zinc-500">Reason:</span> <?php echo e($movement->reason ?: '-'); ?></p>
                                        <p><span class="text-zinc-500">Expected Return:</span> <?php echo e(\App\Support\DateFormat::display($movement->expected_return_date)); ?></p>
                                    </div>

                                    <div class="space-y-2 rounded-lg border border-zinc-200 bg-zinc-50 px-3 py-2.5">
                                        <p class="text-[11px] font-semibold uppercase tracking-wide text-zinc-500">Documentation</p>
                                        <?php if($movement->notes): ?>
                                            <p class="text-sm text-zinc-700"><span class="text-zinc-500">Notes:</span> <?php echo e($movement->notes); ?></p>
                                        <?php else: ?>
                                            <p class="text-sm text-zinc-500">Notes: -</p>
                                        <?php endif; ?>

                                        <?php if($movement->condition_report): ?>
                                            <p class="text-sm text-zinc-700"><span class="text-zinc-500">Condition:</span> <?php echo e($movement->condition_report); ?></p>
                                        <?php else: ?>
                                            <p class="text-sm text-zinc-500">Condition: -</p>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            </div>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                            <p class="text-zinc-500">No movement history yet.</p>
                        <?php endif; ?>
                    </div>
                </article>
            </div>

            <aside class="space-y-4">
                <article class="museum-panel">
                    <h3 class="museum-section-title text-base!">Financial Summary</h3>
                    <div class="mt-4 space-y-3 text-sm">
                        <div>
                            <p class="text-zinc-500">Purchase Price</p>
                            <p class="font-semibold text-zinc-900"><?php echo e(\App\Support\Currency::symbol()); ?><?php echo e(number_format((float) $artwork->acquisition_price, 2)); ?></p>
                        </div>

                        <div class="border-t border-zinc-200 pt-3">
                            <p class="text-zinc-500">Current Valuation</p>
                            <p class="font-semibold text-zinc-900"><?php echo e(\App\Support\Currency::symbol()); ?><?php echo e(number_format((float) $artwork->current_valuation, 2)); ?></p>
                        </div>

                        <div class="border-t border-zinc-200 pt-3">
                            <p class="text-zinc-500">Unrealised Gain/Loss</p>
                            <?php $gain = (float) $artwork->current_valuation - (float) $artwork->acquisition_price; ?>
                            <p class="font-semibold <?php echo e($gain >= 0 ? 'text-emerald-600' : 'text-rose-600'); ?>">
                                <?php echo e($gain >= 0 ? '+' : '-'); ?><?php echo e(\App\Support\Currency::symbol()); ?><?php echo e(number_format(abs($gain), 2)); ?>

                            </p>
                        </div>

                        <div class="border-t border-zinc-200 pt-3">
                            <p class="text-zinc-500">Insurance Coverage</p>
                            <p class="font-semibold text-zinc-900"><?php echo e(\App\Support\Currency::symbol()); ?><?php echo e(number_format((float) $artwork->current_valuation, 2)); ?></p>
                        </div>
                    </div>
                </article>

                <article class="museum-panel text-center">
                    <h3 class="museum-section-title text-base! text-left">Artwork QR Code</h3>
                    <img src="<?php echo e($qrCodeUrl); ?>" alt="QR code for <?php echo e($artwork->title); ?>" class="mx-auto mt-4 h-40 w-40 rounded-md border border-zinc-200 bg-white p-2">
                    <p class="mt-3 text-xs text-zinc-500">Scan to view artwork details</p>
                </article>

                <article class="museum-panel">
                    <a href="<?php echo e(route('artworks.edit', ['artwork' => $artwork, 'from' => $origin, 'return' => $isSafeReturnUrl ? $returnUrl : null])); ?>" class="museum-btn w-full justify-center">Edit Artwork</a>
                </article>
            </aside>
        </div>
    </section>

    <div class="museum-modal-overlay <?php echo e($errors->any() ? 'is-open' : ''); ?>" id="record-movement">
        <div class="museum-modal" role="dialog" aria-modal="true" aria-labelledby="artwork-movement-modal-title">
            <a href="<?php echo e($selfRoute); ?>" class="museum-modal-close" aria-label="Close">&times;</a>

            <span id="artwork-movement-modal-title" class="museum-section-title block">Record Artwork Movement</span>

            <form action="<?php echo e(route('movements.store')); ?>" method="POST" class="mt-5 grid gap-4 md:grid-cols-2">
                <?php echo csrf_field(); ?>
                <input type="hidden" name="artwork_id" value="<?php echo e($artwork->id); ?>">

                <label class="museum-field md:col-span-2">
                    <span>From Location <em class="text-rose-500 not-italic">*</em></span>
                    <input
                        type="text"
                        name="from_location"
                        list="movement-location-options"
                        value="<?php echo e(old('from_location', $artwork->location?->name)); ?>"
                        placeholder="e.g., Private Residence - Main Gallery"
                        required
                    >
                </label>

                <label class="museum-field md:col-span-2">
                    <span>To Location <em class="text-rose-500 not-italic">*</em></span>
                    <input
                        type="text"
                        name="to_location"
                        list="movement-location-options"
                        value="<?php echo e(old('to_location')); ?>"
                        placeholder="e.g., Main Gallery - Wall B"
                        required
                    >
                </label>

                <datalist id="movement-location-options">
                    <?php $__currentLoopData = $locationOptions; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $loc): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                        <option value="<?php echo e($loc); ?>"></option>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                </datalist>

                <label class="museum-field">
                    <span>Date Out <em class="text-rose-500 not-italic">*</em></span>
                    <input type="date" name="date_out" value="<?php echo e(old('date_out', now()->toDateString())); ?>" required>
                </label>

                <label class="museum-field">
                    <span>Expected Return</span>
                    <input type="date" name="expected_return_date" value="<?php echo e(old('expected_return_date')); ?>">
                </label>

                <label class="museum-field">
                    <span>Reason <em class="text-rose-500 not-italic">*</em></span>
                    <select name="reason" required>
                        <?php $__currentLoopData = $reasonOptions; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $reason): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                            <option value="<?php echo e($reason); ?>" <?php if(old('reason', 'Storage') === $reason): echo 'selected'; endif; ?>><?php echo e($reason); ?></option>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                    </select>
                </label>

                <label class="museum-field">
                    <span>Responsible Handler <em class="text-rose-500 not-italic">*</em></span>
                    <input name="responsible_handler" value="<?php echo e(old('responsible_handler')); ?>" placeholder="Your name or handler's name" required>
                </label>

                <label class="museum-field md:col-span-2">
                    <span>Status <em class="text-rose-500 not-italic">*</em></span>
                    <select name="status" required>
                        <?php $__currentLoopData = $statusOptions; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $status): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                            <option value="<?php echo e($status); ?>" <?php if(old('status', 'Scheduled') === $status): echo 'selected'; endif; ?>><?php echo e($status); ?></option>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                    </select>
                </label>

                <label class="museum-field md:col-span-2">
                    <span>Notes (Optional)</span>
                    <textarea name="notes" rows="3" placeholder="Add any relevant notes about this movement..."><?php echo e(old('notes')); ?></textarea>
                </label>

                <label class="museum-field md:col-span-2">
                    <span>Condition Report</span>
                    <textarea name="condition_report" rows="2" placeholder="Document the artwork condition before movement..."><?php echo e(old('condition_report')); ?></textarea>
                </label>

                <div class="md:col-span-2 flex justify-end gap-3">
                    <a href="<?php echo e($selfRoute); ?>" class="museum-btn-secondary museum-modal-cancel">Cancel</a>
                    <button type="submit" class="museum-btn museum-modal-submit">Record Movement</button>
                </div>
            </form>
        </div>
    </div>
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
<?php /**PATH /Users/syed/MEGA/WORK/RELIVA/Website/LEBTECH/museum-azman/resources/views/artworks/show.blade.php ENDPATH**/ ?>