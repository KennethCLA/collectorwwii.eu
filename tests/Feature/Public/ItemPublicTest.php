<?php

namespace Tests\Feature\Public;

use App\Models\Item;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ItemPublicTest extends TestCase
{
    use RefreshDatabase;

    public function test_index_loads_with_200(): void
    {
        $response = $this->get(route('items.index'));

        $response->assertOk();
    }

    public function test_index_with_search_and_sort_loads_with_200(): void
    {
        Item::create(['title' => 'Iron Cross']);

        $response = $this->get(route('items.index', ['search' => 'Iron', 'sort' => 'title_asc']));

        $response->assertOk();
        $response->assertSee('Iron Cross');
    }

    public function test_show_loads_with_200_for_existing_item(): void
    {
        $item = Item::create(['title' => 'Field Telephone']);

        $response = $this->get(route('items.show', $item));

        $response->assertOk();
        $response->assertSee('Field Telephone');
    }

    public function test_show_returns_404_for_missing_item(): void
    {
        $response = $this->get(route('items.show', 999999));

        $response->assertNotFound();
    }
}
