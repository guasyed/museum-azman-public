<?php

namespace App\Http\Controllers;

use App\Models\Location;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\View\View;

class LocationController extends Controller
{
    public function create(): View
    {
        $typeOptions = [
            'Private Residence',
            'Storage Facility',
            'Museum',
            'Gallery',
            'Exhibition Venue',
            'Overseas Warehouse',
        ];

        return view('locations.create', compact('typeOptions'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255', 'unique:locations,name'],
            'type' => ['nullable', 'string', 'max:255'],
            'address' => ['nullable', 'string', 'max:255'],
            'last_audit_date' => ['nullable', 'date'],
        ]);

        $location = Location::create($validated);

        return redirect()->route('locations.show', $location)->with('success', 'Location added successfully.');
    }

    public function index(Request $request): View
    {
        $all = Location::query()
            ->with(['artworks:id,location_id,current_valuation'])
            ->withCount('artworks')
            ->orderBy('name')
            ->get()
            ->map(function (Location $location) {
                $type = $this->displayType($location);

                $location->display_type = $type;
                $location->type_badge_class = match ($type) {
                    'Private Residence' => 'bg-blue-100 text-blue-700',
                    'Storage Facility' => 'bg-purple-100 text-purple-700',
                    'Museum' => 'bg-emerald-100 text-emerald-700',
                    'Exhibition Venue' => 'bg-rose-100 text-rose-700',
                    'Gallery' => 'bg-amber-100 text-amber-700',
                    'Overseas Warehouse' => 'bg-zinc-200 text-zinc-700',
                    default => 'bg-zinc-100 text-zinc-700',
                };
                $location->insured_value = (float) $location->artworks->sum('current_valuation');
                $mapQuery = $this->mapQuery($location);
                $location->map_url = $this->mapUrl($mapQuery);
                $location->map_embed_url = $this->mapEmbedUrl($mapQuery);

                return $location;
            });

        $q = trim((string) $request->string('q'));
        $selectedType = trim((string) $request->string('type'));
        $view = in_array((string) $request->string('view'), ['grid', 'list'], true)
            ? (string) $request->string('view')
            : 'grid';

        $locations = $all
            ->when($q !== '', fn (Collection $c) => $c->filter(function ($loc) use ($q) {
                return str_contains(strtolower((string) $loc->name), strtolower($q))
                    || str_contains(strtolower((string) ($loc->address ?? '')), strtolower($q));
            }))
            ->when($selectedType !== '' && $selectedType !== 'All Types', fn (Collection $c) => $c->where('display_type', $selectedType))
            ->values();

        $stats = [
            'total_locations' => $locations->count(),
            'artworks_stored' => (int) $locations->sum('artworks_count'),
            'insured_value' => (float) $locations->sum('insured_value'),
        ];

        $typeOptions = collect([
            'All Types',
            'Private Residence',
            'Storage Facility',
            'Museum',
            'Gallery',
            'Exhibition Venue',
        ]);

        return view('locations.index', compact('locations', 'stats', 'typeOptions', 'q', 'selectedType', 'view'));
    }

    public function show(Location $location): View
    {
        $location->load([
            'artworks' => fn ($query) => $query->with('artist:id,name')->latest()->limit(24),
        ]);

        $location = $this->enrichLocation($location);

        return view('locations.show', compact('location'));
    }

    public function edit(Location $location): View
    {
        $typeOptions = [
            'Private Residence',
            'Storage Facility',
            'Museum',
            'Gallery',
            'Exhibition Venue',
            'Overseas Warehouse',
        ];

        return view('locations.edit', compact('location', 'typeOptions'));
    }

    public function update(Request $request, Location $location)
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255', 'unique:locations,name,'.$location->id],
            'type' => ['nullable', 'string', 'max:255'],
            'address' => ['nullable', 'string', 'max:255'],
            'last_audit_date' => ['nullable', 'date'],
        ]);

        $location->update($validated);

        return redirect()->route('locations.show', $location)->with('success', 'Location updated successfully.');
    }

    private function enrichLocation(Location $location): Location
    {
        $type = $this->displayType($location);

        $location->display_type = $type;
        $location->type_badge_class = match ($type) {
            'Private Residence' => 'bg-blue-100 text-blue-700',
            'Storage Facility' => 'bg-purple-100 text-purple-700',
            'Museum' => 'bg-emerald-100 text-emerald-700',
            'Exhibition Venue' => 'bg-rose-100 text-rose-700',
            'Gallery' => 'bg-amber-100 text-amber-700',
            'Overseas Warehouse' => 'bg-zinc-200 text-zinc-700',
            default => 'bg-zinc-100 text-zinc-700',
        };
        $location->insured_value = (float) $location->artworks->sum('current_valuation');
        $mapQuery = $this->mapQuery($location);
        $location->map_url = $this->mapUrl($mapQuery);
        $location->map_embed_url = $this->mapEmbedUrl($mapQuery);

        return $location;
    }

    private function mapQuery(Location $location): string
    {
        $parts = [
            trim((string) $location->name),
            trim((string) ($location->address ?? '')),
        ];

        return implode(', ', array_values(array_filter($parts, fn (string $value) => $value !== '')));
    }

    private function mapUrl(string $query): ?string
    {
        if ($query === '') {
            return null;
        }

        return 'https://www.google.com/maps/search/?api=1&query=' . rawurlencode($query);
    }

    private function mapEmbedUrl(string $query): ?string
    {
        if ($query === '') {
            return null;
        }

        return 'https://www.google.com/maps?q=' . rawurlencode($query) . '&output=embed';
    }

    private function displayType(Location $location): string
    {
        $type = trim((string) ($location->type ?? ''));
        if ($type !== '') {
            return $type;
        }

        $name = strtolower($location->name);
        if (str_contains($name, 'museum')) return 'Museum';
        if (str_contains($name, 'gallery')) return 'Gallery';
        if (str_contains($name, 'vault') || str_contains($name, 'storage') || str_contains($name, 'store')) return 'Storage Facility';
        if (str_contains($name, 'warehouse') || str_contains($name, 'freeport')) return 'Overseas Warehouse';
        if (str_contains($name, 'basel') || str_contains($name, 'exhibition')) return 'Exhibition Venue';

        return 'Private Residence';
    }
}
