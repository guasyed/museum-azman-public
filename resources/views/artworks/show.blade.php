<x-layout :title="$artwork->title.' - Museum Azman'">
    @php
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
    @endphp

    <section class="space-y-4">
        <div class="flex items-center justify-between gap-3">
            <a href="{{ $backRoute }}" class="inline-flex items-center gap-2 text-sm font-semibold text-zinc-700 hover:text-zinc-900">
                <span>←</span>
                <span>{{ $backLabel }}</span>
            </a>

            <a href="#record-movement" class="museum-btn text-xs">+ Record Movement</a>
        </div>

        <div class="grid gap-4 xl:grid-cols-[1.55fr_0.78fr]">
            <div class="space-y-4">
                <article class="museum-panel overflow-hidden p-0">
                    @if($artwork->primary_image_url)
                        <img
                            src="{{ $artwork->primary_image_url }}"
                            alt="{{ $artwork->title }}"
                            class="h-130 w-full object-cover"
                            onerror="this.classList.add('hidden'); this.nextElementSibling.classList.remove('hidden');"
                        >
                        <div class="hidden h-130 flex items-center justify-center bg-zinc-100 text-zinc-500">No Image</div>
                    @else
                        <div class="flex h-130 items-center justify-center bg-zinc-100 text-zinc-500">No Image</div>
                    @endif

                    <div class="space-y-5 p-5">
                        <div class="flex items-end justify-between gap-3 border-b border-zinc-200 pb-3">
                            <div>
                                <h2 class="museum-page-title text-[2rem]!">{{ $artwork->title }}</h2>
                                <p class="mt-1 text-zinc-600">{{ $artwork->artist?->name ?? 'Unknown Artist' }}</p>
                            </div>
                            <span class="inline-flex rounded-lg px-2.5 py-1 text-xs font-semibold {{ $statusClass }}">{{ $artwork->status ?: 'Unknown' }}</span>
                        </div>

                        <div class="grid gap-x-8 gap-y-3 border-b border-zinc-200 pb-4 text-sm md:grid-cols-2">
                            <div><p class="text-zinc-500">Year</p><p class="font-medium">{{ $artwork->year ?: '-' }}</p></div>
                            <div><p class="text-zinc-500">Medium</p><p class="font-medium">{{ $artwork->medium ?: '-' }}</p></div>
                            <div><p class="text-zinc-500">Dimensions</p><p class="font-medium">{{ $artwork->size_from_cm ?: '-' }} × {{ $artwork->size_to_cm ?: '-' }} cm</p></div>
                            <div><p class="text-zinc-500">Country of Origin</p><p class="font-medium">{{ $artwork->artist?->country ?: 'Malaysia' }}</p></div>
                            <div><p class="text-zinc-500">Region</p><p class="font-medium">{{ $artwork->artist?->country ?: '-' }}</p></div>
                            <div><p class="text-zinc-500">Acquisition Date</p><p class="font-medium">{{ \App\Support\DateFormat::display($artwork->acquisition_date) }}</p></div>
                        </div>

                        <div class="space-y-2 border-b border-zinc-200 pb-4 text-sm">
                            <p class="text-zinc-500">Description</p>
                            <p class="text-zinc-700">{{ $artwork->description ?: '-' }}</p>
                        </div>

                        <div class="rounded-xl bg-zinc-100 px-4 py-3 text-sm">
                            <p class="mb-1 text-zinc-500">Current Location</p>
                            <p class="font-semibold text-zinc-900">{{ $artwork->location?->name ?: 'Unknown Location' }}</p>
                        </div>

                        <div class="space-y-2 text-sm">
                            <p class="text-zinc-500">Provenance</p>
                            <p class="text-zinc-700">{{ $artwork->provenance ?: '-' }}</p>
                        </div>
                    </div>
                </article>

                <article class="museum-panel p-0 overflow-hidden">
                    <div class="grid grid-cols-2 border-b border-zinc-200 bg-zinc-50 text-center text-xs font-semibold text-zinc-700">
                        <div class="px-4 py-2">Movement History</div>
                        <div class="px-4 py-2">Documentation</div>
                    </div>

                    <div class="p-4 text-sm">
                        @forelse($artwork->movements->take(6) as $movement)
                            <div class="border-b border-zinc-200 py-3.5 last:border-b-0">
                                <div class="mb-2 flex items-center justify-between gap-2">
                                    <p class="text-[0.8rem] font-semibold leading-none text-zinc-900">{{ \App\Support\DateFormat::display($movement->date_out) }}</p>
                                    <span class="inline-flex rounded-xl bg-zinc-900 px-3 py-1 text-[0.8rem] font-semibold text-white">{{ $movement->status ?: 'Completed' }}</span>
                                </div>

                                <div class="grid gap-4 md:grid-cols-2">
                                    <div class="space-y-1.5 text-[0.8rem] text-zinc-700">
                                        <p><span class="text-zinc-500">From:</span> {{ $movement->from_location ?: '-' }}</p>
                                        <p><span class="text-zinc-500">To:</span> {{ $movement->to_location ?: '-' }}</p>
                                        <p><span class="text-zinc-500">Responsible Handler:</span> {{ $movement->responsible_handler ?: '-' }}</p>
                                        <p><span class="text-zinc-500">Reason:</span> {{ $movement->reason ?: '-' }}</p>
                                        <p><span class="text-zinc-500">Expected Return:</span> {{ \App\Support\DateFormat::display($movement->expected_return_date) }}</p>
                                    </div>

                                    <div class="space-y-2 rounded-lg border border-zinc-200 bg-zinc-50 px-3 py-2.5">
                                        <p class="text-[11px] font-semibold uppercase tracking-wide text-zinc-500">Documentation</p>
                                        @if($movement->notes)
                                            <p class="text-sm text-zinc-700"><span class="text-zinc-500">Notes:</span> {{ $movement->notes }}</p>
                                        @else
                                            <p class="text-sm text-zinc-500">Notes: -</p>
                                        @endif

                                        @if($movement->condition_report)
                                            <p class="text-sm text-zinc-700"><span class="text-zinc-500">Condition:</span> {{ $movement->condition_report }}</p>
                                        @else
                                            <p class="text-sm text-zinc-500">Condition: -</p>
                                        @endif
                                    </div>
                                </div>
                            </div>
                        @empty
                            <p class="text-zinc-500">No movement history yet.</p>
                        @endforelse
                    </div>
                </article>
            </div>

            <aside class="space-y-4">
                <article class="museum-panel">
                    <h3 class="museum-section-title text-base!">Financial Summary</h3>
                    <div class="mt-4 space-y-3 text-sm">
                        <div>
                            <p class="text-zinc-500">Purchase Price</p>
                            <p class="font-semibold text-zinc-900">{{ \App\Support\Currency::symbol() }}{{ number_format((float) $artwork->acquisition_price, 2) }}</p>
                        </div>

                        <div class="border-t border-zinc-200 pt-3">
                            <p class="text-zinc-500">Current Valuation</p>
                            <p class="font-semibold text-zinc-900">{{ \App\Support\Currency::symbol() }}{{ number_format((float) $artwork->current_valuation, 2) }}</p>
                        </div>

                        <div class="border-t border-zinc-200 pt-3">
                            <p class="text-zinc-500">Unrealised Gain/Loss</p>
                            @php $gain = (float) $artwork->current_valuation - (float) $artwork->acquisition_price; @endphp
                            <p class="font-semibold {{ $gain >= 0 ? 'text-emerald-600' : 'text-rose-600' }}">
                                {{ $gain >= 0 ? '+' : '-' }}{{ \App\Support\Currency::symbol() }}{{ number_format(abs($gain), 2) }}
                            </p>
                        </div>

                        <div class="border-t border-zinc-200 pt-3">
                            <p class="text-zinc-500">Insurance Coverage</p>
                            <p class="font-semibold text-zinc-900">{{ \App\Support\Currency::symbol() }}{{ number_format((float) $artwork->current_valuation, 2) }}</p>
                        </div>
                    </div>
                </article>

                <article class="museum-panel text-center">
                    <h3 class="museum-section-title text-base! text-left">Artwork QR Code</h3>
                    <img src="{{ $qrCodeUrl }}" alt="QR code for {{ $artwork->title }}" class="mx-auto mt-4 h-40 w-40 rounded-md border border-zinc-200 bg-white p-2">
                    <p class="mt-3 text-xs text-zinc-500">Scan to view artwork details</p>
                </article>

                <article class="museum-panel">
                    <a href="{{ route('artworks.edit', ['artwork' => $artwork, 'from' => $origin, 'return' => $isSafeReturnUrl ? $returnUrl : null]) }}" class="museum-btn w-full justify-center">Edit Artwork</a>
                </article>
            </aside>
        </div>
    </section>

    <div class="museum-modal-overlay {{ $errors->any() ? 'is-open' : '' }}" id="record-movement">
        <div class="museum-modal" role="dialog" aria-modal="true" aria-labelledby="artwork-movement-modal-title">
            <a href="{{ $selfRoute }}" class="museum-modal-close" aria-label="Close">&times;</a>

            <span id="artwork-movement-modal-title" class="museum-section-title block">Record Artwork Movement</span>

            <form action="{{ route('movements.store') }}" method="POST" class="mt-5 grid gap-4 md:grid-cols-2">
                @csrf
                <input type="hidden" name="artwork_id" value="{{ $artwork->id }}">

                <label class="museum-field md:col-span-2">
                    <span>From Location <em class="text-rose-500 not-italic">*</em></span>
                    <input
                        type="text"
                        name="from_location"
                        list="movement-location-options"
                        value="{{ old('from_location', $artwork->location?->name) }}"
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
                        value="{{ old('to_location') }}"
                        placeholder="e.g., Main Gallery - Wall B"
                        required
                    >
                </label>

                <datalist id="movement-location-options">
                    @foreach($locationOptions as $loc)
                        <option value="{{ $loc }}"></option>
                    @endforeach
                </datalist>

                <label class="museum-field">
                    <span>Date Out <em class="text-rose-500 not-italic">*</em></span>
                    <input type="date" name="date_out" value="{{ old('date_out', now()->toDateString()) }}" required>
                </label>

                <label class="museum-field">
                    <span>Expected Return</span>
                    <input type="date" name="expected_return_date" value="{{ old('expected_return_date') }}">
                </label>

                <label class="museum-field">
                    <span>Reason <em class="text-rose-500 not-italic">*</em></span>
                    <select name="reason" required>
                        @foreach($reasonOptions as $reason)
                            <option value="{{ $reason }}" @selected(old('reason', 'Storage') === $reason)>{{ $reason }}</option>
                        @endforeach
                    </select>
                </label>

                <label class="museum-field">
                    <span>Responsible Handler <em class="text-rose-500 not-italic">*</em></span>
                    <input name="responsible_handler" value="{{ old('responsible_handler') }}" placeholder="Your name or handler's name" required>
                </label>

                <label class="museum-field md:col-span-2">
                    <span>Status <em class="text-rose-500 not-italic">*</em></span>
                    <select name="status" required>
                        @foreach($statusOptions as $status)
                            <option value="{{ $status }}" @selected(old('status', 'Scheduled') === $status)>{{ $status }}</option>
                        @endforeach
                    </select>
                </label>

                <label class="museum-field md:col-span-2">
                    <span>Notes (Optional)</span>
                    <textarea name="notes" rows="3" placeholder="Add any relevant notes about this movement...">{{ old('notes') }}</textarea>
                </label>

                <label class="museum-field md:col-span-2">
                    <span>Condition Report</span>
                    <textarea name="condition_report" rows="2" placeholder="Document the artwork condition before movement...">{{ old('condition_report') }}</textarea>
                </label>

                <div class="md:col-span-2 flex justify-end gap-3">
                    <a href="{{ $selfRoute }}" class="museum-btn-secondary museum-modal-cancel">Cancel</a>
                    <button type="submit" class="museum-btn museum-modal-submit">Record Movement</button>
                </div>
            </form>
        </div>
    </div>
</x-layout>
