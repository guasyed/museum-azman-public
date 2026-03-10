<?php if (isset($component)) { $__componentOriginal23a33f287873b564aaf305a1526eada4 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal23a33f287873b564aaf305a1526eada4 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.layout','data' => ['title' => 'Movement Tracker - Museum Azman']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('layout'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['title' => 'Movement Tracker - Museum Azman']); ?>
    <section class="space-y-6">
        <div class="flex items-start justify-between gap-4">
            <div>
                <h2 class="museum-page-title">Movement Tracker</h2>
                <p class="museum-page-subtitle">Track artwork movements, loans, and transfers</p>
            </div>
            <a href="#record-movement" class="museum-btn">+ Record Movement</a>
        </div>

        <div class="grid gap-4 md:grid-cols-3">
            <div class="museum-stat-card">
                <p>In Stage</p>
                <strong class="text-blue-600"><?php echo e($stats['in_stage']); ?></strong>
                <span class="mt-1 block text-zinc-600">Works staged for display</span>
            </div>
            <div class="museum-stat-card">
                <p>On Loan</p>
                <strong class="text-violet-700"><?php echo e($stats['on_loan']); ?></strong>
                <span class="mt-1 block text-zinc-600">Works currently loaned</span>
            </div>
            <div class="museum-stat-card">
                <p>Under Restoration</p>
                <strong class="text-amber-600"><?php echo e($stats['under_restoration']); ?></strong>
                <span class="mt-1 block text-zinc-600">Restoration in progress</span>
            </div>
        </div>

        <article class="museum-panel p-5">
            <div class="mb-4 flex items-center justify-between">
                <h3 class="museum-section-title">Movement History</h3>
                <span class="text-sm text-zinc-500">All Movements</span>
            </div>

            <div class="overflow-x-auto">
                <table class="w-full min-w-[980px] text-sm">
                    <thead>
                    <tr class="border-b border-zinc-200 text-left text-zinc-600">
                        <th class="py-2">Artwork</th>
                        <th class="py-2">From</th>
                        <th class="py-2">To</th>
                        <th class="py-2">Date Out</th>
                        <th class="py-2">Expected Return</th>
                        <th class="py-2">Handler</th>
                        <th class="py-2">Reason</th>
                        <th class="py-2">Status</th>
                        <?php if(auth()->user()?->isAdmin()): ?>
                            <th class="py-2">Actions</th>
                        <?php endif; ?>
                    </tr>
                    </thead>
                    <tbody>
                    <?php $__empty_1 = true; $__currentLoopData = $movements; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $movement): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                        <?php
                            $status = $movement->status;
                            $statusClass = match($status) {
                                'On Display' => 'bg-emerald-100 text-emerald-700',
                                'In Stage' => 'bg-blue-100 text-blue-700',
                                'On Loan' => 'bg-violet-100 text-violet-700',
                                'Under Restoration' => 'bg-amber-100 text-amber-700',
                                'Completed' => 'bg-emerald-100 text-emerald-700',
                                'Scheduled' => 'bg-blue-100 text-blue-700',
                                'In Transit', 'Overdue' => 'bg-amber-100 text-amber-700',
                                default => 'bg-zinc-100 text-zinc-700',
                            };
                        ?>
                        <tr class="border-b border-zinc-100 align-top">
                            <td class="py-3">
                                <p class="font-semibold"><?php echo e($movement->artwork?->title); ?></p>
                                <p class="text-zinc-500"><?php echo e($movement->artwork?->artist?->name); ?></p>
                            </td>
                            <td class="py-3"><?php echo e($movement->from_location); ?></td>
                            <td class="py-3"><?php echo e($movement->to_location); ?></td>
                            <td class="py-3"><?php echo e($movement->date_out?->format('M j, Y')); ?></td>
                            <td class="py-3"><?php echo e($movement->expected_return_date?->format('M j, Y') ?? '-'); ?></td>
                            <td class="py-3"><?php echo e($movement->responsible_handler); ?></td>
                            <td class="py-3"><span class="rounded-md border border-zinc-200 px-2 py-1"><?php echo e($movement->reason); ?></span></td>
                            <td class="py-3"><span class="rounded-lg px-2.5 py-1 text-xs font-semibold <?php echo e($statusClass); ?>"><?php echo e($status); ?></span></td>
                            <?php if(auth()->user()?->isAdmin()): ?>
                                <td class="py-3">
                                    <a href="<?php echo e(route('movements.edit', $movement)); ?>" class="museum-btn-secondary px-3 py-1.5 text-xs">Edit</a>
                                </td>
                            <?php endif; ?>
                        </tr>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                        <tr>
                            <td class="py-4 text-zinc-500" colspan="<?php echo e(auth()->user()?->isAdmin() ? 9 : 8); ?>">No movement records yet.</td>
                        </tr>
                    <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </article>

        <article class="museum-panel p-5">
            <h3 class="museum-section-title">Active Movements</h3>
            <p class="text-zinc-600">Detailed view of artworks currently on loan, staged, or under restoration</p>

            <div class="mt-4 space-y-4">
                <?php $__empty_1 = true; $__currentLoopData = $activeMovements; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $movement): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                    <?php
                        $activeStatusClass = match($movement->status) {
                            'On Display' => 'bg-emerald-100 text-emerald-700',
                            'In Stage' => 'bg-blue-100 text-blue-700',
                            'On Loan' => 'bg-violet-100 text-violet-700',
                            'Under Restoration' => 'bg-amber-100 text-amber-700',
                            'Completed' => 'bg-emerald-100 text-emerald-700',
                            'Scheduled' => 'bg-blue-100 text-blue-700',
                            'In Transit', 'Overdue' => 'bg-amber-100 text-amber-700',
                            default => 'bg-zinc-100 text-zinc-700',
                        };
                    ?>
                    <div class="rounded-xl border border-zinc-200 bg-white p-4">
                        <div class="mb-2 flex items-center justify-between">
                            <div>
                                <h4 class="museum-card-title"><?php echo e($movement->artwork?->title); ?></h4>
                                <p class="text-zinc-600"><?php echo e($movement->artwork?->artist?->name); ?></p>
                            </div>
                            <span class="rounded-lg px-3 py-1 text-sm font-semibold <?php echo e($activeStatusClass); ?>"><?php echo e($movement->status); ?></span>
                        </div>

                        <div class="grid gap-4 md:grid-cols-2">
                            <div>
                                <p class="text-zinc-500">From</p>
                                <p class="font-semibold"><?php echo e($movement->from_location); ?></p>
                                <p class="mt-2 text-zinc-500">Date Out</p>
                                <p><?php echo e($movement->date_out?->format('M j, Y')); ?></p>
                                <p class="mt-2 text-zinc-500">Handler</p>
                                <p><?php echo e($movement->responsible_handler); ?></p>
                            </div>
                            <div>
                                <p class="text-zinc-500">To</p>
                                <p class="font-semibold"><?php echo e($movement->to_location); ?></p>
                                <p class="mt-2 text-zinc-500">Expected Return</p>
                                <p><?php echo e($movement->expected_return_date?->format('M j, Y') ?? '-'); ?></p>
                                <p class="mt-2 text-zinc-500">Reason</p>
                                <p><span class="rounded-md border border-zinc-200 px-2 py-0.5 text-sm"><?php echo e($movement->reason); ?></span></p>
                            </div>
                        </div>

                        <?php if($movement->notes): ?>
                            <p class="mt-3 text-zinc-600"><?php echo e($movement->notes); ?></p>
                        <?php endif; ?>

                        <?php if($movement->condition_report): ?>
                            <div class="mt-3 rounded-lg border border-zinc-200 bg-zinc-50 px-3 py-2">
                                <p class="text-zinc-500">Condition Report</p>
                                <p><?php echo e($movement->condition_report); ?></p>
                            </div>
                        <?php endif; ?>

                        <?php if(auth()->user()?->isAdmin()): ?>
                            <div class="mt-3 flex justify-end">
                                <a href="<?php echo e(route('movements.edit', $movement)); ?>" class="museum-btn-secondary px-3 py-1.5 text-xs">Edit Movement</a>
                            </div>
                        <?php endif; ?>
                    </div>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                    <p class="text-zinc-500">No active movements.</p>
                <?php endif; ?>
            </div>
        </article>

    </section>

    <?php
        $initialArtworkId = (int) old('artwork_id', request()->integer('artwork'));
        $initialArtwork = $artworks->firstWhere('id', $initialArtworkId);
        $initialArtworkLabel = old('artwork_search', $initialArtwork
            ? ($initialArtwork->title.' — '.($initialArtwork->artist?->name ?? 'Unknown Artist'))
            : '');
        $artworkSearchOptions = $artworks->map(function ($artwork) {
            return [
                'id' => $artwork->id,
                'title' => (string) $artwork->title,
                'artist' => (string) ($artwork->artist?->name ?? 'Unknown Artist'),
                'label' => (string) ($artwork->title.' — '.($artwork->artist?->name ?? 'Unknown Artist')),
            ];
        })->values();
    ?>

    <div class="museum-modal-overlay <?php echo e($errors->any() ? 'is-open' : ''); ?>" id="record-movement">
        <div class="museum-modal" role="dialog" aria-modal="true" aria-labelledby="movement-modal-title">
            <a href="<?php echo e(route('movements.index')); ?>" class="museum-modal-close" aria-label="Close">&times;</a>

            <span id="movement-modal-title" class="museum-section-title block">Record Movement</span>
            <p class="mt-2 text-zinc-600">Create a new movement record for artwork transfer, loan, or exhibition</p>

            <form action="<?php echo e(route('movements.store')); ?>" method="POST" class="mt-6 grid gap-5 md:grid-cols-2">
                <?php echo csrf_field(); ?>
                <label class="museum-field md:col-span-2">
                    <span>Artwork <em class="text-rose-500 not-italic">*</em></span>
                    <div class="relative">
                        <input
                            type="text"
                            id="movement-artwork-search"
                            name="artwork_search"
                            value="<?php echo e($initialArtworkLabel); ?>"
                            placeholder="Search artwork by title or artist..."
                            autocomplete="off"
                            spellcheck="false"
                        >
                        <input type="hidden" name="artwork_id" id="movement-artwork-id" value="<?php echo e(old('artwork_id', request()->query('artwork'))); ?>">
                        <div
                            id="movement-artwork-suggestions"
                            class="absolute left-0 right-0 z-30 mt-2 hidden max-h-64 overflow-y-auto rounded-xl border border-zinc-200 bg-white p-1 shadow-xl"
                            role="listbox"
                        ></div>
                    </div>
                </label>

                <label class="museum-field">
                    <span>From Location <em class="text-rose-500 not-italic">*</em></span>
                    <input
                        type="text"
                        name="from_location"
                        list="movement-location-options"
                        value="<?php echo e(old('from_location')); ?>"
                        placeholder="e.g., Museum 3"
                        required
                    >
                </label>

                <label class="museum-field">
                    <span>To Location <em class="text-rose-500 not-italic">*</em></span>
                    <input
                        type="text"
                        name="to_location"
                        list="movement-location-options"
                        value="<?php echo e(old('to_location')); ?>"
                        placeholder="e.g., Hall 1"
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
                    <input type="date" name="date_out" value="<?php echo e(old('date_out')); ?>" placeholder="dd-mm-yyyy" required>
                </label>

                <label class="museum-field">
                    <span>Expected Return Date</span>
                    <input type="date" name="expected_return_date" value="<?php echo e(old('expected_return_date')); ?>" placeholder="dd-mm-yyyy">
                </label>

                <label class="museum-field">
                    <span>Responsible Handler <em class="text-rose-500 not-italic">*</em></span>
                    <input name="responsible_handler" value="<?php echo e(old('responsible_handler')); ?>" placeholder="e.g., Crown Fine Art" required>
                </label>

                <label class="museum-field">
                    <span>Reason <em class="text-rose-500 not-italic">*</em></span>
                    <select name="reason" required>
                        <?php $__currentLoopData = ['Loan','Exhibition','Storage','Restoration','Sale Prep']; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $reason): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                            <option value="<?php echo e($reason); ?>" <?php if(old('reason', 'Exhibition')===$reason): echo 'selected'; endif; ?>><?php echo e($reason); ?></option>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                    </select>
                </label>

                <label class="museum-field md:col-span-1">
                    <span>Status <em class="text-rose-500 not-italic">*</em></span>
                    <select name="status" required>
                        <?php $__currentLoopData = collect($statusOptions)->sort(); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $status): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                            <option value="<?php echo e($status); ?>" <?php if(old('status', \App\Models\Status::DEFAULT_NAMES[0])===$status): echo 'selected'; endif; ?>><?php echo e($status); ?></option>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                    </select>
                </label>
                <div class="hidden md:block"></div>

                <label class="museum-field md:col-span-2">
                    <span>Movement Notes</span>
                    <textarea name="notes" rows="2" placeholder="Add any additional notes about this movement..."><?php echo e(old('notes')); ?></textarea>
                </label>

                <label class="museum-field md:col-span-2">
                    <span>Condition Report</span>
                    <textarea name="condition_report" rows="2" placeholder="Document the condition of the artwork before movement..."><?php echo e(old('condition_report')); ?></textarea>
                </label>

                <div class="md:col-span-2 flex justify-end gap-3 pt-1">
                    <a href="<?php echo e(route('movements.index')); ?>" class="museum-btn-secondary museum-modal-cancel">Cancel</a>
                    <button type="submit" class="museum-btn museum-modal-submit">Record Movement</button>
                </div>
            </form>
        </div>
    </div>

    <script>
