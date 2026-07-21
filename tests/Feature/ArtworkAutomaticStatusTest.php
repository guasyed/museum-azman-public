<?php

namespace Tests\Feature;

use App\Models\Location;
use App\Models\Artwork;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ArtworkAutomaticStatusTest extends TestCase
{
    use RefreshDatabase;

    public function test_internal_location_status_is_derived_and_remarks_are_saved(): void
    {
        $user = User::factory()->create();
        Location::create(['name' => 'Museum 1', 'type' => 'Museum']);

        $this->actingAs($user)->post(route('artworks.store'), [
            'title' => 'Automatic Status Artwork',
            'artist_name' => 'Test Artist',
            'location_name' => 'Museum 1',
            'location_type' => 'Museum',
            'status' => 'Sold or Left',
            'remarks' => 'Optional internal reference.',
        ])->assertRedirect();

        $this->assertDatabaseHas('artworks', [
            'title' => 'Automatic Status Artwork',
            'status' => 'On Display',
            'remarks' => 'Optional internal reference.',
        ]);

        $artwork = Artwork::query()->where('title', 'Automatic Status Artwork')->firstOrFail();
        Location::create(['name' => 'Store', 'type' => 'Storage']);

        $this->actingAs($user)->put(route('artworks.update', $artwork), [
            'title' => $artwork->title,
            'artist_name' => 'Test Artist',
            'location_name' => 'Store',
            'location_type' => 'Storage',
            'status' => 'In Storage',
            'remarks' => 'Optional internal reference.',
        ])->assertRedirect();

        $this->assertDatabaseHas('activity_logs', [
            'action' => 'artwork.updated',
            'subject_id' => $artwork->id,
            'description' => 'Location changed: Museum 1 → Store.',
        ]);

        $this->actingAs($user)
            ->get(route('dashboard'))
            ->assertOk()
            ->assertSee('Recent Artwork Activities')
            ->assertSee('Location changed: Museum 1 → Store.');
    }

    public function test_store_and_external_locations_use_reference_status_rules(): void
    {
        $user = User::factory()->create();
        Location::create(['name' => 'Store', 'type' => 'Storage']);
        Location::create(['name' => 'External', 'type' => 'External']);

        $this->actingAs($user)->post(route('artworks.store'), [
            'title' => 'Stored Artwork',
            'artist_name' => 'Test Artist',
            'location_name' => 'Store',
            'status' => 'On Display',
        ])->assertRedirect();

        $this->actingAs($user)->post(route('artworks.store'), [
            'title' => 'External Artwork',
            'artist_name' => 'Test Artist',
            'location_name' => 'External',
            'status' => 'Loaned Out',
        ])->assertRedirect();

        $this->assertDatabaseHas('artworks', ['title' => 'Stored Artwork', 'status' => 'In Storage']);
        $this->assertDatabaseHas('artworks', ['title' => 'External Artwork', 'status' => 'Loaned Out']);
    }
}
