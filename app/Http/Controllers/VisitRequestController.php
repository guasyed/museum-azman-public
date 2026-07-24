<?php

namespace App\Http\Controllers;

use App\Models\VisitRequest as MuseumVisitRequest;
use App\Mail\VisitRequestSubmitted;
use App\Services\DatabaseSmtpConfigurator;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

class VisitRequestController extends Controller
{
    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'phone' => ['required', 'string', 'max:50'],
            'email' => ['required', 'email', 'max:255'],
            'occupation' => ['required', 'string', 'max:255'],
            'company' => ['required', 'string', 'max:255'],
            'city' => ['required', 'string', 'max:255'],
            'social' => ['nullable', 'string', 'max:255'],
            'purpose' => ['required', 'string', 'max:255'],
            'category' => ['required', 'string', 'max:255'],
            'date' => ['required', 'date', 'after_or_equal:today'],
            'guests' => ['required', 'integer', 'min:1', 'max:6'],
            'source' => ['required', 'string', 'max:255'],
            'message' => ['nullable', 'string', 'max:5000'],
            'preference' => ['nullable', 'array'],
            'preference.*' => ['in:outside-hours,curator,events,updates'],
            'consent' => ['accepted'],
        ]);

        $visitRequest = MuseumVisitRequest::create([
            ...collect($validated)->except(['date', 'preference', 'consent'])->all(),
            'preferred_date' => $validated['date'],
            'preferences' => $validated['preference'] ?? [],
            'ip_address' => $request->ip(),
        ]);

        try {
            $smtp = app(DatabaseSmtpConfigurator::class)->configure();
            if ($smtp['enabled']) {
                Mail::to($smtp['recipient'])->send(new VisitRequestSubmitted($visitRequest));
            }
        } catch (\Throwable $exception) {
            Log::error('Visit request email could not be sent.', [
                'visit_request_id' => $visitRequest->id,
                'error' => $exception->getMessage(),
            ]);
        }

        return redirect()
            ->route('public.visit')
            ->with('visit_success', 'Thank you. Your visit request has been received for review.');
    }
}
