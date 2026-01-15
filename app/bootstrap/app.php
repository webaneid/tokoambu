<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Spatie\Permission\PermissionServiceProvider;
use Spatie\Permission\Middleware\RoleMiddleware;
use Spatie\Permission\Middleware\PermissionMiddleware;
use Spatie\Permission\Middleware\RoleOrPermissionMiddleware;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        api: __DIR__.'/../routes/api.php',
        commands: __DIR__.'/../routes/console.php',
        then: function ($router) {
            require __DIR__.'/../routes/warehouse.php';
            require __DIR__.'/../routes/storefront.php';
        },
        health: '/up',
    )
    ->withProviders([
        App\Providers\AppServiceProvider::class,
        App\Providers\EventServiceProvider::class,
        PermissionServiceProvider::class,
    ])
    ->withSchedule(function (Illuminate\Console\Scheduling\Schedule $schedule) {
        $schedule->job(new App\Jobs\ComputeDeadStockStatusJob())->daily();
        $schedule->job(new App\Jobs\SyncShipmentTrackingJob())->dailyAt('07:00');
        $schedule->job(new App\Jobs\SyncShipmentTrackingJob())->dailyAt('09:00');
        $schedule->job(new App\Jobs\SyncShipmentTrackingJob())->dailyAt('12:00');
        $schedule->job(new App\Jobs\SyncShipmentTrackingJob())->dailyAt('15:00');
    })
    ->withMiddleware(function (Middleware $middleware): void {
        $middleware->alias([
            'role' => RoleMiddleware::class,
            'permission' => PermissionMiddleware::class,
            'role_or_permission' => RoleOrPermissionMiddleware::class,
        ]);
        $middleware->validateCsrfTokens(except: [
            '/ipaymu/notify',
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        //
    })->create();
