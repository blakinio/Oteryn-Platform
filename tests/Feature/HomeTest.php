<?php

namespace Tests\Feature;

use Tests\TestCase;

class HomeTest extends TestCase
{
    public function test_home_page_renders_the_blade_foundation(): void
    {
        $response = $this->get('/');

        $response->assertOk();
        $response->assertSee('Oteryn Platform');
        $response->assertSee('Laravel 13 foundation is online.');
    }
}
