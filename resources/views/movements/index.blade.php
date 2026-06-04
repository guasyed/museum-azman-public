<x-layout title="Movement Tracker - Museum Azman">
    <section class="space-y-6">
        <div class="flex items-start justify-between gap-4">
            <div>
                <h2 class="museum-page-title">Movement Tracker</h2>
                <p class="museum-page-subtitle">Track artwork movements, loans, and transfers</p>
            </div>
            @if($canRecordMovement ?? true)
                <a href="#record-movement" class="museum-btn">+ Record Movement</a>
            @endif
        </div>

        <div class="grid gap-4 md:grid-cols-3">
            <div class="museum-stat-card">
                <p>In Stage</p>
                <strong class="text-blue-600">{{ $stats['in_stage'] }}</strong>
                <span class="mt-1 block text-zinc-600">Works staged for display</span>
            </div>
            <div class="museum-stat-card">
                <p>On Loan</p>
                <strong class="text-violet-700">{{ $stats['on_loan'] }}</strong>
                <span class="mt-1 block text-zinc-600">Works currently loaned</span>
            </div>
            <div class="museum-stat-card">
                <p>Under Restoration</p>
                <strong class="text-amber-600">{{ $stats['under_restoration'] }}</strong>
                <span class="mt-1 block text-zinc-600">Restoration in progress</span>
            </div>
        </div>
        <article class="museum-panel p-5">
            <h3 class="museum-section-title">Active Movements</h3>
            <p class="text-zinc-600">Detailed view of artworks currently on loan, staged, or under restoration</p>

            <div class="mt-4 space-y-4">
                @forelse($activeMovements as $movement)
                    @php
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
                        $canEditMovement = auth()->user()?->isAdmin()
                            || (($isAssignedOnlyView ?? false)
                                && strtolower(trim((string) $movement->responsible_handler)) === strtolower(trim((string) auth()->user()?->name)));
                    @endphp
                    <div class="rounded-xl border border-zinc-200 bg-white p-4">
                        <div class="mb-2 flex items-center justify-between">
                            <div>
                                <h4 class="museum-card-title">{{ $movement->artwork?->title }}</h4>
                                <p class="text-zinc-600">{{ $movement->artwork?->artist?->name }}</p>
                            </div>
                            <span class="rounded-lg px-3 py-1 text-sm font-semibold {{ $activeStatusClass }}">{{ $movement->status }}</span>
                        </div>

                        <div class="grid gap-4 md:grid-cols-2">
                            <div>
                                <p class="text-zinc-500">From</p>
                                <p class="font-semibold">{{ $movement->from_location }}</p>
                                <p class="mt-2 text-zinc-500">Date Out</p>
                                <p>{{ \App\Support\DateFormat::display($movement->date_out) }}</p>
                                <p class="mt-2 text-zinc-500">Moved By</p>
                                <p>{{ $movement->responsible_handler }}</p>
                            </div>
                            <div>
                                <p class="text-zinc-500">To</p>
                                <p class="font-semibold">{{ $movement->to_location }}</p>
                                <p class="mt-2 text-zinc-500">Expected Return</p>
                                <p>{{ \App\Support\DateFormat::display($movement->expected_return_date) }}</p>
                                <p class="mt-2 text-zinc-500">Movement Type</p>
                                <p><span class="rounded-md border border-zinc-200 px-2 py-0.5 text-sm">{{ $movement->movement_type ?: $movement->reason }}</span></p>
                            </div>
                        </div>

                        @if($movement->notes)
                            <p class="mt-3 text-zinc-600">{{ $movement->notes }}</p>
                        @endif

                        @if($movement->condition_report)
                            <div class="mt-3 rounded-lg border border-zinc-200 bg-zinc-50 px-3 py-2">
                                <p class="text-zinc-500">Condition Report</p>
                                <p>{{ $movement->condition_report }}</p>
                            </div>
                        @endif

                        @if($canEditMovement)
                            <div class="mt-3 flex justify-end">
                                <a href="{{ route('movements.edit', $movement) }}" class="museum-btn-secondary px-3 py-1.5 text-xs">Edit Movement</a>
                            </div>
                        @endif
                    </div>
                @empty
                    <p class="text-zinc-500">No active movements.</p>
                @endforelse
            </div>

            @if($activeMovements->hasPages())
                <div class="mt-5">
                    {{ $activeMovements->links() }}
                </div>
            @endif
        </article>
        <article class="museum-panel p-5">
            <div class="mb-4 flex items-center justify-between">
                <h3 class="museum-section-title">Movement History</h3>
                <span class="text-sm text-zinc-500">{{ ($isAssignedOnlyView ?? false) ? 'Assigned to You' : 'All Movements' }}</span>
            </div>

            <div class="overflow-x-auto">
                @php
                    $showActionsColumn = auth()->user()?->isAdmin() || ($isAssignedOnlyView ?? false);
                @endphp
                <table class="w-full min-w-[980px] text-sm">
                    <thead>
                    <tr class="border-b border-zinc-200 text-left text-zinc-600">
                        <th class="py-2 w-14">No.</th>
                        <th class="py-2">
                            @php
                                $isArtworkSort = ($sortColumn ?? 'date_out') === 'artwork_title';
                                $nextArtworkDirection = $isArtworkSort && ($direction ?? 'desc') === 'asc' ? 'desc' : 'asc';
                            @endphp
                            <a
                                href="{{ route('movements.index', array_merge(request()->query(), ['sort' => 'artwork_title', 'direction' => $nextArtworkDirection])) }}"
                                class="inline-flex items-center gap-1 hover:text-zinc-900"
                            >
                                <span>Artwork</span>
                                @if($isArtworkSort)
                                    <span class="text-xs">{{ ($direction ?? 'desc') === 'asc' ? '▲' : '▼' }}</span>
                                @endif
                            </a>
                        </th>
                        <th class="py-2">
                            @php
                                $isFromSort = ($sortColumn ?? 'date_out') === 'from_location';
                                $nextFromDirection = $isFromSort && ($direction ?? 'desc') === 'asc' ? 'desc' : 'asc';
                            @endphp
                            <a
                                href="{{ route('movements.index', array_merge(request()->query(), ['sort' => 'from_location', 'direction' => $nextFromDirection])) }}"
                                class="inline-flex items-center gap-1 hover:text-zinc-900"
                            >
                                <span>From</span>
                                @if($isFromSort)
                                    <span class="text-xs">{{ ($direction ?? 'desc') === 'asc' ? '▲' : '▼' }}</span>
                                @endif
                            </a>
                        </th>
                        <th class="py-2">
                            @php
                                $isToSort = ($sortColumn ?? 'date_out') === 'to_location';
                                $nextToDirection = $isToSort && ($direction ?? 'desc') === 'asc' ? 'desc' : 'asc';
                            @endphp
                            <a
                                href="{{ route('movements.index', array_merge(request()->query(), ['sort' => 'to_location', 'direction' => $nextToDirection])) }}"
                                class="inline-flex items-center gap-1 hover:text-zinc-900"
                            >
                                <span>To</span>
                                @if($isToSort)
                                    <span class="text-xs">{{ ($direction ?? 'desc') === 'asc' ? '▲' : '▼' }}</span>
                                @endif
                            </a>
                        </th>
                        <th class="py-2">
                            @php
                                $isDateOutSort = ($sortColumn ?? 'date_out') === 'date_out';
                                $nextDateOutDirection = $isDateOutSort && ($direction ?? 'desc') === 'desc' ? 'asc' : 'desc';
                            @endphp
                            <a
                                href="{{ route('movements.index', array_merge(request()->query(), ['sort' => 'date_out', 'direction' => $nextDateOutDirection])) }}"
                                class="inline-flex items-center gap-1 hover:text-zinc-900"
                            >
                                <span>Date Out</span>
                                @if($isDateOutSort)
                                    <span class="text-xs">{{ ($direction ?? 'desc') === 'asc' ? '▲' : '▼' }}</span>
                                @endif
                            </a>
                        </th>
                        <th class="py-2">
                            @php
                                $isExpectedSort = ($sortColumn ?? 'date_out') === 'expected_return_date';
                                $nextExpectedDirection = $isExpectedSort && ($direction ?? 'desc') === 'asc' ? 'desc' : 'asc';
                            @endphp
                            <a
                                href="{{ route('movements.index', array_merge(request()->query(), ['sort' => 'expected_return_date', 'direction' => $nextExpectedDirection])) }}"
                                class="inline-flex items-center gap-1 hover:text-zinc-900"
                            >
                                <span>Expected Return</span>
                                @if($isExpectedSort)
                                    <span class="text-xs">{{ ($direction ?? 'desc') === 'asc' ? '▲' : '▼' }}</span>
                                @endif
                            </a>
                        </th>
                        <th class="py-2">
                            @php
                                $isHandlerSort = ($sortColumn ?? 'date_out') === 'responsible_handler';
                                $nextHandlerDirection = $isHandlerSort && ($direction ?? 'desc') === 'asc' ? 'desc' : 'asc';
                            @endphp
                            <a
                                href="{{ route('movements.index', array_merge(request()->query(), ['sort' => 'responsible_handler', 'direction' => $nextHandlerDirection])) }}"
                                class="inline-flex items-center gap-1 hover:text-zinc-900"
                            >
                                <span>Moved By</span>
                                @if($isHandlerSort)
                                    <span class="text-xs">{{ ($direction ?? 'desc') === 'asc' ? '▲' : '▼' }}</span>
                                @endif
                            </a>
                        </th>
                        <th class="py-2">
                            @php
                                $isReasonSort = ($sortColumn ?? 'date_out') === 'reason';
                                $nextReasonDirection = $isReasonSort && ($direction ?? 'desc') === 'asc' ? 'desc' : 'asc';
                            @endphp
                            <a
                                href="{{ route('movements.index', array_merge(request()->query(), ['sort' => 'reason', 'direction' => $nextReasonDirection])) }}"
                                class="inline-flex items-center gap-1 hover:text-zinc-900"
                            >
                                <span>External Reason</span>
                                @if($isReasonSort)
                                    <span class="text-xs">{{ ($direction ?? 'desc') === 'asc' ? '▲' : '▼' }}</span>
                                @endif
                            </a>
                        </th>
                        <th class="py-2">
                            @php
                                $isStatusSort = ($sortColumn ?? 'date_out') === 'status';
                                $nextStatusDirection = $isStatusSort && ($direction ?? 'desc') === 'asc' ? 'desc' : 'asc';
                            @endphp
                            <a
                                href="{{ route('movements.index', array_merge(request()->query(), ['sort' => 'status', 'direction' => $nextStatusDirection])) }}"
                                class="inline-flex items-center gap-1 hover:text-zinc-900"
                            >
                                <span>Status After Movement</span>
                                @if($isStatusSort)
                                    <span class="text-xs">{{ ($direction ?? 'desc') === 'asc' ? '▲' : '▼' }}</span>
                                @endif
                            </a>
                        </th>
                        @if($showActionsColumn)
                            <th class="py-2 text-right">Actions</th>
                        @endif
                    </tr>
                    </thead>
                    <tbody>
                    @forelse($movements as $movement)
                        @php
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
                        @endphp
                        @php
                            $canEditMovement = auth()->user()?->isAdmin()
                                || (($isAssignedOnlyView ?? false)
                                    && strtolower(trim((string) $movement->responsible_handler)) === strtolower(trim((string) auth()->user()?->name)));
                            $rowNumber = (($movements->currentPage() - 1) * $movements->perPage()) + $loop->iteration;
                        @endphp
                        <tr class="border-b border-zinc-100 align-top">
                            <td class="py-3 font-medium text-zinc-500">{{ $rowNumber }}</td>
                            <td class="py-3">
                                <p class="font-semibold">{{ $movement->artwork?->title }}</p>
                                <p class="text-zinc-500">{{ $movement->artwork?->artist?->name }}</p>
                            </td>
                            <td class="py-3">
                                <p>{{ $movement->from_location }}</p>
                                @if($movement->from_location_code)
                                    <p class="text-xs text-zinc-400">{{ $movement->from_location_code }}</p>
                                @endif
                            </td>
                            <td class="py-3">
                                <p>{{ $movement->to_location }}</p>
                                @if($movement->to_location_code)
                                    <p class="text-xs text-zinc-400">{{ $movement->to_location_code }}</p>
                                @endif
                            </td>
                            <td class="py-3">{{ \App\Support\DateFormat::display($movement->date_out) }}</td>
                            <td class="py-3">{{ \App\Support\DateFormat::display($movement->expected_return_date) }}</td>
                            <td class="py-3">{{ $movement->responsible_handler }}</td>
                            <td class="py-3">
                                <p><span class="rounded-md border border-zinc-200 px-2 py-1">{{ $movement->external_reason ?: '-' }}</span></p>
                                @if($movement->movement_type)
                                    <p class="mt-1 text-xs text-zinc-500">Type: {{ $movement->movement_type }}</p>
                                @endif
                                @if($movement->external_party)
                                    <p class="mt-1 text-xs text-zinc-500">Party: {{ $movement->external_party }}</p>
                                @endif
                            </td>
                            <td class="py-3"><span class="rounded-lg px-2.5 py-1 text-xs font-semibold {{ $statusClass }}">{{ $status }}</span></td>
                            @if($showActionsColumn)
                                <td class="py-3 text-right">
                                    @if($canEditMovement)
                                        <div class="inline-flex items-center justify-end gap-2 whitespace-nowrap">
                                            <a href="{{ route('movements.edit', $movement, false) }}" class="museum-btn-secondary px-3 py-1.5 text-xs">Edit</a>
                                            <form method="POST" action="{{ route('movements.destroy', $movement, false) }}" onsubmit="return confirm('Delete this movement record? This action cannot be undone.')">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="rounded-lg border border-rose-200 bg-rose-50 px-3 py-1.5 text-xs font-semibold text-rose-700 transition hover:bg-rose-100">Delete</button>
                                            </form>
                                        </div>
                                    @else
                                        <span class="text-xs text-zinc-400">-</span>
                                    @endif
                                </td>
                            @endif
                        </tr>
                    @empty
                        <tr>
                            <td class="py-4 text-zinc-500" colspan="{{ $showActionsColumn ? 10 : 9 }}">No movement records yet.</td>
                        </tr>
                    @endforelse
                    </tbody>
                </table>
            </div>

            @if($movements->hasPages())
                <div class="mt-5">
                    {{ $movements->links() }}
                </div>
            @endif
        </article>

    </section>

    @if($canRecordMovement ?? true)
    @php
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
    @endphp

    <div class="museum-modal-overlay {{ $errors->any() ? 'is-open' : '' }}" id="record-movement">
        <div class="museum-modal" role="dialog" aria-modal="true" aria-labelledby="movement-modal-title">
            <a href="{{ route('movements.index', [], false) }}" class="museum-modal-close" aria-label="Close">&times;</a>

            <span id="movement-modal-title" class="museum-section-title block">Record Movement</span>
            <p class="mt-2 text-zinc-600">Create a Movement Log entry using the Excel inventory fields</p>

            <form action="{{ route('movements.store', [], false) }}" method="POST" class="mt-6 grid gap-5 md:grid-cols-2">
                @csrf
                <label class="museum-field md:col-span-2">
                    <span>Artwork <em class="text-rose-500 not-italic">*</em></span>
                    <div class="relative">
                        <input
                            type="text"
                            id="movement-artwork-search"
                            name="artwork_search"
                            value="{{ $initialArtworkLabel }}"
                            placeholder="Search artwork by title or artist..."
                            autocomplete="off"
                            spellcheck="false"
                            style="width:50%"
                        >
                        <input type="hidden" name="artwork_id" id="movement-artwork-id" value="{{ old('artwork_id', request()->query('artwork')) }}">
                        <div
                            id="movement-artwork-suggestions"
                            class="absolute left-0 right-0 z-30 mt-2 hidden max-h-64 overflow-y-auto rounded-xl border border-zinc-200 bg-white p-1 shadow-xl"
                            role="listbox"
                        ></div>
                    </div>
                </label>

                <label class="museum-field">
                    <span>From Location <em class="text-rose-500 not-italic">*</em></span>
                    @php
                        $selectedFromLocation = old('from_location');
                        $selectedToLocation = old('to_location');
                        $locationOptionsList = collect($locationOptions);
                        $locationOptionRowsList = collect($locationOptionRows ?? []);
                    @endphp
                    <input type="hidden" name="from_location_code" value="{{ old('from_location_code') }}" data-location-code-input="from">
                    <select name="from_location" required data-location-select="from">
                        <option value="">Select origin</option>
                        @foreach($locationOptionRowsList as $loc)
                            <option value="{{ $loc['name'] }}" data-code="{{ $loc['code'] }}" data-type="{{ $loc['type'] }}" @selected($selectedFromLocation === $loc['name'])>{{ $loc['label'] }}</option>
                        @endforeach
                        @if($selectedFromLocation && !$locationOptionsList->contains($selectedFromLocation))
                            <option value="{{ $selectedFromLocation }}" selected>{{ $selectedFromLocation }}</option>
                        @endif
                    </select>
                </label>

                <label class="museum-field">
                    <span>To Location <em class="text-rose-500 not-italic">*</em></span>
                    <input type="hidden" name="to_location_code" value="{{ old('to_location_code') }}" data-location-code-input="to">
                    <select name="to_location" required data-location-select="to">
                        <option value="">Select destination</option>
                        @foreach($locationOptionRowsList as $loc)
                            <option value="{{ $loc['name'] }}" data-code="{{ $loc['code'] }}" data-type="{{ $loc['type'] }}" @selected($selectedToLocation === $loc['name'])>{{ $loc['label'] }}</option>
                        @endforeach
                        @if($selectedToLocation && !$locationOptionsList->contains($selectedToLocation))
                            <option value="{{ $selectedToLocation }}" selected>{{ $selectedToLocation }}</option>
                        @endif
                    </select>
                </label>

                <label class="museum-field">
                    <span>Movement Timestamp <em class="text-rose-500 not-italic">*</em></span>
                    <input type="date" name="date_out" value="{{ old('date_out') }}" placeholder="dd-mm-yyyy" required>
                </label>

                <label class="museum-field">
                    <span>Expected Return Date</span>
                    <input type="date" name="expected_return_date" value="{{ old('expected_return_date') }}" placeholder="dd-mm-yyyy">
                </label>

                <label class="museum-field">
                    <span>Completed Date</span>
                    <input type="date" name="completed_date" value="{{ old('completed_date') }}" placeholder="dd-mm-yyyy">
                </label>

                <label class="museum-field">
                    <span>Movement Type <em class="text-rose-500 not-italic">*</em></span>
                    @php
                        $selectedMovementType = old('movement_type', 'Display');
                        $movementTypeOptionsList = collect($movementTypeOptions ?? []);
                    @endphp
                    <select name="movement_type" required>
                        @foreach($movementTypeOptionsList as $movementType)
                            <option value="{{ $movementType }}" @selected($selectedMovementType === $movementType)>{{ $movementType }}</option>
                        @endforeach
                        @if($selectedMovementType && !$movementTypeOptionsList->contains($selectedMovementType))
                            <option value="{{ $selectedMovementType }}" selected>{{ $selectedMovementType }}</option>
                        @endif
                    </select>
                </label>

                <label class="museum-field">
                    <span>Status After Movement <em class="text-rose-500 not-italic">*</em></span>
                    <select name="status" required data-status-after-movement>
                        @foreach(collect($statusOptions)->sort() as $status)
                            <option value="{{ $status }}" @selected(old('status', \App\Models\Status::DEFAULT_NAMES[0])===$status)>{{ $status }}</option>
                        @endforeach
                    </select>
                </label>

                <label class="museum-field">
                    <span>External Reason</span>
                    @php
                        $selectedExternalReason = old('external_reason');
                        $externalReasonOptionsList = collect($externalReasonOptions ?? $reasonOptions ?? [])->sort()->values();
                    @endphp
                    <select name="external_reason">
                        <option value="">Select external reason</option>
                        @foreach($externalReasonOptionsList as $externalReason)
                            <option value="{{ $externalReason }}" @selected($selectedExternalReason === $externalReason)>{{ $externalReason }}</option>
                        @endforeach
                        @if($selectedExternalReason && !$externalReasonOptionsList->contains($selectedExternalReason))
                            <option value="{{ $selectedExternalReason }}" selected>{{ $selectedExternalReason }}</option>
                        @endif
                    </select>
                </label>

                <label class="museum-field">
                    <span>External Party</span>
                    <input type="text" name="external_party" value="{{ old('external_party') }}" placeholder="External party, auction house, or borrower">
                </label>

                <label class="museum-field">
                    <span>Moved By <em class="text-rose-500 not-italic">*</em></span>
                    @php
                        $selectedHandler = old('responsible_handler');
                        $handlerOptionsList = collect($handlerOptions ?? [])->values();
                    @endphp
                    <select name="responsible_handler" required>
                        <option value="">Select moved by</option>
                        @foreach($handlerOptionsList as $handlerName)
                            <option value="{{ $handlerName }}" @selected($selectedHandler === $handlerName)>{{ $handlerName }}</option>
                        @endforeach
                        @if($selectedHandler && !$handlerOptionsList->contains($selectedHandler))
                            <option value="{{ $selectedHandler }}" selected>{{ $selectedHandler }}</option>
                        @endif
                    </select>
                </label>

                <label class="museum-field">
                    <span>Approved By</span>
                    <input type="text" name="approved_by" value="{{ old('approved_by') }}" placeholder="Approver name">
                </label>

                <label class="museum-field md:col-span-2">
                    <span>Notes</span>
                    <textarea name="notes" rows="3" placeholder="Movement Log notes...">{{ old('notes') }}</textarea>
                </label>

                <div class="md:col-span-2 flex justify-end gap-3 pt-1">
                    <a href="{{ route('movements.index', [], false) }}" class="museum-btn-secondary museum-modal-cancel">Cancel</a>
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
    const movementForm = modal ? modal.querySelector('form[action="{{ route('movements.store', [], false) }}"]') : null;
    const locationSelects = modal ? Array.from(modal.querySelectorAll('[data-location-select]')) : [];

    if (!modal || !searchInput || !artworkIdInput || !suggestionBox || !movementForm) {
        return;
    }

    const artworkOptions = @json($artworkSearchOptions);
    const syncLocationCode = (select) => {
        const key = select.dataset.locationSelect;
        const codeInput = modal.querySelector(`[data-location-code-input="${key}"]`);

        if (!codeInput) {
            return;
        }

        codeInput.value = select.selectedOptions[0]?.dataset.code || '';
    };
    const suggestStatusForLocation = (type, name) => {
        const normalizedType = (type || '').toLowerCase();
        const normalizedName = (name || '').toLowerCase();

        if (normalizedType.includes('storage') || normalizedName.includes('store')) return 'In Storage';
        if (normalizedType.includes('residence')) return 'In Residence';
        if (normalizedType.includes('office')) return 'In Office';
        if (
            normalizedType.includes('museum')
            || normalizedType.includes('garden')
            || normalizedType.includes('hall')
            || normalizedType.includes('library')
            || normalizedType.includes('restaurant')
        ) return 'On Display';
        if (normalizedType.includes('disposition') || normalizedName.includes('sold') || normalizedName.includes('left')) return 'Sold or Left';
        if (normalizedType.includes('external')) return 'External';

        return null;
    };
    const syncStatusAfterMovement = (select) => {
        if (select.dataset.locationSelect !== 'to') {
            return;
        }

        const statusSelect = modal.querySelector('[data-status-after-movement]');
        const selectedOption = select.selectedOptions[0];
        const suggested = suggestStatusForLocation(selectedOption?.dataset.type, select.value);

        if (!statusSelect || !suggested) {
            return;
        }

        let option = Array.from(statusSelect.options).find((item) => item.value === suggested);
        if (!option) {
            option = document.createElement('option');
            option.value = suggested;
            option.text = suggested;
            statusSelect.appendChild(option);
        }

        statusSelect.value = suggested;
    };

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
        locationSelects.forEach((select) => {
            syncLocationCode(select);
            syncStatusAfterMovement(select);
        });

        if (String(artworkIdInput.value).trim() !== '') {
            searchInput.setCustomValidity('');
            return;
        }

        event.preventDefault();
        searchInput.setCustomValidity('Please select an artwork from suggestions.');
        searchInput.reportValidity();
    });

    locationSelects.forEach((select) => {
        syncLocationCode(select);
        syncStatusAfterMovement(select);
        select.addEventListener('change', () => syncLocationCode(select));
        select.addEventListener('change', () => syncStatusAfterMovement(select));
    });
});
    </script>
    @endif
</x-layout>
