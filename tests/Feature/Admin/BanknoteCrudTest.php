<?php

namespace Tests\Feature\Admin;

use App\Http\Middleware\IsAdmin;
use App\Models\Banknote;
use App\Models\BanknoteSeries;
use App\Models\BanknoteTimePeriod;
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

class BanknoteCrudTest extends TestCase
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

    /** @return array{country_id:int,currency_id:int,nominal_value_id:int,series_id:int,time_period_id:int} */
    private function makeRequiredLookups(): array
    {
        return [
            'country_id' => Country::create(['name' => 'Germany'])->id,
            'currency_id' => Currency::create(['name' => 'Reichsmark'])->id,
            'nominal_value_id' => NominalValue::create(['name' => '10'])->id,
            'series_id' => BanknoteSeries::create(['name' => 'Series A'])->id,
            'time_period_id' => BanknoteTimePeriod::create(['name' => '1933-1945'])->id,
        ];
    }

    public function test_index_loads_with_200(): void
    {
        $this->actingAs($this->makeAdminUser());

        $response = $this->get(route('admin.banknotes.index'));

        $response->assertOk();
    }

    public function test_create_form_loads_with_200(): void
    {
        $this->actingAs($this->makeAdminUser());

        $response = $this->get(route('admin.banknotes.create'));

        $response->assertOk();
    }

    public function test_store_creates_banknote_with_required_lookups(): void
    {
        $this->actingAs($this->makeAdminUser());

        $lookups = $this->makeRequiredLookups();

        $response = $this->post(route('admin.banknotes.store'), $lookups);

        $response->assertRedirect();
        $this->assertDatabaseHas('banknotes', ['country_id' => $lookups['country_id']]);
    }

    public function test_store_accepts_inline_image_upload(): void
    {
        $this->actingAs($this->makeAdminUser());

        $response = $this->post(route('admin.banknotes.store'), $this->makeRequiredLookups() + [
            'images' => [UploadedFile::fake()->image('cover.jpg', 800, 600)],
        ]);

        $response->assertRedirect();
        $banknote = Banknote::latest('id')->firstOrFail();
        $media = $banknote->media()->where('collection', 'images')->first();
        $this->assertNotNull($media);
        $this->assertTrue((bool) $media->is_main);
        $this->assertNotNull($media->thumb_path);
        Storage::disk('b2')->assertExists($media->path);
    }

    public function test_store_without_required_lookups_fails_validation_not_500(): void
    {
        $this->actingAs($this->makeAdminUser());

        $response = $this->post(route('admin.banknotes.store'), []);

        $response->assertSessionHasErrors(['country_id', 'currency_id', 'nominal_value_id', 'series_id', 'time_period_id']);
        $this->assertDatabaseCount('banknotes', 0);
    }

    public function test_update_changes_fields(): void
    {
        $this->actingAs($this->makeAdminUser());

        $lookups = $this->makeRequiredLookups();
        $banknote = Banknote::create($lookups + ['year' => 1940]);

        $response = $this->put(route('admin.banknotes.update', $banknote), $lookups + [
            'year' => 1944,
        ]);

        $response->assertRedirect();
        $this->assertDatabaseHas('banknotes', ['id' => $banknote->id, 'year' => 1944]);
    }

    public function test_destroy_soft_deletes(): void
    {
        $this->actingAs($this->makeAdminUser());

        $banknote = Banknote::create($this->makeRequiredLookups());

        $response = $this->delete(route('admin.banknotes.destroy', $banknote));

        $response->assertRedirect();
        $this->assertSoftDeleted('banknotes', ['id' => $banknote->id]);
    }

    public function test_non_admin_gets_403(): void
    {
        $this->withMiddleware(IsAdmin::class);
        $this->withMiddleware(Authorize::class);

        $this->actingAs($this->makeNonAdminUser());

        $response = $this->get(route('admin.banknotes.index'));

        $response->assertForbidden();
    }
}
