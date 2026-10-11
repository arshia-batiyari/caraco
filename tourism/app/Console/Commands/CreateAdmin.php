<?php

namespace App\Console\Commands;

use App\Models\User;
use Illuminate\Console\Command;
use Illuminate\Support\Str;

class CreateAdmin extends Command
{
    protected $signature = 'karako:admin {email} {--name=Administrator} {--password=}';

    protected $description = 'Create or update a tourism-site administrator';

    public function handle(): int
    {
        $email = mb_strtolower(trim((string) $this->argument('email')));
        $password = (string) ($this->option('password') ?: Str::password(20));

        User::query()->updateOrCreate(
            ['email' => $email],
            [
                'name' => (string) $this->option('name'),
                'password' => $password,
                'role' => 'admin',
            ]
        );

        $this->info('Administrator saved for '.$email);
        if (! $this->option('password')) {
            $this->line('Password: '.$password);
        }

        return self::SUCCESS;
    }
}
