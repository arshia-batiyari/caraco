<?php

namespace Tests\Unit;

use App\Support\EnsureAppKey;
use PHPUnit\Framework\TestCase;

class EnsureAppKeyTest extends TestCase
{
    private string $directory = '';

    protected function tearDown(): void
    {
        if ($this->directory !== '' && is_dir($this->directory)) {
            $cache = $this->directory.'/bootstrap/cache/config.php';
            if (is_file($cache)) {
                unlink($cache);
            }
            if (is_file($this->directory.'/.env')) {
                unlink($this->directory.'/.env');
            }
            @rmdir($this->directory.'/bootstrap/cache');
            @rmdir($this->directory.'/bootstrap');
            @rmdir($this->directory);
        }

        parent::tearDown();
    }

    public function test_an_empty_key_line_is_written_once(): void
    {
        $env = $this->envFile("APP_NAME=KARAKO\nAPP_KEY=\n");
        file_put_contents($this->directory.'/bootstrap/cache/config.php', '<?php return [];');

        $key = EnsureAppKey::ensure($env, '');

        $this->assertTrue(EnsureAppKey::$generated);
        $this->assertNull(EnsureAppKey::$error);
        $this->assertMatchesRegularExpression('/^base64:[A-Za-z0-9+\/=]+$/', $key);
        $this->assertStringContainsString('APP_KEY='.$key."\n", (string) file_get_contents($env));
        $this->assertFileDoesNotExist($this->directory.'/bootstrap/cache/config.php');

        $again = EnsureAppKey::ensure($env, '');

        $this->assertFalse(EnsureAppKey::$generated);
        $this->assertSame($key, $again);
        $this->assertSame(1, preg_match_all('/^APP_KEY=/m', (string) file_get_contents($env)));
    }

    public function test_a_key_already_in_the_file_is_not_replaced_when_config_is_empty(): void
    {
        $env = $this->envFile("APP_KEY=base64:existingkeyvalue\n");
        $before = file_get_contents($env);

        $key = EnsureAppKey::ensure($env, null);

        $this->assertSame('base64:existingkeyvalue', $key);
        $this->assertFalse(EnsureAppKey::$generated);
        $this->assertNull(EnsureAppKey::$error);
        $this->assertSame($before, file_get_contents($env));
    }

    public function test_a_configured_key_leaves_the_file_untouched(): void
    {
        $env = $this->envFile("APP_KEY=\n");
        $before = file_get_contents($env);

        $key = EnsureAppKey::ensure($env, 'base64:alreadyloaded');

        $this->assertSame('base64:alreadyloaded', $key);
        $this->assertFalse(EnsureAppKey::$generated);
        $this->assertSame($before, file_get_contents($env));
    }

    public function test_a_missing_key_line_is_reported_and_a_request_key_is_still_returned(): void
    {
        $env = $this->envFile("APP_NAME=KARAKO\n");

        $key = EnsureAppKey::ensure($env, '');

        $this->assertFalse(EnsureAppKey::$generated);
        $this->assertSame('APP_KEY could not be written. The .env file has no APP_KEY line.', EnsureAppKey::$error);
        $this->assertMatchesRegularExpression('/^base64:/', $key);
        $this->assertSame("APP_NAME=KARAKO\n", file_get_contents($env));
    }

    private function envFile(string $contents): string
    {
        $this->directory = sys_get_temp_dir().'/karako-key-'.bin2hex(random_bytes(4));
        mkdir($this->directory.'/bootstrap/cache', 0777, true);
        $path = $this->directory.'/.env';
        file_put_contents($path, $contents);

        return $path;
    }
}
