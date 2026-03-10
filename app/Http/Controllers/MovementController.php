<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreMovementRequest;
use App\Models\Artwork;
use App\Models\Location;
use App\Models\Movement;
use App\Models\Status;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class MovementController extends Controller
{
    public function index(Request $request): View
    {
        $sort = (string) $request->string('sort', 'date_out');
        $direction = strtolower((string) $request->string('direction', 'desc')) === 'asc' ? 'asc' : 'desc';
        $sortColumn = in_array($sort, [
            'artwork_title',
            'from_location',
            'to_location',
            'date_out',
            'expected_return_date',
            'responsible_handler',
            'reason',
            'status',
        ], true) ? $sort : 'date_out';

        $movementsQuery = Movement::query()->with('artwork.artist');

        if ($sortColumn === 'artwork_title') {
            $movementsQuery
                ->leftJoin('artworks', 'artworks.id', '=', 'movements.artwork_id')
                ->select('movements.*')
                ->orderBy('artworks.title', $direction)
                ->orderByDesc('movements.id');
        } else {
            $columnMap = [
                'from_location' => 'from_location',
                'to_location' => 'to_location',
                'date_out' => 'date_out',
                'expected_return_date' => 'expected_return_date',
                'responsible_handler' => 'responsible_handler',
                'reason' => 'reason',
                'status' => 'status',
            ];

            $orderColumn = $columnMap[$sortColumn] ?? 'date_out';

            $movementsQuery
                ->orderBy($orderColumn, $direction)
                ->orderByDesc('id');
        }

        $movements = $movementsQuery->get();
        $artworks = Artwork::query()->with(['artist', 'location'])->orderBy('title')->get();
        $statusOptions = Status::allowedNames();
        $handlerOptions = User::query()
            ->with('roleRelation')
            ->whereHas('roleRelation', fn ($query) => $query->where('slug', 'logistics-handler'))
            ->orderBy('name')
            ->pluck('name')
            ->filter(fn ($name) => is_string($name) && trim($name) !== '')
            ->values();
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

        return view('movements.index', compact('movements', 'artworks', 'stats', 'activeMovements', 'locationOptions', 'statusOptions', 'handlerOptions', 'sortColumn', 'direction'));
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
        $handlerOptions = User::query()
            ->with('roleRelation')
            ->whereHas('roleRelation', fn ($query) => $query->where('slug', 'logistics-handler'))
            ->orderBy('name')
            ->pluck('name')
            ->filter(fn ($name) => is_string($name) && trim($name) !== '')
            ->values();

        return view('movements.edit', compact('movement', 'artworks', 'locationOptions', 'reasonOptions', 'statusOptions', 'handlerOptions'));
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
