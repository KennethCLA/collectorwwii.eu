<?php

namespace Tests\Feature\Public;

use App\Models\Banknote;
use App\Models\BanknoteSeries;
use App\Models\BanknoteTimePeriod;
use App\Models\Country;
use App\Models\Currency;
use App\Models\NominalValue;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class BanknotePublicTest extends TestCase
{
    use RefreshDatabase;

    private function makeBanknote(): Banknote
    {
        return Banknote::create([
            'country_id' => Country::create(['name' => 'Germany'])->id,
            'currency_id' => Currency::create(['name' => 'Reichsmark'])->id,
            'nominal_value_id' => NominalValue::create(['name' => '10'])->id,
            'series_id' => BanknoteSeries::create(['name' => 'Series A'])->id,
            'time_period_id' => BanknoteTimePeriod::create(['name' => '1933-1945'])->id,
            'year' => 1940,
        ]);
    }

    public function test_index_loads_with_200(): void
    {
        $response = $this->get(route('banknotes.index'));

        $response->assertOk();
    }

    public function test_show_loads_with_200_for_existing_banknote(): void
    {
        $banknote = $this->makeBanknote();

        $response = $this->get(route('banknotes.show', $banknote));

        $response->assertOk();
    }

    public function test_show_returns_404_for_missing_banknote(): void
    {
        $response = $this->get(route('banknotes.show', 999999));

        $response->assertNotFound();
    }

    public function test_default_sort_matches_newest_and_preserves_explicit_sort_and_filters(): void
    {
        $older = $this->makeBanknote();
        $older->forceFill(['created_at' => '2020-01-01', 'for_sale' => true])->save();
        $newer = $older->replicate();
        $newer->forceFill(['created_at' => '2024-01-01'])->save();
        $excluded = $older->replicate();
        $excluded->forceFill(['for_sale' => false])->save();

        $params = ['for_sale' => 1];
        $default = $this->get(route('banknotes.index', $params))->assertOk();
        $explicit = $this->get(route('banknotes.index', $params + ['sort' => 'created_at_asc']))->assertOk();
        $this->assertSame([$newer->id, $older->id], $default->viewData('banknotes')->pluck('id')->all());
        $this->assertSame($explicit->viewData('banknotes')->pluck('id')->all(), $default->viewData('banknotes')->pluck('id')->all());
        $oldest = $this->get(route('banknotes.index', $params + ['sort' => 'created_at_desc']))->assertOk();
        $this->assertSame([$older->id, $newer->id], $oldest->viewData('banknotes')->pluck('id')->all());
        $url = $oldest->viewData('banknotes')->url(2);
        $this->assertStringContainsString('sort=created_at_desc', $url);
        $this->assertStringContainsString('for_sale=1', $url);
        $default->assertSee('value="created_at_asc" selected', false);
    }
}
