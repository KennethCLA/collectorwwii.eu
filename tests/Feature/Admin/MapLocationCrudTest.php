<?php

namespace Tests\Feature\Admin;

use App\Http\Middleware\IsAdmin;
use App\Models\MapLocation;
use App\Models\User;
use Illuminate\Auth\Middleware\Authorize;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class MapLocationCrudTest extends TestCase
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

    public function test_index_loads_with_200(): void
    {
        $this->actingAs($this->makeAdminUser());

        $response = $this->get(route('admin.map-locations.index'));

        $response->assertStatus(200);
    }

    public function test_create_form_loads_with_200(): void
    {
        $this->actingAs($this->makeAdminUser());

        $response = $this->get(route('admin.map-locations.create'));

        $response->assertStatus(200);
    }

    public function test_store_without_photos(): void
    {
        $this->actingAs($this->makeAdminUser());

        $response = $this->post(route('admin.map-locations.store'), [
            'name' => 'Normandy Beach',
            'latitude' => 49.369444,
            'longitude' => -0.878333,
            'description' => 'D-Day landing site',
        ]);

        $response->assertRedirect();

        $location = MapLocation::query()->latest('id')->firstOrFail();
        $this->assertSame('Normandy Beach', $location->name);
        $this->assertStringContainsString(',', $location->coordinates);
        $this->assertEqualsWithDelta(49.369444, $location->latitude(), 0.001);
        $this->assertEqualsWithDelta(-0.878333, $location->longitude(), 0.001);
    }

    public function test_store_with_photos(): void
    {
        Storage::fake('b2');

        $this->actingAs($this->makeAdminUser());

        $photo1 = UploadedFile::fake()->create('beach.jpg', 100, 'image/jpeg');
        $photo2 = UploadedFile::fake()->create('bunker.jpg', 100, 'image/jpeg');

        $response = $this->post(route('admin.map-locations.store'), [
            'name' => 'Normandy Beach',
            'latitude' => 49.369444,
            'longitude' => -0.878333,
            'photos' => [$photo1, $photo2],
        ]);

        $response->assertRedirect();

        $location = MapLocation::query()->latest('id')->firstOrFail();

        $images = $location->media()->where('collection', 'images')->get();
        $this->assertCount(2, $images);

        $mainCount = $location->media()->where('is_main', true)->count();
        $this->assertSame(1, $mainCount);

        $main = $location->media()->where('is_main', true)->firstOrFail();
        $this->assertSame(0, (int) $main->sort_order);

        Storage::disk('b2')->assertExists($main->path);
    }

    public function test_validates_required_fields(): void
    {
        $this->actingAs($this->makeAdminUser());

        $response = $this->post(route('admin.map-locations.store'), []);

        $response->assertSessionHasErrors(['name', 'latitude', 'longitude']);
    }

    public function test_validates_coordinate_ranges(): void
    {
        $this->actingAs($this->makeAdminUser());

        $response = $this->post(route('admin.map-locations.store'), [
            'name' => 'Invalid Place',
            'latitude' => 91.0,
            'longitude' => -181.0,
        ]);

        $response->assertSessionHasErrors(['latitude', 'longitude']);
    }

    public function test_edit_form_loads_with_location_data(): void
    {
        $this->actingAs($this->makeAdminUser());

        $location = MapLocation::create([
            'name' => 'Test Location',
            'coordinates' => '50.850300,4.351700',
        ]);

        $response = $this->get(route('admin.map-locations.edit', $location));

        $response->assertStatus(200);
        $response->assertSee('Test Location');
    }

    public function test_update_changes_name_and_coordinates(): void
    {
        $this->actingAs($this->makeAdminUser());

        $location = MapLocation::create([
            'name' => 'Old Name',
            'coordinates' => '50.000000,4.000000',
        ]);

        $response = $this->put(route('admin.map-locations.update', $location), [
            'name' => 'New Name',
            'latitude' => 51.123456,
            'longitude' => 3.654321,
        ]);

        $response->assertRedirect();

        $location->refresh();
        $this->assertSame('New Name', $location->name);
        $this->assertEqualsWithDelta(51.123456, $location->latitude(), 0.001);
        $this->assertEqualsWithDelta(3.654321, $location->longitude(), 0.001);
    }

    public function test_destroy_soft_deletes_location_and_cleans_up_media(): void
    {
        Storage::fake('b2');

        $this->actingAs($this->makeAdminUser());

        $location = MapLocation::create([
            'name' => 'Doomed Location',
            'coordinates' => '50.000000,4.000000',
        ]);

        $fakePath = "map-locations/{$location->id}/test.jpg";
        Storage::disk('b2')->put($fakePath, 'fake image content');

        $location->media()->create([
            'disk' => 'b2',
            'path' => $fakePath,
            'mime_type' => 'image/jpeg',
            'size' => 100,
            'original_name' => 'test.jpg',
            'collection' => 'images',
            'is_main' => true,
            'sort_order' => 0,
        ]);

        $response = $this->delete(route('admin.map-locations.destroy', $location));

        $response->assertRedirect(route('admin.map-locations.index'));

        $this->assertSoftDeleted('map_locations', ['id' => $location->id]);
        $this->assertDatabaseMissing('media_files', ['attachable_id' => $location->id, 'attachable_type' => MapLocation::class]);
    }

    public function test_non_admin_gets_403(): void
    {
        $this->withMiddleware(IsAdmin::class);
        $this->withMiddleware(Authorize::class);

        $this->actingAs($this->makeNonAdminUser());

        $response = $this->get(route('admin.map-locations.index'));

        $response->assertStatus(403);
    }
}
