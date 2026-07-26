<?php

namespace Tests\Feature\Books;

use App\Models\Book;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdminCreateBookTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_can_access_create_books_page()
    {
        $admin = User::factory()->create(['role_id' => 1]);

        $this->actingAs($admin)
            ->get('/admin/books/create')
            ->assertStatus(200);
    }

    public function test_non_admin_cannot_access_create_books_page()
    {
        $user = User::factory()->create(['role_id' => 2]);

        $this->actingAs($user)
            ->get('/admin/books/create')
            ->assertStatus(403);
    }

    public function test_book_without_images_can_be_viewed()
    {
        $book = Book::create(['title' => 'Book Without Images']);

        $this->get(route('books.show', $book))
            ->assertStatus(200);
    }

    public function test_books_index_loads()
    {
        for ($i = 0; $i < 5; $i++) {
            Book::create(['title' => "Book {$i}"]);
        }

        $this->get(route('books.index'))
            ->assertStatus(200);
    }

    public function test_admin_books_index_loads_and_filters_by_for_sale(): void
    {
        $admin = User::factory()->create(['role_id' => 1]);
        Book::create(['title' => 'For Sale Book', 'for_sale' => true]);
        Book::create(['title' => 'Not For Sale Book', 'for_sale' => false]);

        $response = $this->actingAs($admin)->get(route('admin.books.index', ['for_sale' => 1]));

        $response->assertOk();
        $response->assertSee('For Sale Book');
        $response->assertDontSee('Not For Sale Book');
    }

    public function test_admin_books_index_filter_links_stay_within_admin(): void
    {
        $admin = User::factory()->create(['role_id' => 1]);
        Book::create(['title' => 'Any Book', 'for_sale' => true]);

        $response = $this->actingAs($admin)->get(route('admin.books.index'));

        $response->assertOk();
        // The for-sale filter link (and every other sidebar filter link) must
        // point back at the admin route, not the public books.index — else
        // clicking it bounces an admin user out to the public site.
        $response->assertSee(route('admin.books.index', ['for_sale' => 1]), false);
    }
}
