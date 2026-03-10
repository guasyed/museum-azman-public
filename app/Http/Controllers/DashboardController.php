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
            'in_transit' => $artworks->where('status', 'In Transit')->count(),
            'on_loan' => $artworks->where('status', 'On Loan')->count(),
        ];

        $geo = $this->geographyCounts($artworks);
        $totalGeo = max($geo['malaysia'] + $geo['southeast_asia'] + $geo['rest_of_world'], 1);
        $donutStops = sprintf(
            '#111111 0%% %1$.2f%%, #7f7a76 %1$.2f%% %2$.2f%%, #ada8a3 %2$.2f%% 100%%',
            ($geo['malaysia'] / $totalGeo) * 100,
            (($geo['malaysia'] + $geo['southeast_asia']) / $totalGeo) * 100
        );

        $recentMovements = Artwork::query()
            ->with('location')
            ->whereIn('status', ['In Transit', 'On Loan'])
            ->latest()
            ->take(3)
            ->get();

        $recentArtworks = Artwork::query()
            ->with(['artist'])
            ->orderByDesc('acquisition_date')
            ->orderByDesc('created_at')
            ->take(4)
            ->get();

        return view('dashboard.index', compact('stats', 'recentArtworks', 'geo', 'donutStops', 'recentMovements'));
    }

    private function geographyCounts(Collection $artworks): array
    {
        $seaCountries = [
            'malaysia', 'singapore', 'indonesia', 'thailand', 'philippines',
            'vietnam', 'myanmar', 'cambodia', 'laos', 'brunei', 'timor-leste',
        ];

        $malaysia = 0;
        $southeastAsia = 0;
        $rest = 0;

        foreach ($artworks as $artwork) {
            $country = strtolower((string) ($artwork->artist?->country ?? ''));

            if ($country === 'malaysia' || $country === 'malaysian') {
                $malaysia++;
                continue;
            }

            if (in_array($country, $seaCountries, true)) {
                $southeastAsia++;
                continue;
            }

            $rest++;
        }

        return [
            'malaysia' => $malaysia,
            'southeast_asia' => $southeastAsia,
            'rest_of_world' => $rest,
        ];
    }
}
