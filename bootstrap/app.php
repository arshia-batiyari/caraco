<?php

use App\Http\Middleware\EnsureUserHasRole;
use App\Http\Middleware\SetLocale;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware) {
        $middleware->alias([
            'role' => EnsureUserHasRole::class,
        ]);

        // این میدل‌ور روی همه‌ی درخواست‌های سایت اجرا می‌شه تا سوییچ
        // زبان (fa/en) که تو هدر سایت هست واقعاً کار کنه.
        $middleware->web(append: [SetLocale::class]);
    })
    ->withExceptions(function (Exceptions $exceptions) {
        //
    })->create();
