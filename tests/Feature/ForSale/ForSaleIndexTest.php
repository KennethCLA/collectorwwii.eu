<?php

namespace Tests\Feature\ForSale;

use App\Models\Book;
use App\Models\Item;
use App\Models\Magazine;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ForSaleIndexTest extends TestCase
{
    use RefreshDatabase;

    private function makeBook(array $overrides = []): Book
    {
        return Book::create(array_merge([
            'title' => 'Test Book',
            'for_sale' => true,
            'selling_price' => 25.00,
        ], $overrides));
    }

    private function makeItem(array $overrides = []): Item
    {
        return Item::create(array_merge([
            'title' => 'Test Item',
            'for_sale' => true,
            'selling_price' => 15.00,
        ], $overrides));
    }

    private function makeMagazine(array $overrides = []): \App\Models\Magazine
    {
        return Magazine::create(array_merge([
            'title' => 'Test Magazine',
            'for_sale' => true,
            'selling_price' => 10.00,
        ], $overrides));
    }

    public function test_page_loads_with_200(): void
    {
        $response = $this->get(route('for-sale.index'));

        $response->assertStatus(200);
    }

    public function test_aggregates_for_sale_records_from_multiple_models(): void
    {
        $this->makeBook(['title' => 'Sale Book']);
        $this->makeItem(['title' => 'Sale Item']);
        $this->makeMagazine(['title' => 'Sale Magazine']);

        $response = $this->get(route('for-sale.index'));

        $response->assertStatus(200);
        $response->assertSee('Sale Book');
        $response->assertSee('Sale Item');
        $response->assertSee('Sale Magazine');
    }

    public function test_excludes_records_with_for_sale_false(): void
    {
        $this->makeBook(['title' => 'For Sale Book', 'for_sale' => true]);
        $this->makeBook(['title' => 'Not For Sale Book', 'for_sale' => false]);

        $response = $this->get(route('for-sale.index'));

        $response->assertSee('For Sale Book');
        $response->assertDontSee('Not For Sale Book');
    }

    public function test_type_filter_returns_only_books(): void
    {
        $this->makeBook(['title' => 'Sale Book']);
        $this->makeItem(['title' => 'Sale Item']);

        $response = $this->get(route('for-sale.index', ['type' => 'books']));

        $response->assertSee('Sale Book');
        $response->assertDontSee('Sale Item');
    }

    public function test_search_filter_by_title(): void
    {
        $this->makeBook(['title' => 'WW2 Tanks']);
        $this->makeBook(['title' => 'Aviation History']);

        $response = $this->get(route('for-sale.index', ['q' => 'Tanks']));

        $response->assertSee('WW2 Tanks');
        $response->assertDontSee('Aviation History');
    }

    public function test_sort_price_asc(): void
    {
        $this->makeBook(['title' => 'Expensive Book', 'selling_price' => 99.99]);
        $this->makeBook(['title' => 'Cheap Book', 'selling_price' => 5.00]);

        $response = $this->get(route('for-sale.index', ['sort' => 'price_asc']));

        $response->assertStatus(200);
        $content = $response->getContent();
        $cheapPos = strpos($content, 'Cheap Book');
        $expensivePos = strpos($content, 'Expensive Book');
        $this->assertLessThan($expensivePos, $cheapPos);
    }

    public function test_sort_price_desc(): void
    {
        $this->makeBook(['title' => 'Expensive Book', 'selling_price' => 99.99]);
        $this->makeBook(['title' => 'Cheap Book', 'selling_price' => 5.00]);

        $response = $this->get(route('for-sale.index', ['sort' => 'price_desc']));

        $response->assertStatus(200);
        $content = $response->getContent();
        $cheapPos = strpos($content, 'Cheap Book');
        $expensivePos = strpos($content, 'Expensive Book');
        $this->assertLessThan($cheapPos, $expensivePos);
    }

    public function test_sort_title_asc(): void
    {
        $this->makeBook(['title' => 'Zebra Book']);
        $this->makeBook(['title' => 'Alpha Book']);

        $response = $this->get(route('for-sale.index', ['sort' => 'title_asc']));

        $response->assertStatus(200);
        $content = $response->getContent();
        $alphaPos = strpos($content, 'Alpha Book');
        $zebraPos = strpos($content, 'Zebra Book');
        $this->assertLessThan($zebraPos, $alphaPos);
    }

    public function test_pagination_caps_at_24_per_page(): void
    {
        for ($i = 1; $i <= 30; $i++) {
            $this->makeBook(['title' => "Book {$i}", 'selling_price' => $i]);
        }

        $response = $this->get(route('for-sale.index'));
        $response->assertStatus(200);

        $paginator = $response->viewData('forSale');
        $this->assertSame(24, $paginator->count());
        $this->assertSame(30, $paginator->total());
    }

    public function test_each_result_has_required_keys(): void
    {
        $this->makeBook(['title' => 'Keyed Book']);

        $response = $this->get(route('for-sale.index'));
        $paginator = $response->viewData('forSale');

        $first = $paginator->first();
        $this->assertArrayHasKey('type', $first);
        $this->assertArrayHasKey('type_label', $first);
        $this->assertArrayHasKey('title', $first);
        $this->assertArrayHasKey('price', $first);
        $this->assertArrayHasKey('created_at', $first);
        $this->assertArrayHasKey('image', $first);
        $this->assertArrayHasKey('url', $first);
    }

    public function test_default_sort_matches_existing_newest_option(): void
    {
        $old = $this->makeBook(['title' => 'Alpha older']);
        $old->forceFill(['created_at' => '2020-01-01'])->save();
        $new = $this->makeMagazine(['title' => 'Zulu newer']);
        $new->forceFill(['created_at' => '2024-01-01'])->save();
        $this->get(route('for-sale.index'))->assertOk()->assertSeeInOrder(['Zulu newer', 'Alpha older']);
        $this->get(route('for-sale.index', ['sort' => 'created_at_desc']))->assertOk()->assertSeeInOrder(['Zulu newer', 'Alpha older']);
        $this->get(route('for-sale.index', ['sort' => 'created_at_asc']))->assertOk()->assertSeeInOrder(['Alpha older', 'Zulu newer']);
    }
}
