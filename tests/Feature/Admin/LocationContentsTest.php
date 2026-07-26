<?php

namespace Tests\Feature\Admin;

use App\Http\Middleware\IsAdmin;
use App\Models\Book;
use App\Models\Location;
use App\Models\User;
use Illuminate\Auth\Middleware\Authorize;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class LocationContentsTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->withoutMiddleware(IsAdmin::class);
        $this->withoutMiddleware(Authorize::class);
    }

    private function makeAdminUser(): User
    {
        $roleId = DB::table('roles')->where('name', 'Admin')->value('id')
            ?? DB::table('roles')->insertGetId(['name' => 'Admin', 'created_at' => now(), 'updated_at' => now()]);

        return User::factory()->create(['role_id' => $roleId]);
    }

    public function test_shows_records_stored_directly_at_the_location(): void
    {
        $this->actingAs($this->makeAdminUser());

        $location = Location::create(['name' => 'Box 32']);
        Book::create(['title' => 'Findable Book', 'location_id' => $location->id]);
        Book::create(['title' => 'Elsewhere Book']);

        $response = $this->get(route('admin.lookups.locations.contents', $location));

        $response->assertOk();
        $response->assertSee('Findable Book');
        $response->assertDontSee('Elsewhere Book');
    }

    public function test_includes_records_from_descendant_locations(): void
    {
        $this->actingAs($this->makeAdminUser());

        $shelf = Location::create(['name' => 'Shelf']);
        $box = Location::create(['name' => 'Box', 'parent_id' => $shelf->id]);
        Book::create(['title' => 'Nested Book', 'location_id' => $box->id]);

        $response = $this->get(route('admin.lookups.locations.contents', $shelf));

        $response->assertOk();
        $response->assertSee('Nested Book');
    }

    public function test_labels_page_renders_qr_code_per_location(): void
    {
        $this->actingAs($this->makeAdminUser());

        Location::create(['name' => 'Box 32']);

        $response = $this->get(route('admin.lookups.locations.labels'));

        $response->assertOk();
        $response->assertSee('Box 32');
        $response->assertSee('<svg', false);
    }

    public function test_shows_empty_state_when_nothing_stored(): void
    {
        $this->actingAs($this->makeAdminUser());

        $location = Location::create(['name' => 'Empty Box']);

        $response = $this->get(route('admin.lookups.locations.contents', $location));

        $response->assertOk();
        $response->assertSee('Nothing stored at this location yet.');
    }
}
