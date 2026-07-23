<?php

namespace App\Http\Controllers;

use App\Models\Setting;
use App\Support\AboutPageContent;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;

class AdminAboutPageController extends Controller
{
    public function index(): View
    {
        $content = array_replace(
            AboutPageContent::DEFAULTS,
            Setting::query()->whereIn('key', array_keys(AboutPageContent::DEFAULTS))->pluck('value', 'key')->all(),
        );

        return view('admin.about.index', [
            'content' => $content,
            'heroImageUrl' => $this->imageUrl($content['public_about_hero_image_path']),
            'spaceImageUrl' => $this->imageUrl($content['public_about_space_image_path']),
        ]);
    }

    public function update(Request $request): RedirectResponse
    {
        $rules = [];
        foreach (AboutPageContent::DEFAULTS as $key => $default) {
            if (str_ends_with($key, '_image_path')) {
                continue;
            }

            $rules[$key] = ['required', 'string', str_contains($key, 'paragraph') || str_ends_with($key, '_description') ? 'max:2500' : 'max:255'];
        }
        $rules['hero_image'] = ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:10240'];
        $rules['space_image'] = ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:10240'];

        $validated = $request->validate($rules);

        foreach (array_keys(AboutPageContent::DEFAULTS) as $key) {
            if (! str_ends_with($key, '_image_path')) {
                Setting::updateOrCreate(['key' => $key], ['value' => trim($validated[$key])]);
            }
        }

        $this->saveImage($request, 'hero_image', 'public_about_hero_image_path');
        $this->saveImage($request, 'space_image', 'public_about_space_image_path');

        return back()->with('success', 'About page updated successfully.');
    }

    private function saveImage(Request $request, string $field, string $settingKey): void
    {
        if (! $request->hasFile($field)) {
            return;
        }

        $existing = Setting::query()->where('key', $settingKey)->value('value');
        if ($existing) {
            Storage::disk('public')->delete($existing);
        }

        $path = $request->file($field)->store('about', 'public');
        Setting::updateOrCreate(['key' => $settingKey], ['value' => $path]);
    }

    private function imageUrl(?string $path): ?string
    {
        return $path && Storage::disk('public')->exists($path) ? Storage::url($path) : null;
    }
}
