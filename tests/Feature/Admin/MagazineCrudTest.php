<?php

namespace Tests\Feature\Admin;

use App\Http\Middleware\IsAdmin;
use App\Models\Magazine;
use App\Models\User;
use Illuminate\Auth\Middleware\Authorize;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class MagazineCrudTest extends TestCase
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

        $response = $this->get(route('admin.magazines.index'));

        $response->assertOk();
    }

    public function test_create_form_loads_with_200(): void
    {
        $this->actingAs($this->makeAdminUser());

        $response = $this->get(route('admin.magazines.create'));

        $response->assertOk();
    }

    public function test_store_creates_magazine_with_minimal_fields(): void
    {
        $this->actingAs($this->makeAdminUser());

        $response = $this->post(route('admin.magazines.store'), [
            'title' => 'Signal Magazine',
        ]);

        $response->assertRedirect();
        $this->assertDatabaseHas('magazines', ['title' => 'Signal Magazine', 'page_count' => null]);
    }

    public function test_store_accepts_inline_image_upload(): void
    {
        $this->actingAs($this->makeAdminUser());

        $response = $this->post(route('admin.magazines.store'), [
            'title' => 'Magazine With Photo',
            'images' => [UploadedFile::fake()->image('cover.jpg', 800, 600)],
        ]);

        $response->assertRedirect();
        $magazine = Magazine::where('title', 'Magazine With Photo')->firstOrFail();
        $media = $magazine->media()->where('collection', 'images')->first();
        $this->assertNotNull($media);
        $this->assertTrue((bool) $media->is_main);
        $this->assertNotNull($media->thumb_path);
        Storage::disk('b2')->assertExists($media->path);
    }

    public function test_store_validates_required_title(): void
    {
        $this->actingAs($this->makeAdminUser());

        $response = $this->post(route('admin.magazines.store'), []);

        $response->assertSessionHasErrors('title');
    }

    public function test_update_changes_fields(): void
    {
        $this->actingAs($this->makeAdminUser());

        $magazine = Magazine::create(['title' => 'Original Title']);

        $response = $this->put(route('admin.magazines.update', $magazine), [
            'title' => 'Updated Title',
        ]);

        $response->assertRedirect();
        $this->assertDatabaseHas('magazines', ['id' => $magazine->id, 'title' => 'Updated Title']);
    }

    public function test_destroy_permanently_deletes(): void
    {
        $this->actingAs($this->makeAdminUser());

        $magazine = Magazine::create(['title' => 'To Delete']);

        $response = $this->delete(route('admin.magazines.destroy', $magazine));

        $response->assertRedirect();
        $this->assertDatabaseMissing('magazines', ['id' => $magazine->id]);
    }

    public function test_non_admin_gets_403(): void
    {
        $this->withMiddleware(IsAdmin::class);
        $this->withMiddleware(Authorize::class);

        $this->actingAs($this->makeNonAdminUser());

        $response = $this->get(route('admin.magazines.index'));

        $response->assertForbidden();
    }

    public function test_pages_and_series_persist_on_create_and_edit_and_can_be_cleared(): void
    {
        $this->actingAs($this->makeAdminUser());
        $series = \App\Models\MagazineSeries::create(['name' => 'Magazine series']);
        $this->post(route('admin.magazines.store'), ['title' => 'Pages test', 'page_count' => 48, 'series_id' => $series->id])->assertSessionHasNoErrors()->assertRedirect();
        $magazine = Magazine::where('title', 'Pages test')->firstOrFail();
        $this->assertSame(48, $magazine->page_count);
        $this->assertSame($series->id, $magazine->series_id);
        $this->get(route('admin.magazines.edit', $magazine))->assertOk()->assertSee('name="page_count" value="48"', false)->assertSee('Magazine series');
        $this->put(route('admin.magazines.update', $magazine), ['title' => 'Pages test', 'page_count' => 64, 'series_id' => $series->id])->assertSessionHasNoErrors()->assertRedirect();
        $this->assertSame(64, $magazine->fresh()->page_count);
        $this->put(route('admin.magazines.update', $magazine), ['title' => 'Pages test', 'page_count' => '', 'series_id' => ''])->assertSessionHasNoErrors()->assertRedirect();
        $this->assertNull($magazine->fresh()->page_count);
        $this->assertNull($magazine->fresh()->series_id);
    }

    public function test_pages_reject_invalid_values_on_create_and_update(): void
    {
        $this->actingAs($this->makeAdminUser());
        $magazine = Magazine::create(['title' => 'Valid', 'page_count' => 48]);
        foreach ([0, -1, 1.5, 'text', 4294967296] as $pages) {
            $data = ['title' => 'Invalid', 'page_count' => $pages];
            $this->post(route('admin.magazines.store'), $data)->assertSessionHasErrors('page_count');
            $this->put(route('admin.magazines.update', $magazine), $data)->assertSessionHasErrors('page_count');
        }
        $this->assertSame(48, $magazine->fresh()->page_count);
        $this->assertDatabaseMissing('magazines', ['title' => 'Invalid']);
    }
}
