<?php if (isset($component)) { $__componentOriginal23a33f287873b564aaf305a1526eada4 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal23a33f287873b564aaf305a1526eada4 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.layout','data' => ['title' => 'Edit Movement - Museum Azman']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('layout'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['title' => 'Edit Movement - Museum Azman']); ?>
    <section class="space-y-6">
        <div class="flex items-start justify-between gap-4">
            <div>
                <h2 class="museum-page-title">Edit Movement</h2>
                <p class="museum-page-subtitle">Update transfer details for <?php echo e($movement->artwork?->title ?? 'selected artwork'); ?></p>
            </div>
            <a href="<?php echo e(route('movements.index')); ?>" class="museum-btn-secondary">Back to Movement Tracker</a>
        </div>

        <article class="museum-panel p-5">
            <form action="<?php echo e(route('movements.update', $movement)); ?>" method="POST" class="grid gap-5 md:grid-cols-2">
                <?php echo csrf_field(); ?>
                <?php echo method_field('PUT'); ?>

                <label class="museum-field md:col-span-2">
                    <span>Artwork <em class="text-rose-500 not-italic">*</em></span>
                    <select name="artwork_id" required>
                        <?php $__currentLoopData = $artworks; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $artwork): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                            <option value="<?php echo e($artwork->id); ?>" <?php if(old('artwork_id', $movement->artwork_id) == $artwork->id): echo 'selected'; endif; ?>>
                                <?php echo e($artwork->title); ?> - <?php echo e($artwork->artist?->name ?? 'Unknown Artist'); ?>

                            </option>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                    </select>
                </label>

                <?php
                    $selectedFrom = old('from_location', $movement->from_location);
                    $selectedTo = old('to_location', $movement->to_location);
                    $selectedReason = old('reason', $movement->reason);
                    $selectedStatus = old('status', $movement->status);
                    $selectedHandler = old('responsible_handler', $movement->responsible_handler);
                    $locationOptionsList = collect($locationOptions);
                    $handlerOptionsList = collect($handlerOptions ?? [])->values();
                    $reasonOptionsList = collect($reasonOptions)->sort()->values();
                    $statusOptionsList = collect($statusOptions)->sort()->values();
                ?>

                <label class="museum-field">
                    <span>From Location <em class="text-rose-500 not-italic">*</em></span>
                    <select name="from_location" required>
                        <option value="">Select origin</option>
                        <?php $__currentLoopData = $locationOptions; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $loc): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                            <option value="<?php echo e($loc); ?>" <?php if($selectedFrom === $loc): echo 'selected'; endif; ?>><?php echo e($loc); ?></option>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                        <?php if($selectedFrom && !$locationOptionsList->contains($selectedFrom)): ?>
                            <option value="<?php echo e($selectedFrom); ?>" selected><?php echo e($selectedFrom); ?></option>
                        <?php endif; ?>
                    </select>
                </label>

                <label class="museum-field">
                    <span>To Location <em class="text-rose-500 not-italic">*</em></span>
                    <select name="to_location" required>
                        <option value="">Select destination</option>
                        <?php $__currentLoopData = $locationOptions; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $loc): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                            <option value="<?php echo e($loc); ?>" <?php if($selectedTo === $loc): echo 'selected'; endif; ?>><?php echo e($loc); ?></option>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                        <?php if($selectedTo && !$locationOptionsList->contains($selectedTo)): ?>
                            <option value="<?php echo e($selectedTo); ?>" selected><?php echo e($selectedTo); ?></option>
                        <?php endif; ?>
                    </select>
                </label>

                <label class="museum-field">
                    <span>Date Out <em class="text-rose-500 not-italic">*</em></span>
                    <input type="date" name="date_out" value="<?php echo e(old('date_out', optional($movement->date_out)->toDateString())); ?>" required>
                </label>

                <label class="museum-field">
                    <span>Expected Return Date</span>
                    <input type="date" name="expected_return_date" value="<?php echo e(old('expected_return_date', optional($movement->expected_return_date)->toDateString())); ?>">
                </label>

                <label class="museum-field">
                    <span>Responsible Handler <em class="text-rose-500 not-italic">*</em></span>
                    <select name="responsible_handler" required>
                        <option value="">Select handler</option>
                        <?php $__currentLoopData = $handlerOptionsList; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $handlerName): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                            <option value="<?php echo e($handlerName); ?>" <?php if($selectedHandler === $handlerName): echo 'selected'; endif; ?>><?php echo e($handlerName); ?></option>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                        <?php if($selectedHandler && !$handlerOptionsList->contains($selectedHandler)): ?>
                            <option value="<?php echo e($selectedHandler); ?>" selected><?php echo e($selectedHandler); ?></option>
                        <?php endif; ?>
                    </select>
                </label>

                <label class="museum-field">
                    <span>Reason <em class="text-rose-500 not-italic">*</em></span>
                    <select name="reason" required>
                        <?php $__currentLoopData = $reasonOptionsList; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $reason): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                            <option value="<?php echo e($reason); ?>" <?php if($selectedReason === $reason): echo 'selected'; endif; ?>><?php echo e($reason); ?></option>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                        <?php if($selectedReason && !$reasonOptionsList->contains($selectedReason)): ?>
                            <option value="<?php echo e($selectedReason); ?>" selected><?php echo e($selectedReason); ?></option>
                        <?php endif; ?>
                    </select>
                </label>

                <label class="museum-field md:col-span-2">
                    <span>Status <em class="text-rose-500 not-italic">*</em></span>
                    <select name="status" required>
                        <?php $__currentLoopData = $statusOptionsList; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $status): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                            <option value="<?php echo e($status); ?>" <?php if($selectedStatus === $status): echo 'selected'; endif; ?>><?php echo e($status); ?></option>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                        <?php if($selectedStatus && !$statusOptionsList->contains($selectedStatus)): ?>
                            <option value="<?php echo e($selectedStatus); ?>" selected><?php echo e($selectedStatus); ?></option>
                        <?php endif; ?>
                    </select>
                </label>

                <label class="museum-field md:col-span-2">
                    <span>Movement Notes</span>
                    <textarea name="notes" rows="3"><?php echo e(old('notes', $movement->notes)); ?></textarea>
                </label>

                <label class="museum-field md:col-span-2">
                    <span>Condition Report</span>
                    <textarea name="condition_report" rows="3"><?php echo e(old('condition_report', $movement->condition_report)); ?></textarea>
                </label>

                <div class="md:col-span-2 flex justify-end gap-3 pt-1">
                    <a href="<?php echo e(route('movements.index')); ?>" class="museum-btn-secondary">Cancel</a>
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
<?php /**PATH /Users/syed/MEGA/WORK/RELIVA/Website/LEBTECH/museum-azman/resources/views/movements/edit.blade.php ENDPATH**/ ?>