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
