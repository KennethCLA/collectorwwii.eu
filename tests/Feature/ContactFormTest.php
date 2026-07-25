<?php

namespace Tests\Feature;

use App\Mail\ContactSubmitted;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Mail;
use Tests\TestCase;

class ContactFormTest extends TestCase
{
    use RefreshDatabase;

    public function test_contact_page_loads(): void
    {
        $response = $this->get(route('contact'));

        $response->assertStatus(200);
    }

    public function test_valid_submission_stores_and_sends_mail(): void
    {
        Mail::fake();

        $roleId = \DB::table('roles')->where('name', 'admin')->value('id')
            ?? \DB::table('roles')->insertGetId(['name' => 'admin', 'created_at' => now(), 'updated_at' => now()]);
        $admin = User::factory()->create(['role_id' => $roleId]);

        $response = $this->post(route('contact.store'), [
            'name' => 'John Doe',
            'email' => 'john@example.com',
            'message' => 'Hello, this is a test message.',
        ]);

        $response->assertRedirect();
        $response->assertSessionHas('success');

        $this->assertDatabaseHas('contacts', [
            'name' => 'John Doe',
            'email' => 'john@example.com',
            'message' => 'Hello, this is a test message.',
        ]);

        Mail::assertSent(ContactSubmitted::class, function ($mail) use ($admin) {
            return $mail->hasTo($admin->email);
        });
    }

    public function test_validation_rejects_missing_fields(): void
    {
        $response = $this->post(route('contact.store'), []);

        $response->assertSessionHasErrors(['name', 'email', 'message']);
    }

    public function test_validation_rejects_invalid_email(): void
    {
        $response = $this->post(route('contact.store'), [
            'name' => 'John Doe',
            'email' => 'not-an-email',
            'message' => 'Hello.',
        ]);

        $response->assertSessionHasErrors(['email']);
    }
}
