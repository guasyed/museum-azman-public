<?php

namespace App\Http\Controllers;

use App\Models\Setting;
use App\Models\MuseumEvent;
use App\Models\PublicArtistProfile;
use App\Models\PublicCollectionItem;
use App\Support\HomePageContent;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;

class AdminHomePageController extends Controller
{
    public function index(): View
    {
        $content = array_replace(HomePageContent::DEFAULTS, Setting::query()->whereIn('key', array_keys(HomePageContent::DEFAULTS))->pluck('value', 'key')->all());

        $selectionSettings = Setting::query()->whereIn('key', [
            'public_home_featured_event_ids',
            'public_home_featured_artist_ids',
            'public_home_selected_work_ids',
        ])->pluck('value', 'key');

        return view('admin.home.index', [
            'content' => $content,
            'events' => MuseumEvent::query()->where('is_published', true)->orderBy('title')->get(),
            'artists' => PublicArtistProfile::query()->where('is_published', true)->with('artist')->get()->sortBy(fn ($profile) => strtolower($profile->artist?->name ?? '')),
            'works' => PublicCollectionItem::query()->where('is_published', true)->with(['artwork.artist'])->get()->sortBy(fn ($item) => strtolower($item->artwork?->title ?? '')),
            'selectedEventIds' => $this->decodedIds($selectionSettings['public_home_featured_event_ids'] ?? null),
            'selectedArtistIds' => $this->decodedIds($selectionSettings['public_home_featured_artist_ids'] ?? null),
            'selectedWorkIds' => $this->decodedIds($selectionSettings['public_home_selected_work_ids'] ?? null),
        ]);
    }

    public function update(Request $request): RedirectResponse
    {
        $rules = [];
        foreach (HomePageContent::DEFAULTS as $key => $default) {
            if (str_ends_with($key, '_path')) continue;
            $rules[$key] = str_starts_with($key, 'public_home_hero_')
                ? ['nullable', 'string', 'max:500']
                : ['required', 'string', str_contains($key, 'description') || str_contains($key, 'paragraph') || str_ends_with($key, '_note') ? 'max:2500' : 'max:255'];
        }
        $rules['hero_video'] = ['nullable', 'file', 'mimes:mp4,webm', 'max:102400'];
        $rules['hero_poster'] = ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:10240'];
        $rules['featured_event_ids'] = ['nullable', 'array', 'max:3'];
        $rules['featured_event_ids.*'] = ['nullable', 'integer', 'distinct', 'exists:museum_events,id'];
        $rules['featured_artist_ids'] = ['nullable', 'array', 'max:4'];
        $rules['featured_artist_ids.*'] = ['nullable', 'integer', 'distinct', 'exists:public_artist_profiles,id'];
        $rules['selected_work_ids'] = ['nullable', 'array', 'max:3'];
        $rules['selected_work_ids.*'] = ['nullable', 'integer', 'distinct', 'exists:public_collection_items,id'];
        $validated = $request->validate($rules);

        foreach (array_keys(HomePageContent::DEFAULTS) as $key) {
            if (! str_ends_with($key, '_path')) Setting::updateOrCreate(['key' => $key], ['value' => trim((string) ($validated[$key] ?? ''))]);
        }
        $this->saveUpload($request, 'hero_video', 'public_home_hero_video_path');
        $this->saveUpload($request, 'hero_poster', 'public_home_hero_poster_path');
        $this->saveIds('public_home_featured_event_ids', $validated['featured_event_ids'] ?? []);
        $this->saveIds('public_home_featured_artist_ids', $validated['featured_artist_ids'] ?? []);
        $this->saveIds('public_home_selected_work_ids', $validated['selected_work_ids'] ?? []);

        return back()->with('success', 'Home page updated successfully.');
    }

    private function decodedIds(?string $value): array
    {
        $decoded = json_decode((string) $value, true);
        return is_array($decoded) ? array_values(array_map('intval', $decoded)) : [];
    }

    private function saveIds(string $key, array $ids): void
    {
        $ids = array_values(array_map(fn ($id) => filled($id) ? (int) $id : 0, $ids));
        Setting::updateOrCreate(['key' => $key], ['value' => json_encode($ids)]);
    }

    private function saveUpload(Request $request, string $field, string $key): void
    {
        if (! $request->hasFile($field)) return;
        $old = Setting::query()->where('key', $key)->value('value');
        if ($old) Storage::disk('public')->delete($old);
        Setting::updateOrCreate(['key' => $key], ['value' => $request->file($field)->store('home', 'public')]);
    }
}
