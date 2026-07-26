<?php

namespace Tests\Feature\Admin;

use App\Http\Controllers\Admin\LookupIndexController;
use App\Http\Middleware\IsAdmin;
use App\Models\User;
use Illuminate\Auth\Middleware\Authorize;
use Illuminate\Support\Facades\DB;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\TestCase;

/**
 * The inline "+" add-lookup modal (Admin\Ajax\LookupController) used to keep
 * its own hardcoded list of supported types, separate from
 * LookupIndexController::types() — the list that drives /admin/lookups/{type}.
 * They drifted: 25 of 39 registered lookup types had a full management page
 * but no inline-add support at all. LookupController now reads directly from
 * LookupIndexController::types(), so this test derives its type list from the
 * same source rather than hardcoding a second copy that could drift again.
 */
class LookupAjaxSmokeTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        $this->withoutMiddleware(IsAdmin::class);
        $this->withoutMiddleware(Authorize::class);
    }

    private function makeAdminUser(): User
    {
        $roleId = DB::table('roles')->where('name', 'Admin')->value('id')
            ?? DB::table('roles')->insertGetId([
                'name' => 'Admin',
                'created_at' => now(),
                'updated_at' => now(),
            ]);

        return User::factory()->create(['role_id' => $roleId]);
    }

    /** @return iterable<string, array{string, array}> */
    public static function typeProvider(): iterable
    {
        foreach (LookupIndexController::types() as $type => $config) {
            yield $type => [$type, $config];
        }
    }

    #[DataProvider('typeProvider')]
    public function test_inline_add_store_works_for_every_type(string $type, array $config): void
    {
        $this->actingAs($this->makeAdminUser());

        // nominal_values.name is a decimal column (face values like 5.00,
        // 0.50) by design, unlike every other lookup table's varchar name.
        $name = $type === 'nominal-values' ? '5.00' : 'Inline Add Test';

        $response = $this->postJson(route('admin.lookups.ajax.store', $type), [
            'name' => $name,
        ]);

        $response->assertOk();
        $response->assertJsonStructure(['id', 'name']);

        $this->assertDatabaseHas($config['table'], ['name' => $name]);
    }

    #[DataProvider('typeProvider')]
    public function test_parents_endpoint_matches_tree_config(string $type, array $config): void
    {
        $this->actingAs($this->makeAdminUser());

        $response = $this->getJson(route('admin.lookups.ajax.parents', $type));

        if ($config['tree'] ?? false) {
            $response->assertOk();
            $this->assertIsArray($response->json());
        } else {
            $response->assertNotFound();
        }
    }

    public function test_unregistered_type_404s(): void
    {
        $this->actingAs($this->makeAdminUser());

        $this->postJson(route('admin.lookups.ajax.store', 'not-a-real-type'), ['name' => 'x'])
            ->assertNotFound();
    }

    public function test_duplicate_submission_reuses_existing_row(): void
    {
        $this->actingAs($this->makeAdminUser());

        $first = $this->postJson(route('admin.lookups.ajax.store', 'coin-shapes'), ['name' => 'Round']);
        $second = $this->postJson(route('admin.lookups.ajax.store', 'coin-shapes'), ['name' => 'Round']);

        $first->assertOk();
        $second->assertOk();
        $this->assertSame($first->json('id'), $second->json('id'));
        $this->assertSame(1, DB::table('coin_shapes')->where('name', 'Round')->count());
    }

    public function test_nominal_value_rejects_non_numeric_name_with_validation_error(): void
    {
        $this->actingAs($this->makeAdminUser());

        $this->postJson(route('admin.lookups.ajax.store', 'nominal-values'), ['name' => 'not a number'])
            ->assertStatus(422)
            ->assertJsonValidationErrors('name');
    }
}
