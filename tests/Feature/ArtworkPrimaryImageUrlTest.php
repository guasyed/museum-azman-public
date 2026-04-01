<?php

namespace Tests\Feature;

use App\Models\Artwork;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class ArtworkPrimaryImageUrlTest extends TestCase
{
    public function test_it_returns_the_local_primary_image_url_when_the_file_exists(): void
    {
        Storage::fake('public');
        Storage::disk('public')->put('artworks/primary.jpg', 'image');

        $artwork = new Artwork([
            'primary_image_path' => 'artworks/primary.jpg',
            'source_image_url' => 'https://example.com/fallback.jpg',
        ]);

        $artwork->setRelation('images', collect());

        $this->assertSame(asset('storage/artworks/primary.jpg'), $artwork->primary_image_url);
    }

    public function test_it_falls_back_to_the_source_image_url_when_no_local_file_exists(): void
    {
        Storage::fake('public');

        $artwork = new Artwork([
            'source_image_url' => 'https://example.com/imported-artwork.jpg',
        ]);

        $artwork->setRelation('images', collect());

        $this->assertSame('https://example.com/imported-artwork.jpg', $artwork->primary_image_url);
    }

    public function test_it_returns_null_for_an_invalid_source_image_url_without_local_files(): void
    {
        Storage::fake('public');

        $artwork = new Artwork([
            'source_image_url' => 'not-a-url',
        ]);

        $artwork->setRelation('images', collect());

        $this->assertNull($artwork->primary_image_url);
    }
}