<?php

namespace Tests\Feature;

use App\Models\MuseumEvent;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class EventsCmsTest extends TestCase
{
    use RefreshDatabase;

    public function test_public_events_page_displays_the_programmes_editorial_layout(): void
    {
        $this->get(route('public.events'))
            ->assertOk()
            ->assertSee('Programmes')
            ->assertSee('Ways of being here.')
            ->assertSee('Museum Tours')
            ->assertSee('Private &amp; Special Visits', false)
            ->assertSee('Education Programmes')
            ->assertSee('The conversation continues.');
    }

    public function test_admin_can_create_and_publish_an_event(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);

        $this->actingAs($admin)->post(route('admin.events.store'), [
            'title' => 'Museum Preview Night',
            'section' => 'upcoming',
            'event_type' => 'Private Event',
            'schedule' => 'Coming in 2027',
            'sort_order' => 1,
            'is_published' => '1',
        ])->assertRedirect();

        $this->assertDatabaseHas('museum_events', [
            'title' => 'Museum Preview Night',
            'section' => 'upcoming',
            'is_published' => true,
        ]);

        $this->get(route('public.events'))
            ->assertOk()
            ->assertSee('Museum Preview Night')
            ->assertSee('Coming in 2027');

        $this->actingAs($admin)
            ->get(route('admin.events.index'))
            ->assertOk()
            ->assertSee('Events CMS')
            ->assertSee('Museum Preview Night');
    }

    public function test_admin_can_update_the_events_page_content(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $content = MuseumEvent::CONTENT_DEFAULTS;
        $content['public_events_page_title'] = 'Museum Programmes';
        $content['public_events_page_description'] = 'A CMS-managed introduction for the events page.';
        $content['public_events_programming_title'] = 'Our Programmes';
        $content['public_events_program_1_title'] = 'New Exhibitions';

        $this->actingAs($admin)
            ->put(route('admin.events.content.update'), $content)
            ->assertRedirect();

        $this->assertDatabaseHas('settings', [
            'key' => 'public_events_page_title',
            'value' => 'Museum Programmes',
        ]);

        $this->get(route('public.events'))
            ->assertOk()
            ->assertSee('Museum Programmes')
            ->assertSee('A CMS-managed introduction for the events page.')
            ->assertSee('Our Programmes')
            ->assertSee('New Exhibitions');
    }
}
