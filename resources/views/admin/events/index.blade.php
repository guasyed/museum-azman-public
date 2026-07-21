<x-layout title="Events CMS - Museum Azman">
    <section class="space-y-6">
        <div>
            <h2 class="museum-page-title">Events CMS</h2>
            <p class="museum-page-subtitle">Manage the event cards displayed on the public website</p>
        </div>

        <details class="museum-panel" open>
            <summary class="museum-section-title cursor-pointer">Add Event</summary>
            <form method="POST" action="{{ route('admin.events.store', [], false) }}" enctype="multipart/form-data" class="mt-5 grid gap-4 md:grid-cols-2">
                @csrf
                <label class="museum-field"><span>Title *</span><input name="title" value="{{ old('title') }}" required></label>
                <label class="museum-field"><span>Section *</span><select name="section" required>@foreach($sections as $value => $label)<option value="{{ $value }}" @selected(old('section') === $value)>{{ $label }}</option>@endforeach</select></label>
                <label class="museum-field"><span>Event Type</span><input name="event_type" value="{{ old('event_type') }}" placeholder="Exhibition, Artist Talk, Private Event"></label>
                <label class="museum-field"><span>Schedule</span><input name="schedule" value="{{ old('schedule') }}" placeholder="March 2027 or Every Saturday, 2pm"></label>
                <label class="museum-field"><span>Display Order</span><input name="sort_order" type="number" min="0" max="999" value="{{ old('sort_order', 0) }}"></label>
                <label class="museum-field"><span>Image</span><input name="image" type="file" accept="image/jpeg,image/png,image/webp"></label>
                <label class="museum-field md:col-span-2"><span>Description</span><textarea name="description" rows="3">{{ old('description') }}</textarea></label>
                <label class="flex items-center gap-2 text-sm font-semibold text-zinc-700"><input name="is_published" type="checkbox" value="1" @checked(old('is_published'))> Display on public website</label>
                <div class="md:col-span-2"><button class="museum-btn" type="submit">Add Event</button></div>
            </form>
        </details>

        @foreach($sections as $sectionKey => $sectionLabel)
            <div class="space-y-3">
                <div class="flex items-center justify-between gap-3">
                    <h3 class="museum-section-title">{{ $sectionLabel }}</h3>
                    <span class="text-sm text-zinc-500">{{ collect($events->get($sectionKey, []))->where('is_published', true)->count() }} published</span>
                </div>

                @forelse($events->get($sectionKey, []) as $event)
                    <article class="museum-panel">
                        <form method="POST" action="{{ route('admin.events.update', $event, false) }}" enctype="multipart/form-data" class="grid gap-4 lg:grid-cols-[160px_1fr]">
                            @csrf
                            @method('PUT')
                            <div>
                                @if($event->image_url)
                                    <img src="{{ $event->image_url }}" alt="{{ $event->title }}" class="h-40 w-full rounded-xl object-cover">
                                @else
                                    <div class="flex h-40 items-center justify-center rounded-xl bg-zinc-100 text-sm text-zinc-500">Coming Soon</div>
                                @endif
                            </div>
                            <div class="grid gap-4 md:grid-cols-2">
                                <label class="museum-field"><span>Title *</span><input name="title" value="{{ $event->title }}" required></label>
                                <label class="museum-field"><span>Section *</span><select name="section" required>@foreach($sections as $value => $label)<option value="{{ $value }}" @selected($event->section === $value)>{{ $label }}</option>@endforeach</select></label>
                                <label class="museum-field"><span>Event Type</span><input name="event_type" value="{{ $event->event_type }}"></label>
                                <label class="museum-field"><span>Schedule</span><input name="schedule" value="{{ $event->schedule }}"></label>
                                <label class="museum-field"><span>Display Order</span><input name="sort_order" type="number" min="0" max="999" value="{{ $event->sort_order }}"></label>
                                <label class="museum-field"><span>Replace Image</span><input name="image" type="file" accept="image/jpeg,image/png,image/webp"></label>
                                <label class="museum-field md:col-span-2"><span>Description</span><textarea name="description" rows="2">{{ $event->description }}</textarea></label>
                                <label class="flex items-center gap-2 text-sm font-semibold text-zinc-700"><input name="is_published" type="checkbox" value="1" @checked($event->is_published)> Display on public website</label>
                                <div class="flex flex-wrap gap-2 md:col-span-2"><button class="museum-btn" type="submit">Save Changes</button></div>
                            </div>
                        </form>
                        <form method="POST" action="{{ route('admin.events.destroy', $event, false) }}" class="mt-3 flex justify-end" onsubmit="return confirm('Delete this event?')">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="museum-btn-danger">Delete Event</button>
                        </form>
                    </article>
                @empty
                    <div class="museum-panel p-6 text-center text-zinc-500">No CMS events yet. The public section displays three Coming Soon cards.</div>
                @endforelse
            </div>
        @endforeach
    </section>
</x-layout>
