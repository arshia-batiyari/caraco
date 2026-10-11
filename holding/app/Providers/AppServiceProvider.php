<?php

namespace App\Providers;

use App\Models\User;
use App\Support\SecureUrls;
use App\View\SiteComposer;
use Illuminate\Pagination\Paginator;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\View;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        $path = config('karako.public_path');
        if (is_string($path) && $path !== '') {
            $this->app->usePublicPath($path);
        }
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        SecureUrls::apply();
        Paginator::defaultView('pagination.karako');
        Paginator::defaultSimpleView('pagination.karako');
        View::composer('layouts.site', SiteComposer::class);

        Gate::define('edit-content', function (User $user): bool {
            return in_array($user->role, ['admin', 'editor'], true);
        });
    }
}
