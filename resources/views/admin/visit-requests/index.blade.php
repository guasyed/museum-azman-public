<x-layout title="Visit Requests - Museum Azman">
    <section class="space-y-6">
        <div>
            <h2 class="museum-page-title">Visit Requests</h2>
            <p class="museum-page-subtitle">Private viewing registrations submitted through the public website</p>
        </div>

        <div class="grid gap-3 sm:grid-cols-2">
            <article class="museum-stat-card"><p>Pending Review</p><strong>{{ number_format($pendingCount) }}</strong></article>
            <article class="museum-stat-card"><p>Requests Found</p><strong>{{ number_format($visitRequests->total()) }}</strong></article>
        </div>

        <form method="GET" action="{{ route('admin.visit-requests.index', [], false) }}" class="museum-panel flex flex-wrap items-end gap-3 p-4">
            <label class="museum-field min-w-56 flex-1"><span>Search</span><input type="search" name="q" value="{{ $search }}" placeholder="Name, email, phone, or organisation"></label>
            <label class="museum-field min-w-40"><span>Status</span><select name="status"><option value="">All requests</option><option value="pending" @selected(request('status') === 'pending')>Pending only</option></select></label>
            <button type="submit" class="museum-btn">Filter</button>
            @if($search !== '' || request('status'))<a href="{{ route('admin.visit-requests.index', [], false) }}" class="museum-btn-secondary">Clear</a>@endif
        </form>

        @forelse($visitRequests as $visitRequest)
            <article class="museum-panel {{ $visitRequest->reviewed_at ? '' : 'border-l-4 border-l-amber-500' }}">
                <div class="flex flex-wrap items-start justify-between gap-4">
                    <div>
                        <div class="flex flex-wrap items-center gap-2">
                            <h3 class="museum-section-title">{{ $visitRequest->name }}</h3>
                            @if(!$visitRequest->reviewed_at)<span class="rounded-full bg-amber-100 px-2 py-1 text-xs font-semibold text-amber-800">Pending</span>@endif
                        </div>
                        <a class="mt-2 block text-sm text-zinc-600 hover:underline" href="mailto:{{ $visitRequest->email }}">{{ $visitRequest->email }}</a>
                        <a class="text-sm text-zinc-600 hover:underline" href="tel:{{ $visitRequest->phone }}">{{ $visitRequest->phone }}</a>
                    </div>
                    <div class="text-right text-xs text-zinc-500">
                        <p>Submitted {{ $visitRequest->created_at->diffForHumans() }}</p>
                        <p class="mt-1">{{ $visitRequest->created_at->format('d M Y, g:i A') }}</p>
                    </div>
                </div>

                <div class="mt-5 grid gap-3 sm:grid-cols-2 lg:grid-cols-4">
                    <div class="museum-detail"><span>Preferred Date</span><strong>{{ $visitRequest->preferred_date->format('d M Y') }}</strong></div>
                    <div class="museum-detail"><span>Guests</span><strong>{{ $visitRequest->guests }}</strong></div>
                    <div class="museum-detail"><span>Purpose</span><strong>{{ $visitRequest->purpose }}</strong></div>
                    <div class="museum-detail"><span>Interest</span><strong>{{ $visitRequest->category }}</strong></div>
                    <div class="museum-detail"><span>Occupation</span><strong>{{ $visitRequest->occupation }}</strong></div>
                    <div class="museum-detail"><span>Organisation</span><strong>{{ $visitRequest->company }}</strong></div>
                    <div class="museum-detail"><span>City / Country</span><strong>{{ $visitRequest->city }}</strong></div>
                    <div class="museum-detail"><span>Referral Source</span><strong>{{ $visitRequest->source }}</strong></div>
                </div>

                @if($visitRequest->social)<p class="mt-4 text-sm text-zinc-600"><strong>Social profile:</strong> {{ $visitRequest->social }}</p>@endif
                @if($visitRequest->message)<p class="mt-4 whitespace-pre-wrap border-t border-zinc-200 pt-4 text-sm leading-6 text-zinc-700">{{ $visitRequest->message }}</p>@endif
                @if($visitRequest->preferences)
                    <div class="mt-4 flex flex-wrap gap-2">
                        @foreach($visitRequest->preferences as $preference)<span class="rounded-full bg-zinc-100 px-3 py-1 text-xs text-zinc-700">{{ str($preference)->replace('-', ' ')->title() }}</span>@endforeach
                    </div>
                @endif

                <div class="mt-5 flex flex-wrap gap-2">
                    <a class="museum-btn-secondary" href="mailto:{{ $visitRequest->email }}?subject={{ rawurlencode('Museum Azman visit request') }}">Reply by Email</a>
                    @if(!$visitRequest->reviewed_at)
                        <form method="POST" action="{{ route('admin.visit-requests.reviewed', $visitRequest, false) }}">@csrf @method('PATCH')<button type="submit" class="museum-btn">Mark Reviewed</button></form>
                    @else
                        <span class="inline-flex items-center px-3 text-xs text-zinc-500">Reviewed {{ $visitRequest->reviewed_at->diffForHumans() }}</span>
                    @endif
                </div>
            </article>
        @empty
            <div class="museum-panel p-8 text-center text-zinc-500">No visit requests found.</div>
        @endforelse

        @if($visitRequests->hasPages())<div>{{ $visitRequests->links() }}</div>@endif
    </section>
</x-layout>
