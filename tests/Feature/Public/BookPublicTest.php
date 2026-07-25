<?php

namespace Tests\Feature\Public;

use App\Models\Book;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class BookPublicTest extends TestCase
{
    use RefreshDatabase;

    public function test_index_loads_with_200(): void
    {
        $response = $this->get(route('books.index'));

        $response->assertOk();
    }

    public function test_index_with_search_and_sort_loads_with_200(): void
    {
        Book::create(['title' => 'Mein Kampf']);

        $response = $this->get(route('books.index', ['search' => 'Kampf', 'sort' => 'title_asc']));

        $response->assertOk();
        $response->assertSee('Mein Kampf');
    }

    public function test_show_loads_with_200_for_existing_book(): void
    {
        $book = Book::create(['title' => 'Commandant of Auschwitz']);

        $response = $this->get(route('books.show', $book));

        $response->assertOk();
        $response->assertSee('Commandant of Auschwitz');
    }

    public function test_show_returns_404_for_missing_book(): void
    {
        $response = $this->get(route('books.show', 999999));

        $response->assertNotFound();
    }
}
