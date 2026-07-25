<?php

namespace Tests\Feature\Admin;

use App\Http\Middleware\IsAdmin;
use App\Models\Coin;
use App\Models\Country;
use App\Models\Currency;
use App\Models\NominalValue;
use App\Models\User;
use Illuminate\Auth\Middleware\Authorize;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class CoinCrudTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->withoutMiddleware(IsAdmin::class);
        $this->withoutMiddleware(Authorize::class);
    }

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
        return User::factory()->create(['role_id' => $this->findOrCreateRole('Admin')]);
    }

    private function makeNonAdminUser(): User
    {
        return User::factory()->create(['role_id' => $this->findOrCreateRole('User')]);
    }

    /** @return array{country_id:int,currency_id:int,nominal_value_id:int} */
    private function makeRequiredLookups(): array
    {
        return [
            'country_id' => Country::create(['name' => 'Germany'])->id,
            'currency_id' => Currency::create(['name' => 'Reichsmark'])->id,
            'nominal_value_id' => NominalValue::create(['name' => '5'])->id,
        ];
    }

    public function test_index_loads_with_200(): void
    {
        $this->actingAs($this->makeAdminUser());

        $response = $this->get(route('admin.coins.index'));

        $response->assertOk();
    }

    public function test_create_form_loads_with_200(): void
    {
        $this->actingAs($this->makeAdminUser());

        $response = $this->get(route('admin.coins.create'));

        $response->assertOk();
    }

    public function test_store_creates_coin_with_required_lookups(): void
    {
        $this->actingAs($this->makeAdminUser());

        $lookups = $this->makeRequiredLookups();

        $response = $this->post(route('admin.coins.store'), $lookups);

        $response->assertRedirect();
        $this->assertDatabaseHas('coins', ['country_id' => $lookups['country_id']]);
    }

    public function test_store_accepts_inline_image_upload(): void
    {
        $this->actingAs($this->makeAdminUser());

        $response = $this->post(route('admin.coins.store'), $this->makeRequiredLookups() + [
            'images' => [UploadedFile::fake()->image('cover.jpg', 800, 600)],
        ]);

        $response->assertRedirect();
        $coin = Coin::latest('id')->firstOrFail();
        $media = $coin->media()->where('collection', 'images')->first();
        $this->assertNotNull($media);
        $this->assertTrue((bool) $media->is_main);
        $this->assertNotNull($media->thumb_path);
        Storage::disk('b2')->assertExists($media->path);
    }

    public function test_store_without_required_lookups_fails_validation_not_500(): void
    {
        $this->actingAs($this->makeAdminUser());

        $response = $this->post(route('admin.coins.store'), []);

        $response->assertSessionHasErrors(['country_id', 'currency_id', 'nominal_value_id']);
        $this->assertDatabaseCount('coins', 0);
    }

    public function test_update_changes_fields(): void
    {
        $this->actingAs($this->makeAdminUser());

        $lookups = $this->makeRequiredLookups();
        $coin = Coin::create($lookups + ['year' => 1940]);

        $response = $this->put(route('admin.coins.update', $coin), $lookups + [
            'year' => 1944,
        ]);

        $response->assertRedirect();
        $this->assertDatabaseHas('coins', ['id' => $coin->id, 'year' => 1944]);
    }

    public function test_destroy_soft_deletes(): void
    {
        $this->actingAs($this->makeAdminUser());

        $coin = Coin::create($this->makeRequiredLookups());

        $response = $this->delete(route('admin.coins.destroy', $coin));

        $response->assertRedirect();
        $this->assertSoftDeleted('coins', ['id' => $coin->id]);
    }

    public function test_non_admin_gets_403(): void
    {
        $this->withMiddleware(IsAdmin::class);
        $this->withMiddleware(Authorize::class);

        $this->actingAs($this->makeNonAdminUser());

        $response = $this->get(route('admin.coins.index'));

        $response->assertForbidden();
    }
}
