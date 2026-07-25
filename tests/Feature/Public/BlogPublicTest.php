<?php

namespace Tests\Feature\Public;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class BlogPublicTest extends TestCase
{
    use RefreshDatabase;

    public function test_home_page_loads_with_200(): void
    {
        $response = $this->get(route('home'));

        $response->assertOk();
    }

    public function test_blog_page_loads_with_200(): void
    {
        $response = $this->get(route('blog'));

        $response->assertOk();
    }
}
