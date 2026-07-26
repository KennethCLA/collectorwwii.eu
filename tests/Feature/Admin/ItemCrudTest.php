<?php

namespace Tests\Feature\Admin;

use App\Http\Middleware\IsAdmin;
use App\Models\Item;
use App\Models\User;
use Illuminate\Auth\Middleware\Authorize;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class ItemCrudTest extends TestCase
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

        $response = $this->get(route('admin.items.index'));

        $response->assertOk();
    }

    public function test_create_form_loads_with_200(): void
    {
        $this->actingAs($this->makeAdminUser());

        $response = $this->get(route('admin.items.create'));

        $response->assertOk();
    }

    public function test_store_creates_item_with_minimal_fields(): void
    {
        $this->actingAs($this->makeAdminUser());

        $response = $this->post(route('admin.items.store'), [
            'title' => 'Iron Cross',
        ]);

        $response->assertRedirect();
        $this->assertDatabaseHas('items', ['title' => 'Iron Cross']);
    }

    public function test_store_validates_required_title(): void
    {
        $this->actingAs($this->makeAdminUser());

        $response = $this->post(route('admin.items.store'), []);

        $response->assertSessionHasErrors('title');
    }

    public function test_store_validates_sold_price_requires_sold_at(): void
    {
        $this->actingAs($this->makeAdminUser());

        $response = $this->post(route('admin.items.store'), [
            'title' => 'Iron Cross',
            'sold_price' => 50,
        ]);

        $response->assertSessionHasErrors('sold_at');
    }

    public function test_store_validates_sold_at_not_in_future(): void
    {
        $this->actingAs($this->makeAdminUser());

        $response = $this->post(route('admin.items.store'), [
            'title' => 'Iron Cross',
            'sold_at' => now()->addDay()->toDateString(),
        ]);

        $response->assertSessionHasErrors('sold_at');
    }

    public function test_store_validates_purchase_date_not_in_future(): void
    {
        $this->actingAs($this->makeAdminUser());

        $response = $this->post(route('admin.items.store'), [
            'title' => 'Iron Cross',
            'purchase_date' => now()->addDay()->toDateString(),
        ]);

        $response->assertSessionHasErrors('purchase_date');
    }

    public function test_store_marking_sold_clears_for_sale_and_selling_price(): void
    {
        $this->actingAs($this->makeAdminUser());

        $response = $this->post(route('admin.items.store'), [
            'title' => 'Iron Cross',
            'for_sale' => '1',
            'selling_price' => 100,
            'sold_at' => now()->toDateString(),
            'sold_price' => 80,
        ]);

        $response->assertRedirect();
        $this->assertDatabaseHas('items', [
            'title' => 'Iron Cross',
            'for_sale' => false,
            'selling_price' => null,
        ]);
    }

    public function test_store_accepts_inline_image_upload(): void
    {
        $this->actingAs($this->makeAdminUser());

        $response = $this->post(route('admin.items.store'), [
            'title' => 'Item With Photo',
            'images' => [UploadedFile::fake()->image('cover.jpg', 800, 600)],
        ]);

        $response->assertRedirect();
        $item = Item::where('title', 'Item With Photo')->firstOrFail();
        $media = $item->media()->where('collection', 'images')->first();
        $this->assertNotNull($media);
        $this->assertTrue((bool) $media->is_main);
        $this->assertNotNull($media->thumb_path);
        Storage::disk('b2')->assertExists($media->path);
    }

    public function test_update_changes_fields(): void
    {
        $this->actingAs($this->makeAdminUser());

        $item = Item::create(['title' => 'Original Title']);

        $response = $this->put(route('admin.items.update', $item), [
            'title' => 'Updated Title',
        ]);

        $response->assertRedirect();
        $this->assertDatabaseHas('items', ['id' => $item->id, 'title' => 'Updated Title']);
    }

    public function test_destroy_soft_deletes(): void
    {
        $this->actingAs($this->makeAdminUser());

        $item = Item::create(['title' => 'To Delete']);

        $response = $this->delete(route('admin.items.destroy', $item));

        $response->assertRedirect();
        $this->assertSoftDeleted('items', ['id' => $item->id]);
    }

    public function test_non_admin_gets_403(): void
    {
        $this->withMiddleware(IsAdmin::class);
        $this->withMiddleware(Authorize::class);

        $this->actingAs($this->makeNonAdminUser());

        $response = $this->get(route('admin.items.index'));

        $response->assertForbidden();
    }
}
