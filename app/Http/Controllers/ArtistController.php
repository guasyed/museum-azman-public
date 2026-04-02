<?php

namespace App\Http\Controllers;

use App\Models\Artist;
use App\Models\Country;
use App\Services\ActivityLogger;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\View\View;

class ArtistController extends Controller
{
    public function index(Request $request): View
    {
        $q = trim((string) $request->string('q'));
        $sort = (string) $request->string('sort');
        $sort = in_array($sort, ['value', 'works', 'name'], true) ? $sort : 'value';

        $artists = Artist::query()
            ->withCount('artworks')
            ->withSum('artworks as portfolio_value', 'current_valuation')
            ->with(['artworks:id,artist_id,location_id,title,year,medium,size_from_cm,size_to_cm,current_valuation,status,primary_image_path', 'artworks.location:id,name'])
            ->when($q !== '', function ($query) use ($q) {
                $like = '%'.Str::lower($q).'%';
                $query->where(function ($inner) use ($like) {
                    $inner->whereRaw('LOWER(name) LIKE ?', [$like])
                        ->orWhereRaw('LOWER(country) LIKE ?', [$like])
                        ->orWhereHas('artworks', fn ($artworkQuery) => $artworkQuery->whereRaw('LOWER(medium) LIKE ?', [$like]));
                });
            })
            ->orderBy('name')
            ->get()
            ->map(function (Artist $artist) {
                $sortedArtworks = $artist->artworks
                    ->sortByDesc(fn ($artwork) => (float) $artwork->current_valuation)
                    ->values();

                $style = $artist->artworks
                    ->pluck('medium')
                    ->map(fn ($medium) => trim((string) $medium))
                    ->filter()
                    ->countBy()
                    ->sortDesc()
                    ->keys()
                    ->first();

                $artist->style_label = $style ?: 'Mixed Media';
                $artist->portfolio_value = (float) ($artist->portfolio_value ?? 0);
                $artist->avg_value_per_work = $artist->artworks_count > 0
                    ? (float) $artist->portfolio_value / (int) $artist->artworks_count
                    : 0.0;
                $artist->setRelation('artworks', $sortedArtworks);

                return $artist;
            });

        $artists = match ($sort) {
            'value' => $artists->sortByDesc(fn (Artist $artist) => (float) $artist->portfolio_value)->values(),
            'works' => $artists->sortByDesc(fn (Artist $artist) => (int) $artist->artworks_count)->values(),
            default => $artists->sortBy(fn (Artist $artist) => Str::lower((string) $artist->name))->values(),
        };

        $artistSuggestions = Artist::query()
            ->with(['artworks:id,artist_id,medium'])
            ->orderBy('name')
            ->get()
            ->flatMap(function (Artist $artist) {
                $style = $artist->artworks
                    ->pluck('medium')
                    ->map(fn ($medium) => trim((string) $medium))
                    ->filter()
                    ->countBy()
                    ->sortDesc()
                    ->keys()
                    ->first();

                $items = collect([
                    [
                        'query' => (string) $artist->name,
                        'label' => (string) $artist->name,
                        'meta' => 'Artist',
                    ],
                ]);

                if ($artist->country) {
                    $items->push([
                        'query' => (string) $artist->country,
                        'label' => (string) $artist->country,
                        'meta' => 'Nationality',
                    ]);
                }

                if ($style) {
                    $items->push([
                        'query' => (string) $style,
                        'label' => (string) $style,
                        'meta' => 'Style',
                    ]);
                }

                return $items;
            })
            ->unique(fn ($item) => Str::lower($item['query']).'|'.Str::lower($item['meta']))
            ->values();

        $stats = [
            'total_artists' => $artists->count(),
            'total_artworks' => (int) $artists->sum('artworks_count'),
            'total_portfolio_value' => (float) $artists->sum('portfolio_value'),
        ];

        $perPage = 20;
        $currentPage = max(1, (int) $request->integer('page', 1));
        $paginatedArtists = new LengthAwarePaginator(
            items: $artists->slice(($currentPage - 1) * $perPage, $perPage)->values(),
            total: $artists->count(),
            perPage: $perPage,
            currentPage: $currentPage,
            options: [
                'path' => $request->getPathInfo(),
                'query' => $request->query(),
            ],
        );

        return view('artists.index', [
            'artists' => $paginatedArtists,
            'stats' => $stats,
            'q' => $q,
            'sort' => $sort,
            'artistSuggestions' => $artistSuggestions,
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name'       => ['required', 'string', 'max:255', 'unique:artists,name'],
            'country'    => ['nullable', 'string', 'max:255'],
            'birth_year' => ['nullable', 'integer', 'min:1000', 'max:' . date('Y')],
            'biography'  => ['nullable', 'string', 'max:5000'],
        ]);

        $countryName = trim((string) ($validated['country'] ?? ''));
        if ($countryName !== '') {
            $countryRow = Country::query()->firstOrCreate(['name' => $countryName]);
            $validated['country_id'] = $countryRow->id;
        }

        $artist = Artist::query()->create($validated);

        ActivityLogger::log('artist.created', "Artist created: {$artist->name}", $artist);

        return redirect()->route('artists.index')->with('success', "Artist \"{$artist->name}\" added successfully.");
    }

    public function edit(Artist $artist): View
    {
        return view('artists.edit', compact('artist'));
    }

    public function update(Request $request, Artist $artist): RedirectResponse
    {
        $validated = $request->validate([
            'name'       => ['required', 'string', 'max:255', 'unique:artists,name,' . $artist->id],
            'country'    => ['nullable', 'string', 'max:255'],
            'birth_year' => ['nullable', 'integer', 'min:1000', 'max:' . date('Y')],
            'biography'  => ['nullable', 'string', 'max:5000'],
        ]);

        $countryName = trim((string) ($validated['country'] ?? ''));
        if ($countryName !== '') {
            $countryRow = Country::query()->firstOrCreate(['name' => $countryName]);
            $validated['country_id'] = $countryRow->id;
        } else {
            $validated['country_id'] = null;
        }

        $artist->update($validated);

        ActivityLogger::log('artist.updated', "Artist updated: {$artist->name}", $artist);

        return redirect()->route('artists.index')->with('success', "Artist \"{$artist->name}\" updated successfully.");
    }

    public function destroy(Artist $artist): RedirectResponse
    {
        if ($artist->artworks()->exists()) {
            return redirect()->route('artists.index')->withErrors([
                'artists' => "Cannot delete \"{$artist->name}\" — they still have artworks in the collection.",
            ]);
        }

        $name = $artist->name;
        $artist->delete();

        ActivityLogger::log('artist.deleted', "Artist deleted: {$name}");

        return redirect()->route('artists.index')->with('success', "Artist \"{$name}\" deleted.");
    }
}
