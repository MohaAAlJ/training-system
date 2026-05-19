<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use App\Http\Middleware\CheckMaintenanceMode;
use App\Services\Telegram\TelegramMonitorService;
use Illuminate\Support\Facades\Cache;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__ . '/../routes/web.php',
        api: __DIR__ . '/../routes/api.php',
        commands: __DIR__ . '/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        $middleware->validateCsrfTokens(except: [
            '/telegram/webhook', // Allow Telegram to post updates
            '/whatsapp/webhook', // Allow WhatsApp to post updates
            '/api/*',            // Allow API requests without CSRF
        ]);
        // Removed statefulApi() to prevent Admin session cookies from interfering with MOH API tokens
        $middleware->trustProxies(at: '*');
        $middleware->append(\App\Http\Middleware\SetCspHeaders::class);
        $middleware->web(append: [
            CheckMaintenanceMode::class,
            'throttle:60,1',
        ]);
        $middleware->api(append: [
            'throttle:30,1', // Max 30 requests per minute for API
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        // Report exceptions to Telegram
        $exceptions->report(function (Throwable $e) {
            try {
                // Increment daily error counter
                $errorCount = Cache::get('telegram_errors_today', 0);
                Cache::put('telegram_errors_today', $errorCount + 1, now()->endOfDay());

                // Send to Telegram (async to not block the request)
                $telegram = app(TelegramMonitorService::class);
                $telegram->sendError($e);
            } catch (Throwable $reportError) {
                // Silently fail - don't break the app if reporting fails (e.g. DB/Cache down)
                try {
                    \Log::warning('Failed to report error to Telegram/Cache: ' . $reportError->getMessage());
                } catch (Throwable $logError) {
                    // Even logging might fail if disk/cache is full/down
                }
            }
        });
    })->create();
