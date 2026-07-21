<?php

namespace Tests\Feature;

use Tests\TestCase;

class HomeTest extends TestCase
{
    public function test_home_page_renders_the_player_facing_portal(): void
    {
        $response = $this->get('/');

        $response->assertOk();
        $response->assertSee('Oteryn Platform');
        $response->assertSee('Find a character');
        $response->assertSee('World information');
    }
}
