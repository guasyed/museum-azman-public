<?php

namespace App\Http\Controllers;

use App\Models\ContactMessage;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class AdminContactMessageController extends Controller
{
    public function index(Request $request): View
    {
        $query = ContactMessage::query();
        $search = trim((string) $request->string('q'));

        if ($search !== '') {
            $query->where(function ($builder) use ($search) {
                $like = '%'.$search.'%';
                $builder->where('name', 'like', $like)
                    ->orWhere('email', 'like', $like)
                    ->orWhere('subject', 'like', $like)
                    ->orWhere('message', 'like', $like);
            });
        }

        if ($request->string('status')->toString() === 'unread') {
            $query->whereNull('read_at');
        }

        $messages = $query->latest()->paginate(20)->withQueryString();
        $unreadCount = ContactMessage::query()->whereNull('read_at')->count();

        return view('admin.contact-messages.index', compact('messages', 'search', 'unreadCount'));
    }

    public function markRead(ContactMessage $contactMessage): RedirectResponse
    {
        if ($contactMessage->read_at === null) {
            $contactMessage->update(['read_at' => now()]);
        }

        return back()->with('success', 'Message marked as read.');
    }
}
