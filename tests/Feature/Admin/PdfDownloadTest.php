<?php

namespace Tests\Feature\Admin;

use App\Http\Middleware\IsAdmin;
use App\Models\Banknote;
use App\Models\BanknoteSeries;
use App\Models\BanknoteTimePeriod;
use App\Models\Book;
use App\Models\Coin;
use App\Models\Country;
use App\Models\Currency;
use App\Models\Item;
use App\Models\Magazine;
use App\Models\Newspaper;
use App\Models\NominalValue;
use App\Models\Postcard;
use App\Models\Stamp;
use App\Models\User;
use Illuminate\Auth\Middleware\Authorize;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\TestCase;

class PdfDownloadTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->withoutMiddleware(IsAdmin::class);
        $this->withoutMiddleware(Authorize::class);
    }

    private function makeAdminUser(): User
    {
        $roleId = DB::table('roles')->where('name', 'Admin')->value('id')
            ?? DB::table('roles')->insertGetId([
                'name' => 'Admin',
                'created_at' => now(),
                'updated_at' => now(),
            ]);

        return User::factory()->create(['role_id' => $roleId]);
    }

    private static function lookupIds(): array
    {
        return [
            'country_id' => Country::create(['name' => 'Germany'])->id,
            'currency_id' => Currency::create(['name' => 'Reichsmark'])->id,
            'nominal_value_id' => NominalValue::create(['name' => '10'])->id,
        ];
    }

    /** @return iterable<string, array{string, \Closure}> */
    public static function typeProvider(): iterable
    {
        yield 'books' => ['books', fn () => Book::create(['title' => 'Test Book'])];
        yield 'items' => ['items', fn () => Item::create(['title' => 'Test Item'])];
        yield 'magazines' => ['magazines', fn () => Magazine::create(['title' => 'Test Magazine'])];
        yield 'newspapers' => ['newspapers', fn () => Newspaper::create(['title' => 'Test Newspaper'])];
        yield 'postcards' => ['postcards', fn () => Postcard::create(['year' => 1943])];
        yield 'stamps' => ['stamps', fn () => Stamp::create(['year' => 1943])];
        yield 'banknotes' => ['banknotes', function () {
            return Banknote::create(self::lookupIds() + [
                'series_id' => BanknoteSeries::create(['name' => 'Series A'])->id,
                'time_period_id' => BanknoteTimePeriod::create(['name' => '1933-1945'])->id,
            ]);
        }];
        yield 'coins' => ['coins', fn () => Coin::create(self::lookupIds())];
    }

    #[DataProvider('typeProvider')]
    public function test_pdf_download_works_for_each_type(string $type, \Closure $makeModel): void
    {
        $this->actingAs($this->makeAdminUser());

        $model = $makeModel();

        $response = $this->get(route('admin.pdf', [$type, $model->id]));

        $response->assertOk();
        $response->assertHeader('content-type', 'application/pdf');
    }

    public function test_pdf_download_returns_404_for_unknown_type(): void
    {
        $this->actingAs($this->makeAdminUser());

        $response = $this->get(route('admin.pdf', ['not-a-real-type', 1]));

        $response->assertNotFound();
    }

    public function test_pdf_download_returns_404_for_missing_record(): void
    {
        $this->actingAs($this->makeAdminUser());

        $response = $this->get(route('admin.pdf', ['books', 999999]));

        $response->assertNotFound();
    }
}
