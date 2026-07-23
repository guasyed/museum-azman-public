<?php

namespace Tests\Feature;

use App\Models\User;
use App\Support\AboutPageContent;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AboutCmsTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_can_update_about_page_content(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $content = AboutPageContent::DEFAULTS;
        unset($content['public_about_hero_image_path'], $content['public_about_space_image_path']);
        $content['public_about_hero_title'] = 'About Our Collection';
        $content['public_about_mission_title'] = 'A New Mission';
        $content['public_about_space_title'] = 'Our Gallery Space';

        $this->actingAs($admin)
            ->put(route('admin.about.update'), $content)
            ->assertRedirect();

        $this->assertDatabaseHas('settings', [
            'key' => 'public_about_hero_title',
            'value' => 'About Our Collection',
        ]);

        $this->get(route('public.about'))
            ->assertOk()
            ->assertSee('About Our Collection')
            ->assertSee('A New Mission')
            ->assertSee('Our Gallery Space');

        $this->actingAs($admin)->get(route('admin.about.index'))
            ->assertOk()
            ->assertSee('About CMS')
            ->assertSee('About Our Collection');
    }
}
