<?php

namespace Tests\Feature;

use App\Models\MuseumEvent;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class EventsCmsTest extends TestCase
{
    use RefreshDatabase;

    public function test_public_events_page_displays_three_coming_soon_slots_per_section(): void
    {
        $this->get(route('public.events'))
            ->assertOk()
            ->assertSee('Currently Active')
            ->assertSee('Upcoming')
            ->assertSee('Archive')
            ->assertSeeInOrder(['Currently Active', 'Coming Soon', 'Upcoming', 'Coming Soon', 'Archive', 'Coming Soon']);
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
}
