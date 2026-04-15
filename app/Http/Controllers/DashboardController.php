<?php

namespace App\Http\Controllers;

use App\Models\Artwork;
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
            'in_stage' => $artworks->where('status', 'In Stage')->count(),
            'on_loan' => $artworks->where('status', 'On Loan')->count(),
        ];

        $geoByCountry = $this->geographyCounts($artworks);

        $recentMovements = Artwork::query()
            ->with('location')
            ->whereIn('status', ['In Stage', 'On Loan', 'Under Restoration'])
            ->latest()
            ->take(3)
            ->get();

        $recentArtworks = Artwork::query()
            ->with(['artist', 'images:id,artwork_id,path,position'])
            ->orderByDesc('updated_at')
            ->take(32)
            ->get();

        $recentArtworks = $recentArtworks
            ->sortBy([
                fn (Artwork $artwork) => $artwork->primary_image_url ? 0 : 1,
                fn (Artwork $artwork) => -((int) optional($artwork->updated_at)->getTimestamp()),
            ])
            ->take(4)
            ->values();

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
}
