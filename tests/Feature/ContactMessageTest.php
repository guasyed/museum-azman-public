<?php

namespace Tests\Feature;

use App\Models\ContactMessage;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ContactMessageTest extends TestCase
{
    use RefreshDatabase;

    public function test_public_contact_form_saves_a_message(): void
    {
        $response = $this->post(route('public.contact.store'), [
            'name' => 'Museum Visitor',
            'email' => 'visitor@example.com',
            'subject' => 'Private viewing',
            'message' => 'I would like to arrange a viewing.',
        ]);

        $response->assertRedirect(route('public.contact'));
        $response->assertSessionHas('contact_success');
        $this->assertDatabaseHas('contact_messages', [
            'email' => 'visitor@example.com',
            'subject' => 'Private viewing',
        ]);
    }

    public function test_admin_can_view_and_mark_contact_messages_as_read(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $message = ContactMessage::create([
            'name' => 'Collector',
            'email' => 'collector@example.com',
            'subject' => 'Collection enquiry',
            'message' => 'Please send more information.',
        ]);

        $this->actingAs($admin)
            ->get(route('admin.contact-messages.index'))
            ->assertOk()
            ->assertSee('Collection enquiry')
            ->assertSee('collector@example.com');

        $this->actingAs($admin)
            ->patch(route('admin.contact-messages.read', $message))
            ->assertRedirect();

        $this->assertNotNull($message->fresh()->read_at);
    }
}
