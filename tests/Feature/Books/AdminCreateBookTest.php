<?php

namespace Tests\Feature\Books;

use App\Models\Book;
use App\Models\User;
use Tests\TestCase;

class AdminCreateBookTest extends TestCase
{
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
}
