<?php

namespace App\Support;

final class EnsureAppKey
{
    public static bool $generated = false;

    public static ?string $error = null;

    public static function ensure(string $envPath, ?string $currentKey): string
    {
        self::$generated = false;
        self::$error = null;

        if (is_string($currentKey) && $currentKey !== '') {
            return $currentKey;
        }

        $key = self::readOrWrite($envPath);
        self::forgetCachedConfig($envPath);

        return $key;
    }

    private static function readOrWrite(string $envPath): string
    {
        $generated = 'base64:'.base64_encode(random_bytes(32));

        if (! is_file($envPath)) {
            self::$error = 'The .env file is missing, so APP_KEY was not saved.';

            return $generated;
        }

        $contents = file_get_contents($envPath);
        if ($contents === false) {
            self::$error = 'The .env file could not be read, so APP_KEY was not saved.';

            return $generated;
        }

        if (preg_match('/^APP_KEY=(.*)$/m', $contents, $matches) !== 1) {
            self::$error = 'APP_KEY could not be written. The .env file has no APP_KEY line.';

            return $generated;
        }

        $inline = self::unwrap((string) $matches[1]);
        if ($inline !== '') {
            return $inline;
        }

        $replaced = preg_replace('/^APP_KEY=.*$/m', 'APP_KEY='.$generated, $contents, 1, $count);
        if ($replaced === null || $count !== 1 || file_put_contents($envPath, $replaced, LOCK_EX) === false) {
            self::$error = 'APP_KEY could not be written. Make the .env file writable by PHP.';

            return $generated;
        }

        self::$generated = true;

        return $generated;
    }

    private static function unwrap(string $value): string
    {
        $value = trim($value);
        if (
            (str_starts_with($value, '"') && str_ends_with($value, '"'))
            || (str_starts_with($value, "'") && str_ends_with($value, "'"))
        ) {
            return substr($value, 1, -1);
        }

        return $value;
    }

    private static function forgetCachedConfig(string $envPath): void
    {
        $cache = dirname($envPath).'/bootstrap/cache/config.php';
        if (! is_file($cache)) {
            return;
        }

        if (! unlink($cache) && self::$error === null) {
            self::$error = 'APP_KEY is set, but bootstrap/cache/config.php could not be removed. Delete that file on the host.';
        }
    }
}
