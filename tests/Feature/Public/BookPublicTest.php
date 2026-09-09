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

    public function test_default_sort_matches_newest_and_preserves_explicit_sort_and_filters(): void
    {
        $older = Book::create(['title' => 'Sort fixture']);
        $older->forceFill(['created_at' => '2020-01-01', 'for_sale' => true])->save();
        $newer = $older->replicate();
        $newer->forceFill(['created_at' => '2024-01-01'])->save();
        $excluded = $older->replicate();
        $excluded->forceFill(['for_sale' => false])->save();

        $params = ['for_sale' => 1];
        $default = $this->get(route('books.index', $params))->assertOk();
        $explicit = $this->get(route('books.index', $params + ['sort' => 'created_at_asc']))->assertOk();
        $this->assertSame([$newer->id, $older->id], $default->viewData('books')->pluck('id')->all());
        $this->assertSame($explicit->viewData('books')->pluck('id')->all(), $default->viewData('books')->pluck('id')->all());
        $oldest = $this->get(route('books.index', $params + ['sort' => 'created_at_desc']))->assertOk();
        $this->assertSame([$older->id, $newer->id], $oldest->viewData('books')->pluck('id')->all());
        $url = $oldest->viewData('books')->url(2);
        $this->assertStringContainsString('sort=created_at_desc', $url);
        $this->assertStringContainsString('for_sale=1', $url);
        $default->assertSee('value="created_at_asc" selected', false);
    }
}
