<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;

class MuseumEvent extends Model
{
    public const SECTIONS = [
        'currently_active' => 'Featured Programmes',
        'upcoming' => 'Upcoming Programmes',
        'archive' => 'Past Programmes',
    ];

    public const CONTENT_DEFAULTS = [
        'public_events_page_title' => "Programmes\n& stories",
        'public_events_page_description' => 'A thoughtful rhythm of visits, close looking and cultural exchange—rooted in the collection and designed to make room for reflection.',
        'public_events_programming_title' => 'The conversation continues.',
        'public_events_program_1_title' => 'Collector Conversations',
        'public_events_program_1_description' => 'A series of intimate exchanges on the instincts, encounters and responsibilities that shape a collection.',
        'public_events_program_2_title' => 'Museum Azman Conversations',
        'public_events_program_2_description' => 'New voices from across the collection: artists, scholars and thinkers in sustained dialogue.',
    ];

    protected $fillable = [
        'title',
        'section',
        'event_type',
        'schedule',
        'description',
        'image_path',
        'sort_order',
        'is_published',
    ];

    protected $appends = ['image_url'];

    protected function casts(): array
    {
        return ['is_published' => 'boolean'];
    }

    public function getImageUrlAttribute(): ?string
    {
        return $this->image_path && Storage::disk('public')->exists($this->image_path)
            ? Storage::url($this->image_path)
            : null;
    }
}
