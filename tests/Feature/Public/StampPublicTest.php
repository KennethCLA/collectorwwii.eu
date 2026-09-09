<?php

namespace Tests\Feature\Public;

use App\Models\Stamp;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class StampPublicTest extends TestCase
{
    use RefreshDatabase;

    public function test_index_loads_with_200(): void
    {
        $response = $this->get(route('stamps.index'));

        $response->assertOk();
    }

    public function test_show_loads_with_200_for_existing_stamp(): void
    {
        $stamp = Stamp::create(['year' => 1943]);

        $response = $this->get(route('stamps.show', $stamp));

        $response->assertOk();
    }

    public function test_show_returns_404_for_missing_stamp(): void
    {
        $response = $this->get(route('stamps.show', 999999));

        $response->assertNotFound();
    }

    public function test_default_sort_matches_newest_and_preserves_explicit_sort_and_filters(): void
    {
        $older = Stamp::create(['year' => 1943]);
        $older->forceFill(['created_at' => '2020-01-01', 'for_sale' => true])->save();
        $newer = $older->replicate();
        $newer->forceFill(['created_at' => '2024-01-01'])->save();
        $excluded = $older->replicate();
        $excluded->forceFill(['for_sale' => false])->save();

        $params = ['for_sale' => 1];
        $default = $this->get(route('stamps.index', $params))->assertOk();
        $explicit = $this->get(route('stamps.index', $params + ['sort' => 'created_at_asc']))->assertOk();
        $this->assertSame([$newer->id, $older->id], $default->viewData('stamps')->pluck('id')->all());
        $this->assertSame($explicit->viewData('stamps')->pluck('id')->all(), $default->viewData('stamps')->pluck('id')->all());
        $oldest = $this->get(route('stamps.index', $params + ['sort' => 'created_at_desc']))->assertOk();
        $this->assertSame([$older->id, $newer->id], $oldest->viewData('stamps')->pluck('id')->all());
        $url = $oldest->viewData('stamps')->url(2);
        $this->assertStringContainsString('sort=created_at_desc', $url);
        $this->assertStringContainsString('for_sale=1', $url);
        $default->assertSee('value="created_at_asc" selected', false);
    }
}
