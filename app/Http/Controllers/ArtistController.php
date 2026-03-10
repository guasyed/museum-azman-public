<?php

namespace App\Http\Controllers;

use App\Models\Artist;
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

        return view('artists.index', compact('artists', 'stats', 'q', 'sort', 'artistSuggestions'));
    }
}
