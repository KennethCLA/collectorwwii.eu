<?php

namespace Tests\Feature\Public;

use App\Models\Book;
use App\Models\Item;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SearchTest extends TestCase
{
    use RefreshDatabase;

    public function test_search_page_loads_with_no_query(): void
    {
        $response = $this->get(route('search.index'));

        $response->assertOk();
    }

    public function test_short_query_returns_no_results_without_crashing(): void
    {
        $response = $this->get(route('search.index', ['q' => 'a']));

        $response->assertOk();
    }

    public function test_search_finds_a_matching_book(): void
    {
        Book::create(['title' => 'Mein Kampf']);

        $response = $this->get(route('search.index', ['q' => 'Kampf']));

        $response->assertOk();
        $response->assertSee('Mein Kampf');
    }

    public function test_search_finds_matches_across_multiple_types(): void
    {
        Book::create(['title' => 'Unique Search Term Book']);
        Item::create(['title' => 'Unique Search Term Item']);

        $response = $this->get(route('search.index', ['q' => 'Unique Search Term']));

        $response->assertOk();
        $response->assertSee('Unique Search Term Book');
        $response->assertSee('Unique Search Term Item');
    }

    public function test_search_with_no_matches_returns_ok(): void
    {
        $response = $this->get(route('search.index', ['q' => 'zzz-nonexistent-zzz']));

        $response->assertOk();
    }
}
