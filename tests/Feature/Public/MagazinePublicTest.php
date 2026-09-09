<?php

namespace Tests\Feature\Public;

use App\Models\Magazine;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class MagazinePublicTest extends TestCase
{
    use RefreshDatabase;

    public function test_index_loads_with_200(): void
    {
        $response = $this->get(route('magazines.index'));

        $response->assertOk();
    }

    public function test_show_loads_with_200_for_existing_magazine(): void
    {
        $magazine = Magazine::create(['title' => 'Signal']);

        $response = $this->get(route('magazines.show', $magazine));

        $response->assertOk();
        $response->assertSee('Signal');
    }

    public function test_show_returns_404_for_missing_magazine(): void
    {
        $response = $this->get(route('magazines.show', 999999));

        $response->assertNotFound();
    }

    public function test_default_sort_matches_newest_and_preserves_explicit_sort_and_filters(): void
    {
        $older = Magazine::create(['title' => 'Sort fixture']);
        $older->forceFill(['created_at' => '2020-01-01', 'for_sale' => true])->save();
        $newer = $older->replicate();
        $newer->forceFill(['created_at' => '2024-01-01'])->save();
        $excluded = $older->replicate();
        $excluded->forceFill(['for_sale' => false])->save();

        $params = ['for_sale' => 1];
        $default = $this->get(route('magazines.index', $params))->assertOk();
        $explicit = $this->get(route('magazines.index', $params + ['sort' => 'created_at_asc']))->assertOk();
        $this->assertSame([$newer->id, $older->id], $default->viewData('magazines')->pluck('id')->all());
        $this->assertSame($explicit->viewData('magazines')->pluck('id')->all(), $default->viewData('magazines')->pluck('id')->all());
        $oldest = $this->get(route('magazines.index', $params + ['sort' => 'created_at_desc']))->assertOk();
        $this->assertSame([$older->id, $newer->id], $oldest->viewData('magazines')->pluck('id')->all());
        $url = $oldest->viewData('magazines')->url(2);
        $this->assertStringContainsString('sort=created_at_desc', $url);
        $this->assertStringContainsString('for_sale=1', $url);
        $default->assertSee('value="created_at_asc" selected', false);
    }

    public function test_series_pages_and_actual_subtitle_are_displayed(): void
    {
        $series = \App\Models\MagazineSeries::create(['name' => 'Signal series']);
        $magazine = Magazine::create(['title' => 'Signal', 'subtitle' => 'Actual subtitle', 'publisher' => 'Metadata publisher', 'series_id' => $series->id, 'page_count' => 48]);
        $this->get(route('magazines.index'))->assertOk()->assertSee('Actual subtitle')->assertDontSee('Metadata publisher');
        $this->get(route('magazines.show', $magazine))->assertOk()->assertSee('Signal series')->assertSee('Pages')->assertSee('48')->assertSee('Metadata publisher');
        $magazine->update(['series_id' => null, 'page_count' => null, 'subtitle' => null]);
        $this->get(route('magazines.show', $magazine))->assertOk()->assertDontSee('>Series</dt>', false)->assertDontSee('>Pages</dt>', false);
        $this->get(route('magazines.index'))->assertOk()->assertDontSee('Metadata publisher');
    }
}
