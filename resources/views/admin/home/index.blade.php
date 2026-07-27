<x-layout title="Home CMS - Museum Azman">
    <section class="space-y-6">
        <div>
            <h2 class="museum-page-title">Home CMS</h2>
            <p class="museum-page-subtitle">Manage the content and featured items displayed on the public homepage.</p>
        </div>

        <form method="POST" action="{{ route('admin.home.update', [], false) }}" enctype="multipart/form-data" class="space-y-6">
            @csrf
            @method('PUT')

            <details class="museum-panel" open>
                <summary class="museum-section-title cursor-pointer">Hero</summary>
                <div class="mt-5 grid gap-4 md:grid-cols-2">
                    <label class="museum-field">
                        <span>Hero Title</span>
                        <input name="public_home_hero_title" value="{{ old('public_home_hero_title', $content['public_home_hero_title']) }}" placeholder="Museum Azman">
                    </label>
                    <label class="museum-field">
                        <span>Hero Subtitle</span>
                        <input name="public_home_hero_subtitle" value="{{ old('public_home_hero_subtitle', $content['public_home_hero_subtitle']) }}" placeholder="A private contemporary art museum creating dialogue between East and West.">
                    </label>
                    <label class="museum-field md:col-span-2">
                        <span>Replace Hero Image</span>
                        <input type="file" name="hero_image" accept="image/jpeg,image/png,image/webp">
                        <small>Leave blank to retain the current homepage image. Recommended wide image, at least 1920px.</small>
                    </label>
                </div>
            </details>

            <details class="museum-panel" open>
                <summary class="museum-section-title cursor-pointer">Museum Programmes</summary>
                <div class="mt-5 grid gap-4 md:grid-cols-2">
                    <label class="museum-field">
                        <span>Section Title *</span>
                        <input name="public_home_events_title" value="{{ old('public_home_events_title', $content['public_home_events_title']) }}" required>
                    </label>
                    <label class="museum-field">
                        <span>Section Description *</span>
                        <textarea name="public_home_events_description" rows="2" required>{{ old('public_home_events_description', $content['public_home_events_description']) }}</textarea>
                    </label>
                </div>
                <div class="mt-6 border-t border-zinc-200 pt-6">
                    <h3 class="font-semibold">Programme Cards — 3 fixed positions</h3>
                    <p class="text-sm text-zinc-500">Choose a published event for each homepage card.</p>
                    <div class="mt-3 grid gap-4 md:grid-cols-3">
                        @foreach(range(0, 2) as $slot)
                            <label class="museum-field">
                                <span>Programme {{ $slot + 1 }}</span>
                                <select name="featured_event_ids[]">
                                    <option value="">Use default programme</option>
                                    @foreach($events as $event)
                                        <option value="{{ $event->id }}" @selected((string) old('featured_event_ids.'.$slot, $selectedEventIds[$slot] ?? '') === (string) $event->id)>{{ $event->title }}</option>
                                    @endforeach
                                </select>
                            </label>
                        @endforeach
                    </div>
                </div>
            </details>

            <details class="museum-panel" open>
                <summary class="museum-section-title cursor-pointer">Collection in Focus</summary>
                <div class="mt-5 grid gap-4 md:grid-cols-2">
                    <label class="museum-field">
                        <span>Section Title *</span>
                        <input name="public_home_works_title" value="{{ old('public_home_works_title', $content['public_home_works_title']) }}" required>
                    </label>
                    <label class="museum-field">
                        <span>Section Description *</span>
                        <textarea name="public_home_works_description" rows="2" required>{{ old('public_home_works_description', $content['public_home_works_description']) }}</textarea>
                    </label>
                </div>
                <div class="mt-6 border-t border-zinc-200 pt-6">
                    <h3 class="font-semibold">Collection Cards — 3 fixed positions</h3>
                    <p class="text-sm text-zinc-500">Choose a published artwork for each homepage card.</p>
                    <div class="mt-3 grid gap-4 md:grid-cols-3">
                        @foreach(range(0, 2) as $slot)
                            <label class="museum-field">
                                <span>Artwork {{ $slot + 1 }}</span>
                                <select name="selected_work_ids[]">
                                    <option value="">Use default artwork</option>
                                    @foreach($works as $item)
                                        <option value="{{ $item->id }}" @selected((string) old('selected_work_ids.'.$slot, $selectedWorkIds[$slot] ?? '') === (string) $item->id)>{{ $item->artwork?->title ?: 'Untitled' }} — {{ $item->artwork?->artist?->name ?: 'Unknown artist' }}</option>
                                    @endforeach
                                </select>
                            </label>
                        @endforeach
                    </div>
                </div>
            </details>

            <details class="museum-panel" open>
                <summary class="museum-section-title cursor-pointer">One Artwork, One Story</summary>
                <div class="mt-5 grid gap-4 md:grid-cols-2">
                    <label class="museum-field">
                        <span>Image Source *</span>
                        <select name="public_home_story_source" required>
                            <option value="collection" @selected(old('public_home_story_source', $content['public_home_story_source']) === 'collection')>Choose from collection</option>
                            <option value="custom" @selected(old('public_home_story_source', $content['public_home_story_source']) === 'custom')>Custom upload</option>
                        </select>
                    </label>
                    <label class="museum-field">
                        <span>Collection Artwork</span>
                        <select name="story_work_id">
                            <option value="">Use the first Collection in Focus artwork</option>
                            @foreach($works as $item)
                                <option value="{{ $item->id }}" @selected((string) old('story_work_id', $selectedStoryWorkId) === (string) $item->id)>{{ $item->artwork?->title ?: 'Untitled' }} — {{ $item->artwork?->artist?->name ?: 'Unknown artist' }}</option>
                            @endforeach
                        </select>
                        <small>Used when the image source is “Choose from collection”.</small>
                    </label>
                    <label class="museum-field md:col-span-2">
                        <span>Custom Story Image</span>
                        <input type="file" name="story_image" accept="image/jpeg,image/png,image/webp">
                        <small>{{ $storyImageUrl ? 'Leave blank to retain the current custom image.' : 'Upload a JPG, PNG or WebP image up to 15 MB.' }}</small>
                    </label>
                    @if($storyImageUrl)
                        <div class="md:col-span-2">
                            <p class="mb-2 text-sm font-medium text-zinc-700">Current Custom Image</p>
                            <img src="{{ $storyImageUrl }}" alt="Current custom story image" class="h-48 max-w-full rounded-xl border border-zinc-200 object-contain">
                        </div>
                    @endif
                    <label class="museum-field">
                        <span>Eyebrow *</span>
                        <input name="public_home_story_eyebrow" value="{{ old('public_home_story_eyebrow', $content['public_home_story_eyebrow']) }}" required>
                    </label>
                    <label class="museum-field">
                        <span>Optional Display Title</span>
                        <input name="public_home_story_title" value="{{ old('public_home_story_title', $content['public_home_story_title']) }}" placeholder="Uses artwork title when blank">
                    </label>
                    <label class="museum-field md:col-span-2">
                        <span>Optional Story Description</span>
                        <textarea name="public_home_story_description" rows="3" placeholder="Uses an automatically generated description when blank">{{ old('public_home_story_description', $content['public_home_story_description']) }}</textarea>
                    </label>
                    <label class="museum-field">
                        <span>Button Text *</span>
                        <input name="public_home_story_button" value="{{ old('public_home_story_button', $content['public_home_story_button']) }}" required>
                    </label>
                </div>
            </details>

            <details class="museum-panel" open>
                <summary class="museum-section-title cursor-pointer">Experience Art Intimately</summary>
                <div class="mt-5 grid gap-4 md:grid-cols-2">
                    <label class="museum-field"><span>Title *</span><input name="public_home_experience_title" value="{{ old('public_home_experience_title', $content['public_home_experience_title']) }}" required></label>
                    <label class="museum-field"><span>Button Text *</span><input name="public_home_experience_button" value="{{ old('public_home_experience_button', $content['public_home_experience_button']) }}" required></label>
                    <label class="museum-field md:col-span-2"><span>Description *</span><textarea name="public_home_experience_description" rows="3" required>{{ old('public_home_experience_description', $content['public_home_experience_description']) }}</textarea></label>
                </div>
            </details>

            <details class="museum-panel" open>
                <summary class="museum-section-title cursor-pointer">Our Vision</summary>
                <div class="mt-5 grid gap-4 md:grid-cols-2">
                    <label class="museum-field md:col-span-2"><span>Title *</span><input name="public_home_vision_title" value="{{ old('public_home_vision_title', $content['public_home_vision_title']) }}" required></label>
                    <label class="museum-field md:col-span-2"><span>Paragraph 1 *</span><textarea name="public_home_vision_paragraph_1" rows="3" required>{{ old('public_home_vision_paragraph_1', $content['public_home_vision_paragraph_1']) }}</textarea></label>
                    <label class="museum-field md:col-span-2"><span>Paragraph 2 *</span><textarea name="public_home_vision_paragraph_2" rows="3" required>{{ old('public_home_vision_paragraph_2', $content['public_home_vision_paragraph_2']) }}</textarea></label>
                    <label class="museum-field md:col-span-2"><span>Highlighted Note *</span><textarea name="public_home_vision_note" rows="2" required>{{ old('public_home_vision_note', $content['public_home_vision_note']) }}</textarea></label>
                </div>
            </details>

            <details class="museum-panel" open>
                <summary class="museum-section-title cursor-pointer">Stay Connected</summary>
                <div class="mt-5 grid gap-4 md:grid-cols-2">
                    <label class="museum-field"><span>Title *</span><input name="public_home_connect_title" value="{{ old('public_home_connect_title', $content['public_home_connect_title']) }}" required></label>
                    <label class="museum-field"><span>Button Text *</span><input name="public_home_connect_button" value="{{ old('public_home_connect_button', $content['public_home_connect_button']) }}" required></label>
                    <label class="museum-field md:col-span-2"><span>Description *</span><textarea name="public_home_connect_description" rows="3" required>{{ old('public_home_connect_description', $content['public_home_connect_description']) }}</textarea></label>
                </div>
            </details>

            <div class="sticky bottom-4 z-20 flex justify-end">
                <button class="museum-btn shadow-lg" type="submit">Save Home Page</button>
            </div>
        </form>
    </section>
</x-layout>
