<x-layout title="Collection CMS - Museum Azman">
    <section class="space-y-6">
        <div>
            <h2 class="museum-page-title">Collection CMS</h2>
            <p class="museum-page-subtitle">Choose and arrange artworks displayed on the public Collection page</p>
        </div>

        <details class="museum-panel" open>
            <summary class="museum-section-title cursor-pointer">Page Content</summary>
            <p class="mt-2 text-sm text-zinc-500">Edit the page introduction and Collecting Philosophy text panel.</p>
            <form method="POST" action="{{ route('admin.public-collection.content.update', [], false) }}" class="mt-5 grid gap-4 md:grid-cols-2">
                @csrf
                @method('PUT')
                <label class="museum-field"><span>Page Title *</span><input name="public_collection_page_title" value="{{ old('public_collection_page_title', $content['public_collection_page_title']) }}" required></label>
                <label class="museum-field md:col-span-2"><span>Page Introduction *</span><textarea name="public_collection_page_description" rows="3" required>{{ old('public_collection_page_description', $content['public_collection_page_description']) }}</textarea></label>
                <label class="museum-field"><span>Text Panel Title *</span><input name="public_collection_philosophy_title" value="{{ old('public_collection_philosophy_title', $content['public_collection_philosophy_title']) }}" required></label>
                @foreach(range(1, 3) as $number)
                    <label class="museum-field md:col-span-2"><span>Text Panel Paragraph {{ $number }} *</span><textarea name="public_collection_philosophy_paragraph_{{ $number }}" rows="3" required>{{ old('public_collection_philosophy_paragraph_'.$number, $content['public_collection_philosophy_paragraph_'.$number]) }}</textarea></label>
                @endforeach
                <div class="md:col-span-2"><button class="museum-btn" type="submit">Save Page Content</button></div>
            </form>
        </details>

        <details class="museum-panel" open>
            <summary class="museum-section-title cursor-pointer">Add Artwork</summary>
            @if($availableArtworks->isEmpty())
                <p class="mt-4 text-sm text-zinc-500">All artworks are already in the Collection CMS.</p>
            @else
                <form method="POST" action="{{ route('admin.public-collection.store', [], false) }}" class="mt-5 grid gap-4 md:grid-cols-2">
                    @csrf
                    <div class="museum-field relative" data-artwork-picker>
                        <span>Search and Select Artwork *</span>
                        <input type="hidden" name="artwork_id" value="{{ old('artwork_id') }}" data-artwork-value required>
                        <div class="relative">
                            <svg class="pointer-events-none absolute left-3.5 top-1/2 z-10 -translate-y-1/2 text-zinc-400" style="width: 1rem; height: 1rem;" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" aria-hidden="true"><circle cx="11" cy="11" r="8"></circle><path d="m21 21-4.3-4.3"></path></svg>
                            <input class="w-full" style="padding-left: 2.75rem; padding-right: 2.5rem;" type="search" placeholder="Search title, artist or inventory code..." autocomplete="off" data-artwork-search aria-label="Search artwork" aria-expanded="false">
                        </div>
                        <div class="absolute left-0 right-0 z-30 hidden max-h-72 overflow-y-auto rounded-xl border border-zinc-200 bg-white p-1 shadow-xl" style="top: calc(100% + 0.25rem);" data-artwork-options>
                            @foreach($availableArtworks as $artwork)
                                @php $optionLabel = ($artwork->title ?: 'Untitled').' — '.($artwork->artist?->name ?: 'Unknown artist').' ('.$artwork->display_inventory_code.')'; @endphp
                                <button type="button" class="block w-full rounded-lg px-3 py-2 text-left text-sm hover:bg-zinc-100" data-artwork-option data-value="{{ $artwork->id }}" data-search="{{ strtolower($optionLabel) }}">{{ $optionLabel }}</button>
                            @endforeach
                            <p class="hidden px-3 py-3 text-sm text-zinc-500" data-artwork-empty>No matching artwork found.</p>
                        </div>
                    </div>
                    <label class="museum-field"><span>Display Order</span><input name="sort_order" type="number" min="0" max="999" value="{{ old('sort_order', 0) }}"></label>
                    <label class="flex items-center gap-2 text-sm font-semibold text-zinc-700"><input name="is_published" type="checkbox" value="1" @checked(old('is_published', true))> Display on public website</label>
                    <div class="md:col-span-2"><button class="museum-btn" type="submit">Add Artwork</button></div>
                </form>
            @endif
        </details>

        <div class="flex items-center justify-between gap-3"><h3 class="museum-section-title">Selected Artworks</h3><span class="text-sm text-zinc-500">{{ $items->where('is_published', true)->count() }} published</span></div>
        @forelse($items as $item)
            <article class="museum-panel">
                <form method="POST" action="{{ route('admin.public-collection.update', $item, false) }}" class="grid gap-4 lg:grid-cols-[160px_1fr]">
                    @csrf
                    @method('PUT')
                    <div>
                        @if($item->artwork?->primary_image_url)
                            <img src="{{ $item->artwork->primary_image_url }}" alt="{{ $item->artwork->title }}" class="h-40 w-full rounded-xl object-cover">
                        @else
                            <div class="flex h-40 items-center justify-center rounded-xl bg-zinc-100 text-sm text-zinc-500">No image</div>
                        @endif
                    </div>
                    <div class="grid gap-4 md:grid-cols-2">
                        <input type="hidden" name="artwork_id" value="{{ $item->artwork_id }}">
                        <div><span class="text-xs font-bold uppercase text-zinc-500">Artwork</span><p class="mt-1 font-semibold">{{ $item->artwork?->title ?: 'Untitled' }}</p><p class="text-sm text-zinc-500">{{ $item->artwork?->artist?->name ?: 'Unknown artist' }} · {{ $item->artwork?->display_inventory_code }}</p></div>
                        <label class="museum-field"><span>Display Order</span><input name="sort_order" type="number" min="0" max="999" value="{{ $item->sort_order }}"></label>
                        <label class="flex items-center gap-2 text-sm font-semibold text-zinc-700"><input name="is_published" type="checkbox" value="1" @checked($item->is_published)> Display on public website</label>
                        <div class="flex flex-wrap gap-2 md:col-span-2">
                            <button class="museum-btn" type="submit">Save Display Settings</button>
                            @if($item->artwork)
                                <a class="museum-btn-secondary" href="{{ route('artworks.edit', ['artwork' => $item->artwork, 'return' => route('admin.public-collection.index', [], false)], false) }}">Edit Artwork Details</a>
                            @endif
                        </div>
                    </div>
                </form>
                <form method="POST" action="{{ route('admin.public-collection.destroy', $item, false) }}" class="mt-3 flex justify-end" onsubmit="return confirm('Remove this artwork from the public Collection page?')">@csrf @method('DELETE')<button type="submit" class="museum-btn-danger">Remove Artwork</button></form>
            </article>
        @empty
            <div class="museum-panel p-6 text-center text-zinc-500">No artworks configured yet. The current public collection layout remains visible until the first artwork is added.</div>
        @endforelse
    </section>

    <script>
        document.addEventListener('DOMContentLoaded', () => {
            const picker = document.querySelector('[data-artwork-picker]');
            if (!picker) return;

            const search = picker.querySelector('[data-artwork-search]');
            const value = picker.querySelector('[data-artwork-value]');
            const panel = picker.querySelector('[data-artwork-options]');
            const options = [...picker.querySelectorAll('[data-artwork-option]')];
            const empty = picker.querySelector('[data-artwork-empty]');

            const open = () => { panel.classList.remove('hidden'); search.setAttribute('aria-expanded', 'true'); };
            const close = () => { panel.classList.add('hidden'); search.setAttribute('aria-expanded', 'false'); };
            const filter = () => {
                const query = search.value.trim().toLowerCase();
                let visible = 0;
                options.forEach(option => {
                    const matches = option.dataset.search.includes(query);
                    option.classList.toggle('hidden', !matches);
                    if (matches) visible++;
                });
                empty.classList.toggle('hidden', visible !== 0);
            };

            search.addEventListener('focus', open);
            search.addEventListener('input', () => { value.value = ''; filter(); open(); });
            options.forEach(option => option.addEventListener('click', () => {
                value.value = option.dataset.value;
                search.value = option.textContent.trim();
                close();
            }));
            document.addEventListener('click', event => { if (!picker.contains(event.target)) close(); });
        });
    </script>
</x-layout>
