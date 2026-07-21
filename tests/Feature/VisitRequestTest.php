<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\VisitRequest;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class VisitRequestTest extends TestCase
{
    use RefreshDatabase;

    public function test_public_visit_form_saves_a_request(): void
    {
        $response = $this->post(route('public.visit.store'), [
            'name' => 'Museum Visitor',
            'phone' => '+60123456789',
            'email' => 'visitor@example.com',
            'occupation' => 'Curator',
            'company' => 'Example Gallery',
            'city' => 'Kuala Lumpur, Malaysia',
            'purpose' => 'Curatorial research',
            'category' => 'Southeast Asian art',
            'date' => now()->addWeek()->toDateString(),
            'guests' => 2,
            'source' => 'Collector referral',
            'preference' => ['curator'],
            'consent' => '1',
        ]);

        $response->assertRedirect(route('public.visit'));
        $response->assertSessionHas('visit_success');
        $this->assertDatabaseHas('visit_requests', [
            'email' => 'visitor@example.com',
            'guests' => 2,
        ]);
    }

    public function test_admin_can_view_and_review_visit_requests(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $visitRequest = VisitRequest::create([
            'name' => 'Collector',
            'phone' => '+60111111111',
            'email' => 'collector@example.com',
            'occupation' => 'Collector',
            'company' => 'Private Collection',
            'city' => 'Penang, Malaysia',
            'purpose' => 'Collector viewing',
            'category' => 'Contemporary painting',
            'preferred_date' => now()->addWeek()->toDateString(),
            'guests' => 1,
            'source' => 'Artist referral',
        ]);

        $this->actingAs($admin)
            ->get(route('admin.visit-requests.index'))
            ->assertOk()
            ->assertSee('collector@example.com')
            ->assertSee('Collector viewing');

        $this->actingAs($admin)
            ->patch(route('admin.visit-requests.reviewed', $visitRequest))
            ->assertRedirect();

        $this->assertNotNull($visitRequest->fresh()->reviewed_at);
    }
}
