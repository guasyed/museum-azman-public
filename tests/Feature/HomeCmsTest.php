<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\Artist;
use App\Models\Artwork;
use App\Models\MuseumEvent;
use App\Models\PublicArtistProfile;
use App\Models\PublicCollectionItem;
use App\Support\HomePageContent;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class HomeCmsTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_can_update_home_page_content(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $content = HomePageContent::DEFAULTS;
        unset($content['public_home_hero_video_path'], $content['public_home_hero_poster_path']);
        $content['public_home_hero_title'] = 'Thoughtfully Curated';
        $content['public_home_events_title'] = 'Current Programmes';
        $content['public_home_vision_title'] = 'A Shared Vision';

        $this->actingAs($admin)->put(route('admin.home.update'), $content)->assertRedirect();

        $this->assertDatabaseHas('settings', ['key' => 'public_home_hero_title', 'value' => 'Thoughtfully Curated']);
        $this->get(route('home'))
            ->assertOk()
            ->assertSee('Thoughtfully Curated')
            ->assertSee('Current Programmes')
            ->assertSee('A Shared Vision')
            ->assertSee('href="'.route('public.visit', [], false).'">Register Now', false);
        $this->actingAs($admin)->get(route('admin.home.index'))->assertOk()->assertSee('Home CMS');
    }

    public function test_admin_can_choose_items_for_fixed_home_page_boxes(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $event = MuseumEvent::create(['title' => 'Chosen Home Event', 'section' => 'upcoming', 'is_published' => true]);
        $artist = Artist::create(['name' => 'Chosen Home Artist', 'country' => 'Malaysia']);
        $profile = PublicArtistProfile::create(['artist_id' => $artist->id, 'is_published' => true]);
        $artwork = Artwork::create(['artist_id' => $artist->id, 'title' => 'Chosen Home Work', 'slug' => 'chosen-home-work']);
        $work = PublicCollectionItem::create(['artwork_id' => $artwork->id, 'is_published' => true]);
        $content = HomePageContent::DEFAULTS;
        unset($content['public_home_hero_video_path'], $content['public_home_hero_poster_path']);
        $content['featured_event_ids'] = [$event->id, '', ''];
        $content['featured_artist_ids'] = [$profile->id, '', '', ''];
        $content['selected_work_ids'] = [$work->id, '', ''];

        $this->actingAs($admin)->put(route('admin.home.update'), $content)->assertRedirect();

        $this->assertDatabaseHas('settings', ['key' => 'public_home_featured_event_ids', 'value' => json_encode([$event->id, 0, 0])]);
        $this->get(route('home'))->assertOk()->assertSee('Chosen Home Event')->assertSee('Chosen Home Artist')->assertSee('Chosen Home Work')->assertSee('Private &amp; Special Visits', false);
    }

    public function test_admin_can_use_a_custom_image_for_the_home_story(): void
    {
        Storage::fake('public');
        $admin = User::factory()->create(['role' => 'admin']);
        $content = HomePageContent::DEFAULTS;
        unset(
            $content['public_home_hero_video_path'],
            $content['public_home_hero_poster_path'],
            $content['public_home_story_image_path'],
        );
        $content['public_home_story_source'] = 'custom';
        $content['story_image'] = UploadedFile::fake()->image('story.jpg', 1200, 800);

        $this->actingAs($admin)->put(route('admin.home.update'), $content)->assertRedirect();

        $path = \App\Models\Setting::query()->where('key', 'public_home_story_image_path')->value('value');
        Storage::disk('public')->assertExists($path);
        $this->assertDatabaseHas('settings', ['key' => 'public_home_story_source', 'value' => 'custom']);
        $this->get(route('home'))->assertOk()->assertSee(Storage::url($path), false);
    }
}