document.addEventListener('DOMContentLoaded', () => {
    const modal = document.getElementById('record-movement');
    const searchInput = document.getElementById('movement-artwork-search');
    const artworkIdInput = document.getElementById('movement-artwork-id');
    const suggestionBox = document.getElementById('movement-artwork-suggestions');
    const movementForm = modal ? modal.querySelector('form[action="<?php echo e(route('movements.store')); ?>"]') : null;

    if (!modal || !searchInput || !artworkIdInput || !suggestionBox || !movementForm) {
        return;
    }

    const artworkOptions = <?php echo json_encode($artworkSearchOptions, 15, 512) ?>;

    let filteredOptions = [];
    let activeIndex = -1;
    let selectedLabel = searchInput.value.trim();

    const hideSuggestions = () => {
        suggestionBox.classList.add('hidden');
        suggestionBox.innerHTML = '';
        filteredOptions = [];
        activeIndex = -1;
        searchInput.setAttribute('aria-expanded', 'false');
    };

    const renderSuggestions = () => {
        suggestionBox.innerHTML = '';
        if (!filteredOptions.length) {
            hideSuggestions();
            return;
        }

        filteredOptions.forEach((item, index) => {
            const option = document.createElement('button');
            option.type = 'button';
            option.className = [
                'w-full rounded-lg px-3 py-2 text-left transition',
                index === activeIndex ? 'bg-zinc-100' : 'hover:bg-zinc-50',
            ].join(' ');
            option.setAttribute('role', 'option');
            option.setAttribute('aria-selected', index === activeIndex ? 'true' : 'false');
            option.innerHTML = `
                <p class="truncate text-sm font-semibold text-zinc-900">${item.title}</p>
                <p class="truncate text-xs text-zinc-600">${item.artist}</p>
            `;
            option.addEventListener('mousedown', (event) => {
                event.preventDefault();
                artworkIdInput.value = String(item.id);
                searchInput.value = item.label;
                selectedLabel = item.label;
                searchInput.setCustomValidity('');
                hideSuggestions();
            });
            suggestionBox.appendChild(option);
        });

        suggestionBox.classList.remove('hidden');
        searchInput.setAttribute('aria-expanded', 'true');
    };

    const updateSuggestions = () => {
        const q = searchInput.value.trim().toLowerCase();
        if (!q) {
            filteredOptions = artworkOptions.slice(0, 8);
            activeIndex = -1;
            renderSuggestions();
            return;
        }

        filteredOptions = artworkOptions
            .filter((item) => item.title.toLowerCase().includes(q) || item.artist.toLowerCase().includes(q))
            .slice(0, 8);
        activeIndex = -1;
        renderSuggestions();
    };

    searchInput.addEventListener('focus', updateSuggestions);

    searchInput.addEventListener('input', () => {
        if (searchInput.value.trim() !== selectedLabel) {
            artworkIdInput.value = '';
        }
        searchInput.setCustomValidity('');
        updateSuggestions();
    });

    searchInput.addEventListener('keydown', (event) => {
        if (!filteredOptions.length) {
            return;
        }

        if (event.key === 'ArrowDown') {
            event.preventDefault();
            activeIndex = (activeIndex + 1) % filteredOptions.length;
            renderSuggestions();
            return;
        }

        if (event.key === 'ArrowUp') {
            event.preventDefault();
            activeIndex = activeIndex <= 0 ? filteredOptions.length - 1 : activeIndex - 1;
            renderSuggestions();
            return;
        }

        if (event.key === 'Enter') {
            event.preventDefault();
            const index = activeIndex >= 0 ? activeIndex : 0;
            const selected = filteredOptions[index];
            if (!selected) {
                return;
            }
            artworkIdInput.value = String(selected.id);
            searchInput.value = selected.label;
            selectedLabel = selected.label;
            searchInput.setCustomValidity('');
            hideSuggestions();
            return;
        }

        if (event.key === 'Escape') {
            hideSuggestions();
        }
    });

    document.addEventListener('click', (event) => {
        if (!modal.contains(event.target)) {
            return;
        }
        if (!searchInput.closest('.relative').contains(event.target)) {
            hideSuggestions();
        }
    });

    movementForm.addEventListener('submit', (event) => {
        if (String(artworkIdInput.value).trim() !== '') {
            searchInput.setCustomValidity('');
            return;
        }

        event.preventDefault();
        searchInput.setCustomValidity('Please select an artwork from suggestions.');
        searchInput.reportValidity();
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
<?php /**PATH /Users/syed/MEGA/WORK/RELIVA/Website/LEBTECH/museum-azman/resources/views/movements/index.blade.php ENDPATH**/ ?>