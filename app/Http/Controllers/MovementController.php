<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreMovementRequest;
use App\Models\Artwork;
use App\Models\Location;
use App\Models\Movement;
use App\Models\Status;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class MovementController extends Controller
{
    public function index(): View
    {
        $movements = Movement::query()->with('artwork.artist')->latest('date_out')->get();
        $artworks = Artwork::query()->with(['artist', 'location'])->orderBy('title')->get();
        $statusOptions = Status::allowedNames();
        $locationOptions = Location::query()
            ->whereNotNull('name')
            ->where('name', '!=', '')
            ->orderBy('name')
            ->pluck('name')
            ->sort()
            ->values();

        $stats = [
            'in_stage' => $movements->where('status', 'In Stage')->count(),
            'on_loan' => $movements->where('status', 'On Loan')->count(),
            'under_restoration' => $movements->where('status', 'Under Restoration')->count(),
        ];

        $activeMovements = $movements->whereIn('status', ['In Stage', 'On Loan', 'Under Restoration']);

        return view('movements.index', compact('movements', 'artworks', 'stats', 'activeMovements', 'locationOptions', 'statusOptions'));
    }

    public function store(StoreMovementRequest $request): RedirectResponse
    {
        $movement = Movement::create($request->validated());

        $this->syncArtworkStatus((int) $movement->artwork_id);

        return redirect()->back()->with('success', 'Movement recorded successfully.');
    }

    public function edit(Movement $movement): View
    {
        $movement->loadMissing('artwork.artist');

        $artworks = Artwork::query()->with(['artist', 'location'])->orderBy('title')->get();
        $locationOptions = Location::query()
            ->whereNotNull('name')
            ->where('name', '!=', '')
            ->orderBy('name')
            ->pluck('name')
            ->sort()
            ->values();

        $reasonOptions = ['Loan', 'Exhibition', 'Storage', 'Restoration', 'Sale Prep'];
        $statusOptions = Status::allowedNames();

        return view('movements.edit', compact('movement', 'artworks', 'locationOptions', 'reasonOptions', 'statusOptions'));
    }

    public function update(StoreMovementRequest $request, Movement $movement): RedirectResponse
    {
        $oldArtworkId = (int) $movement->artwork_id;

        $movement->update($request->validated());

        $this->syncArtworkStatus((int) $movement->artwork_id);
        if ($oldArtworkId !== (int) $movement->artwork_id) {
            $this->syncArtworkStatus($oldArtworkId);
        }

        return redirect()->route('movements.index')->with('success', 'Movement updated successfully.');
    }

    private function syncArtworkStatus(int $artworkId): void
    {
        if ($artworkId <= 0) {
            return;
        }

        $latestMovement = Movement::query()
            ->where('artwork_id', $artworkId)
            ->orderByDesc('date_out')
            ->orderByDesc('id')
            ->first(['status', 'to_location']);

        $allowedStatuses = Status::allowedNames();
        $nextArtworkStatus = Status::DEFAULT_NAMES[0];
        if ($latestMovement && in_array((string) $latestMovement->status, $allowedStatuses, true)) {
            $nextArtworkStatus = (string) $latestMovement->status;
        }

        $updatePayload = [
            'status' => $nextArtworkStatus,
        ];

        $nextLocationName = trim((string) ($latestMovement?->to_location ?? ''));
        if ($nextLocationName !== '') {
            $locationId = Location::query()
                ->where('name', $nextLocationName)
                ->value('id');

            if ($locationId) {
                $updatePayload['location_id'] = $locationId;
            }
        }

        Artwork::query()->whereKey($artworkId)->update($updatePayload);
    }
}
