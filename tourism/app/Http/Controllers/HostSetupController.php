<?php

namespace App\Http\Controllers;

use Illuminate\Http\Response;
use Illuminate\Support\Facades\Artisan;
use Throwable;

class HostSetupController extends Controller
{
    public function __invoke(): Response
    {
        $body = $this->keyGenerate()."\n\n".$this->migrate()."\n\n".$this->storageLink()."\n";

        return response($body, 200, [
            'Content-Type' => 'text/plain; charset=UTF-8',
        ]);
    }

    private function keyGenerate(): string
    {
        if (filled(config('app.key'))) {
            return 'APP_KEY is already set.';
        }

        try {
            Artisan::call('key:generate', ['--force' => true]);
            $output = trim(Artisan::output());

            return $output !== '' ? $output : 'key:generate finished.';
        } catch (Throwable $exception) {
            return $exception->getMessage();
        }
    }

    private function migrate(): string
    {
        try {
            Artisan::call('migrate', ['--force' => true]);
            $output = trim(Artisan::output());

            return $output !== '' ? $output : 'migrate finished.';
        } catch (Throwable $exception) {
            return $exception->getMessage();
        }
    }

    private function storageLink(): string
    {
        $link = public_path('storage');
        if (file_exists($link) || is_link($link)) {
            return 'public/storage link already exists.';
        }

        try {
            Artisan::call('storage:link');
            $output = trim(Artisan::output());

            return $output !== '' ? $output : 'storage:link finished.';
        } catch (Throwable $exception) {
            return $exception->getMessage();
        }
    }
}
