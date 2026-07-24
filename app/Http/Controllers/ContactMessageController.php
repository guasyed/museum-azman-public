<?php

namespace App\Http\Controllers;

use App\Mail\ContactMessageSubmitted;
use App\Models\ContactMessage;
use App\Services\DatabaseSmtpConfigurator;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

class ContactMessageController extends Controller
{
    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255'],
            'subject' => ['nullable', 'string', 'max:255'],
            'message' => ['required', 'string', 'max:5000'],
        ]);

        $contactMessage = ContactMessage::create([
            ...$validated,
            'ip_address' => $request->ip(),
        ]);

        try {
            $smtp = app(DatabaseSmtpConfigurator::class)->configure();
            if ($smtp['enabled']) {
                Mail::to($smtp['recipient'])->send(new ContactMessageSubmitted($contactMessage));
            }
        } catch (\Throwable $exception) {
            Log::error('Contact form email could not be sent.', [
                'contact_message_id' => $contactMessage->id,
                'error' => $exception->getMessage(),
            ]);
        }

        return redirect()
            ->route('public.contact')
            ->with('contact_success', 'Thank you. Your message has been received.');
    }
}
