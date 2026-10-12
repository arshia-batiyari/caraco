<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

class PrepareHost extends Command
{
    protected $signature = 'karako:prepare';

    protected $description = 'Fill an empty APP_KEY and create the public storage link';

    public function handle(): int
    {
        if (! $this->ensureKey()) {
            return self::FAILURE;
        }

        if (! $this->ensureStorageLink()) {
            return self::FAILURE;
        }

        return self::SUCCESS;
    }

    private function ensureKey(): bool
    {
        $path = base_path('.env');
        if (! is_file($path)) {
            $example = base_path('.env.example');
            if (! is_file($example) || ! copy($example, $path)) {
                $this->error('Could not create .env from .env.example.');

                return false;
            }
            $this->info('Created .env from .env.example.');
        }

        if ($this->keyFrom($path) !== null) {
            $this->info('APP_KEY is already set.');

            return true;
        }

        $exit = $this->call('key:generate');
        if ($exit !== 0 || $this->keyFrom($path) === null) {
            $this->error('APP_KEY is still empty.');

            return false;
        }

        return true;
    }

    private function ensureStorageLink(): bool
    {
        $link = public_path('storage');
        $target = storage_path('app/public');

        if (! is_dir($target) && ! mkdir($target, 0755, true) && ! is_dir($target)) {
            $this->error('Could not create storage/app/public.');

            return false;
        }

        if (file_exists($link) && ! is_link($link)) {
            $this->warn('public/storage is a real directory, so it was left in place.');
            $this->line('Point FILESYSTEM_PUBLIC_ROOT at that directory if uploads should be stored there.');

            return true;
        }

        $relative = $this->relativePath($target, dirname($link));
        if (is_link($link) && readlink($link) === $relative) {
            $this->info('public/storage already uses a relative link.');

            return true;
        }

        if (is_link($link) && ! unlink($link)) {
            $this->error('Could not replace the existing public/storage link.');

            return false;
        }

        if (! $this->createLink($relative, $link)) {
            $this->error('The storage link was not created.');
            $this->line('In the file manager, create a normal directory named storage in the web root, set FILESYSTEM_PUBLIC_ROOT to its absolute path, and run php artisan config:cache.');

            return false;
        }

        $this->info('public/storage now points at '.$relative.'.');

        return true;
    }

    private function createLink(string $relative, string $link): bool
    {
        if (function_exists('symlink') && @symlink($relative, $link)) {
            return true;
        }

        if (! function_exists('exec')) {
            return false;
        }

        exec('ln -s '.escapeshellarg($relative).' '.escapeshellarg($link), $output, $code);

        return $code === 0 && is_link($link);
    }

    private function relativePath(string $target, string $fromDirectory): string
    {
        $targetParts = array_values(array_filter(explode('/', str_replace('\\', '/', $target)), fn (string $part): bool => $part !== ''));
        $fromParts = array_values(array_filter(explode('/', str_replace('\\', '/', $fromDirectory)), fn (string $part): bool => $part !== ''));

        while ($targetParts !== [] && $fromParts !== [] && strcasecmp($targetParts[0], $fromParts[0]) === 0) {
            array_shift($targetParts);
            array_shift($fromParts);
        }

        return str_repeat('../', count($fromParts)).implode('/', $targetParts);
    }

    private function keyFrom(string $path): ?string
    {
        $contents = file_get_contents($path);
        if (! is_string($contents) || preg_match('/^APP_KEY=(.*)$/m', $contents, $matches) !== 1) {
            return null;
        }

        $value = trim($matches[1], " \t\"'");

        return $value === '' ? null : $value;
    }
}
