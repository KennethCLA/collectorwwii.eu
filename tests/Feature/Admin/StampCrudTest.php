<?php

namespace Tests\Feature\Admin;

use App\Http\Middleware\IsAdmin;
use App\Models\Stamp;
use App\Models\User;
use Illuminate\Auth\Middleware\Authorize;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class StampCrudTest extends TestCase
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

    public function test_index_loads_with_200(): void
    {
        $this->actingAs($this->makeAdminUser());

        $response = $this->get(route('admin.stamps.index'));

        $response->assertOk();
    }

    public function test_index_search_filters_by_michel_number(): void
    {
        $this->actingAs($this->makeAdminUser());

        $match = Stamp::create(['michel_number' => 'MC-42']);
        Stamp::create(['michel_number' => 'Unrelated']);

        $response = $this->get(route('admin.stamps.index', ['search' => 'MC-42']));
        $response->assertOk();
        $results = $response->viewData('stamps');
        $this->assertSame(1, $results->total());
        $this->assertSame($match->id, $results->first()->id);
    }

    public function test_create_form_loads_with_200(): void
    {
        $this->actingAs($this->makeAdminUser());

        $response = $this->get(route('admin.stamps.create'));

        $response->assertOk();
    }

    public function test_store_creates_stamp_with_minimal_fields(): void
    {
        $this->actingAs($this->makeAdminUser());

        $response = $this->post(route('admin.stamps.store'), [
            'year' => 1943,
        ]);

        $response->assertRedirect();
        $this->assertDatabaseHas('stamps', ['year' => 1943]);
    }

    public function test_store_accepts_inline_image_upload(): void
    {
        $this->actingAs($this->makeAdminUser());

        $response = $this->post(route('admin.stamps.store'), [
            'year' => 1943,
            'images' => [UploadedFile::fake()->image('cover.jpg', 800, 600)],
        ]);

        $response->assertRedirect();
        $stamp = Stamp::latest('id')->firstOrFail();
        $media = $stamp->media()->where('collection', 'images')->first();
        $this->assertNotNull($media);
        $this->assertTrue((bool) $media->is_main);
        $this->assertNotNull($media->thumb_path);
        Storage::disk('b2')->assertExists($media->path);
    }

    public function test_store_validates_year_range(): void
    {
        $this->actingAs($this->makeAdminUser());

        $response = $this->post(route('admin.stamps.store'), [
            'year' => 1500,
        ]);

        $response->assertSessionHasErrors('year');
    }

    public function test_update_changes_fields(): void
    {
        $this->actingAs($this->makeAdminUser());

        $stamp = Stamp::create(['year' => 1940]);

        $response = $this->put(route('admin.stamps.update', $stamp), [
            'year' => 1945,
        ]);

        $response->assertRedirect();
        $this->assertDatabaseHas('stamps', ['id' => $stamp->id, 'year' => 1945]);
    }

    public function test_destroy_permanently_deletes(): void
    {
        $this->actingAs($this->makeAdminUser());

        $stamp = Stamp::create(['year' => 1940]);

        $response = $this->delete(route('admin.stamps.destroy', $stamp));

        $response->assertRedirect();
        $this->assertDatabaseMissing('stamps', ['id' => $stamp->id]);
    }

    public function test_non_admin_gets_403(): void
    {
        $this->withMiddleware(IsAdmin::class);
        $this->withMiddleware(Authorize::class);

        $this->actingAs($this->makeNonAdminUser());

        $response = $this->get(route('admin.stamps.index'));

        $response->assertForbidden();
    }
}
