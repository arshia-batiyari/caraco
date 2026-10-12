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

    public function test_missing_page_renders_when_profile_translations_is_absent(): void
    {
        Schema::drop('profile_translations');

        $this->get('/this-page-is-not-real')
            ->assertNotFound();
    }

    public function test_setup_migrates_an_empty_database_and_seeds_divisions_once(): void
    {
        $path = storage_path('framework/testing-setup-holding.sqlite');
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
                ->assertSee('create_profile_translations_table')
                ->assertSee('Seeding database');

            $this->assertTrue(Schema::hasTable('profile_translations'));
            $this->assertSame(9, \App\Models\Division::query()->count());

            $this->get('/setup')
                ->assertOk()
                ->assertSee('Nothing to migrate')
                ->assertDontSee('Seeding database');

            $this->assertSame(9, \App\Models\Division::query()->count());
        } finally {
            DB::disconnect('sqlite');
            if (is_file($path)) {
                unlink($path);
            }
        }
    }
}
