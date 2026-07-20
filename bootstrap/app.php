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
        
        // 💡 අපේ Custom Role Middleware එක ලාරවෙල් වලට හඳුන්වා දීම (Register Alias)
        $middleware->alias([
            'role' => \App\Http\Middleware\CheckRole::class,
        ]);

    })
   ->withExceptions(function (Exceptions $exceptions): void {
        // 💡 Laravel Exception Handler එකට කියනවා වැරැද්දක් වුණු සැනින් 
        // HTML views හොයන්නේ නැතුව කෙලින්ම සැබෑ වැරැද්ද (Original Error) Dump කරන්න කියලා.
        $exceptions->render(function (\Throwable $e, Request $request) {
            return response()->make(
                "<div style='background:#fff5f5; border:1px solid #fc8181; padding:30px; border-radius:12px; font-family:sans-serif; max-width:800px; margin:40px auto; color:#2d3748; text-align: left;'>" .
                "<h2 style='color:#e53e3e; margin-top:0;'>💥 Laravel Original Error</h2>" .
                "<p><strong>Message:</strong> <span style='color:#e53e3e;'>" . htmlspecialchars($e->getMessage()) . "</span></p>" .
                "<p><strong>File:</strong> " . htmlspecialchars($e->getFile()) . " on line <strong>" . $e->getLine() . "</strong></p>" .
                "<h3 style='margin-bottom:8px; margin-top:20px;'>Stack Trace:</h3>" .
                "<pre style='background:#1a202c; color:#9ae6b4; padding:15px; border-radius:8px; overflow-x:auto; font-size:0.85rem; line-height:1.5;'>" . htmlspecialchars($e->getTraceAsString()) . "</pre>" .
                "</div>",
                500
            );
        });
    })->create();