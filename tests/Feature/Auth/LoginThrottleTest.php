<?php

namespace Tests\Feature\Auth;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;
use Tests\TestCase;

class LoginThrottleTest extends TestCase
{
    use RefreshDatabase;

    private function findOrCreateRole(string $name): int
    {
        return \DB::table('roles')->where('name', $name)->value('id')
            ?? \DB::table('roles')->insertGetId([
                'name' => $name,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
    }

    protected function setUp(): void
    {
        parent::setUp();

        Cache::flush();
    }

    public function test_valid_credentials_log_in_successfully(): void
    {
        $user = User::factory()->create([
            'email' => 'admin@example.com',
            'password' => bcrypt('correct-password'),
            'role_id' => $this->findOrCreateRole('admin'),
        ]);

        $response = $this->post('/login', [
            'email' => 'admin@example.com',
            'password' => 'correct-password',
        ]);

        $response->assertRedirect();
        $this->assertAuthenticatedAs($user);
    }

    public function test_invalid_credentials_are_rejected(): void
    {
        User::factory()->create([
            'email' => 'admin@example.com',
            'password' => bcrypt('correct-password'),
            'role_id' => $this->findOrCreateRole('admin'),
        ]);

        $response = $this->post('/login', [
            'email' => 'admin@example.com',
            'password' => 'wrong-password',
        ]);

        $response->assertSessionHasErrors('email');
        $this->assertGuest();
    }

    public function test_sixth_attempt_within_a_minute_is_throttled(): void
    {
        User::factory()->create([
            'email' => 'admin@example.com',
            'password' => bcrypt('correct-password'),
            'role_id' => $this->findOrCreateRole('admin'),
        ]);

        for ($i = 0; $i < 5; $i++) {
            $response = $this->post('/login', [
                'email' => 'admin@example.com',
                'password' => 'wrong-password',
            ]);
            $response->assertSessionHasErrors('email');
        }

        // 6th attempt should be locked out, even with the correct password.
        $response = $this->post('/login', [
            'email' => 'admin@example.com',
            'password' => 'correct-password',
        ]);

        $response->assertSessionHasErrors('email');
        $this->assertGuest();
        $this->assertStringContainsString(
            'Too many login attempts',
            collect(session('errors')->get('email'))->first()
        );
    }

    public function test_successful_login_clears_previous_failed_attempts(): void
    {
        $user = User::factory()->create([
            'email' => 'admin@example.com',
            'password' => bcrypt('correct-password'),
            'role_id' => $this->findOrCreateRole('admin'),
        ]);

        // A few failed attempts, but under the throttle threshold.
        for ($i = 0; $i < 3; $i++) {
            $this->post('/login', [
                'email' => 'admin@example.com',
                'password' => 'wrong-password',
            ]);
        }

        $response = $this->post('/login', [
            'email' => 'admin@example.com',
            'password' => 'correct-password',
        ]);

        $response->assertRedirect();
        $this->assertAuthenticatedAs($user);
    }
}
