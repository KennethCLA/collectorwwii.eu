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
}
