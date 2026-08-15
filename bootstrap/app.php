<?php

use App\Http\Middleware\AccountApprovedMiddleware;
use App\Http\Middleware\OnboardingCompleteMiddleware;
use App\Http\Middleware\RoleMiddleware;
use App\Http\Middleware\SetLocale;
use App\Http\Middleware\WorkerActiveMiddleware;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        api: __DIR__.'/../routes/api.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        $middleware->alias([
            'locale'              => SetLocale::class,
            'role'                => RoleMiddleware::class,
            'worker.active'       => WorkerActiveMiddleware::class,
            'onboarding.complete' => OnboardingCompleteMiddleware::class,
            'account.approved'    => AccountApprovedMiddleware::class,
        ]);

        $middleware->web(append: [
            SetLocale::class,
        ]);

        $middleware->api(append: [
            SetLocale::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        //
    })->create();
