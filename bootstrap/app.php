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
        commands: __DIR__ . '/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        $middleware->validateCsrfTokens(except: [
            '/telegram/webhook', // Allow Telegram to post updates
            '/whatsapp/webhook', // Allow WhatsApp to post updates
        ]);
        $middleware->statefulApi();
        $middleware->trustProxies(at: '*');
        $middleware->append(\App\Http\Middleware\SetCspHeaders::class);
        $middleware->web(append: [
            CheckMaintenanceMode::class,
            'throttle:60,1',
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        // Report exceptions to Telegram
        $exceptions->report(function (Throwable $e) {
            // Increment daily error counter
            $errorCount = Cache::get('telegram_errors_today', 0);
            Cache::put('telegram_errors_today', $errorCount + 1, now()->endOfDay());

            // Send to Telegram (async to not block the request)
            try {
                $telegram = app(TelegramMonitorService::class);
                $telegram->sendError($e);
            } catch (Throwable $telegramError) {
                // Silently fail - don't break the app if Telegram fails
                \Log::warning('Failed to send error to Telegram: ' . $telegramError->getMessage());
            }
        });
    })->create();

