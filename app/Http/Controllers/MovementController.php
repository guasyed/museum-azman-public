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
use App\Services\ActivityLogger;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Schema;
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

        $movements = $movementsQuery->paginate(10, ['*'], 'page')->withQueryString();

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
            ->tap(fn (Builder $query) => $this->orderLocationsByCleanedInventory($query))
            ->pluck('name')
            ->values();

        // Stats come from a lightweight count query (not affected by pagination)
        $statsQuery = Movement::query();
        if ($isAssignedOnlyView) {
            $statsQuery->whereRaw('LOWER(responsible_handler) = ?', [strtolower((string) $currentUser->name)]);
        }
        $stats = [
            'in_stage' => (clone $statsQuery)->whereIn('status', ['In Stage', 'In Storage', 'In Residence', 'In Office'])->count(),
            'on_loan' => (clone $statsQuery)->whereIn('status', ['On Loan', 'Loaned Out'])->count(),
            'under_restoration' => (clone $statsQuery)->where('status', 'Under Restoration')->count(),
        ];

        // Active movements are paginated separately
        $activeQuery = Movement::query()->with('artwork.artist')
            ->whereNotIn('status', ['On Display', 'Sold or Left']);
        if ($isAssignedOnlyView) {
            $activeQuery->whereRaw('LOWER(responsible_handler) = ?', [strtolower((string) $currentUser->name)]);
        }
        $activeMovements = $activeQuery->orderByDesc('id')->paginate(10, ['*'], 'active_page')->withQueryString();

        $canRecordMovement = ! $isAssignedOnlyView;

        $reasonOptions = $this->reasonOptions();

        return view('movements.index', compact('movements', 'artworks', 'stats', 'activeMovements', 'locationOptions', 'statusOptions', 'handlerOptions', 'reasonOptions', 'sortColumn', 'direction', 'canRecordMovement', 'isAssignedOnlyView'));
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

        ActivityLogger::log('movement.created', "Movement recorded for artwork ID {$movement->artwork_id}", $movement);

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
            ->tap(fn (Builder $query) => $this->orderLocationsByCleanedInventory($query))
            ->pluck('name')
            ->values();

        $reasonOptions = $this->reasonOptions();
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

        ActivityLogger::log('movement.updated', "Movement updated for artwork ID {$movement->artwork_id}", $movement);

        return redirect()->route('movements.index')->with('success', 'Movement updated successfully.');
    }

    public function destroy(Request $request, Movement $movement): RedirectResponse
    {
        $currentUser = $request->user();
        if (! $this->canEditMovement($currentUser, $movement)) {
            abort(403, 'You are not allowed to delete this movement.');
        }

        $artworkId = (int) $movement->artwork_id;

        $movement->delete();

        $this->syncArtworkStatus($artworkId);

        ActivityLogger::log('movement.deleted', "Movement deleted for artwork ID {$artworkId}");

        return redirect()->route('movements.index')->with('success', 'Movement deleted successfully.');
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

        // Prefer explicit movement status when present and allowed
        if ($latestMovement && is_string($latestMovement->status) && trim($latestMovement->status) !== '' && in_array((string) $latestMovement->status, $allowedStatuses, true)) {
            $nextArtworkStatus = (string) $latestMovement->status;
        } else {
            // Fallback: infer status from destination location type (cleaned inventory mapping)
            $nextLocationName = trim((string) ($latestMovement?->to_location ?? ''));
            if ($nextLocationName !== '') {
                $locationType = Location::query()->where('name', $nextLocationName)->value('type');
                $mapping = [
                    'storage' => 'In Storage',
                    'residence' => 'In Residence',
                    'office' => 'In Office',
                    'museum' => 'On Display',
                    'garden' => 'On Display',
                    'library' => 'On Display',
                    'hall' => 'On Display',
                    'external' => 'External',
                    'disposition' => 'Sold or Left',
                    'restaurant' => 'On Display',
                ];

                if (is_string($locationType) && $locationType !== '') {
                    $key = strtolower(trim($locationType));
                    if (isset($mapping[$key]) && in_array($mapping[$key], $allowedStatuses, true)) {
                        $nextArtworkStatus = $mapping[$key];
                    }
                }
            }
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

    private function orderLocationsByCleanedInventory(Builder $query): void
    {
        if (Schema::hasColumn('locations', 'code')) {
            if (Location::query()->whereNotNull('code')->exists()) {
                $query->whereNotNull('code');
            }

            $query->orderByRaw('code IS NULL')->orderBy('id');

            return;
        }

        $query->orderBy('name');
    }

    private function reasonOptions(): array
    {
        return [
            'Display',
            'Storage',
            'Loan Out',
            'Loan Return',
            'Restoration',
            'Transit',
            'Photography',
            'Conservation',
            'Internal Transfer',
            'Sale',
            'Deaccession',
            'Loan',
            'Auction Evaluation',
            'Auction Consignment',
            'Third-Party Evaluation',
            'Other External Custody',
        ];
    }
}
