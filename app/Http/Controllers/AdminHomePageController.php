<?php

namespace App\Http\Controllers;

use App\Models\Setting;
use App\Models\MuseumEvent;
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
            'public_home_story_work_id',
        ])->pluck('value', 'key');

        return view('admin.home.index', [
            'content' => $content,
            'events' => MuseumEvent::query()->where('is_published', true)->orderBy('title')->get(),
            'works' => PublicCollectionItem::query()->where('is_published', true)->with(['artwork.artist'])->get()->sortBy(fn ($item) => strtolower($item->artwork?->title ?? '')),
            'selectedEventIds' => $this->decodedIds($selectionSettings['public_home_featured_event_ids'] ?? null),
            'selectedArtistIds' => $this->decodedIds($selectionSettings['public_home_featured_artist_ids'] ?? null),
            'selectedWorkIds' => $this->decodedIds($selectionSettings['public_home_selected_work_ids'] ?? null),
            'selectedStoryWorkId' => $this->decodedIds($selectionSettings['public_home_story_work_id'] ?? null)[0] ?? null,
            'storyImageUrl' => $this->imageUrl($content['public_home_story_image_path']),
            'programmeCustomImageUrls' => $this->customImageUrls($content, 'programme'),
            'collectionCustomImageUrls' => $this->customImageUrls($content, 'collection'),
            'experienceBackgroundUrl' => $this->imageUrl($content['public_home_experience_background_path']),
        ]);
    }

    public function update(Request $request): RedirectResponse
    {
        $rules = [];
        foreach (HomePageContent::DEFAULTS as $key => $default) {
            if (str_ends_with($key, '_path')) continue;
            if (in_array($key, ['public_home_artists_title', 'public_home_artists_description', 'public_home_vision_button'], true)) continue;
            $rules[$key] = str_starts_with($key, 'public_home_hero_') || $default === ''
                ? ['nullable', 'string', 'max:500']
                : ['required', 'string', str_contains($key, 'description') || str_contains($key, 'paragraph') || str_ends_with($key, '_note') ? 'max:2500' : 'max:255'];
        }
        $rules['hero_image'] = ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:15360'];
        $rules['featured_event_ids'] = ['nullable', 'array', 'max:3'];
        $rules['featured_event_ids.*'] = ['nullable', 'integer', 'distinct', 'exists:museum_events,id'];
        $rules['selected_work_ids'] = ['nullable', 'array', 'max:3'];
        $rules['selected_work_ids.*'] = ['nullable', 'integer', 'distinct', 'exists:public_collection_items,id'];
        $rules['story_work_id'] = ['nullable', 'integer', 'exists:public_collection_items,id'];
        $rules['public_home_story_source'] = ['required', 'in:collection,custom'];
        $rules['story_image'] = ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:15360'];
        $rules['experience_background'] = ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:15360'];
        foreach (range(1, 3) as $slot) {
            $rules["public_home_programme_{$slot}_source"] = ['required', 'in:existing,custom'];
            $rules["programme_{$slot}_image"] = ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:15360'];
            $rules["public_home_collection_{$slot}_source"] = ['required', 'in:existing,custom'];
            $rules["collection_{$slot}_image"] = ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:15360'];
            $rules["public_home_programme_{$slot}_link"] = ['nullable', 'url:http,https', 'max:500'];
            $rules["public_home_collection_{$slot}_link"] = ['nullable', 'url:http,https', 'max:500'];
        }
        $validated = $request->validate($rules);

        foreach (array_keys(HomePageContent::DEFAULTS) as $key) {
            if (! str_ends_with($key, '_path') && array_key_exists($key, $validated)) {
                Setting::updateOrCreate(['key' => $key], ['value' => trim((string) ($validated[$key] ?? ''))]);
            }
        }
        $this->saveUpload($request, 'hero_image', 'public_home_hero_poster_path');
        $this->saveUpload($request, 'story_image', 'public_home_story_image_path');
        $this->saveUpload($request, 'experience_background', 'public_home_experience_background_path');
        foreach (range(1, 3) as $slot) {
            $this->saveUpload($request, "programme_{$slot}_image", "public_home_programme_{$slot}_image_path");
            $this->saveUpload($request, "collection_{$slot}_image", "public_home_collection_{$slot}_image_path");
        }
        $this->saveIds('public_home_featured_event_ids', $validated['featured_event_ids'] ?? []);
        $this->saveIds('public_home_selected_work_ids', $validated['selected_work_ids'] ?? []);
        $this->saveIds('public_home_story_work_id', filled($validated['story_work_id'] ?? null) ? [(int) $validated['story_work_id']] : []);

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

    private function imageUrl(?string $path): ?string
    {
        return $path && Storage::disk('public')->exists($path) ? Storage::url($path) : null;
    }

    private function customImageUrls(array $content, string $type): array
    {
        return collect(range(1, 3))
            ->mapWithKeys(fn (int $slot) => [
                $slot => $this->imageUrl($content["public_home_{$type}_{$slot}_image_path"] ?? null),
            ])
            ->all();
    }
}
