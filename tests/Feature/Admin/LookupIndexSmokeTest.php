<?php

namespace Tests\Feature\Admin;

use App\Http\Middleware\IsAdmin;
use App\Models\User;
use Illuminate\Auth\Middleware\Authorize;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\TestCase;

/**
 * Every lookup type's index page queries `select id, name, created_at` (or,
 * for tree types, a recursive name-based tree) against its configured table.
 * A table created with a differently-named column (as coin_strike_marks and
 * six other coin lookup tables were) 500s here. Loop every configured type
 * so a future naming mistake like that fails a test instead of shipping.
 */
class LookupIndexSmokeTest extends TestCase
{
    use RefreshDatabase;

    private const TYPES = [
        'book-topics', 'book-covers', 'book-series', 'origins', 'locations',
        'item-categories', 'item-nationalities', 'item-organizations',
        'magazine-series', 'newspaper-series',
        'countries', 'currencies', 'nominal-values',
        'banknote-series', 'banknote-time-periods', 'banknote-designers', 'banknote-watermarks',
        'heads-of-state', 'colours', 'print-types',
        'coin-shapes', 'coin-materials', 'coin-occasions', 'coin-designers',
        'coin-strike-marks', 'coin-front-images', 'coin-front-texts',
        'coin-reverse-images', 'coin-reverse-texts', 'coin-rims', 'coin-rim-texts',
        'postcard-types', 'postcard-valuation-images',
        'stamp-types', 'stamp-designers', 'stamp-watermarks', 'stamp-gums',
        'stamp-perforations', 'stamp-printing-houses',
    ];

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

    /** @return iterable<string, array{string}> */
    public static function typeProvider(): iterable
    {
        foreach (self::TYPES as $type) {
            yield $type => [$type];
        }
    }

    #[DataProvider('typeProvider')]
    public function test_lookup_index_loads_for_every_type(string $type): void
    {
        $this->actingAs($this->makeAdminUser());

        $response = $this->get(route('admin.lookups.index', $type));

        $response->assertOk();
    }

    public function test_store_and_delete_round_trip_for_a_flat_type(): void
    {
        $this->actingAs($this->makeAdminUser());

        $store = $this->post(route('admin.lookups.store', 'coin-strike-marks'), [
            'name' => 'Test Strike Mark',
        ]);
        $store->assertSessionDoesntHaveErrors();

        $id = DB::table('coin_strike_marks')->where('name', 'Test Strike Mark')->value('id');
        $this->assertNotNull($id);

        $destroy = $this->delete(route('admin.lookups.destroy', ['coin-strike-marks', $id]));
        $destroy->assertRedirect();

        $this->assertSoftDeleted('coin_strike_marks', ['id' => $id]);
    }

    public function test_store_and_delete_round_trip_for_a_tree_type(): void
    {
        $this->actingAs($this->makeAdminUser());

        $store = $this->post(route('admin.lookups.store', 'book-topics'), [
            'name' => 'Test Topic',
        ]);
        $store->assertSessionDoesntHaveErrors();

        $id = DB::table('book_topics')->where('name', 'Test Topic')->value('id');
        $this->assertNotNull($id);

        $destroy = $this->delete(route('admin.lookups.destroy', ['book-topics', $id]));
        $destroy->assertRedirect();
    }
}
