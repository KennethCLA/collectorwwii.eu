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
}
