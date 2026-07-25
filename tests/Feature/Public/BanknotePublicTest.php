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
}
