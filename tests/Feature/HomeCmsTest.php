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

    public function test_admin_can_create_custom_programme_and_collection_cards(): void
    {
        Storage::fake('public');
        $admin = User::factory()->create(['role' => 'admin']);
        $content = collect(HomePageContent::DEFAULTS)
            ->reject(fn ($value, $key) => str_ends_with($key, '_path'))
            ->all();
        $content['public_home_programme_1_source'] = 'custom';
        $content['public_home_programme_1_label'] = 'Members only';
        $content['public_home_programme_1_title'] = 'Custom Programme';
        $content['public_home_programme_1_description'] = 'A custom programme description.';
        $content['public_home_programme_1_link'] = 'https://example.com/programme';
        $content['programme_1_image'] = UploadedFile::fake()->image('programme.jpg', 900, 1200);
        $content['public_home_collection_1_source'] = 'custom';
        $content['public_home_collection_1_title'] = 'Custom Artwork';
        $content['public_home_collection_1_artist'] = 'Custom Artist';
        $content['public_home_collection_1_year'] = '2026';
        $content['public_home_collection_1_medium'] = 'Oil on canvas';
        $content['public_home_collection_1_link'] = 'https://example.com/artwork';
        $content['collection_1_image'] = UploadedFile::fake()->image('artwork.jpg', 900, 1200);
        $content['experience_background'] = UploadedFile::fake()->image('experience.jpg', 1600, 900);

        $this->actingAs($admin)->put(route('admin.home.update'), $content)->assertRedirect();

        $programmePath = \App\Models\Setting::query()->where('key', 'public_home_programme_1_image_path')->value('value');
        $collectionPath = \App\Models\Setting::query()->where('key', 'public_home_collection_1_image_path')->value('value');
        $experiencePath = \App\Models\Setting::query()->where('key', 'public_home_experience_background_path')->value('value');
        Storage::disk('public')->assertExists($programmePath);
        Storage::disk('public')->assertExists($collectionPath);
        Storage::disk('public')->assertExists($experiencePath);
        $this->get(route('home'))
            ->assertOk()
            ->assertSee('Custom Programme')
            ->assertSee('A custom programme description.')
            ->assertSee('Custom Artwork')
            ->assertSee('Custom Artist')
            ->assertSee('Oil on canvas')
            ->assertSee('https://example.com/programme', false)
            ->assertSee('https://example.com/artwork', false)
            ->assertSee(Storage::url($programmePath), false)
            ->assertSee(Storage::url($collectionPath), false)
            ->assertSee(Storage::url($experiencePath), false);
    }
}
