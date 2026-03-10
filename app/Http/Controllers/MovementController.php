<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreMovementRequest;
use App\Models\Artwork;
use App\Models\Location;
use App\Models\Movement;
use App\Models\Status;
use App\Models\User;
use App\Notifications\MovementTrackerAssignedNotification;
use App\Notifications\MovementTrackerUpdatedByHandlerNotification;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class MovementController extends Controller
{
    public function index(Request $request): View
    {
        $currentUser = $request->user();
        $isAssignedOnlyView = (bool) ($currentUser && ! $currentUser->isAdmin() && $currentUser->isLogisticsHandler());

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

        if ($isAssignedOnlyView) {
            $movementsQuery->whereRaw('LOWER(responsible_handler) = ?', [strtolower((string) $currentUser->name)]);
        }

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
        $handlerOptions = $this->handlerUsersQuery()
            ->orderBy('name')
            ->pluck('name')
            ->filter(fn ($name) => is_string($name) && trim($name) !== '')
            ->unique()
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
        $canRecordMovement = ! $isAssignedOnlyView;

        return view('movements.index', compact('movements', 'artworks', 'stats', 'activeMovements', 'locationOptions', 'statusOptions', 'handlerOptions', 'sortColumn', 'direction', 'canRecordMovement', 'isAssignedOnlyView'));
    }

    public function store(StoreMovementRequest $request): RedirectResponse
    {
        $currentUser = $request->user();
        if ($currentUser && ! $currentUser->isAdmin() && $currentUser->isLogisticsHandler()) {
            return redirect()
                ->route('movements.index')
                ->withErrors(['movement' => 'Handlers can only view movements assigned to them.']);
        }

        $movement = Movement::create($request->validated());

        $this->notifyResponsibleHandlerAssignment($movement);

        $this->syncArtworkStatus((int) $movement->artwork_id);

        return redirect()->back()->with('success', 'Movement recorded successfully.');
    }

    public function edit(Movement $movement): View
    {
        $currentUser = request()->user();
        if (! $this->canEditMovement($currentUser, $movement)) {
            abort(403, 'You are not allowed to edit this movement.');
        }

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
        $handlerOptions = $this->handlerUsersQuery()
            ->orderBy('name')
            ->pluck('name')
            ->filter(fn ($name) => is_string($name) && trim($name) !== '')
            ->unique()
            ->values();

        return view('movements.edit', compact('movement', 'artworks', 'locationOptions', 'reasonOptions', 'statusOptions', 'handlerOptions'));
    }

    public function update(StoreMovementRequest $request, Movement $movement): RedirectResponse
    {
        $currentUser = $request->user();
        if (! $this->canEditMovement($currentUser, $movement)) {
            abort(403, 'You are not allowed to update this movement.');
        }

        $oldArtworkId = (int) $movement->artwork_id;
        $oldResponsibleHandler = (string) $movement->responsible_handler;

        $movement->update($request->validated());

        $this->notifyResponsibleHandlerAssignment($movement, $oldResponsibleHandler);

        $this->syncArtworkStatus((int) $movement->artwork_id);
        if ($oldArtworkId !== (int) $movement->artwork_id) {
            $this->syncArtworkStatus($oldArtworkId);
        }

        $this->notifyAdminsWhenHandlerUpdates($movement, $currentUser);

        return redirect()->route('movements.index')->with('success', 'Movement updated successfully.');
    }

    private function notifyResponsibleHandlerAssignment(Movement $movement, ?string $previousHandler = null): void
    {
        $handlerName = trim((string) $movement->responsible_handler);
        if ($handlerName === '') {
            return;
        }

        $normalizedPreviousHandler = trim((string) ($previousHandler ?? ''));
        if ($normalizedPreviousHandler !== '' && strcasecmp($normalizedPreviousHandler, $handlerName) === 0) {
            return;
        }

        $handlerUser = User::query()
            ->whereRaw('LOWER(name) = ?', [strtolower($handlerName)])
            ->where(function ($query) {
                $query->whereHas('roleRelation', fn ($roleQuery) => $roleQuery->where('slug', 'logistics-handler'))
                    ->orWhereIn('role', ['logistics-handler', 'logistics handler', 'handler', 'movement handler', 'user handler', 'movement-tracker']);
            })
            ->first();

        if (! $handlerUser) {
            return;
        }

        if (! (bool) ($handlerUser->notification_movement_alerts ?? true)) {
            return;
        }

        if (! (bool) ($handlerUser->notification_delivery_email ?? true)
            && ! (bool) ($handlerUser->notification_delivery_browser ?? true)) {
            return;
        }

        $handlerUser->notify(new MovementTrackerAssignedNotification($movement, request()->user()?->name));
    }

    private function handlerUsersQuery(): Builder
    {
        return User::query()
            ->with('roleRelation')
            ->where(function ($query) {
                $query->whereHas('roleRelation', fn ($roleQuery) => $roleQuery->where('slug', 'logistics-handler'))
                    ->orWhereIn('role', ['logistics-handler', 'logistics handler', 'handler', 'movement handler', 'user handler', 'movement-tracker']);
            });
    }

    private function canEditMovement($user, Movement $movement): bool
    {
        if (! $user) {
            return false;
        }

        if ($user->isAdmin()) {
            return true;
        }

        if (! $user->isLogisticsHandler()) {
            return false;
        }

        return strtolower(trim((string) $movement->responsible_handler))
            === strtolower(trim((string) $user->name));
    }

    private function notifyAdminsWhenHandlerUpdates(Movement $movement, $updatedBy): void
    {
        if (! $updatedBy || $updatedBy->isAdmin() || ! $updatedBy->isLogisticsHandler()) {
            return;
        }

        $admins = User::query()
            ->with('roleRelation')
            ->get()
            ->filter(fn (User $candidate) => $candidate->isAdmin() && $candidate->isApproved())
            ->unique('id');

        foreach ($admins as $admin) {
            $admin->notify(new MovementTrackerUpdatedByHandlerNotification($movement, $updatedBy));
        }
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
