<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Http\Request;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        $middleware->web(append: [
            \App\Http\Middleware\SetLocale::class,
        ]);

        $middleware->alias([
            'admin.active' => \App\Http\Middleware\EnsureAdminIsActive::class,
            'owner' => \App\Http\Middleware\EnsureOwner::class,
            'profile.complete' => \App\Http\Middleware\EnsureProfileIsComplete::class,
        ]);

        // ยังไม่ได้ล็อกอินฝั่งแอดมิน ให้ไปหน้าล็อกอินแอดมิน
        // ต้องเช็ค 'admin' ด้วย เพราะหน้าแดชบอร์ดคือ /admin เฉยๆ ไม่มี segment ต่อท้าย
        $middleware->redirectGuestsTo(fn (Request $request) => $request->is('admin', 'admin/*')
            ? route('admin.login')
            : route('customer.login'));
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        $exceptions->shouldRenderJsonWhen(
            fn (Request $request) => $request->is('api/*') || $request->expectsJson(),
        );
    })->create();
