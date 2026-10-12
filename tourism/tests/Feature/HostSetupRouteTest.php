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
            ->assertSee('migrate --force')
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
        $this->withIsolatedSqlite('testing-setup-tourism.sqlite', function () {
            $this->get('/setup')
                ->assertOk()
                ->assertSee('create_tourism_content_tables')
                ->assertDontSee('Seeding database');

            $this->assertTrue(Schema::hasTable('settings'));

            $this->get('/setup')
                ->assertOk()
                ->assertSee('Nothing to migrate');
        });
    }

    public function test_setup_continues_when_users_already_exists(): void
    {
        $this->withIsolatedSqlite('testing-setup-tourism-partial.sqlite', function () {
            Schema::create('users', function (\Illuminate\Database\Schema\Blueprint $table) {
                $table->id();
                $table->string('name');
                $table->string('email', 255)->unique();
                $table->string('password');
                $table->timestamps();
            });

            $this->get('/setup')
                ->assertOk()
                ->assertDontSee('SQLSTATE')
                ->assertSee('create_tourism_content_tables');

            $this->assertTrue(Schema::hasTable('users'));
            $this->assertTrue(Schema::hasTable('password_reset_tokens'));
            $this->assertTrue(Schema::hasTable('sessions'));
            $this->assertTrue(Schema::hasTable('settings'));
        });
    }

    public function test_indexed_strings_stay_within_the_shared_host_key_limit(): void
    {
        $this->assertSame(191, \Illuminate\Database\Schema\Builder::$defaultStringLength);
    }

    private function withIsolatedSqlite(string $filename, callable $callback): void
    {
        $path = storage_path('framework/'.$filename);
        if (is_file($path)) {
            unlink($path);
        }
        touch($path);

        $originalDatabase = config('database.connections.sqlite.database');
        $originalPdo = DB::connection('sqlite')->getPdo();

        config([
            'database.connections.sqlite.database' => $path,
        ]);
        DB::purge('sqlite');

        try {
            $callback();
        } finally {
            while ($originalPdo->inTransaction()) {
                $originalPdo->rollBack();
            }
            DB::disconnect('sqlite');
            config(['database.connections.sqlite.database' => $originalDatabase]);
            DB::purge('sqlite');
            DB::connection('sqlite')->setPdo($originalPdo);
            if (is_file($path)) {
                unlink($path);
            }
        }
    }
}
