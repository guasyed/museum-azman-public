<?php

namespace Tests\Feature;

use App\Models\Artist;
use App\Models\PublicArtistProfile;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ArtistsCmsTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_can_publish_an_artist_on_the_public_page(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $artist = Artist::create([
            'name' => 'CMS Test Artist',
            'country' => 'Malaysia',
        ]);

        $this->actingAs($admin)->post(route('admin.public-artists.store'), [
            'artist_id' => $artist->id,
            'biography' => 'A public artist introduction.',
            'sort_order' => 2,
            'is_published' => '1',
        ])->assertRedirect();

        $this->assertDatabaseHas('public_artist_profiles', [
            'artist_id' => $artist->id,
            'sort_order' => 2,
            'is_published' => true,
        ]);

        $this->get(route('public.artists'))
            ->assertOk()
            ->assertSee('CMS Test Artist')
            ->assertSee('Malaysia')
            ->assertSee('A public artist introduction.');

        $this->actingAs($admin)->get(route('admin.public-artists.index'))
            ->assertOk()
            ->assertSee('Artists CMS')
            ->assertSee('CMS Test Artist');
    }

    public function test_unpublished_cms_artist_is_hidden_from_public_page(): void
    {
        $artist = Artist::create(['name' => 'Hidden CMS Artist', 'country' => 'Malaysia']);

        PublicArtistProfile::create([
            'artist_id' => $artist->id,
            'is_published' => false,
        ]);

        $this->get(route('public.artists'))
            ->assertOk()
            ->assertDontSee('Hidden CMS Artist')
            ->assertSee('Artist profiles coming soon.');
    }

    public function test_admin_can_update_the_artists_page_content(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $content = PublicArtistProfile::CONTENT_DEFAULTS;
        $content['public_artists_page_title'] = 'Featured Creators';
        $content['public_artists_page_description'] = 'A CMS-managed introduction for our artists.';
        $content['public_artists_collaboration_title'] = 'Working With Artists';
        $content['public_artists_collaboration_description'] = 'A CMS-managed collaboration description.';

        $this->actingAs($admin)
            ->put(route('admin.public-artists.content.update'), $content)
            ->assertRedirect();

        $this->assertDatabaseHas('settings', [
            'key' => 'public_artists_page_title',
            'value' => 'Featured Creators',
        ]);

        $this->get(route('public.artists'))
            ->assertOk()
            ->assertSee('Featured Creators')
            ->assertSee('A CMS-managed introduction for our artists.')
            ->assertSee('Working With Artists')
            ->assertSee('A CMS-managed collaboration description.');
    }
}
