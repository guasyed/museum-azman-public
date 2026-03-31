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
            ->with(['artist'])
            ->orderByDesc('acquisition_date')
            ->orderByDesc('created_at')
            ->take(4)
            ->get();

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
