<?php

namespace App\Http\Controllers;

use App\Models\VisitRequest as MuseumVisitRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class AdminVisitRequestController extends Controller
{
    public function index(Request $request): View
    {
        $query = MuseumVisitRequest::query();
        $search = trim((string) $request->string('q'));

        if ($search !== '') {
            $like = '%'.$search.'%';
            $query->where(function ($builder) use ($like) {
                $builder->where('name', 'like', $like)
                    ->orWhere('email', 'like', $like)
                    ->orWhere('phone', 'like', $like)
                    ->orWhere('company', 'like', $like);
            });
        }

        if ($request->string('status')->toString() === 'pending') {
            $query->whereNull('reviewed_at');
        }

        $visitRequests = $query->latest()->paginate(20)->withQueryString();
        $pendingCount = MuseumVisitRequest::query()->whereNull('reviewed_at')->count();

        return view('admin.visit-requests.index', compact('visitRequests', 'search', 'pendingCount'));
    }

    public function markReviewed(MuseumVisitRequest $visitRequest): RedirectResponse
    {
        if ($visitRequest->reviewed_at === null) {
            $visitRequest->update(['reviewed_at' => now()]);
        }

        return back()->with('success', 'Visit request marked as reviewed.');
    }
}
