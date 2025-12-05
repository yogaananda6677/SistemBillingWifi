<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        $middleware->alias([
            'auth' => \Illuminate\Auth\Middleware\Authenticate::class,
            'admin' => \App\Http\Middleware\Admin::class,
            'sales' => \App\Http\Middleware\Sales::class,
        ]);
    })

    ->withSchedule(function (\Illuminate\Console\Scheduling\Schedule $schedule) {
        $schedule->command('tagihan:generate')
            ->monthlyOn(1, '00:05')
            ->timezone('Asia/Jakarta')
            ->withoutOverlapping()
            ->runInBackground();
    })

    ->withExceptions(function (Exceptions $exceptions): void {
        //
    })->create();
