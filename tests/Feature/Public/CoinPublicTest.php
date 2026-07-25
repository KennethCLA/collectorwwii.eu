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
}
