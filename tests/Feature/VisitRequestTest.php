<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\VisitRequest;
use App\Mail\VisitRequestSubmitted;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Mail;
use Tests\TestCase;

class VisitRequestTest extends TestCase
{
    use RefreshDatabase;

    public function test_public_visit_form_saves_a_request(): void
    {
        Mail::fake();

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
        Mail::assertSent(VisitRequestSubmitted::class, fn ($mail) => $mail->hasTo('faiz@museumazman.com'));
    }

    public function test_admin_can_save_encrypted_smtp_settings(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);

        $this->actingAs($admin)->post(route('settings.update', ['section' => 'smtp']), [
            'smtp_enabled' => '1',
            'smtp_host' => 'smtp.museumazman.com',
            'smtp_port' => 587,
            'smtp_encryption' => 'tls',
            'smtp_username' => 'mailer@museumazman.com',
            'smtp_password' => 'secret-password',
            'smtp_from_address' => 'mailer@museumazman.com',
            'smtp_from_name' => 'Museum Azman',
            'visit_request_recipient' => 'faiz@museumazman.com',
        ])->assertRedirect(route('settings.index', ['tab' => 'smtp']));

        $storedPassword = \App\Models\Setting::where('key', 'smtp_password')->value('value');
        $this->assertNotSame('secret-password', $storedPassword);
        $this->assertSame('secret-password', \Illuminate\Support\Facades\Crypt::decryptString($storedPassword));
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
