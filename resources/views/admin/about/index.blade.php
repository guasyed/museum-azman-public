<x-layout title="About CMS - Museum Azman">
    <section class="space-y-6">
        <div>
            <h2 class="museum-page-title">About CMS</h2>
            <p class="museum-page-subtitle">Manage all content and images displayed on the public About page</p>
        </div>

        <form method="POST" action="{{ route('admin.about.update', [], false) }}" enctype="multipart/form-data" class="space-y-6">
            @csrf
            @method('PUT')

            <details class="museum-panel" open>
                <summary class="museum-section-title cursor-pointer">Hero</summary>
                <div class="mt-5 grid gap-4 md:grid-cols-2">
                    <label class="museum-field"><span>Hero Title *</span><input name="public_about_hero_title" value="{{ old('public_about_hero_title', $content['public_about_hero_title']) }}" required></label>
                    <label class="museum-field"><span>Hero Subtitle *</span><input name="public_about_hero_subtitle" value="{{ old('public_about_hero_subtitle', $content['public_about_hero_subtitle']) }}" required></label>
                    <label class="museum-field"><span>Replace Hero Image</span><input name="hero_image" type="file" accept="image/jpeg,image/png,image/webp"></label>
                    @if($heroImageUrl)<img src="{{ $heroImageUrl }}" alt="Current hero" class="h-32 w-full rounded-xl object-cover">@endif
                </div>
            </details>

            <details class="museum-panel" open>
                <summary class="museum-section-title cursor-pointer">Our Mission</summary>
                <div class="mt-5 grid gap-4">
                    <label class="museum-field"><span>Section Title *</span><input name="public_about_mission_title" value="{{ old('public_about_mission_title', $content['public_about_mission_title']) }}" required></label>
                    @foreach(range(1, 3) as $number)<label class="museum-field"><span>Paragraph {{ $number }} *</span><textarea name="public_about_mission_paragraph_{{ $number }}" rows="3" required>{{ old('public_about_mission_paragraph_'.$number, $content['public_about_mission_paragraph_'.$number]) }}</textarea></label>@endforeach
                </div>
            </details>

            <details class="museum-panel" open>
                <summary class="museum-section-title cursor-pointer">Looking Forward</summary>
                <div class="mt-5 grid gap-4">
                    <label class="museum-field"><span>Section Title *</span><input name="public_about_forward_title" value="{{ old('public_about_forward_title', $content['public_about_forward_title']) }}" required></label>
                    @foreach(range(1, 3) as $number)<label class="museum-field"><span>Paragraph {{ $number }}{{ $number === 3 ? ' (Highlighted)' : '' }} *</span><textarea name="public_about_forward_paragraph_{{ $number }}" rows="3" required>{{ old('public_about_forward_paragraph_'.$number, $content['public_about_forward_paragraph_'.$number]) }}</textarea></label>@endforeach
                </div>
            </details>

            <details class="museum-panel" open>
                <summary class="museum-section-title cursor-pointer">Our Values</summary>
                <div class="mt-5 grid gap-4 md:grid-cols-2">
                    <label class="museum-field"><span>Section Title *</span><input name="public_about_values_title" value="{{ old('public_about_values_title', $content['public_about_values_title']) }}" required></label>
                    @foreach(range(1, 3) as $number)
                        <div class="grid gap-4 rounded-xl border border-zinc-200 p-4 md:col-span-2 md:grid-cols-2">
                            <label class="museum-field"><span>Value {{ $number }} Title *</span><input name="public_about_value_{{ $number }}_title" value="{{ old('public_about_value_'.$number.'_title', $content['public_about_value_'.$number.'_title']) }}" required></label>
                            <label class="museum-field"><span>Value {{ $number }} Description *</span><textarea name="public_about_value_{{ $number }}_description" rows="2" required>{{ old('public_about_value_'.$number.'_description', $content['public_about_value_'.$number.'_description']) }}</textarea></label>
                        </div>
                    @endforeach
                </div>
            </details>

            <details class="museum-panel" open>
                <summary class="museum-section-title cursor-pointer">The Space</summary>
                <div class="mt-5 grid gap-4 md:grid-cols-2">
                    <label class="museum-field"><span>Section Title *</span><input name="public_about_space_title" value="{{ old('public_about_space_title', $content['public_about_space_title']) }}" required></label>
                    <label class="museum-field md:col-span-2"><span>Paragraph 1 *</span><textarea name="public_about_space_paragraph_1" rows="3" required>{{ old('public_about_space_paragraph_1', $content['public_about_space_paragraph_1']) }}</textarea></label>
                    <label class="museum-field md:col-span-2"><span>Paragraph 2 *</span><textarea name="public_about_space_paragraph_2" rows="3" required>{{ old('public_about_space_paragraph_2', $content['public_about_space_paragraph_2']) }}</textarea></label>
                    <label class="museum-field"><span>Replace Space Image</span><input name="space_image" type="file" accept="image/jpeg,image/png,image/webp"></label>
                    @if($spaceImageUrl)<img src="{{ $spaceImageUrl }}" alt="Current space" class="h-40 w-full rounded-xl object-cover">@endif
                </div>
            </details>

            <div class="sticky bottom-4 z-20 flex justify-end"><button class="museum-btn shadow-lg" type="submit">Save About Page</button></div>
        </form>
    </section>
</x-layout>
