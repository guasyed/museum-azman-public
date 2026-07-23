<x-layout title="Artists CMS - Museum Azman">
    <section class="space-y-6">
        <div>
            <h2 class="museum-page-title">Artists CMS</h2>
            <p class="museum-page-subtitle">Choose and arrange the artists displayed on the public Artists page</p>
        </div>

        <details class="museum-panel" open>
            <summary class="museum-section-title cursor-pointer">Page Content</summary>
            <p class="mt-2 text-sm text-zinc-500">Edit the introduction and Artist Collaborations section on the public Artists page.</p>
            <form method="POST" action="{{ route('admin.public-artists.content.update', [], false) }}" class="mt-5 grid gap-4 md:grid-cols-2">
                @csrf
                @method('PUT')
                <label class="museum-field"><span>Page Title *</span><input name="public_artists_page_title" value="{{ old('public_artists_page_title', $content['public_artists_page_title']) }}" required></label>
                <label class="museum-field md:col-span-2"><span>Page Introduction *</span><textarea name="public_artists_page_description" rows="3" required>{{ old('public_artists_page_description', $content['public_artists_page_description']) }}</textarea></label>
                <label class="museum-field"><span>Collaboration Section Title *</span><input name="public_artists_collaboration_title" value="{{ old('public_artists_collaboration_title', $content['public_artists_collaboration_title']) }}" required></label>
                <label class="museum-field md:col-span-2"><span>Collaboration Description *</span><textarea name="public_artists_collaboration_description" rows="4" required>{{ old('public_artists_collaboration_description', $content['public_artists_collaboration_description']) }}</textarea></label>
                <div class="md:col-span-2"><button class="museum-btn" type="submit">Save Page Content</button></div>
            </form>
        </details>

        <details class="museum-panel" open>
            <summary class="museum-section-title cursor-pointer">Add Public Artist</summary>
            @if($availableArtists->isEmpty())
                <p class="mt-4 text-sm text-zinc-500">All artists are already in the public CMS.</p>
            @else
                <form method="POST" action="{{ route('admin.public-artists.store', [], false) }}" enctype="multipart/form-data" class="mt-5 grid gap-4 md:grid-cols-2">
                    @csrf
                    <label class="museum-field"><span>Artist *</span><select name="artist_id" required><option value="">Select artist</option>@foreach($availableArtists as $artist)<option value="{{ $artist->id }}" @selected((string) old('artist_id') === (string) $artist->id)>{{ $artist->name }}{{ $artist->country ? ' — '.$artist->country : '' }}</option>@endforeach</select></label>
                    <label class="museum-field"><span>Display Order</span><input name="sort_order" type="number" min="0" max="999" value="{{ old('sort_order', 0) }}"></label>
                    <label class="museum-field"><span>Public Image</span><input name="image" type="file" accept="image/jpeg,image/png,image/webp"><small>Optional. First artwork image is used as fallback.</small></label>
                    <label class="museum-field md:col-span-2"><span>Public Biography</span><textarea name="biography" rows="3" placeholder="Optional short introduction">{{ old('biography') }}</textarea></label>
                    <label class="flex items-center gap-2 text-sm font-semibold text-zinc-700"><input name="is_published" type="checkbox" value="1" @checked(old('is_published', true))> Display on public website</label>
                    <div class="md:col-span-2"><button class="museum-btn" type="submit">Add Artist</button></div>
                </form>
            @endif
        </details>

        <div class="flex items-center justify-between gap-3">
            <h3 class="museum-section-title">Public Artist Profiles</h3>
            <span class="text-sm text-zinc-500">{{ $profiles->where('is_published', true)->count() }} published</span>
        </div>

        @forelse($profiles as $profile)
            <article class="museum-panel">
                <form method="POST" action="{{ route('admin.public-artists.update', $profile, false) }}" enctype="multipart/form-data" class="grid gap-4 lg:grid-cols-[160px_1fr]">
                    @csrf
                    @method('PUT')
                    <div>
                        @if($profile->image_url)
                            <img src="{{ $profile->image_url }}" alt="{{ $profile->artist->name }}" class="h-40 w-full rounded-xl object-cover">
                        @else
                            <div class="flex h-40 items-center justify-center rounded-xl bg-zinc-100 text-center text-sm text-zinc-500">Artwork image<br>used on public page</div>
                        @endif
                    </div>
                    <div class="grid gap-4 md:grid-cols-2">
                        <input type="hidden" name="artist_id" value="{{ $profile->artist_id }}">
                        <div><span class="text-xs font-bold uppercase text-zinc-500">Artist</span><p class="mt-1 font-semibold">{{ $profile->artist->name }}</p><p class="text-sm text-zinc-500">{{ $profile->artist->country ?: 'Country not specified' }}</p></div>
                        <label class="museum-field"><span>Display Order</span><input name="sort_order" type="number" min="0" max="999" value="{{ $profile->sort_order }}"></label>
                        <label class="museum-field"><span>Replace Public Image</span><input name="image" type="file" accept="image/jpeg,image/png,image/webp"></label>
                        <label class="museum-field md:col-span-2"><span>Public Biography</span><textarea name="biography" rows="3">{{ $profile->biography }}</textarea></label>
                        <label class="flex items-center gap-2 text-sm font-semibold text-zinc-700"><input name="is_published" type="checkbox" value="1" @checked($profile->is_published)> Display on public website</label>
                        <div class="md:col-span-2"><button class="museum-btn" type="submit">Save Changes</button></div>
                    </div>
                </form>
                <form method="POST" action="{{ route('admin.public-artists.destroy', $profile, false) }}" class="mt-3 flex justify-end" onsubmit="return confirm('Remove this artist from the public CMS?')">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="museum-btn-danger">Remove Artist</button>
                </form>
            </article>
        @empty
            <div class="museum-panel p-6 text-center text-zinc-500">No artist profiles configured yet. The current public artist layout remains visible until the first profile is added.</div>
        @endforelse
    </section>
</x-layout>
