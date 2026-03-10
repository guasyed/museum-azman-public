<x-layout title="Edit {{ $location->name }} - Location">
    <section class="space-y-6 max-w-3xl">
        <div class="flex items-start justify-between gap-4">
            <div>
                <h2 class="museum-page-title">Edit Location</h2>
                <p class="museum-page-subtitle">Update location details</p>
            </div>
            <a href="{{ route('locations.show', $location) }}" class="museum-btn-secondary">Cancel</a>
        </div>

        <article class="museum-panel">
            <form method="POST" action="{{ route('locations.update', $location) }}" class="space-y-4">
                @csrf
                @method('PUT')

                <label class="museum-field">
                    <span>Location Name</span>
                    <input type="text" name="name" value="{{ old('name', $location->name) }}" required maxlength="255">
                </label>

                <label class="museum-field">
                    <span>Type</span>
                    <select name="type">
                        <option value="">Select type</option>
                        @foreach($typeOptions as $type)
                            <option value="{{ $type }}" @selected(old('type', $location->type) === $type)>{{ $type }}</option>
                        @endforeach
                    </select>
                </label>

                <label class="museum-field">
                    <span>Address</span>
                    <input type="text" name="address" value="{{ old('address', $location->address) }}" maxlength="255">
                </label>

                <label class="museum-field">
                    <span>Last Audit Date</span>
                    <input type="date" name="last_audit_date" value="{{ old('last_audit_date', optional($location->last_audit_date)->format('Y-m-d')) }}">
                </label>

                <div class="pt-2">
                    <button type="submit" class="museum-btn">Save Changes</button>
                </div>
            </form>
        </article>
    </section>
</x-layout>
