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
}
