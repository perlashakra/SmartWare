<?php

use App\Http\Middleware\ClientMiddleware;
use App\Http\Middleware\RoleMiddleware;
use App\Http\Middleware\SetLocale;
use App\Http\Middleware\SuperAdminMiddleware;
use App\Http\Middleware\WarehouseAdminMiddleware;
use App\Http\Middleware\WorkerMiddleware;
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
        // ✅ CORRECT: Pass all aliases in a single array
        $middleware->alias([
            'locale'          => SetLocale::class,
            'role'            => RoleMiddleware::class,
            'super_admin'     => SuperAdminMiddleware::class,
            'warehouse_admin' => WarehouseAdminMiddleware::class,
            'worker'          => WorkerMiddleware::class,
            'client'          => ClientMiddleware::class,
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
