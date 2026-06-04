<x-layout title="Edit Movement - Museum Azman">
    <section class="space-y-6">
        <div class="flex items-start justify-between gap-4">
            <div>
                <h2 class="museum-page-title">Edit Movement</h2>
                <p class="museum-page-subtitle">Update transfer details for {{ $movement->artwork?->title ?? 'selected artwork' }}</p>
            </div>
            <a href="{{ route('movements.index', [], false) }}" class="museum-btn-secondary">Back to Movement Tracker</a>
        </div>

        <article class="museum-panel p-5">
            <form action="{{ route('movements.update', $movement, false) }}" method="POST" class="grid gap-5 md:grid-cols-2">
                @csrf
                @method('PUT')

                <label class="museum-field md:col-span-2">
                    <span>Artwork <em class="text-rose-500 not-italic">*</em></span>
                    <select name="artwork_id" required>
                        @foreach($artworks as $artwork)
                            <option value="{{ $artwork->id }}" @selected(old('artwork_id', $movement->artwork_id) == $artwork->id)>
                                {{ $artwork->display_inventory_code }} - {{ $artwork->title }} - {{ $artwork->artist?->name ?? 'Unknown Artist' }}
                            </option>
                        @endforeach
                    </select>
                </label>

                @php
                    $selectedFrom = old('from_location', $movement->from_location);
                    $selectedFromCode = old('from_location_code', $movement->from_location_code);
                    $selectedTo = old('to_location', $movement->to_location);
                    $selectedToCode = old('to_location_code', $movement->to_location_code);
                    $selectedMovementType = old('movement_type', $movement->movement_type ?: $movement->reason);
                    $selectedExternalReason = old('external_reason', $movement->external_reason);
                    $selectedStatus = old('status', $movement->status);
                    $selectedHandler = old('responsible_handler', $movement->responsible_handler);
                    $locationOptionsList = collect($locationOptions);
                    $locationOptionRowsList = collect($locationOptionRows ?? []);
                    $handlerOptionsList = collect($handlerOptions ?? [])->values();
                    $movementTypeOptionsList = collect($movementTypeOptions ?? [])->values();
                    $externalReasonOptionsList = collect($externalReasonOptions ?? $reasonOptions ?? [])->sort()->values();
                    $statusOptionsList = collect($statusOptions)->sort()->values();
                @endphp

                <label class="museum-field">
                    <span>From Location <em class="text-rose-500 not-italic">*</em></span>
                    <input type="hidden" name="from_location_code" value="{{ $selectedFromCode }}" data-location-code-input="from">
                    <select name="from_location" required data-location-select="from">
                        <option value="">Select origin</option>
                        @foreach($locationOptionRowsList as $loc)
                            <option value="{{ $loc['name'] }}" data-code="{{ $loc['code'] }}" data-type="{{ $loc['type'] }}" @selected($selectedFrom === $loc['name'])>{{ $loc['label'] }}</option>
                        @endforeach
                        @if($selectedFrom && !$locationOptionsList->contains($selectedFrom))
                            <option value="{{ $selectedFrom }}" selected>{{ $selectedFrom }}</option>
                        @endif
                    </select>
                </label>

                <label class="museum-field">
                    <span>To Location <em class="text-rose-500 not-italic">*</em></span>
                    <input type="hidden" name="to_location_code" value="{{ $selectedToCode }}" data-location-code-input="to">
                    <select name="to_location" required data-location-select="to">
                        <option value="">Select destination</option>
                        @foreach($locationOptionRowsList as $loc)
                            <option value="{{ $loc['name'] }}" data-code="{{ $loc['code'] }}" data-type="{{ $loc['type'] }}" @selected($selectedTo === $loc['name'])>{{ $loc['label'] }}</option>
                        @endforeach
                        @if($selectedTo && !$locationOptionsList->contains($selectedTo))
                            <option value="{{ $selectedTo }}" selected>{{ $selectedTo }}</option>
                        @endif
                    </select>
                </label>

                <label class="museum-field">
                    <span>Movement Timestamp <em class="text-rose-500 not-italic">*</em></span>
                    <input type="date" name="date_out" value="{{ old('date_out', optional($movement->date_out)->toDateString()) }}" required>
                </label>

                <label class="museum-field">
                    <span>Expected Return Date</span>
                    <input type="date" name="expected_return_date" value="{{ old('expected_return_date', optional($movement->expected_return_date)->toDateString()) }}">
                </label>

                <label class="museum-field">
                    <span>Completed Date</span>
                    <input type="date" name="completed_date" value="{{ old('completed_date', optional($movement->completed_date)->toDateString()) }}">
                </label>

                <label class="museum-field">
                    <span>Movement Type <em class="text-rose-500 not-italic">*</em></span>
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
                        @foreach($statusOptionsList as $status)
                            <option value="{{ $status }}" @selected($selectedStatus === $status)>{{ $status }}</option>
                        @endforeach
                        @if($selectedStatus && !$statusOptionsList->contains($selectedStatus))
                            <option value="{{ $selectedStatus }}" selected>{{ $selectedStatus }}</option>
                        @endif
                    </select>
                </label>

                <label class="museum-field">
                    <span>External Reason</span>
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
                    <input type="text" name="external_party" value="{{ old('external_party', $movement->external_party) }}" placeholder="External party, auction house, or borrower">
                </label>

                <label class="museum-field">
                    <span>Moved By <em class="text-rose-500 not-italic">*</em></span>
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
                    <input type="text" name="approved_by" value="{{ old('approved_by', $movement->approved_by) }}" placeholder="Approver name">
                </label>

                <label class="museum-field md:col-span-2">
                    <span>Notes</span>
                    <textarea name="notes" rows="3" placeholder="Movement Log notes...">{{ old('notes', $movement->notes) }}</textarea>
                </label>

                <div class="md:col-span-2 flex justify-end gap-3 pt-1">
                    <a href="{{ route('movements.index', [], false) }}" class="museum-btn-secondary">Cancel</a>
                    <button type="submit" class="museum-btn">Save Changes</button>
                </div>
            </form>
        </article>
    </section>

    <script>
        document.addEventListener('DOMContentLoaded', () => {
            const form = document.querySelector('form[action="{{ route('movements.update', $movement, false) }}"]');
            if (!form) {
                return;
            }

            const syncLocationCode = (select) => {
                const key = select.dataset.locationSelect;
                const codeInput = form.querySelector(`[data-location-code-input="${key}"]`);

                if (!codeInput) {
                    return;
                }

                codeInput.value = select.selectedOptions[0]?.dataset.code || codeInput.value || '';
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

                const statusSelect = form.querySelector('[data-status-after-movement]');
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

            form.querySelectorAll('[data-location-select]').forEach((select) => {
                syncLocationCode(select);
                syncStatusAfterMovement(select);
                select.addEventListener('change', () => syncLocationCode(select));
                select.addEventListener('change', () => syncStatusAfterMovement(select));
            });
        });
    </script>
</x-layout>
