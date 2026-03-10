<x-layout title="Movement Tracker - Museum Azman">
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
                <p>In Transit</p>
                <strong class="text-amber-600">{{ $stats['in_transit'] }}</strong>
                <span class="mt-1 block text-zinc-600">Currently moving</span>
            </div>
            <div class="museum-stat-card">
                <p>Scheduled</p>
                <strong class="text-blue-600">{{ $stats['scheduled'] }}</strong>
                <span class="mt-1 block text-zinc-600">Upcoming movements</span>
            </div>
            <div class="museum-stat-card">
                <p>Completed</p>
                <strong class="text-emerald-600">{{ $stats['completed'] }}</strong>
                <span class="mt-1 block text-zinc-600">Total movements</span>
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
                    </tr>
                    </thead>
                    <tbody>
                    @forelse($movements as $movement)
                        @php
                            $status = $movement->status;
                            $statusClass = match($status) {
                                'Completed' => 'bg-emerald-100 text-emerald-700',
                                'Scheduled' => 'bg-blue-100 text-blue-700',
                                default => 'bg-amber-100 text-amber-700',
                            };
                        @endphp
                        <tr class="border-b border-zinc-100 align-top">
                            <td class="py-3">
                                <p class="font-semibold">{{ $movement->artwork?->title }}</p>
                                <p class="text-zinc-500">{{ $movement->artwork?->artist?->name }}</p>
                            </td>
                            <td class="py-3">{{ $movement->from_location }}</td>
                            <td class="py-3">{{ $movement->to_location }}</td>
                            <td class="py-3">{{ $movement->date_out?->format('M j, Y') }}</td>
                            <td class="py-3">{{ $movement->expected_return_date?->format('M j, Y') ?? '-' }}</td>
                            <td class="py-3">{{ $movement->responsible_handler }}</td>
                            <td class="py-3"><span class="rounded-md border border-zinc-200 px-2 py-1">{{ $movement->reason }}</span></td>
                            <td class="py-3"><span class="rounded-lg px-2.5 py-1 text-xs font-semibold {{ $statusClass }}">{{ $status }}</span></td>
                        </tr>
                    @empty
                        <tr>
                            <td class="py-4 text-zinc-500" colspan="8">No movement records yet.</td>
                        </tr>
                    @endforelse
                    </tbody>
                </table>
            </div>
        </article>

        <article class="museum-panel p-5">
            <h3 class="museum-section-title">Active Movements</h3>
            <p class="text-zinc-600">Detailed view of artworks currently in transit</p>

            <div class="mt-4 space-y-4">
                @forelse($activeMovements as $movement)
                    <div class="rounded-xl border border-zinc-200 bg-white p-4">
                        <div class="mb-2 flex items-center justify-between">
                            <div>
                                <h4 class="museum-card-title">{{ $movement->artwork?->title }}</h4>
                                <p class="text-zinc-600">{{ $movement->artwork?->artist?->name }}</p>
                            </div>
                            <span class="rounded-lg bg-amber-100 px-3 py-1 text-sm font-semibold text-amber-700">{{ $movement->status }}</span>
                        </div>

                        <div class="grid gap-4 md:grid-cols-2">
                            <div>
                                <p class="text-zinc-500">From</p>
                                <p class="font-semibold">{{ $movement->from_location }}</p>
                                <p class="mt-2 text-zinc-500">Date Out</p>
                                <p>{{ $movement->date_out?->format('M j, Y') }}</p>
                                <p class="mt-2 text-zinc-500">Handler</p>
                                <p>{{ $movement->responsible_handler }}</p>
                            </div>
                            <div>
                                <p class="text-zinc-500">To</p>
                                <p class="font-semibold">{{ $movement->to_location }}</p>
                                <p class="mt-2 text-zinc-500">Expected Return</p>
                                <p>{{ $movement->expected_return_date?->format('M j, Y') ?? '-' }}</p>
                                <p class="mt-2 text-zinc-500">Reason</p>
                                <p><span class="rounded-md border border-zinc-200 px-2 py-0.5 text-sm">{{ $movement->reason }}</span></p>
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
                    </div>
                @empty
                    <p class="text-zinc-500">No active movements.</p>
                @endforelse
            </div>
        </article>

    </section>

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
            <a href="{{ route('movements.index') }}" class="museum-modal-close" aria-label="Close">&times;</a>

            <span id="movement-modal-title" class="museum-section-title block">Record Movement</span>
            <p class="mt-2 text-zinc-600">Create a new movement record for artwork transfer, loan, or exhibition</p>

            <form action="{{ route('movements.store') }}" method="POST" class="mt-6 grid gap-5 md:grid-cols-2">
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
                    <select name="from_location" required>
                        <option value="">Select origin</option>
                        @foreach($locationOptions as $loc)
                            <option value="{{ $loc }}" @selected(old('from_location') === $loc)>{{ $loc }}</option>
                        @endforeach
                    </select>
                </label>

                <label class="museum-field">
                    <span>To Location <em class="text-rose-500 not-italic">*</em></span>
                    <select name="to_location" required>
                        <option value="">Select destination</option>
                        @foreach($locationOptions as $loc)
                            <option value="{{ $loc }}" @selected(old('to_location') === $loc)>{{ $loc }}</option>
                        @endforeach
                    </select>
                </label>

                <label class="museum-field">
                    <span>Date Out <em class="text-rose-500 not-italic">*</em></span>
                    <input type="date" name="date_out" value="{{ old('date_out') }}" placeholder="dd-mm-yyyy" required>
                </label>

                <label class="museum-field">
                    <span>Expected Return Date</span>
                    <input type="date" name="expected_return_date" value="{{ old('expected_return_date') }}" placeholder="dd-mm-yyyy">
                </label>

                <label class="museum-field">
                    <span>Responsible Handler <em class="text-rose-500 not-italic">*</em></span>
                    <input name="responsible_handler" value="{{ old('responsible_handler') }}" placeholder="e.g., Crown Fine Art" required>
                </label>

                <label class="museum-field">
                    <span>Reason <em class="text-rose-500 not-italic">*</em></span>
                    <select name="reason" required>
                        @foreach(['Loan','Exhibition','Storage','Restoration','Sale Prep'] as $reason)
                            <option value="{{ $reason }}" @selected(old('reason', 'Exhibition')===$reason)>{{ $reason }}</option>
                        @endforeach
                    </select>
                </label>

                <label class="museum-field md:col-span-1">
                    <span>Status <em class="text-rose-500 not-italic">*</em></span>
                    <select name="status" required>
                        @foreach(['Scheduled','In Transit','Completed','Overdue'] as $status)
                            <option value="{{ $status }}" @selected(old('status','Scheduled')===$status)>{{ $status }}</option>
                        @endforeach
                    </select>
                </label>
                <div class="hidden md:block"></div>

                <label class="museum-field md:col-span-2">
                    <span>Movement Notes</span>
                    <textarea name="notes" rows="2" placeholder="Add any additional notes about this movement...">{{ old('notes') }}</textarea>
                </label>

                <label class="museum-field md:col-span-2">
                    <span>Condition Report</span>
                    <textarea name="condition_report" rows="2" placeholder="Document the condition of the artwork before movement...">{{ old('condition_report') }}</textarea>
                </label>

                <div class="md:col-span-2 flex justify-end gap-3 pt-1">
                    <a href="{{ route('movements.index') }}" class="museum-btn-secondary museum-modal-cancel">Cancel</a>
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
    const movementForm = modal ? modal.querySelector('form[action="{{ route('movements.store') }}"]') : null;

    if (!modal || !searchInput || !artworkIdInput || !suggestionBox || !movementForm) {
        return;
    }

    const artworkOptions = @json($artworkSearchOptions);

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
</x-layout>
