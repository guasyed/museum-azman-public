<?php

namespace App\Http\Controllers;

use App\Models\Artist;
use App\Models\PublicArtistProfile;
use App\Models\Setting;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class AdminPublicArtistController extends Controller
{
    public function index(): View
    {
        $profiles = PublicArtistProfile::query()
            ->with('artist:id,name,country,biography')
            ->orderBy('sort_order')
            ->orderBy('id')
            ->get();

        $availableArtists = Artist::query()
            ->whereNotIn('id', $profiles->pluck('artist_id'))
            ->orderByRaw('LOWER(name)')
            ->get(['id', 'name', 'country']);

        $content = array_replace(
            PublicArtistProfile::CONTENT_DEFAULTS,
            Setting::query()->whereIn('key', array_keys(PublicArtistProfile::CONTENT_DEFAULTS))->pluck('value', 'key')->all(),
        );

        return view('admin.public-artists.index', compact('profiles', 'availableArtists', 'content'));
    }

    public function updateContent(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'public_artists_page_title' => ['required', 'string', 'max:255'],
            'public_artists_page_description' => ['required', 'string', 'max:1000'],
            'public_artists_collaboration_title' => ['required', 'string', 'max:255'],
            'public_artists_collaboration_description' => ['required', 'string', 'max:2000'],
        ]);

        foreach ($validated as $key => $value) {
            Setting::updateOrCreate(['key' => $key], ['value' => trim($value)]);
        }

        return back()->with('success', 'Artists page content updated successfully.');
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $this->validated($request);
        $validated['is_published'] = $request->boolean('is_published');
        $validated['sort_order'] = $validated['sort_order'] ?? 0;

        if ($request->hasFile('image')) {
            $validated['image_path'] = $request->file('image')->store('public-artists', 'public');
        }

        PublicArtistProfile::create($validated);

        return back()->with('success', 'Artist added to the public CMS.');
    }

    public function update(Request $request, PublicArtistProfile $profile): RedirectResponse
    {
        $validated = $this->validated($request, $profile);
        $validated['is_published'] = $request->boolean('is_published');
        $validated['sort_order'] = $validated['sort_order'] ?? 0;

        if ($request->hasFile('image')) {
            if ($profile->image_path) {
                Storage::disk('public')->delete($profile->image_path);
            }
            $validated['image_path'] = $request->file('image')->store('public-artists', 'public');
        }

        $profile->update($validated);

        return back()->with('success', 'Public artist profile updated.');
    }

    public function destroy(PublicArtistProfile $profile): RedirectResponse
    {
        if ($profile->image_path) {
            Storage::disk('public')->delete($profile->image_path);
        }

        $profile->delete();

        return back()->with('success', 'Artist removed from the public CMS.');
    }

    private function validated(Request $request, ?PublicArtistProfile $profile = null): array
    {
        return $request->validate([
            'artist_id' => [
                'required',
                'integer',
                'exists:artists,id',
                Rule::unique('public_artist_profiles', 'artist_id')->ignore($profile?->id),
            ],
            'biography' => ['nullable', 'string', 'max:5000'],
            'sort_order' => ['nullable', 'integer', 'min:0', 'max:999'],
            'image' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:10240'],
        ]);
    }
}
