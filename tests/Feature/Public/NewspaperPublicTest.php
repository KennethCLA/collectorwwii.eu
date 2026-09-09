<?php

namespace Tests\Feature\Public;

use App\Models\Newspaper;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class NewspaperPublicTest extends TestCase
{
    use RefreshDatabase;

    public function test_index_loads_with_200(): void
    {
        $response = $this->get(route('newspapers.index'));

        $response->assertOk();
    }

    public function test_show_loads_with_200_for_existing_newspaper(): void
    {
        $newspaper = Newspaper::create(['title' => 'Völkischer Beobachter']);

        $response = $this->get(route('newspapers.show', $newspaper));

        $response->assertOk();
        $response->assertSee('Völkischer Beobachter');
    }

    public function test_show_returns_404_for_missing_newspaper(): void
    {
        $response = $this->get(route('newspapers.show', 999999));

        $response->assertNotFound();
    }

    public function test_default_sort_matches_newest_and_preserves_explicit_sort_and_filters(): void
    {
        $older = Newspaper::create(['title' => 'Sort fixture']);
        $older->forceFill(['created_at' => '2020-01-01', 'for_sale' => true])->save();
        $newer = $older->replicate();
        $newer->forceFill(['created_at' => '2024-01-01'])->save();
        $excluded = $older->replicate();
        $excluded->forceFill(['for_sale' => false])->save();

        $params = ['for_sale' => 1];
        $default = $this->get(route('newspapers.index', $params))->assertOk();
        $explicit = $this->get(route('newspapers.index', $params + ['sort' => 'created_at_asc']))->assertOk();
        $this->assertSame([$newer->id, $older->id], $default->viewData('newspapers')->pluck('id')->all());
        $this->assertSame($explicit->viewData('newspapers')->pluck('id')->all(), $default->viewData('newspapers')->pluck('id')->all());
        $oldest = $this->get(route('newspapers.index', $params + ['sort' => 'created_at_desc']))->assertOk();
        $this->assertSame([$older->id, $newer->id], $oldest->viewData('newspapers')->pluck('id')->all());
        $url = $oldest->viewData('newspapers')->url(2);
        $this->assertStringContainsString('sort=created_at_desc', $url);
        $this->assertStringContainsString('for_sale=1', $url);
        $default->assertSee('value="created_at_asc" selected', false);
    }
}
