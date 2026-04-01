<x-layout title="Activity Logs - Museum Azman">
    <section class="space-y-6">
        <div class="flex items-start justify-between gap-4">
            <div>
                <h2 class="museum-page-title">Activity Logs</h2>
                <p class="museum-page-subtitle">All user and admin actions, newest first</p>
            </div>
        </div>

        {{-- Filters --}}
        <form method="GET" action="{{ route('admin.activity-logs.index') }}" class="museum-panel p-4 flex flex-wrap items-end gap-3">
            <label class="museum-field flex-1 min-w-48">
                <span>Search</span>
                <input type="text" name="q" value="{{ $search }}" placeholder="User, action, description…">
            </label>
            <label class="museum-field min-w-48">
                <span>Action</span>
                <select name="action">
                    <option value="">All actions</option>
                    @foreach($actionOptions as $opt)
                        <option value="{{ $opt }}" @selected($action === $opt)>{{ $opt }}</option>
                    @endforeach
                </select>
            </label>
            <div class="flex gap-2">
                <button type="submit" class="museum-btn">Filter</button>
                @if($search || $action || $userId)
                    <a href="{{ route('admin.activity-logs.index') }}" class="museum-btn-secondary">Clear</a>
                @endif
            </div>
        </form>

        @if($logs->isEmpty())
            <div class="museum-panel p-8 text-center text-zinc-500">No activity logs found.</div>
        @else
            <article class="museum-panel p-0! overflow-hidden">
                <div class="overflow-x-auto">
                    <table class="w-full min-w-200 text-sm">
                        <thead>
                            <tr class="border-b border-zinc-200 text-left text-zinc-500 text-xs uppercase tracking-wide">
                                <th class="px-4 py-3 w-40">When</th>
                                <th class="px-4 py-3 w-36">User</th>
                                <th class="px-4 py-3 w-24">Role</th>
                                <th class="px-4 py-3 w-44">Action</th>
                                <th class="px-4 py-3">Description</th>
                                <th class="px-4 py-3 w-32">IP</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-zinc-100">
                            @foreach($logs as $log)
                                <tr class="hover:bg-zinc-50 transition-colors">
                                    <td class="px-4 py-3 text-zinc-500 whitespace-nowrap" title="{{ $log->created_at->toDateTimeString() }}">
                                        {{ $log->created_at->diffForHumans() }}
                                    </td>
                                    <td class="px-4 py-3 font-medium text-zinc-800">
                                        {{ $log->user_name ?? '—' }}
                                        @if($log->user_id)
                                            <a href="{{ route('admin.users.edit', $log->user_id) }}" class="ml-1 text-xs text-zinc-400 hover:underline">#{{ $log->user_id }}</a>
                                        @endif
                                    </td>
                                    <td class="px-4 py-3">
                                        @if($log->user_role)
                                            <span class="inline-block rounded-full px-2 py-0.5 text-xs font-medium
                                                {{ $log->user_role === 'admin' ? 'bg-violet-100 text-violet-700' : 'bg-zinc-100 text-zinc-600' }}">
                                                {{ $log->user_role }}
                                            </span>
                                        @else
                                            <span class="text-zinc-400">—</span>
                                        @endif
                                    </td>
                                    <td class="px-4 py-3">
                                        @php
                                            $actionBadge = match(true) {
                                                str_starts_with($log->action, 'auth.') => 'bg-blue-50 text-blue-700',
                                                str_starts_with($log->action, 'artwork.') => 'bg-emerald-50 text-emerald-700',
                                                str_starts_with($log->action, 'movement.') => 'bg-amber-50 text-amber-700',
                                                str_starts_with($log->action, 'location.') => 'bg-rose-50 text-rose-700',
                                                str_starts_with($log->action, 'profile.') => 'bg-cyan-50 text-cyan-700',
                                                str_starts_with($log->action, 'admin.') => 'bg-violet-50 text-violet-700',
                                                default => 'bg-zinc-100 text-zinc-600',
                                            };
                                        @endphp
                                        <span class="inline-block rounded-full px-2 py-0.5 text-xs font-mono font-medium {{ $actionBadge }}">
                                            {{ $log->action }}
                                        </span>
                                    </td>
                                    <td class="px-4 py-3 text-zinc-700">
                                        {{ $log->description }}
                                        @if($log->subject_label && $log->subject_type)
                                            <span class="ml-1 text-xs text-zinc-400">[{{ $log->subject_type }}]</span>
                                        @endif
                                    </td>
                                    <td class="px-4 py-3 text-zinc-400 font-mono text-xs">
                                        {{ $log->ip_address ?? '—' }}
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </article>

            <div class="flex justify-between items-center text-sm text-zinc-500">
                <span>{{ number_format($logs->total()) }} total entries</span>
                {{ $logs->links() }}
            </div>
        @endif
    </section>
</x-layout>
