<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PublicCollectionItem extends Model
{
    public const CONTENT_DEFAULTS = [
        'public_collection_page_title' => 'Collection',
        'public_collection_page_description' => 'A selective index of artists represented in the permanent collection. Together, these practices trace the cultural exchanges, material inquiries and distinct perspectives that shape Museum Azman.',
        'public_collection_artists_eyebrow' => 'Selected artists',
        'public_collection_artists_title' => 'In the collection.',
        'public_collection_artists_note' => 'This is a selected listing and does not represent the complete collection.',
        'public_collection_story_eyebrow' => 'One artwork, one story',
        'public_collection_story_title' => "The collection,\nread slowly.",
        'public_collection_story_description' => "Beyond an artwork's title, date and medium lies a wider field of associations. Our collection stories trace the encounters, material decisions and cultural memories that keep a work alive.",
        'public_collection_story_button' => 'Explore the first reading',
        'public_collection_philosophy_title' => 'Collecting Philosophy',
        'public_collection_philosophy_paragraph_1' => "Museum Azman's collection is built on a commitment to artistic excellence and cultural dialogue. We acquire works that challenge conventions, expand perspectives, and demonstrate enduring relevance.",
        'public_collection_philosophy_paragraph_2' => 'Our focus on artists from the Americas to Southeast Asia reflects our belief that these regions offer vital perspectives often underrepresented in global art discourse. The collection grows through careful consideration, prioritizing depth over breadth.',
        'public_collection_philosophy_paragraph_3' => 'Each work is selected not only for its individual merit but for its contribution to the larger narrative we are building, one that honors diverse artistic traditions while embracing contemporary innovation.',
    ];

    protected $fillable = ['artwork_id', 'sort_order', 'is_published'];

    protected function casts(): array
    {
        return ['is_published' => 'boolean'];
    }

    public function artwork(): BelongsTo
    {
        return $this->belongsTo(Artwork::class);
    }
}
