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
        
        // Custom Role Middleware
        $middleware->alias([
            'role' => \App\Http\Middleware::CheckRole::class,
        ]);

    })
    ->withExceptions(function (Exceptions $exceptions): void {
        
        // 💡 අපි අර තාවකාලිකව දාපු Try-Catch එක අයින් කරලා ලාරවෙල් වල default 
        // ආරක්ෂිත වගේම ලස්සන Exception Handler එක සක්‍රීය කළා.
        $exceptions->shouldRenderJsonWhen(
            fn (Request $request) => $request->is('api/*'),
        );

    })->create();