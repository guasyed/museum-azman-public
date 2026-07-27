<?php

namespace App\Http\Controllers;

use App\Models\MuseumEvent;
use App\Models\Setting;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class AdminMuseumEventController extends Controller
{
    public function index(): View
    {
        $events = MuseumEvent::query()
            ->orderByRaw("CASE section WHEN 'currently_active' THEN 1 WHEN 'upcoming' THEN 2 WHEN 'archive' THEN 3 ELSE 4 END")
            ->orderBy('sort_order')
            ->orderBy('id')
            ->get()
            ->groupBy('section');

        return view('admin.events.index', [
            'events' => $events,
            'sections' => MuseumEvent::SECTIONS,
            'content' => array_replace(
                MuseumEvent::CONTENT_DEFAULTS,
                Setting::query()->whereIn('key', array_keys(MuseumEvent::CONTENT_DEFAULTS))->pluck('value', 'key')->all(),
            ),
        ]);
    }

    public function updateContent(Request $request): RedirectResponse
    {
        $rules = [];
        foreach (MuseumEvent::CONTENT_DEFAULTS as $key => $default) {
            $rules[$key] = ['required', 'string', str_ends_with($key, '_description') ? 'max:1000' : 'max:255'];
        }

        $validated = $request->validate($rules);

        foreach ($validated as $key => $value) {
            Setting::updateOrCreate(['key' => $key], ['value' => trim($value)]);
        }

        return back()->with('success', 'Programmes page content updated successfully.');
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $this->validated($request);
        $validated['is_published'] = $request->boolean('is_published');

        if ($request->hasFile('image')) {
            $validated['image_path'] = $request->file('image')->store('events', 'public');
        }

        MuseumEvent::create($validated);

        return back()->with('success', 'Programme created successfully.');
    }

    public function update(Request $request, MuseumEvent $event): RedirectResponse
    {
        $validated = $this->validated($request);
        $validated['is_published'] = $request->boolean('is_published');

        if ($request->hasFile('image')) {
            if ($event->image_path) {
                Storage::disk('public')->delete($event->image_path);
            }
            $validated['image_path'] = $request->file('image')->store('events', 'public');
        }

        $event->update($validated);

        return back()->with('success', 'Programme updated successfully.');
    }

    public function destroy(MuseumEvent $event): RedirectResponse
    {
        if ($event->image_path) {
            Storage::disk('public')->delete($event->image_path);
        }

        $event->delete();

        return back()->with('success', 'Programme deleted successfully.');
    }

    private function validated(Request $request): array
    {
        return $request->validate([
            'title' => ['required', 'string', 'max:255'],
            'section' => ['required', Rule::in(array_keys(MuseumEvent::SECTIONS))],
            'event_type' => ['nullable', 'string', 'max:255'],
            'schedule' => ['nullable', 'string', 'max:255'],
            'description' => ['nullable', 'string', 'max:5000'],
            'sort_order' => ['nullable', 'integer', 'min:0', 'max:999'],
            'image' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:10240'],
        ]);
    }
}
