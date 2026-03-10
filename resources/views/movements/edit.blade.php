<x-layout title="Edit Movement - Museum Azman">
    <section class="space-y-6">
        <div class="flex items-start justify-between gap-4">
            <div>
                <h2 class="museum-page-title">Edit Movement</h2>
                <p class="museum-page-subtitle">Update transfer details for {{ $movement->artwork?->title ?? 'selected artwork' }}</p>
            </div>
            <a href="{{ route('movements.index') }}" class="museum-btn-secondary">Back to Movement Tracker</a>
        </div>

        <article class="museum-panel p-5">
            <form action="{{ route('movements.update', $movement) }}" method="POST" class="grid gap-5 md:grid-cols-2">
                @csrf
                @method('PUT')

                <label class="museum-field md:col-span-2">
                    <span>Artwork <em class="text-rose-500 not-italic">*</em></span>
                    <select name="artwork_id" required>
                        @foreach($artworks as $artwork)
                            <option value="{{ $artwork->id }}" @selected(old('artwork_id', $movement->artwork_id) == $artwork->id)>
                                {{ $artwork->title }} - {{ $artwork->artist?->name ?? 'Unknown Artist' }}
                            </option>
                        @endforeach
                    </select>
                </label>

                @php
                    $selectedFrom = old('from_location', $movement->from_location);
                    $selectedTo = old('to_location', $movement->to_location);
                    $selectedReason = old('reason', $movement->reason);
                    $selectedStatus = old('status', $movement->status);
                    $locationOptionsList = collect($locationOptions);
                    $reasonOptionsList = collect($reasonOptions)->sort()->values();
                    $statusOptionsList = collect($statusOptions)->sort()->values();
                @endphp

                <label class="museum-field">
                    <span>From Location <em class="text-rose-500 not-italic">*</em></span>
                    <select name="from_location" required>
                        <option value="">Select origin</option>
                        @foreach($locationOptions as $loc)
                            <option value="{{ $loc }}" @selected($selectedFrom === $loc)>{{ $loc }}</option>
                        @endforeach
                        @if($selectedFrom && !$locationOptionsList->contains($selectedFrom))
                            <option value="{{ $selectedFrom }}" selected>{{ $selectedFrom }}</option>
                        @endif
                    </select>
                </label>

                <label class="museum-field">
                    <span>To Location <em class="text-rose-500 not-italic">*</em></span>
                    <select name="to_location" required>
                        <option value="">Select destination</option>
                        @foreach($locationOptions as $loc)
                            <option value="{{ $loc }}" @selected($selectedTo === $loc)>{{ $loc }}</option>
                        @endforeach
                        @if($selectedTo && !$locationOptionsList->contains($selectedTo))
                            <option value="{{ $selectedTo }}" selected>{{ $selectedTo }}</option>
                        @endif
                    </select>
                </label>

                <label class="museum-field">
                    <span>Date Out <em class="text-rose-500 not-italic">*</em></span>
                    <input type="date" name="date_out" value="{{ old('date_out', optional($movement->date_out)->toDateString()) }}" required>
                </label>

                <label class="museum-field">
                    <span>Expected Return Date</span>
                    <input type="date" name="expected_return_date" value="{{ old('expected_return_date', optional($movement->expected_return_date)->toDateString()) }}">
                </label>

                <label class="museum-field">
                    <span>Responsible Handler <em class="text-rose-500 not-italic">*</em></span>
                    <input name="responsible_handler" value="{{ old('responsible_handler', $movement->responsible_handler) }}" required>
                </label>

                <label class="museum-field">
                    <span>Reason <em class="text-rose-500 not-italic">*</em></span>
                    <select name="reason" required>
                        @foreach($reasonOptionsList as $reason)
                            <option value="{{ $reason }}" @selected($selectedReason === $reason)>{{ $reason }}</option>
                        @endforeach
                        @if($selectedReason && !$reasonOptionsList->contains($selectedReason))
                            <option value="{{ $selectedReason }}" selected>{{ $selectedReason }}</option>
                        @endif
                    </select>
                </label>

                <label class="museum-field md:col-span-2">
                    <span>Status <em class="text-rose-500 not-italic">*</em></span>
                    <select name="status" required>
                        @foreach($statusOptionsList as $status)
                            <option value="{{ $status }}" @selected($selectedStatus === $status)>{{ $status }}</option>
                        @endforeach
                        @if($selectedStatus && !$statusOptionsList->contains($selectedStatus))
                            <option value="{{ $selectedStatus }}" selected>{{ $selectedStatus }}</option>
                        @endif
                    </select>
                </label>

                <label class="museum-field md:col-span-2">
                    <span>Movement Notes</span>
                    <textarea name="notes" rows="3">{{ old('notes', $movement->notes) }}</textarea>
                </label>

                <label class="museum-field md:col-span-2">
                    <span>Condition Report</span>
                    <textarea name="condition_report" rows="3">{{ old('condition_report', $movement->condition_report) }}</textarea>
                </label>

                <div class="md:col-span-2 flex justify-end gap-3 pt-1">
                    <a href="{{ route('movements.index') }}" class="museum-btn-secondary">Cancel</a>
                    <button type="submit" class="museum-btn">Save Changes</button>
                </div>
            </form>
        </article>
    </section>
</x-layout>
