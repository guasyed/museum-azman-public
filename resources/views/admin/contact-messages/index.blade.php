<x-layout title="Messages - Museum Azman">
    <section class="space-y-6">
        <div>
            <h2 class="museum-page-title">Messages</h2>
            <p class="museum-page-subtitle">Contact enquiries submitted through the public website</p>
        </div>

        <div class="grid gap-3 sm:grid-cols-2">
            <article class="museum-stat-card">
                <p>Unread Messages</p>
                <strong>{{ number_format($unreadCount) }}</strong>
            </article>
            <article class="museum-stat-card">
                <p>Messages Found</p>
                <strong>{{ number_format($messages->total()) }}</strong>
            </article>
        </div>

        <form method="GET" action="{{ route('admin.contact-messages.index', [], false) }}" class="museum-panel flex flex-wrap items-end gap-3 p-4">
            <label class="museum-field min-w-56 flex-1">
                <span>Search</span>
                <input type="search" name="q" value="{{ $search }}" placeholder="Name, email, subject, or message">
            </label>
            <label class="museum-field min-w-40">
                <span>Status</span>
                <select name="status">
                    <option value="">All messages</option>
                    <option value="unread" @selected(request('status') === 'unread')>Unread only</option>
                </select>
            </label>
            <button type="submit" class="museum-btn">Filter</button>
            @if($search !== '' || request('status'))
                <a href="{{ route('admin.contact-messages.index', [], false) }}" class="museum-btn-secondary">Clear</a>
            @endif
        </form>

        @forelse($messages as $contactMessage)
            <article class="museum-panel {{ $contactMessage->read_at ? '' : 'border-l-4 border-l-amber-500' }}">
                <div class="flex flex-wrap items-start justify-between gap-4">
                    <div>
                        <div class="flex flex-wrap items-center gap-2">
                            <h3 class="museum-section-title">{{ $contactMessage->subject ?: 'No subject' }}</h3>
                            @if(!$contactMessage->read_at)
                                <span class="rounded-full bg-amber-100 px-2 py-1 text-xs font-semibold text-amber-800">Unread</span>
                            @endif
                        </div>
                        <p class="mt-2 font-semibold text-zinc-800">{{ $contactMessage->name }}</p>
                        <a class="text-sm text-zinc-500 hover:underline" href="mailto:{{ $contactMessage->email }}">{{ $contactMessage->email }}</a>
                    </div>
                    <div class="text-right text-xs text-zinc-500">
                        <p title="{{ $contactMessage->created_at->toDateTimeString() }}">{{ $contactMessage->created_at->diffForHumans() }}</p>
                        <p class="mt-1">{{ $contactMessage->created_at->format('d M Y, g:i A') }}</p>
                    </div>
                </div>

                <p class="mt-5 whitespace-pre-wrap border-t border-zinc-200 pt-4 text-sm leading-6 text-zinc-700">{{ $contactMessage->message }}</p>

                <div class="mt-5 flex flex-wrap gap-2">
                    <a class="museum-btn-secondary" href="mailto:{{ $contactMessage->email }}?subject={{ rawurlencode('Re: '.($contactMessage->subject ?: 'Museum Azman enquiry')) }}">Reply by Email</a>
                    @if(!$contactMessage->read_at)
                        <form method="POST" action="{{ route('admin.contact-messages.read', $contactMessage, false) }}">
                            @csrf
                            @method('PATCH')
                            <button type="submit" class="museum-btn">Mark as Read</button>
                        </form>
                    @else
                        <span class="inline-flex items-center px-3 text-xs text-zinc-500">Read {{ $contactMessage->read_at->diffForHumans() }}</span>
                    @endif
                </div>
            </article>
        @empty
            <div class="museum-panel p-8 text-center text-zinc-500">No messages found.</div>
        @endforelse

        @if($messages->hasPages())
            <div>{{ $messages->links() }}</div>
        @endif
    </section>
</x-layout>
