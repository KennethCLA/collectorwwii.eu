<?php

namespace Tests\Feature\Admin;

use App\Http\Middleware\IsAdmin;
use App\Models\Book;
use App\Models\Item;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class LookupDeleteProtectionTest extends TestCase
{
    use RefreshDatabase;

    private function findOrCreateRole(string $name): int
    {
        return DB::table('roles')->where('name', $name)->value('id')
            ?? DB::table('roles')->insertGetId([
                'name' => $name,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
    }

    private function makeAdminUser(): User
    {
        return User::factory()->create([
            'role_id' => $this->findOrCreateRole('Admin'),
        ]);
    }

    private function makeNonAdminUser(): User
    {
        return User::factory()->create([
            'role_id' => $this->findOrCreateRole('User'),
        ]);
    }

    protected function setUp(): void
    {
        parent::setUp();

        $this->withoutMiddleware(IsAdmin::class);
    }

    public function test_index_loads_for_admin(): void
    {
        $this->actingAs($this->makeAdminUser());

        $response = $this->get(route('admin.lookups.index', ['type' => 'origins']));

        $response->assertStatus(200);
    }

    public function test_admin_can_add_a_lookup_value(): void
    {
        $this->actingAs($this->makeAdminUser());

        $response = $this->post(route('admin.lookups.store', ['type' => 'origins']), [
            'name' => 'Germany',
        ]);

        $response->assertRedirect(route('admin.lookups.index', ['type' => 'origins']));
        $this->assertDatabaseHas('origins', ['name' => 'Germany']);
    }

    public function test_duplicate_name_does_not_error(): void
    {
        $this->actingAs($this->makeAdminUser());

        DB::table('origins')->insert([
            'name' => 'Germany',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $response = $this->post(route('admin.lookups.store', ['type' => 'origins']), [
            'name' => 'Germany',
        ]);

        $response->assertRedirect();
        $response->assertSessionHas('success');
    }

    public function test_admin_can_delete_unused_lookup_value(): void
    {
        $this->actingAs($this->makeAdminUser());

        $originId = DB::table('origins')->insertGetId([
            'name' => 'Unused Origin',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $response = $this->delete(route('admin.lookups.destroy', ['type' => 'origins', 'id' => $originId]));

        $response->assertRedirect(route('admin.lookups.index', ['type' => 'origins']));
        $response->assertSessionHas('success');
    }

    public function test_delete_blocked_when_lookup_is_in_use(): void
    {
        $this->actingAs($this->makeAdminUser());

        $originId = DB::table('origins')->insertGetId([
            'name' => 'Used Origin',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        Book::create([
            'title' => 'Book with origin',
            'origin_id' => $originId,
        ]);

        $response = $this->delete(route('admin.lookups.destroy', ['type' => 'origins', 'id' => $originId]));

        $response->assertRedirect(route('admin.lookups.index', ['type' => 'origins']));
        $response->assertSessionHas('error');
    }

    public function test_delete_protection_checks_across_multiple_reference_tables(): void
    {
        $this->actingAs($this->makeAdminUser());

        $originId = DB::table('origins')->insertGetId([
            'name' => 'Shared Origin',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        // Origin referenced by an item (not a book)
        Item::create([
            'title' => 'Item with origin',
            'origin_id' => $originId,
        ]);

        $response = $this->delete(route('admin.lookups.destroy', ['type' => 'origins', 'id' => $originId]));

        $response->assertRedirect();
        $response->assertSessionHas('error');
    }

    public function test_soft_deleted_references_dont_block_deletion(): void
    {
        $this->actingAs($this->makeAdminUser());

        $originId = DB::table('origins')->insertGetId([
            'name' => 'Soft Deleted Origin',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $book = Book::create([
            'title' => 'Soft deleted book',
            'origin_id' => $originId,
        ]);
        $book->delete(); // soft delete

        $response = $this->delete(route('admin.lookups.destroy', ['type' => 'origins', 'id' => $originId]));

        $response->assertRedirect();
        $response->assertSessionHas('success');
    }

    public function test_search_filter_works(): void
    {
        $this->actingAs($this->makeAdminUser());

        DB::table('origins')->insert([
            ['name' => 'Germany', 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'France', 'created_at' => now(), 'updated_at' => now()],
        ]);

        $response = $this->get(route('admin.lookups.index', ['type' => 'origins', 'q' => 'Germ']));

        $response->assertStatus(200);
        $response->assertSee('Germany');
        $response->assertDontSee('France');
    }

    public function test_non_admin_gets_403_without_middleware_bypass(): void
    {
        // Re-enable the IsAdmin middleware for this test
        $this->withMiddleware(IsAdmin::class);

        $user = $this->makeNonAdminUser();
        $this->actingAs($user);

        $response = $this->get(route('admin.lookups.index', ['type' => 'origins']));

        $response->assertStatus(403);
    }
}
