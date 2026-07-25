<?php

namespace Tests\Feature\Public;

use App\Models\Postcard;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PostcardPublicTest extends TestCase
{
    use RefreshDatabase;

    public function test_index_loads_with_200(): void
    {
        $response = $this->get(route('postcards.index'));

        $response->assertOk();
    }

    public function test_show_loads_with_200_for_existing_postcard(): void
    {
        $postcard = Postcard::create(['year' => 1943]);

        $response = $this->get(route('postcards.show', $postcard));

        $response->assertOk();
    }

    public function test_show_returns_404_for_missing_postcard(): void
    {
        $response = $this->get(route('postcards.show', 999999));

        $response->assertNotFound();
    }
}
