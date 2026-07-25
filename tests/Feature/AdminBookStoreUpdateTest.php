<?php

namespace Tests\Feature;

use App\Http\Middleware\IsAdmin;
use App\Models\Author;
use App\Models\Book;
use App\Models\User;
use Illuminate\Auth\Middleware\Authorize;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class AdminBookStoreUpdateTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        // admin gate + policies uitzetten voor deze test (optioneel allebei)
        $this->withoutMiddleware(IsAdmin::class);
        $this->withoutMiddleware(Authorize::class); // dit is die "can:*" middleware door authorizeResource
    }

    private function makeAdminUser(): User
    {
        // roles heeft geen factory => insert zelf (idempotent, kan meermaals aangeroepen worden)
        $roleId = DB::table('roles')->where('name', 'Admin')->value('id')
            ?? DB::table('roles')->insertGetId([
                'name' => 'Admin',
                'created_at' => now(),
                'updated_at' => now(),
            ]);

        // users factory faalde eerder door role_id FK, dus zet role_id expliciet
        // (en enkel velden die je users-table écht heeft)
        return User::factory()->create([
            'role_id' => $roleId,
        ]);
    }

    private function makeLookupIds(): array
    {
        $topicId = DB::table('book_topics')->insertGetId([
            'name' => 'Test topic',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $seriesId = DB::table('book_series')->insertGetId([
            'name' => 'Test series',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $coverId = DB::table('book_covers')->insertGetId([
            'name' => 'Hardcover',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $locationId = DB::table('locations')->insertGetId([
            'name' => 'Shelf A',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        return compact('topicId', 'seriesId', 'coverId', 'locationId');
    }

    public function test_it_stores_a_book_with_all_fields_and_media_and_one_main_image(): void
    {
        Storage::fake('b2');

        $user = $this->makeAdminUser();
        $this->actingAs($user);

        ['topicId' => $topicId, 'seriesId' => $seriesId, 'coverId' => $coverId, 'locationId' => $locationId] = $this->makeLookupIds();

        $img1 = UploadedFile::fake()->image('a.jpg');
        $img2 = UploadedFile::fake()->image('b.jpg');
        $pdf1 = UploadedFile::fake()->create('doc.pdf', 200, 'application/pdf');

        $payload = [
            'isbn' => '978-1 2345-6789-X',
            'title' => 'Test Title',
            'subtitle' => 'Test Subtitle',

            'title_first_edition' => 'First ed title',
            'subtitle_first_edition' => 'First ed subtitle',
            'description' => 'Desc',
            'translator' => 'Translator',
            'pages' => 123,

            'copyright_year' => 2001,
            'issue_number' => 'IV',
            'issue_year' => 2002,

            'topic_id' => $topicId,
            'series_id' => $seriesId,
            'series_number' => '12',
            'cover_id' => $coverId,

            'copyright_year_first_issue' => 1999,
            'publisher_name' => 'Publisher',
            'publisher_first_issue' => 'Publisher First',

            'purchase_price' => 10.50,
            'purchase_date' => '2024-01-01',
            'notes' => 'Some notes',
            'location_id' => $locationId,

            'for_sale' => 1,
            'selling_price' => 99.99,

            'weight' => 1000,
            'width' => 10,
            'height' => 20,
            'thickness' => 3,

            'authors' => 'John Doe, Jane Doe',

            'images' => [$img1, $img2],
            'pdfs' => [$pdf1],
            'main_image_index' => 1,
            'after_save' => 'show',
        ];

        $response = $this->post(route('admin.books.store'), $payload);
        $response->assertRedirect();

        $book = Book::query()->latest('id')->firstOrFail();

        $this->assertSame('978123456789X', $book->isbn);
        $this->assertSame($locationId, $book->location_id);
        $this->assertTrue((bool) $book->for_sale);
        $this->assertSame('99.99', (string) $book->selling_price);

        $this->assertSame(2, $book->authors()->count());

        $images = $book->media()->where('collection', 'images')->orderBy('sort_order')->get();
        $this->assertCount(2, $images);

        $mainCount = $book->media()->where('collection', 'images')->where('is_main', 1)->count();
        $this->assertSame(1, $mainCount);

        $main = $book->media()->where('collection', 'images')->where('is_main', 1)->firstOrFail();
        $this->assertSame(1, (int) $main->sort_order);

        $files = $book->media()->where('collection', 'files')->get();
        $this->assertCount(1, $files);
        $this->assertSame('application/pdf', $files->first()->mime_type);

        Storage::disk('b2')->assertExists($main->path);
    }

    public function test_it_updates_all_db_fields_and_resyncs_authors(): void
    {
        $user = $this->makeAdminUser();
        $this->actingAs($user);

        ['locationId' => $locationId] = $this->makeLookupIds();

        // Book factory bestaat niet -> create via model
        $book = Book::create([
            'isbn' => null,
            'title' => 'Original',
            'subtitle' => null,
            'for_sale' => 0,
            'selling_price' => null,
        ]);

        // bestaande author
        $oldAuthorId = DB::table('authors')->insertGetId([
            'name' => 'Old One',
            'created_at' => now(),
            'updated_at' => now(),
        ]);
        DB::table('book_authors')->insert([
            'book_id' => $book->id,
            'author_id' => $oldAuthorId,
        ]);

        $payload = [
            'isbn' => '978-0-11111-222-3',
            'title' => 'Updated Title',
            'subtitle' => 'Updated Subtitle',
            'title_first_edition' => 'Updated 1st',
            'subtitle_first_edition' => 'Updated 1st sub',
            'description' => 'Updated desc',
            'translator' => 'Updated translator',
            'pages' => 321,

            'copyright_year' => 2005,
            'issue_number' => 'V',
            'issue_year' => 2006,

            'series_id' => null,
            'series_number' => null,
            'cover_id' => null,
            'topic_id' => null,

            'copyright_year_first_issue' => 2000,
            'publisher_name' => 'Updated pub',
            'publisher_first_issue' => 'Updated pub first',

            'purchase_price' => 22.00,
            'purchase_date' => '2024-02-02',
            'notes' => 'Updated notes',
            'location_id' => $locationId,

            'for_sale' => 0,
            'selling_price' => 123.45, // moet null worden

            'weight' => 900,
            'width' => 11,
            'height' => 21,
            'thickness' => 4,

            'authors' => 'New One, New Two',
        ];

        $response = $this->put(route('admin.books.update', $book), $payload);
        $response->assertRedirect(route('books.show', $book));

        $book->refresh();

        $this->assertSame('9780111112223', $book->isbn);
        $this->assertSame('Updated Title', $book->title);
        $this->assertSame($locationId, $book->location_id);

        $this->assertFalse((bool) $book->for_sale);
        $this->assertNull($book->selling_price);

        $this->assertSame(2, $book->authors()->count());
    }
}
