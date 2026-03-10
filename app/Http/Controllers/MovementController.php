<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreMovementRequest;
use App\Models\Artwork;
use App\Models\Movement;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class MovementController extends Controller
{
    public function index(): View
    {
        $movements = Movement::query()->with('artwork.artist')->latest('date_out')->get();
        $artworks = Artwork::query()->with(['artist', 'location'])->orderBy('title')->get();
        $locationOptions = $artworks
            ->pluck('location.name')
            ->filter()
            ->merge($movements->pluck('from_location'))
            ->merge($movements->pluck('to_location'))
            ->filter()
            ->unique()
            ->sort()
            ->values();

        $stats = [
            'in_transit' => $movements->where('status', 'In Transit')->count(),
            'scheduled' => $movements->where('status', 'Scheduled')->count(),
            'completed' => $movements->where('status', 'Completed')->count(),
        ];

        $activeMovements = $movements->whereIn('status', ['In Transit', 'Scheduled']);

        return view('movements.index', compact('movements', 'artworks', 'stats', 'activeMovements', 'locationOptions'));
    }

    public function store(StoreMovementRequest $request): RedirectResponse
    {
        $movement = Movement::create($request->validated());

        $movement->artwork()->update([
            'status' => $movement->status,
        ]);

        return redirect()->back()->with('success', 'Movement recorded successfully.');
    }
}
