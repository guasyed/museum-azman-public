<?php

namespace App\Http\Controllers;

use App\Models\Artwork;
use App\Models\PublicCollectionItem;
use App\Models\Setting;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class AdminPublicCollectionController extends Controller
{
    public function index(): View
    {
        $items = PublicCollectionItem::query()
            ->with(['artwork.artist', 'artwork.images'])
            ->orderBy('sort_order')
            ->orderBy('id')
            ->get();

        $availableArtworks = Artwork::query()
            ->with('artist:id,name')
            ->whereNotIn('id', $items->pluck('artwork_id'))
            ->orderBy('title')
            ->orderBy('id')
            ->get(['id', 'inventory_code', 'artist_id', 'title', 'year']);

        $content = array_replace(
            PublicCollectionItem::CONTENT_DEFAULTS,
            Setting::query()->whereIn('key', array_keys(PublicCollectionItem::CONTENT_DEFAULTS))->pluck('value', 'key')->all(),
        );

        return view('admin.public-collection.index', compact('items', 'availableArtworks', 'content'));
    }

    public function updateContent(Request $request): RedirectResponse
    {
        $rules = [];
        foreach (PublicCollectionItem::CONTENT_DEFAULTS as $key => $default) {
            $rules[$key] = [
                'required',
                'string',
                str_contains($key, 'description') || str_contains($key, 'paragraph') || str_ends_with($key, '_note')
                    ? 'max:2000'
                    : 'max:255',
            ];
        }
        $validated = $request->validate($rules);

        foreach ($validated as $key => $value) {
            Setting::updateOrCreate(['key' => $key], ['value' => trim($value)]);
        }

        return back()->with('success', 'Collection page content updated successfully.');
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $this->validated($request);
        $validated['sort_order'] = $validated['sort_order'] ?? 0;
        $validated['is_published'] = $request->boolean('is_published');

        PublicCollectionItem::create($validated);

        return back()->with('success', 'Artwork added to the public Collection page.');
    }

    public function update(Request $request, PublicCollectionItem $item): RedirectResponse
    {
        $validated = $this->validated($request, $item);
        $validated['sort_order'] = $validated['sort_order'] ?? 0;
        $validated['is_published'] = $request->boolean('is_published');
        $item->update($validated);

        return back()->with('success', 'Public collection item updated.');
    }

    public function destroy(PublicCollectionItem $item): RedirectResponse
    {
        $item->delete();

        return back()->with('success', 'Artwork removed from the public Collection page.');
    }

    private function validated(Request $request, ?PublicCollectionItem $item = null): array
    {
        return $request->validate([
            'artwork_id' => [
                'required',
                'integer',
                'exists:artworks,id',
                Rule::unique('public_collection_items', 'artwork_id')->ignore($item?->id),
            ],
            'sort_order' => ['nullable', 'integer', 'min:0', 'max:999'],
        ]);
    }
}
