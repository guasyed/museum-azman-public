<x-layout :title="$artwork->title.' - Museum Azman'">
    <style>
        .artwork-image-container {
            overflow: hidden;
        }

        .artwork-image {
            transition: transform 0.35s ease-in-out;
            cursor: zoom-in;
        }

        .artwork-image-container:hover .artwork-image {
            transform: scale(1.15);
        }

    </style>

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
        $artworksIndexRoute = route('artworks.index', [], false);
        $artworksIndexPath = parse_url($artworksIndexRoute, PHP_URL_PATH) ?: $artworksIndexRoute;
        $returnUrl = request()->query('return');
        $returnPath = is_string($returnUrl) ? parse_url($returnUrl, PHP_URL_PATH) : null;
        $isSafeArtworksReturnUrl = is_string($returnUrl)
            && $returnUrl !== ''
            && parse_url($returnUrl, PHP_URL_HOST) === request()->getHost()
            && is_string($returnPath)
            && $returnPath === $artworksIndexPath
            && str_starts_with($returnPath, '/');
        $backRoute = $artworksIndexRoute;
        $backLabel = 'Back to Artworks';
        $selfRoute = route('artworks.show', [
            'artwork' => $artwork,
            'from' => $origin,
            'return' => $isSafeArtworksReturnUrl ? $returnUrl : null,
        ], false);

        $descriptionText = trim((string) ($artwork->description ?? ''));

        $derivedYear = null;
        if ($descriptionText !== '' && preg_match('/\b(1[89]\d{2}|20\d{2}|21\d{2})\b/u', $descriptionText, $yearMatch) === 1) {
            $derivedYear = $yearMatch[1];
        }

        $derivedDimensions = null;
        if ($descriptionText !== '' && preg_match('/(\d+(?:\.\d+)?)\s*(?:cm)?\s*[x×]\s*(\d+(?:\.\d+)?)\s*(?:cm)?/iu', $descriptionText, $dimensionMatch) === 1) {
            $left = rtrim(rtrim($dimensionMatch[1], '0'), '.');
            $right = rtrim(rtrim($dimensionMatch[2], '0'), '.');
            $derivedDimensions = $left.' × '.$right.' cm';
        }

        $derivedMedium = null;
        if ($descriptionText !== '') {
            $mediumCandidate = preg_replace('/\b(1[89]\d{2}|20\d{2}|21\d{2})\b/u', '', $descriptionText, 1);
            $mediumCandidate = preg_replace('/(\d+(?:\.\d+)?)\s*(?:cm)?\s*[x×]\s*(\d+(?:\.\d+)?)\s*(?:cm)?/iu', '', (string) $mediumCandidate, 1);
            $mediumCandidate = trim((string) preg_replace('/\s{2,}/', ' ', (string) $mediumCandidate));

            if ($mediumCandidate !== '') {
                $derivedMedium = $mediumCandidate;
            }
        }

        $displayYear = (string) ($artwork->year ?: ($derivedYear ?? '-'));
        $displayMedium = (string) ($artwork->medium ?: ($derivedMedium ?? '-'));

        $derivedAcquisitionDate = null;
        if ($descriptionText !== '') {
            if (preg_match('/\b(\d{4}-\d{1,2}-\d{1,2})\b/u', $descriptionText, $isoDateMatch) === 1) {
                $derivedAcquisitionDate = $isoDateMatch[1];
            } elseif (preg_match('/\b(\d{1,2}[\/\-]\d{1,2}[\/\-]\d{2,4})\b/u', $descriptionText, $slashDateMatch) === 1) {
                $normalizedDate = str_replace('/', '-', $slashDateMatch[1]);
                if (strtotime($normalizedDate) !== false) {
                    $derivedAcquisitionDate = date('Y-m-d', strtotime($normalizedDate));
                }
            }
        }

        $displayAcquisitionDate = \App\Support\DateFormat::display($artwork->acquisition_date);
        if ($displayAcquisitionDate === '-') {
            if ($derivedAcquisitionDate) {
                $displayAcquisitionDate = \App\Support\DateFormat::display($derivedAcquisitionDate);
            } elseif ($derivedYear) {
                $displayAcquisitionDate = $derivedYear;
            }
        }

        $hasStoredDimensions = !is_null($artwork->size_from_cm) && !is_null($artwork->size_to_cm);
        if ($hasStoredDimensions) {
            $displayDimensions = $artwork->size_from_cm.' × '.$artwork->size_to_cm.' cm';
        } else {
            $displayDimensions = $derivedDimensions ?? '-';
        }
    @endphp

    <section class="space-y-4">
        <div class="flex items-center justify-between gap-3">
            <a href="{{ $backRoute }}" class="inline-flex items-center gap-2 text-sm font-semibold text-zinc-700 hover:text-zinc-900">
                <span>←</span>
                <span>{{ $backLabel }}</span>
            </a>

            <a href="#record-movement" class="hidden museum-btn text-xs" style="display: none !important;">+ Record Movement</a>
        </div>

        <div class="grid gap-4 xl:grid-cols-[1.55fr_0.78fr]">
            <div class="space-y-4">
                <article class="museum-panel overflow-hidden p-0">
                    <div class="artwork-image-container h-130">
                        @if($artwork->primary_image_url)
                            <img
                                src="{{ $artwork->primary_image_url }}"
                                alt="{{ $artwork->title }}"
                                class="artwork-image h-130 w-full object-cover"
                                onerror="this.classList.add('hidden'); this.parentElement.nextElementSibling.classList.remove('hidden');"
                            >
                            <div class="hidden h-130 flex items-center justify-center bg-zinc-100 text-zinc-500">No Image</div>
                        @else
                            <div class="flex h-130 items-center justify-center bg-zinc-100 text-zinc-500">No Image</div>
                        @endif
                    </div>

                    <div class="space-y-5 p-5">
                        <div class="flex items-end justify-between gap-3 border-b border-zinc-200 pb-3">
                            <div>
                                <!--<p class="mb-2 font-mono text-sm font-semibold uppercase tracking-wide text-zinc-500">{{ $artwork->display_inventory_code }}</p>-->
                                <h2 class="museum-page-title text-[2rem]!">{{ $artwork->title }}</h2>
                                <p class="mt-1 text-zinc-600">{{ $artwork->artist?->name ?? 'Unknown Artist' }}</p>
                            </div>
                            <span class="inline-flex rounded-lg px-2.5 py-1 text-xs font-semibold {{ $statusClass }}">{{ $artwork->status ?: 'Unknown' }}</span>
                        </div>

                        <div class="grid gap-x-8 gap-y-3 border-b border-zinc-200 pb-4 text-sm md:grid-cols-2">
                            <div><p class="text-zinc-500">Artwork ID</p><p class="font-mono font-medium">{{ $artwork->display_inventory_code }}</p></div>
                            <div><p class="text-zinc-500">Year</p><p class="font-medium">{{ $displayYear }}</p></div>
                            <div><p class="text-zinc-500">Medium</p><p class="font-medium">{{ $displayMedium }}</p></div>
                            <div><p class="text-zinc-500">Dimensions</p><p class="font-medium">{{ $displayDimensions }}</p></div>
                            <div><p class="text-zinc-500">Country of Origin</p><p class="font-medium">{{ $artwork->artist?->country ?: 'Malaysia' }}</p></div>
                            <div><p class="text-zinc-500">Region</p><p class="font-medium">{{ $artwork->artist?->country ?: '-' }}</p></div>
                            <div><p class="text-zinc-500">Acquisition Date</p><p class="font-medium">{{ $displayAcquisitionDate }}</p></div>
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

                        @if($artwork->remarks)
                            <div class="space-y-2 border-t border-zinc-200 pt-4 text-sm">
                                <p class="text-zinc-500">Remarks</p>
                                <p class="whitespace-pre-wrap text-zinc-700">{{ $artwork->remarks }}</p>
                            </div>
                        @endif
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
                                        <p><span class="text-zinc-500">From Location:</span> {{ $movement->from_location ?: '-' }}{{ $movement->from_location_code ? ' ('.$movement->from_location_code.')' : '' }}</p>
                                        <p><span class="text-zinc-500">To Location:</span> {{ $movement->to_location ?: '-' }}{{ $movement->to_location_code ? ' ('.$movement->to_location_code.')' : '' }}</p>
                                        <p><span class="text-zinc-500">Movement Type:</span> {{ $movement->movement_type ?: '-' }}</p>
                                        <p><span class="text-zinc-500">External Reason:</span> {{ $movement->external_reason ?: '-' }}</p>
                                        <p><span class="text-zinc-500">External Party:</span> {{ $movement->external_party ?: '-' }}</p>
                                        <p><span class="text-zinc-500">Moved By:</span> {{ $movement->responsible_handler ?: '-' }}</p>
                                        <p><span class="text-zinc-500">Approved By:</span> {{ $movement->approved_by ?: '-' }}</p>
                                        <p><span class="text-zinc-500">Expected Return:</span> {{ \App\Support\DateFormat::display($movement->expected_return_date) }}</p>
                                        <p><span class="text-zinc-500">Completed Date:</span> {{ \App\Support\DateFormat::display($movement->completed_date) }}</p>
                                    </div>

                                    <div class="space-y-2 rounded-lg border border-zinc-200 bg-zinc-50 px-3 py-2.5">
                                        <p class="text-[11px] font-semibold uppercase tracking-wide text-zinc-500">Documentation</p>
                                        @if($movement->notes)
                                            <p class="text-sm text-zinc-700"><span class="text-zinc-500">Notes:</span> {{ $movement->notes }}</p>
                                        @else
                                            <p class="text-sm text-zinc-500">Notes: -</p>
                                        @endif

                                        <p class="text-sm text-zinc-500">Status After Movement: {{ $movement->status ?: '-' }}</p>
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
                    <a href="{{ route('artworks.edit', ['artwork' => $artwork, 'from' => $origin, 'return' => $isSafeArtworksReturnUrl ? $returnUrl : null]) }}" class="museum-btn w-full justify-center">Edit Artwork</a>
                </article>
            </aside>
        </div>
    </section>

    <div class="museum-modal-overlay {{ $errors->any() ? 'is-open' : '' }}" id="record-movement">
        <div class="museum-modal" role="dialog" aria-modal="true" aria-labelledby="artwork-movement-modal-title">
            <a href="{{ $selfRoute }}" class="museum-modal-close" aria-label="Close">&times;</a>

            <span id="artwork-movement-modal-title" class="museum-section-title block">Record Artwork Movement</span>

            <form action="{{ route('movements.store', [], false) }}" method="POST" class="mt-5 grid gap-4 md:grid-cols-2">
                @csrf
                <input type="hidden" name="artwork_id" value="{{ $artwork->id }}">

                <label class="museum-field md:col-span-2">
                    <span>From Location <em class="text-rose-500 not-italic">*</em></span>
                    @php
                        $selectedFromLocation = old('from_location', $artwork->location?->name);
                        $selectedToLocation = old('to_location');
                        $locationOptionsList = collect($locationOptions);
                    @endphp
                    <select id="from_location" name="from_location" required>
                        <option value="">Select origin</option>
                        @foreach($locationOptions as $loc)
                            <option value="{{ $loc }}" @selected($selectedFromLocation === $loc)>{{ $loc }}</option>
                        @endforeach
                        @if($selectedFromLocation && !$locationOptionsList->contains($selectedFromLocation))
                            <option value="{{ $selectedFromLocation }}" selected>{{ $selectedFromLocation }}</option>
                        @endif
                    </select>
                </label>

                <label class="museum-field md:col-span-2">
                    <span>To Location <em class="text-rose-500 not-italic">*</em></span>
                    <select id="to_location" name="to_location" required>
                        <option value="">Select destination</option>
                        @foreach($locationOptions as $loc)
                            <option value="{{ $loc }}" @selected($selectedToLocation === $loc)>{{ $loc }}</option>
                        @endforeach
                        @if($selectedToLocation && !$locationOptionsList->contains($selectedToLocation))
                            <option value="{{ $selectedToLocation }}" selected>{{ $selectedToLocation }}</option>
                        @endif
                    </select>
                </label>

                <label class="museum-field">
                    <span>Movement Timestamp <em class="text-rose-500 not-italic">*</em></span>
                    <input type="date" name="date_out" value="{{ old('date_out', now()->toDateString()) }}" required>
                </label>

                <label class="museum-field">
                    <span>Expected Return</span>
                    <input type="date" name="expected_return_date" value="{{ old('expected_return_date') }}">
                </label>

                <label class="museum-field">
                    <span>Movement Type <em class="text-rose-500 not-italic">*</em></span>
                    <select name="movement_type" required>
                        @foreach($movementTypeOptions as $movementType)
                            <option value="{{ $movementType }}" @selected(old('movement_type', 'Storage') === $movementType)>{{ $movementType }}</option>
                        @endforeach
                    </select>
                </label>

                <label class="museum-field">
                    <span>Status After Movement <em class="text-rose-500 not-italic">*</em></span>
                    <select id="movement_status" name="status" required>
                        @foreach($statusOptions as $status)
                            <option value="{{ $status }}" @selected(old('status', 'Scheduled') === $status)>{{ $status }}</option>
                        @endforeach
                    </select>
                </label>

                <label class="museum-field md:col-span-2">
                    <span>External Reason</span>
                    <select name="external_reason">
                        <option value="">Select external reason</option>
                        @foreach($externalReasonOptions as $externalReason)
                            <option value="{{ $externalReason }}" @selected(old('external_reason') === $externalReason)>{{ $externalReason }}</option>
                        @endforeach
                    </select>
                </label>

                <label class="museum-field">
                    <span>External Party</span>
                    <input name="external_party" value="{{ old('external_party') }}" placeholder="External party, auction house, or borrower">
                </label>

                <label class="museum-field">
                    <span>Moved By <em class="text-rose-500 not-italic">*</em></span>
                    <input name="responsible_handler" value="{{ old('responsible_handler') }}" placeholder="Mover or handler name" required>
                </label>

                <label class="museum-field md:col-span-2">
                    <span>Approved By</span>
                    <input name="approved_by" value="{{ old('approved_by') }}" placeholder="Approver name">
                </label>

                <label class="museum-field md:col-span-2">
                    <span>Notes</span>
                    <textarea name="notes" rows="3" placeholder="Movement Log notes...">{{ old('notes') }}</textarea>
                </label>

                <div class="md:col-span-2 flex justify-end gap-3">
                    <a href="{{ $selfRoute }}" class="museum-btn-secondary museum-modal-cancel">Cancel</a>
                    <button type="submit" class="museum-btn museum-modal-submit">Record Movement</button>
                </div>
            </form>
        </div>
    </div>

    <script>
        (function () {
            const locationMeta = @json($locationMeta ?? []);
            const toSel = document.getElementById('to_location');
            const statusSel = document.getElementById('movement_status');

            if (! toSel || ! statusSel) {
                return;
            }

            function suggestStatusForLocation(type, name) {
                const t = (type || '').toLowerCase();
                const n = (name || '').toLowerCase();

                if (t.includes('storage') || n.includes('store')) return 'In Storage';
                if (t.includes('museum') || t.includes('garden') || t.includes('hall') || t.includes('library') || t.includes('restaurant')) return 'On Display';
                if (t.includes('external')) return 'External';
                if (t.includes('disposition') || n.includes('sold') || n.includes('left')) return 'Sold or Left';
                if (t.includes('office')) return 'In Office';
                if (t.includes('residence')) return 'In Residence';

                return null;
            }

            function applySuggested() {
                const sel = toSel.value;
                if (! sel) return;
                const type = locationMeta[sel] ?? null;
                const suggested = suggestStatusForLocation(type, sel);
                if (! suggested) return;

                let opt = Array.from(statusSel.options).find(o => o.value === suggested);
                if (! opt) {
                    opt = document.createElement('option');
                    opt.value = suggested;
                    opt.text = suggested;
                    statusSel.appendChild(opt);
                }

                statusSel.value = suggested;
            }

            toSel.addEventListener('change', applySuggested);
            // Initialize on load if a destination is preselected
            document.addEventListener('DOMContentLoaded', function () { applySuggested(); });
        })();
    </script>

</x-layout>
