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
        'public_events_hero_image_path' => '',
        'public_events_hero_kicker' => "Museum Azman / What's on",
        'public_events_hero_label' => "Private museum\nPermanent collection",
        'public_events_list_eyebrow' => 'The museum, in use',
        'public_events_list_title' => 'Ways of being here.',
        'public_events_list_description' => 'The museum does not operate a conventional exhibition calendar. Its programmes offer a deeper way into the works that remain.',
        'public_events_list_button' => 'Request a visit',
        'public_events_story_image_path' => '',
        'public_events_story_eyebrow' => 'One artwork, one story',
        'public_events_story_title' => 'One Artwork, One Story: Marina Perez Simão.',
        'public_events_story_description' => 'Look closely at this painting and a familiar terrain emerges: rolling hills, a winding body of water and a glowing sun. Look closer, and the illusion fractures—nothing here is actually real.',
        'public_events_story_button' => 'Read the first story',
        'public_events_story_caption' => 'Landscapes of the Mind.',
        'public_events_programming_title' => 'The conversation continues.',
        'public_events_programming_description' => 'Two new editorial initiatives are taking shape. Their quiet arrival is part of the programme.',
        'public_events_program_1_title' => 'Collector Conversations',
        'public_events_program_1_description' => 'A series of intimate exchanges on the instincts, encounters and responsibilities that shape a collection.',
        'public_events_program_1_label' => 'In development',
        'public_events_program_2_title' => 'Museum Azman Conversations',
        'public_events_program_2_description' => 'New voices from across the collection: artists, scholars and thinkers in sustained dialogue.',
        'public_events_program_2_label' => 'Audio journal',
        'public_events_research_eyebrow' => 'For collectors & researchers',
        'public_events_research_title' => "A collection\nto return to.",
        'public_events_research_description' => 'Whether you are beginning a research enquiry or planning a thoughtful visit, our team can help shape an encounter with the collection.',
        'public_events_research_button' => 'Start a conversation',
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
