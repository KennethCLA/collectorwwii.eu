<?php

namespace Tests\Feature\Public;

use App\Models\Coin;
use App\Models\Country;
use App\Models\Currency;
use App\Models\NominalValue;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CoinPublicTest extends TestCase
{
    use RefreshDatabase;

    private function makeCoin(): Coin
    {
        return Coin::create([
            'country_id' => Country::create(['name' => 'Germany'])->id,
            'currency_id' => Currency::create(['name' => 'Reichsmark'])->id,
            'nominal_value_id' => NominalValue::create(['name' => '5'])->id,
            'year' => 1940,
        ]);
    }

    public function test_index_loads_with_200(): void
    {
        $response = $this->get(route('coins.index'));

        $response->assertOk();
    }

    public function test_show_loads_with_200_for_existing_coin(): void
    {
        $coin = $this->makeCoin();

        $response = $this->get(route('coins.show', $coin));

        $response->assertOk();
    }

    public function test_show_returns_404_for_missing_coin(): void
    {
        $response = $this->get(route('coins.show', 999999));

        $response->assertNotFound();
    }

    public function test_default_sort_matches_newest_and_preserves_explicit_sort_and_filters(): void
    {
        $older = $this->makeCoin();
        $older->forceFill(['created_at' => '2020-01-01', 'for_sale' => true])->save();
        $newer = $older->replicate();
        $newer->forceFill(['created_at' => '2024-01-01'])->save();
        $excluded = $older->replicate();
        $excluded->forceFill(['for_sale' => false])->save();

        $params = ['for_sale' => 1];
        $default = $this->get(route('coins.index', $params))->assertOk();
        $explicit = $this->get(route('coins.index', $params + ['sort' => 'created_at_asc']))->assertOk();
        $this->assertSame([$newer->id, $older->id], $default->viewData('coins')->pluck('id')->all());
        $this->assertSame($explicit->viewData('coins')->pluck('id')->all(), $default->viewData('coins')->pluck('id')->all());
        $oldest = $this->get(route('coins.index', $params + ['sort' => 'created_at_desc']))->assertOk();
        $this->assertSame([$older->id, $newer->id], $oldest->viewData('coins')->pluck('id')->all());
        $url = $oldest->viewData('coins')->url(2);
        $this->assertStringContainsString('sort=created_at_desc', $url);
        $this->assertStringContainsString('for_sale=1', $url);
        $default->assertSee('value="created_at_asc" selected', false);
    }
}
