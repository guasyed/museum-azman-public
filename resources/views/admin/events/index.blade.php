<x-layout title="Programmes CMS - Museum Azman">
    <section class="space-y-6">
        <div>
            <h2 class="museum-page-title">Programmes CMS</h2>
            <p class="museum-page-subtitle">Manage the programmes, stories and upcoming initiatives displayed on the public website.</p>
        </div>

        <details class="museum-panel" open>
            <summary class="museum-section-title cursor-pointer">Page Content</summary>
            <p class="mt-2 text-sm text-zinc-500">Edit the introduction and “In preparation” section on the public Programmes page.</p>
            <form method="POST" action="{{ route('admin.events.content.update', [], false) }}" enctype="multipart/form-data" class="mt-5 grid gap-4 md:grid-cols-2">
                @csrf
                @method('PUT')
                <label class="museum-field"><span>Page Title *</span><input name="public_events_page_title" value="{{ old('public_events_page_title', $content['public_events_page_title']) }}" required></label>
                <label class="museum-field md:col-span-2"><span>Page Introduction *</span><textarea name="public_events_page_description" rows="3" required>{{ old('public_events_page_description', $content['public_events_page_description']) }}</textarea></label>
                <div class="grid gap-4 rounded-xl border border-zinc-200 p-4 md:col-span-2 md:grid-cols-2">
                    <h3 class="font-semibold md:col-span-2">Hero</h3>
                    <label class="museum-field"><span>Hero Kicker *</span><input name="public_events_hero_kicker" value="{{ old('public_events_hero_kicker', $content['public_events_hero_kicker']) }}" required></label>
                    <label class="museum-field"><span>Hero Label *</span><textarea name="public_events_hero_label" rows="2" required>{{ old('public_events_hero_label', $content['public_events_hero_label']) }}</textarea></label>
                    <label class="museum-field md:col-span-2"><span>Replace Hero Image</span><input name="hero_image" type="file" accept="image/jpeg,image/png,image/webp"><small>Recommended portrait or tall image. Leave blank to retain the current image.</small></label>
                    @if($heroImageUrl)
                        <div class="md:col-span-2"><p class="mb-2 text-sm font-medium text-zinc-700">Current Hero Image</p><img src="{{ $heroImageUrl }}" alt="Current Programmes hero" class="rounded-xl border border-zinc-200 object-cover" style="width: 240px; max-width: 100%; height: 180px;"></div>
                    @endif
                </div>
                <div class="grid gap-4 rounded-xl border border-zinc-200 p-4 md:col-span-2 md:grid-cols-2">
                    <h3 class="font-semibold md:col-span-2">Programme List</h3>
                    <label class="museum-field"><span>Eyebrow *</span><input name="public_events_list_eyebrow" value="{{ old('public_events_list_eyebrow', $content['public_events_list_eyebrow']) }}" required></label>
                    <label class="museum-field"><span>Heading *</span><input name="public_events_list_title" value="{{ old('public_events_list_title', $content['public_events_list_title']) }}" required></label>
                    <label class="museum-field md:col-span-2"><span>Description *</span><textarea name="public_events_list_description" rows="2" required>{{ old('public_events_list_description', $content['public_events_list_description']) }}</textarea></label>
                    <label class="museum-field"><span>Button Text *</span><input name="public_events_list_button" value="{{ old('public_events_list_button', $content['public_events_list_button']) }}" required></label>
                </div>
                <div class="grid gap-4 rounded-xl border border-zinc-200 p-4 md:col-span-2 md:grid-cols-2">
                    <h3 class="font-semibold md:col-span-2">One Artwork, One Story</h3>
                    <label class="museum-field"><span>Eyebrow *</span><input name="public_events_story_eyebrow" value="{{ old('public_events_story_eyebrow', $content['public_events_story_eyebrow']) }}" required></label>
                    <label class="museum-field"><span>Image Caption *</span><input name="public_events_story_caption" value="{{ old('public_events_story_caption', $content['public_events_story_caption']) }}" required></label>
                    <label class="museum-field md:col-span-2"><span>Title *</span><input name="public_events_story_title" value="{{ old('public_events_story_title', $content['public_events_story_title']) }}" required></label>
                    <label class="museum-field md:col-span-2"><span>Description *</span><textarea name="public_events_story_description" rows="3" required>{{ old('public_events_story_description', $content['public_events_story_description']) }}</textarea></label>
                    <label class="museum-field"><span>Button Text *</span><input name="public_events_story_button" value="{{ old('public_events_story_button', $content['public_events_story_button']) }}" required></label>
                    <label class="museum-field md:col-span-2"><span>Replace Story Image</span><input name="story_image" type="file" accept="image/jpeg,image/png,image/webp"><small>Leave blank to retain the current image.</small></label>
                    @if($storyImageUrl)
                        <div class="md:col-span-2"><p class="mb-2 text-sm font-medium text-zinc-700">Current Story Image</p><img src="{{ $storyImageUrl }}" alt="Current Programmes story" class="rounded-xl border border-zinc-200 object-cover" style="width: 240px; max-width: 100%; height: 180px;"></div>
                    @endif
                </div>
                <label class="museum-field"><span>In Preparation Title *</span><input name="public_events_programming_title" value="{{ old('public_events_programming_title', $content['public_events_programming_title']) }}" required></label>
                <label class="museum-field"><span>In Preparation Description *</span><textarea name="public_events_programming_description" rows="2" required>{{ old('public_events_programming_description', $content['public_events_programming_description']) }}</textarea></label>
                @foreach(range(1, 2) as $number)
                    <div class="grid gap-4 rounded-xl border border-zinc-200 p-4 md:col-span-2 md:grid-cols-2">
                        <label class="museum-field"><span>Upcoming Initiative {{ $number }} Label *</span><input name="public_events_program_{{ $number }}_label" value="{{ old('public_events_program_'.$number.'_label', $content['public_events_program_'.$number.'_label']) }}" required></label>
                        <label class="museum-field"><span>Upcoming Initiative {{ $number }} Title *</span><input name="public_events_program_{{ $number }}_title" value="{{ old('public_events_program_'.$number.'_title', $content['public_events_program_'.$number.'_title']) }}" required></label>
                        <label class="museum-field"><span>Upcoming Initiative {{ $number }} Description *</span><textarea name="public_events_program_{{ $number }}_description" rows="2" required>{{ old('public_events_program_'.$number.'_description', $content['public_events_program_'.$number.'_description']) }}</textarea></label>
                    </div>
                @endforeach
                <div class="grid gap-4 rounded-xl border border-zinc-200 p-4 md:col-span-2 md:grid-cols-2">
                    <h3 class="font-semibold md:col-span-2">Research Call to Action</h3>
                    <label class="museum-field"><span>Eyebrow *</span><input name="public_events_research_eyebrow" value="{{ old('public_events_research_eyebrow', $content['public_events_research_eyebrow']) }}" required></label>
                    <label class="museum-field"><span>Button Text *</span><input name="public_events_research_button" value="{{ old('public_events_research_button', $content['public_events_research_button']) }}" required></label>
                    <label class="museum-field md:col-span-2"><span>Title *</span><textarea name="public_events_research_title" rows="2" required>{{ old('public_events_research_title', $content['public_events_research_title']) }}</textarea></label>
                    <label class="museum-field md:col-span-2"><span>Description *</span><textarea name="public_events_research_description" rows="2" required>{{ old('public_events_research_description', $content['public_events_research_description']) }}</textarea></label>
                </div>
                <div class="md:col-span-2"><button class="museum-btn" type="submit">Save Page Content</button></div>
            </form>
        </details>

        <details class="museum-panel" open>
            <summary class="museum-section-title cursor-pointer">Add Programme</summary>
            <form method="POST" action="{{ route('admin.events.store', [], false) }}" enctype="multipart/form-data" class="mt-5 grid gap-4 md:grid-cols-2">
                @csrf
                <label class="museum-field"><span>Title *</span><input name="title" value="{{ old('title') }}" required></label>
                <label class="museum-field"><span>Section *</span><select name="section" required>@foreach($sections as $value => $label)<option value="{{ $value }}" @selected(old('section') === $value)>{{ $label }}</option>@endforeach</select></label>
                <label class="museum-field"><span>Programme Type</span><input name="event_type" value="{{ old('event_type') }}" placeholder="Museum Tour, Private Visit, Education"></label>
                <label class="museum-field"><span>Schedule</span><input name="schedule" value="{{ old('schedule') }}" placeholder="March 2027 or Every Saturday, 2pm"></label>
                <label class="museum-field"><span>Display Order</span><input name="sort_order" type="number" min="0" max="999" value="{{ old('sort_order', 0) }}"></label>
                <label class="museum-field"><span>Image</span><input name="image" type="file" accept="image/jpeg,image/png,image/webp"></label>
                <label class="museum-field md:col-span-2"><span>Description</span><textarea name="description" rows="3">{{ old('description') }}</textarea></label>
                <label class="flex items-center gap-2 text-sm font-semibold text-zinc-700"><input name="is_published" type="checkbox" value="1" @checked(old('is_published'))> Display on public website</label>
                <div class="md:col-span-2"><button class="museum-btn" type="submit">Add Programme</button></div>
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
                                <label class="museum-field"><span>Programme Type</span><input name="event_type" value="{{ $event->event_type }}"></label>
                                <label class="museum-field"><span>Schedule</span><input name="schedule" value="{{ $event->schedule }}"></label>
                                <label class="museum-field"><span>Display Order</span><input name="sort_order" type="number" min="0" max="999" value="{{ $event->sort_order }}"></label>
                                <label class="museum-field"><span>Replace Image</span><input name="image" type="file" accept="image/jpeg,image/png,image/webp"></label>
                                <label class="museum-field md:col-span-2"><span>Description</span><textarea name="description" rows="2">{{ $event->description }}</textarea></label>
                                <label class="flex items-center gap-2 text-sm font-semibold text-zinc-700"><input name="is_published" type="checkbox" value="1" @checked($event->is_published)> Display on public website</label>
                                <div class="flex flex-wrap gap-2 md:col-span-2"><button class="museum-btn" type="submit">Save Changes</button></div>
                            </div>
                        </form>
                        <form method="POST" action="{{ route('admin.events.destroy', $event, false) }}" class="mt-3 flex justify-end" onsubmit="return confirm('Delete this programme?')">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="museum-btn-danger">Delete Programme</button>
                        </form>
                    </article>
                @empty
                    <div class="museum-panel p-6 text-center text-zinc-500">No programmes in this section yet.</div>
                @endforelse
            </div>
        @endforeach
    </section>
</x-layout>
