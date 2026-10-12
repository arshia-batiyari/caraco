<?php

namespace Tests\Feature;

use Tests\TestCase;

class HostSetupRouteTest extends TestCase
{
    public function test_setup_runs_key_generate_and_storage_link_without_replacing_a_key(): void
    {
        $env = base_path('.env');
        $before = file_get_contents($env);

        $this->get('/setup')
            ->assertOk()
            ->assertHeader('Content-Type', 'text/plain; charset=UTF-8')
            ->assertSee('APP_KEY is already set.')
            ->assertSee('public/storage link already exists.');

        $this->assertSame($before, file_get_contents($env));

        $this->get('/robots.txt')
            ->assertOk()
            ->assertSee('Disallow: /setup', false);
    }
}
