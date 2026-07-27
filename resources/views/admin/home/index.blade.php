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
                    <div class="mt-6 grid gap-4 xl:grid-cols-3">
                        @foreach(range(1, 3) as $slot)
                            @php $programmePrefix = "public_home_programme_{$slot}_"; @endphp
                            <details class="rounded-xl border border-zinc-200 p-4" @if(old($programmePrefix.'source', $content[$programmePrefix.'source']) === 'custom') open @endif>
                                <summary class="cursor-pointer font-semibold">Programme {{ $slot }} custom card</summary>
                                <div class="mt-4 space-y-4">
                                    <label class="museum-field">
                                        <span>Card Source *</span>
                                        <select name="{{ $programmePrefix }}source" required>
                                            <option value="existing" @selected(old($programmePrefix.'source', $content[$programmePrefix.'source']) === 'existing')>Selected/default programme</option>
                                            <option value="custom" @selected(old($programmePrefix.'source', $content[$programmePrefix.'source']) === 'custom')>Custom card</option>
                                        </select>
                                    </label>
                                    <label class="museum-field"><span>Custom Image</span><input type="file" name="programme_{{ $slot }}_image" accept="image/jpeg,image/png,image/webp"></label>
                                    @if($programmeCustomImageUrls[$slot])
                                        <img src="{{ $programmeCustomImageUrls[$slot] }}" alt="Programme {{ $slot }} custom image" class="rounded-lg border border-zinc-200 object-cover" style="width: 180px; height: 120px;">
                                    @endif
                                    <label class="museum-field"><span>Label</span><input name="{{ $programmePrefix }}label" value="{{ old($programmePrefix.'label', $content[$programmePrefix.'label']) }}" placeholder="By appointment"></label>
                                    <label class="museum-field"><span>Title</span><input name="{{ $programmePrefix }}title" value="{{ old($programmePrefix.'title', $content[$programmePrefix.'title']) }}"></label>
                                    <label class="museum-field"><span>Description</span><textarea name="{{ $programmePrefix }}description" rows="3">{{ old($programmePrefix.'description', $content[$programmePrefix.'description']) }}</textarea></label>
                                    <label class="museum-field"><span>Link</span><input type="url" name="{{ $programmePrefix }}link" value="{{ old($programmePrefix.'link', $content[$programmePrefix.'link']) }}" placeholder="https://example.com"></label>
                                </div>
                            </details>
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
                    <div class="mt-6 grid gap-4 xl:grid-cols-3">
                        @foreach(range(1, 3) as $slot)
                            @php $collectionPrefix = "public_home_collection_{$slot}_"; @endphp
                            <details class="rounded-xl border border-zinc-200 p-4" @if(old($collectionPrefix.'source', $content[$collectionPrefix.'source']) === 'custom') open @endif>
                                <summary class="cursor-pointer font-semibold">Artwork {{ $slot }} custom card</summary>
                                <div class="mt-4 space-y-4">
                                    <label class="museum-field">
                                        <span>Card Source *</span>
                                        <select name="{{ $collectionPrefix }}source" required>
                                            <option value="existing" @selected(old($collectionPrefix.'source', $content[$collectionPrefix.'source']) === 'existing')>Selected/default artwork</option>
                                            <option value="custom" @selected(old($collectionPrefix.'source', $content[$collectionPrefix.'source']) === 'custom')>Custom card</option>
                                        </select>
                                    </label>
                                    <label class="museum-field"><span>Custom Image</span><input type="file" name="collection_{{ $slot }}_image" accept="image/jpeg,image/png,image/webp"></label>
                                    @if($collectionCustomImageUrls[$slot])
                                        <img src="{{ $collectionCustomImageUrls[$slot] }}" alt="Artwork {{ $slot }} custom image" class="rounded-lg border border-zinc-200 object-contain" style="width: 180px; height: 120px;">
                                    @endif
                                    <label class="museum-field"><span>Title</span><input name="{{ $collectionPrefix }}title" value="{{ old($collectionPrefix.'title', $content[$collectionPrefix.'title']) }}"></label>
                                    <label class="museum-field"><span>Artist</span><input name="{{ $collectionPrefix }}artist" value="{{ old($collectionPrefix.'artist', $content[$collectionPrefix.'artist']) }}"></label>
                                    <div class="grid grid-cols-2 gap-3">
                                        <label class="museum-field"><span>Year</span><input name="{{ $collectionPrefix }}year" value="{{ old($collectionPrefix.'year', $content[$collectionPrefix.'year']) }}"></label>
                                        <label class="museum-field"><span>Medium</span><input name="{{ $collectionPrefix }}medium" value="{{ old($collectionPrefix.'medium', $content[$collectionPrefix.'medium']) }}"></label>
                                    </div>
                                    <label class="museum-field"><span>Link</span><input type="url" name="{{ $collectionPrefix }}link" value="{{ old($collectionPrefix.'link', $content[$collectionPrefix.'link']) }}" placeholder="https://example.com"></label>
                                </div>
                            </details>
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
                            <img
                                src="{{ $storyImageUrl }}"
                                alt="Current custom story image"
                                class="rounded-xl border border-zinc-200 bg-zinc-50 object-contain"
                                style="width: 320px; max-width: 100%; height: 180px;"
                            >
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
                    <label class="museum-field md:col-span-2">
                        <span>Custom Background Image</span>
                        <input type="file" name="experience_background" accept="image/jpeg,image/png,image/webp">
                        <small>{{ $experienceBackgroundUrl ? 'Leave blank to retain the current background.' : 'Upload a wide JPG, PNG or WebP image up to 15 MB.' }}</small>
                    </label>
                    @if($experienceBackgroundUrl)
                        <div class="md:col-span-2">
                            <p class="mb-2 text-sm font-medium text-zinc-700">Current Background</p>
                            <img src="{{ $experienceBackgroundUrl }}" alt="Current Experience Art Intimately background" class="rounded-xl border border-zinc-200 object-cover" style="width: 320px; max-width: 100%; height: 180px;">
                        </div>
                    @endif
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
