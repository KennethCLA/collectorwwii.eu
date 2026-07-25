<?php

namespace Tests\Feature\Public;

use App\Models\MapLocation;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class MapPublicTest extends TestCase
{
    use RefreshDatabase;

    public function test_map_page_loads_with_200(): void
    {
        $response = $this->get(route('map.index'));

        $response->assertOk();
    }

    public function test_map_includes_locations_with_valid_coordinates(): void
    {
        MapLocation::create([
            'name' => 'Berchtesgaden',
            'coordinates' => '47.6304, 13.0007',
        ]);

        $response = $this->get(route('map.index'));

        $response->assertOk();
        $response->assertSee('Berchtesgaden');
    }

    public function test_map_excludes_locations_with_invalid_coordinates(): void
    {
        MapLocation::create([
            'name' => 'No Coordinates Location',
            'coordinates' => 'not-a-coordinate',
        ]);

        $response = $this->get(route('map.index'));

        $response->assertOk();
        $response->assertDontSee('No Coordinates Location');
    }
}
