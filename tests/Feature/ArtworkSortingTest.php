<?php

namespace Tests\Feature;

use App\Models\Artist;
use App\Models\Artwork;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ArtworkSortingTest extends TestCase
{
    use RefreshDatabase;

    public function test_table_columns_can_sort_artworks(): void
    {
        $user = User::factory()->create(['role' => 'admin']);
        $zArtist = Artist::create(['name' => 'Zulu Artist', 'country' => 'Zimbabwe']);
        $aArtist = Artist::create(['name' => 'Alpha Artist', 'country' => 'Australia']);

        Artwork::create(['artist_id' => $zArtist->id, 'title' => 'First Work', 'slug' => 'first-work', 'medium' => 'Watercolour', 'status' => 'On Display']);
        Artwork::create(['artist_id' => $aArtist->id, 'title' => 'Second Work', 'slug' => 'second-work', 'medium' => 'Acrylic', 'status' => 'In Storage']);

        $this->actingAs($user)
            ->get(route('artworks.index', ['view' => 'table', 'sort' => 'artist', 'direction' => 'asc']))
            ->assertOk()
            ->assertSeeInOrder(['Alpha Artist', 'Zulu Artist'])
            ->assertSee('sort=region')
            ->assertSee('sort=medium')
            ->assertSee('sort=status');
    }
}
