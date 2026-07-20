<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Http\Request;

// 💡 Vercel serverless eke filesystem read-only — /tmp විතරයි writable
// Laravel runtime-writable cache files /tmp වලට redirect කරනවා
if (isset($_ENV['VERCEL']) || isset($_SERVER['VERCEL'])) {
    $tmpDir = '/tmp/laravel';

    // Create all required writable directories in /tmp
    foreach ([
        $tmpDir,
        $tmpDir . '/storage/framework/cache/data',
        $tmpDir . '/storage/framework/sessions',
        $tmpDir . '/storage/framework/views',
        $tmpDir . '/storage/logs',
    ] as $dir) {
        if (!is_dir($dir)) {
            mkdir($dir, 0775, true);
        }
    }

    // 💡 Laravel's normalizeCachePath() reads these env vars to locate cache files.
    // We redirect all runtime-written cache files to /tmp.
    // packages.php stays at the pre-built read-only path — no env var needed for it.
    $cacheEnvVars = [
        'APP_SERVICES_CACHE' => $tmpDir . '/services.php',
        'APP_CONFIG_CACHE'   => $tmpDir . '/config.php',
        'APP_ROUTES_CACHE'   => $tmpDir . '/routes-v7.php',
        'APP_EVENTS_CACHE'   => $tmpDir . '/events.php',
    ];

    foreach ($cacheEnvVars as $key => $path) {
        putenv("{$key}={$path}");
        $_ENV[$key]    = $path;
        $_SERVER[$key] = $path;
    }
}

$app = Application::configure(basePath: dirname(__DIR__))->withRouting(
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

        // 💡 ලාරවෙල් වල response() පාවිච්චි නොකර, සැබෑ PHP echo/exit හරහා සැබෑ වැරැද්ද පෙන්වීම
        $exceptions->render(function (\Throwable $e, Request $request) {
            $html = "<div style='background:#fff5f5; border:1px solid #fc8181; padding:30px; border-radius:12px; font-family:sans-serif; max-width:800px; margin:40px auto; color:#2d3748; text-align: left;'>" .
                "<h2 style='color:#e53e3e; margin-top:0;'>💥 Laravel Original Error</h2>" .
                "<p><strong>Message:</strong> <span style='color:#e53e3e;'>" . htmlspecialchars($e->getMessage()) . "</span></p>" .
                "<p><strong>File:</strong> " . htmlspecialchars($e->getFile()) . " on line <strong>" . $e->getLine() . "</strong></p>" .
                "<h3 style='margin-bottom:8px; margin-top:20px;'>Stack Trace:</h3>" .
                "<pre style='background:#1a202c; color:#9ae6b4; padding:15px; border-radius:8px; overflow-x:auto; font-size:0.85rem; line-height:1.5;'>" . htmlspecialchars($e->getTraceAsString()) . "</pre>" .
                "</div>";

            echo $html;
            exit(1); // 👈 💡 සර්වර් එක මෙතනින් නවත්වනවා (No Laravel Response Dependency!)
        });

    })->create();

// 💡 useStoragePath() is a real Laravel method — redirect storage to /tmp
if (isset($_ENV['VERCEL']) || isset($_SERVER['VERCEL'])) {
    $app->useStoragePath('/tmp/laravel/storage');
}

return $app;