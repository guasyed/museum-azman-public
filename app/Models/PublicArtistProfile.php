<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Storage;

class PublicArtistProfile extends Model
{
    public const CONTENT_DEFAULTS = [
        'public_artists_page_title' => 'Artists',
        'public_artists_page_description' => 'Representing voices from the Americas to Southeast Asia, our artists explore contemporary themes through diverse mediums and perspectives.',
        'public_artists_collaboration_title' => 'Artist Collaborations',
        'public_artists_collaboration_description' => 'Museum Azman works directly with artists to create meaningful exhibitions that honor their vision while facilitating dialogue with audiences. We are committed to supporting artistic practice through acquisitions, commissions, and cultural exchange.',
    ];

    protected $fillable = ['artist_id', 'image_path', 'biography', 'sort_order', 'is_published'];

    protected $appends = ['image_url'];

    protected function casts(): array
    {
        return ['is_published' => 'boolean'];
    }

    public function artist(): BelongsTo
    {
        return $this->belongsTo(Artist::class);
    }

    public function getImageUrlAttribute(): ?string
    {
        return $this->image_path && Storage::disk('public')->exists($this->image_path)
            ? Storage::url($this->image_path)
            : null;
    }
}
