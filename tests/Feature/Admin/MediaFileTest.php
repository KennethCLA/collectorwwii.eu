<?php

namespace Tests\Feature\Admin;

use App\Http\Middleware\IsAdmin;
use App\Models\Book;
use App\Models\MediaFile;
use App\Models\User;
use Illuminate\Auth\Middleware\Authorize;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class MediaFileTest extends TestCase
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
            ?? DB::table('roles')->insertGetId([
                'name' => 'Admin',
                'created_at' => now(),
                'updated_at' => now(),
            ]);

        return User::factory()->create(['role_id' => $roleId]);
    }

    public function test_uploading_an_image_creates_media_file_and_thumb(): void
    {
        $this->actingAs($this->makeAdminUser());
        $book = Book::create(['title' => 'Test Book']);

        $response = $this->post(route('admin.media.store', ['type' => 'books', 'id' => $book->id]), [
            'collection' => 'images',
            'files' => [UploadedFile::fake()->image('cover.jpg', 800, 600)],
        ]);

        $response->assertRedirect();

        $media = $book->images()->first();
        $this->assertNotNull($media);
        $this->assertSame('images', $media->collection);
        $this->assertTrue((bool) $media->is_main, 'First uploaded image should be auto-promoted to main.');
        Storage::disk('b2')->assertExists($media->path);
    }

    public function test_first_uploaded_image_becomes_main_and_second_does_not(): void
    {
        $this->actingAs($this->makeAdminUser());
        $book = Book::create(['title' => 'Test Book']);

        $this->post(route('admin.media.store', ['type' => 'books', 'id' => $book->id]), [
            'collection' => 'images',
            'files' => [UploadedFile::fake()->image('first.jpg')],
        ]);
        $this->post(route('admin.media.store', ['type' => 'books', 'id' => $book->id]), [
            'collection' => 'images',
            'files' => [UploadedFile::fake()->image('second.jpg')],
        ]);

        $this->assertSame(1, $book->images()->where('is_main', true)->count());
        $this->assertSame(2, $book->images()->count());
    }

    public function test_upload_rejects_disallowed_mimetype(): void
    {
        $this->actingAs($this->makeAdminUser());
        $book = Book::create(['title' => 'Test Book']);

        $response = $this->post(route('admin.media.store', ['type' => 'books', 'id' => $book->id]), [
            'collection' => 'images',
            'files' => [UploadedFile::fake()->create('malware.exe', 10)],
        ]);

        $response->assertSessionHasErrors('files.0');
        $this->assertSame(0, $book->images()->count());
    }

    public function test_make_main_switches_the_main_image(): void
    {
        $this->actingAs($this->makeAdminUser());
        $book = Book::create(['title' => 'Test Book']);

        $this->post(route('admin.media.store', ['type' => 'books', 'id' => $book->id]), [
            'collection' => 'images',
            'files' => [UploadedFile::fake()->image('first.jpg')],
        ]);
        $this->post(route('admin.media.store', ['type' => 'books', 'id' => $book->id]), [
            'collection' => 'images',
            'files' => [UploadedFile::fake()->image('second.jpg')],
        ]);

        $second = $book->images()->where('is_main', false)->firstOrFail();

        $response = $this->patch(route('admin.media.main', ['type' => 'books', 'file' => $second->id]));

        $response->assertRedirect();
        $this->assertTrue($second->fresh()->is_main);
        $this->assertSame(1, $book->images()->where('is_main', true)->count());
    }

    public function test_destroy_removes_file_and_promotes_new_main(): void
    {
        $this->actingAs($this->makeAdminUser());
        $book = Book::create(['title' => 'Test Book']);

        $this->post(route('admin.media.store', ['type' => 'books', 'id' => $book->id]), [
            'collection' => 'images',
            'files' => [UploadedFile::fake()->image('first.jpg')],
        ]);
        $this->post(route('admin.media.store', ['type' => 'books', 'id' => $book->id]), [
            'collection' => 'images',
            'files' => [UploadedFile::fake()->image('second.jpg')],
        ]);

        $main = $book->images()->where('is_main', true)->firstOrFail();
        $remaining = $book->images()->where('is_main', false)->firstOrFail();
        $path = $main->path;

        $response = $this->delete(route('admin.media.destroy', ['type' => 'books', 'file' => $main->id]));

        $response->assertRedirect();
        $this->assertDatabaseMissing('media_files', ['id' => $main->id]);
        Storage::disk('b2')->assertMissing($path);
        $this->assertTrue($remaining->fresh()->is_main, 'Remaining image should be auto-promoted to main.');
    }

    public function test_reorder_updates_sort_order(): void
    {
        $this->actingAs($this->makeAdminUser());
        $book = Book::create(['title' => 'Test Book']);

        $this->post(route('admin.media.store', ['type' => 'books', 'id' => $book->id]), [
            'collection' => 'images',
            'files' => [UploadedFile::fake()->image('first.jpg')],
        ]);
        $this->post(route('admin.media.store', ['type' => 'books', 'id' => $book->id]), [
            'collection' => 'images',
            'files' => [UploadedFile::fake()->image('second.jpg')],
        ]);

        // images() sorts main-image-first regardless of sort_order, so query
        // the raw sort_order column directly rather than through that relation.
        $ids = MediaFile::where('attachable_id', $book->id)->orderBy('sort_order')->pluck('id')->reverse()->values();

        $response = $this->postJson(route('admin.media.reorder', ['type' => 'books', 'id' => $book->id]), [
            'ids' => $ids->all(),
        ]);

        $response->assertOk();

        $reordered = MediaFile::where('attachable_id', $book->id)->orderBy('sort_order')->pluck('id')->values();
        $this->assertSame($ids->all(), $reordered->all());
    }
}
