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

    public function test_default_sort_matches_newest_and_preserves_explicit_sort_and_filters(): void
    {
        $older = Item::create(['title' => 'Sort fixture']);
        $older->forceFill(['created_at' => '2020-01-01', 'for_sale' => true])->save();
        $newer = $older->replicate();
        $newer->forceFill(['created_at' => '2024-01-01'])->save();
        $excluded = $older->replicate();
        $excluded->forceFill(['for_sale' => false])->save();

        $params = ['for_sale' => 1];
        $default = $this->get(route('items.index', $params))->assertOk();
        $explicit = $this->get(route('items.index', $params + ['sort' => 'created_at_asc']))->assertOk();
        $this->assertSame([$newer->id, $older->id], $default->viewData('items')->pluck('id')->all());
        $this->assertSame($explicit->viewData('items')->pluck('id')->all(), $default->viewData('items')->pluck('id')->all());
        $oldest = $this->get(route('items.index', $params + ['sort' => 'created_at_desc']))->assertOk();
        $this->assertSame([$older->id, $newer->id], $oldest->viewData('items')->pluck('id')->all());
        $url = $oldest->viewData('items')->url(2);
        $this->assertStringContainsString('sort=created_at_desc', $url);
        $this->assertStringContainsString('for_sale=1', $url);
        $default->assertSee('value="created_at_asc" selected', false);
    }
}
