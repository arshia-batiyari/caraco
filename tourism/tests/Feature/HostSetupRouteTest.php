<?php

namespace Tests\Feature;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
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
            ->assertSee('Nothing to migrate')
            ->assertSee('public/storage link already exists.');

        $this->assertSame($before, file_get_contents($env));

        $this->get('/robots.txt')
            ->assertOk()
            ->assertSee('Disallow: /setup', false);
    }

    public function test_missing_page_renders_when_settings_table_is_absent(): void
    {
        Schema::drop('settings');

        $this->get('/this-page-is-not-real')
            ->assertNotFound();
    }

    public function test_setup_migrates_an_empty_database(): void
    {
        $path = storage_path('framework/testing-setup-tourism.sqlite');
        if (is_file($path)) {
            unlink($path);
        }
        touch($path);

        config([
            'database.connections.sqlite.database' => $path,
        ]);
        DB::purge('sqlite');

        try {
            $this->get('/setup')
                ->assertOk()
                ->assertSee('create_tourism_content_tables')
                ->assertDontSee('Seeding database');

            $this->assertTrue(Schema::hasTable('settings'));

            $this->get('/setup')
                ->assertOk()
                ->assertSee('Nothing to migrate');
        } finally {
            DB::disconnect('sqlite');
            if (is_file($path)) {
                unlink($path);
            }
        }
    }
}
