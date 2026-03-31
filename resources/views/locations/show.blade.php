<x-layout title="{{ $location->name }} - Location">
    <section class="space-y-6">
        <div class="flex items-start justify-between gap-4">
            <div>
                <h2 class="museum-page-title">{{ $location->name }}</h2>
                <p class="museum-page-subtitle">Location details and stored artworks</p>
            </div>
            <div class="flex items-center gap-2">
                <a href="{{ route('locations.index') }}" class="museum-btn-secondary">Back to Locations</a>
                @if(auth()->check() && auth()->user()->isAdmin())
                    <a href="{{ route('locations.edit', $location) }}" class="museum-btn">Edit Location</a>
                @endif
            </div>
        </div>

        <div class="grid gap-4 md:grid-cols-3">
            <div class="museum-stat-card">
                <p>Type</p>
                <strong>{{ $location->display_type }}</strong>
            </div>
            <div class="museum-stat-card">
                <p>Artworks Stored</p>
                <strong>{{ number_format($location->artworks_count ?? $location->artworks->count()) }}</strong>
            </div>
            <div class="museum-stat-card">
                <p>Insured Value</p>
                <strong>{{ \App\Support\Currency::symbol() }}{{ number_format($location->insured_value, 0) }}</strong>
            </div>
        </div>

        <article class="museum-panel">
            <h3 class="museum-section-title">Location Info</h3>
            <div class="mt-4 grid gap-3 md:grid-cols-2">
                <div class="museum-detail">
                    <span>Address</span>
                    <strong>{{ $location->address ?: 'Address not set' }}</strong>
                </div>
                <div class="museum-detail">
                    <span>Last Audit</span>
                    <strong>{{ \App\Support\DateFormat::display($location->last_audit_date) }}</strong>
                </div>
            </div>

            @if($location->map_embed_url)
                <div class="mt-4">
                    <div class="mb-2 flex items-center justify-between gap-2">
                        <p class="text-sm font-semibold text-zinc-700">Map Location</p>
                        <a href="{{ $location->map_url }}" target="_blank" rel="noopener noreferrer" class="museum-btn-secondary">Open in Maps</a>
                    </div>
                    <iframe
                        src="{{ $location->map_embed_url }}"
                        title="Map of {{ $location->name }}"
                        class="h-72 w-full rounded-xl border border-zinc-200"
                        loading="lazy"
                        referrerpolicy="no-referrer-when-downgrade"
                    ></iframe>
                </div>
            @endif
        </article>

        <article class="museum-panel">
            <h3 class="museum-section-title">Stored Artworks</h3>

            @if($location->artworks->isEmpty())
                <p class="mt-3 text-zinc-500">No artworks currently linked to this location.</p>
            @else
                <div class="mt-4 overflow-x-auto">
                    <table class="w-full min-w-220 text-sm">
                        <thead>
                            <tr class="border-b border-zinc-200 text-left font-semibold text-zinc-800">
                                <th class="px-3 py-2.5">Title</th>
                                <th class="px-3 py-2.5">Artist</th>
                                <th class="px-3 py-2.5 text-right">Current Valuation</th>
                                <th class="px-3 py-2.5 text-center">Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($location->artworks as $artwork)
                                <tr class="border-b border-zinc-200">
                                    <td class="px-3 py-2.5">{{ $artwork->title }}</td>
                                    <td class="px-3 py-2.5 text-zinc-600">{{ $artwork->artist?->name ?? 'Unknown Artist' }}</td>
                                    <td class="px-3 py-2.5 text-right font-semibold">{{ \App\Support\Currency::symbol() }}{{ number_format((float) $artwork->current_valuation, 0) }}</td>
                                    <td class="px-3 py-2.5 text-center">
                                        <a href="{{ route('artworks.show', $artwork) }}" class="museum-btn-secondary">Open</a>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @endif
        </article>
    </section>
</x-layout>
