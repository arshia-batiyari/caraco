<?php

namespace Tests\Feature;

use Tests\TestCase;

class PrepareHostCommandTest extends TestCase
{
    public function test_prepare_keeps_an_existing_key_and_uses_a_relative_storage_link(): void
    {
        $env = base_path('.env');
        $this->assertFileExists($env);
        $before = file_get_contents($env);

        $this->artisan('karako:prepare')->assertSuccessful();
        $this->artisan('karako:prepare')->assertSuccessful();

        $this->assertSame($before, file_get_contents($env));

        $link = public_path('storage');
        $this->assertTrue(is_link($link));
        $target = readlink($link);
        $this->assertIsString($target);
        $this->assertFalse(str_starts_with($target, '/'));
        $this->assertSame(realpath(storage_path('app/public')), realpath($link));
    }

    public function test_prepare_writes_a_key_when_the_env_line_is_empty(): void
    {
        $env = base_path('.env');
        $before = file_get_contents($env);
        $original = config('app.key');
        config(['app.key' => '']);
        file_put_contents($env, preg_replace('/^APP_KEY=.*$/m', 'APP_KEY=', (string) $before));

        try {
            $this->artisan('karako:prepare')->assertSuccessful();
            $after = (string) file_get_contents($env);
            $this->assertMatchesRegularExpression('/^APP_KEY=base64:[A-Za-z0-9+\/=]+$/m', $after);
        } finally {
            file_put_contents($env, (string) $before);
            config(['app.key' => $original]);
        }
    }
}
