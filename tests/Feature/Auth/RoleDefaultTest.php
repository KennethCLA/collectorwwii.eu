<?php

namespace Tests\Feature\Auth;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class RoleDefaultTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_role_always_occupies_id_one_on_a_fresh_database(): void
    {
        // role_id === 1 is hardcoded app-wide (policies, IsAdmin middleware)
        // to mean admin. On a completely fresh database this must always be
        // true, regardless of migration/seed order.
        $roleOne = DB::table('roles')->find(1);

        $this->assertNotNull($roleOne, 'No role exists at id 1 on a fresh database.');
        $this->assertSame('admin', $roleOne->name);
    }

    public function test_user_created_without_explicit_role_id_is_not_admin(): void
    {
        $id = DB::table('users')->insertGetId([
            'name' => 'No Role Specified',
            'email' => 'norole@example.com',
            'password' => bcrypt('password'),
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $user = User::find($id);

        $this->assertNotSame(1, $user->role_id);
        $this->assertFalse($user->isAdmin());
    }

    public function test_user_with_role_id_one_is_admin(): void
    {
        $user = User::factory()->create(['role_id' => 1]);

        $this->assertTrue($user->isAdmin());
    }
}
