<?php

namespace Tests\Feature;

use App\Models\Artist;
use App\Models\Artwork;
use App\Models\PublicCollectionItem;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CollectionCmsTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_can_choose_an_artwork_for_the_public_collection(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $artist = Artist::create(['name' => 'Collection CMS Artist', 'country' => 'Malaysia']);
        $artwork = Artwork::create([
            'artist_id' => $artist->id,
            'title' => 'Selected CMS Artwork',
            'slug' => 'selected-cms-artwork',
            'year' => 2026,
            'medium' => 'Oil on canvas',
        ]);

        $this->actingAs($admin)->post(route('admin.public-collection.store'), [
            'artwork_id' => $artwork->id,
            'sort_order' => 1,
            'is_published' => '1',
        ])->assertRedirect();

        $this->assertDatabaseHas('public_collection_items', [
            'artwork_id' => $artwork->id,
            'sort_order' => 1,
            'is_published' => true,
        ]);

        $this->get(route('public.collection'))
            ->assertOk()
            ->assertSee('Selected CMS Artwork')
            ->assertSee('Collection CMS Artist')
            ->assertSee('Oil on canvas');

        $this->actingAs($admin)->get(route('admin.public-collection.index'))
            ->assertOk()
            ->assertSee('Collection CMS')
            ->assertSee('Edit Artwork Details')
            ->assertSee('Selected CMS Artwork');
    }

    public function test_unpublished_selected_artwork_is_hidden(): void
    {
        $artwork = Artwork::create(['title' => 'Hidden Selected Artwork', 'slug' => 'hidden-selected-artwork']);
        PublicCollectionItem::create(['artwork_id' => $artwork->id, 'is_published' => false]);

        $this->get(route('public.collection'))
            ->assertOk()
            ->assertDontSee('Hidden Selected Artwork')
            ->assertSee('Collection highlights coming soon.');
    }

    public function test_admin_can_update_collection_intro_and_text_panel(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $content = PublicCollectionItem::CONTENT_DEFAULTS;
        $content['public_collection_page_title'] = 'Curated Collection';
        $content['public_collection_page_description'] = 'A CMS-managed collection introduction.';
        $content['public_collection_philosophy_title'] = 'Our Collection Approach';
        $content['public_collection_philosophy_paragraph_1'] = 'First managed paragraph.';
        $content['public_collection_philosophy_paragraph_2'] = 'Second managed paragraph.';
        $content['public_collection_philosophy_paragraph_3'] = 'Third managed paragraph.';

        $this->actingAs($admin)
            ->put(route('admin.public-collection.content.update'), $content)
            ->assertRedirect();

        $this->assertDatabaseHas('settings', [
            'key' => 'public_collection_page_title',
            'value' => 'Curated Collection',
        ]);

        $this->get(route('public.collection'))
            ->assertOk()
            ->assertSee('Curated Collection')
            ->assertSee('A CMS-managed collection introduction.')
            ->assertSee('Our Collection Approach')
            ->assertSee('First managed paragraph.')
            ->assertSee('Second managed paragraph.')
            ->assertSee('Third managed paragraph.');
    }
}
