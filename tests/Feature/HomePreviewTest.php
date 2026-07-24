<?php

namespace Tests\Feature;

use Tests\TestCase;

class HomePreviewTest extends TestCase
{
    public function test_isolated_homepage_design_preview_renders(): void
    {
        $response = $this->get('/design/home-v2');

        $response->assertOk();
        $response->assertSee('Design preview.');
        $response->assertSee('Answer the call of Oteryn');
        $response->assertSee('Find your character');
        $response->assertSee('css/home-preview.css', false);
        $response->assertSee('noindex,nofollow', false);
    }

    public function test_current_homepage_remains_the_default_root_view(): void
    {
        $response = $this->get('/');

        $response->assertOk();
        $response->assertSee('Enter the world of Oteryn');
        $response->assertDontSee('Design preview.');
        $response->assertDontSee('css/home-preview.css', false);
    }
}
