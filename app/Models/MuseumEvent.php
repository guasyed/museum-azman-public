<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;

class MuseumEvent extends Model
{
    public const SECTIONS = [
        'currently_active' => 'Currently Active',
        'upcoming' => 'Upcoming',
        'archive' => 'Archive',
    ];

    public const CONTENT_DEFAULTS = [
        'public_events_page_title' => 'Events',
        'public_events_page_description' => 'Special exhibitions, artist talks, interviews, and exclusive events exploring contemporary art and cultural dialogue.',
        'public_events_programming_title' => 'Event Programming',
        'public_events_program_1_title' => 'Exhibitions',
        'public_events_program_1_description' => 'Curated presentations of contemporary art featuring solo and group shows.',
        'public_events_program_2_title' => 'Artist Talks',
        'public_events_program_2_description' => 'Intimate conversations with artists about their practice and vision.',
        'public_events_program_3_title' => 'Interviews',
        'public_events_program_3_description' => 'In-depth dialogues exploring artistic processes and cultural contexts.',
        'public_events_program_4_title' => 'Special Events',
        'public_events_program_4_description' => 'Exclusive gatherings, collector evenings, and symposiums.',
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
