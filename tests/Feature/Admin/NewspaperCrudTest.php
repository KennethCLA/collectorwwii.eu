<?php

namespace Tests\Feature\Admin;

use App\Http\Middleware\IsAdmin;
use App\Models\Newspaper;
use App\Models\User;
use Illuminate\Auth\Middleware\Authorize;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class NewspaperCrudTest extends TestCase
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

        $response = $this->get(route('admin.newspapers.index'));

        $response->assertOk();
    }

    public function test_create_form_loads_with_200(): void
    {
        $this->actingAs($this->makeAdminUser());

        $response = $this->get(route('admin.newspapers.create'));

        $response->assertOk();
    }

    public function test_store_creates_newspaper_with_minimal_fields(): void
    {
        $this->actingAs($this->makeAdminUser());

        $response = $this->post(route('admin.newspapers.store'), [
            'title' => 'Völkischer Beobachter',
        ]);

        $response->assertRedirect();
        $this->assertDatabaseHas('newspapers', ['title' => 'Völkischer Beobachter']);
    }

    public function test_store_accepts_inline_image_upload(): void
    {
        $this->actingAs($this->makeAdminUser());

        $response = $this->post(route('admin.newspapers.store'), [
            'title' => 'Newspaper With Photo',
            'images' => [UploadedFile::fake()->image('cover.jpg', 800, 600)],
        ]);

        $response->assertRedirect();
        $newspaper = Newspaper::where('title', 'Newspaper With Photo')->firstOrFail();
        $media = $newspaper->media()->where('collection', 'images')->first();
        $this->assertNotNull($media);
        $this->assertTrue((bool) $media->is_main);
        $this->assertNotNull($media->thumb_path);
        Storage::disk('b2')->assertExists($media->path);
    }

    public function test_store_validates_required_title(): void
    {
        $this->actingAs($this->makeAdminUser());

        $response = $this->post(route('admin.newspapers.store'), []);

        $response->assertSessionHasErrors('title');
    }

    public function test_update_changes_fields(): void
    {
        $this->actingAs($this->makeAdminUser());

        $newspaper = Newspaper::create(['title' => 'Original Title']);

        $response = $this->put(route('admin.newspapers.update', $newspaper), [
            'title' => 'Updated Title',
        ]);

        $response->assertRedirect();
        $this->assertDatabaseHas('newspapers', ['id' => $newspaper->id, 'title' => 'Updated Title']);
    }

    public function test_destroy_permanently_deletes(): void
    {
        $this->actingAs($this->makeAdminUser());

        $newspaper = Newspaper::create(['title' => 'To Delete']);

        $response = $this->delete(route('admin.newspapers.destroy', $newspaper));

        $response->assertRedirect();
        $this->assertDatabaseMissing('newspapers', ['id' => $newspaper->id]);
    }

    public function test_non_admin_gets_403(): void
    {
        $this->withMiddleware(IsAdmin::class);
        $this->withMiddleware(Authorize::class);

        $this->actingAs($this->makeNonAdminUser());

        $response = $this->get(route('admin.newspapers.index'));

        $response->assertForbidden();
    }
}
