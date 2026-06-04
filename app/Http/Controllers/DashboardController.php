<?php

namespace App\Http\Controllers;

use App\Models\Artwork;
use App\Models\Movement;
use Illuminate\Support\Collection;

class DashboardController extends Controller
{
    public function __invoke()
    {
        $artworks = Artwork::query()->with(['artist', 'location'])->get();

        $stats = [
            'total_artworks' => $artworks->count(),
            'total_artists' => $artworks->pluck('artist_id')->filter()->unique()->count(),
            'total_locations' => $artworks->pluck('location_id')->filter()->unique()->count(),
            'collection_value' => (float) $artworks->sum('current_valuation'),
            'in_stage' => $artworks->whereIn('status', ['In Stage', 'In Storage', 'In Residence', 'In Office'])->count(),
            'on_loan' => $artworks->whereIn('status', ['On Loan', 'Loaned Out'])->count(),
        ];

        $geoByCountry = $this->geographyCounts($artworks);

        $recentMovements = Movement::query()
            ->with('artwork.artist')
            ->orderByDesc('created_at')
            ->orderByDesc('id')
            ->get();

        $recentArtworks = $this->randomArtworksWithImagePriority();

        return view('dashboard.index', compact('stats', 'recentArtworks', 'geoByCountry', 'recentMovements'));
    }

    private function geographyCounts(Collection $artworks): array
    {
        $counts = [];

        foreach ($artworks as $artwork) {
            $rawCountry = trim((string) ($artwork->artist?->country ?? ''));
            $country = $rawCountry === '' ? 'Unknown' : $rawCountry;

            if (! isset($counts[$country])) {
                $counts[$country] = 0;
            }

            $counts[$country]++;
        }

        arsort($counts);

        return $counts;
    }

    private function randomArtworksWithImagePriority(): Collection
    {
        $imageArtworks = Artwork::query()
            ->with(['artist', 'images:id,artwork_id,path,position'])
            ->where(function ($query) {
                $query
                    ->whereNotNull('primary_image_path')
                    ->where('primary_image_path', '!=', '')
                    ->orWhere(function ($query) {
                        $query
                            ->whereNotNull('source_image_url')
                            ->where('source_image_url', '!=', '');
                    })
                    ->orWhereHas('images');
            })
            ->inRandomOrder()
            ->take(4)
            ->get();

        if ($imageArtworks->count() >= 4) {
            return $imageArtworks->values();
        }

        $fallbackArtworks = Artwork::query()
            ->with(['artist', 'images:id,artwork_id,path,position'])
            ->whereNotIn('id', $imageArtworks->pluck('id'))
            ->inRandomOrder()
            ->take(4 - $imageArtworks->count())
            ->get();

        return $imageArtworks
            ->merge($fallbackArtworks)
            ->take(4)
            ->values();
    }
}
