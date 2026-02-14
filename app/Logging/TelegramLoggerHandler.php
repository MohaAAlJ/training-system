<?php

namespace App\Logging;

use App\Services\Telegram\TelegramMonitorService;
use Monolog\Handler\AbstractProcessingHandler;
use Monolog\LogRecord;
use Monolog\Level;

class TelegramLoggerHandler extends AbstractProcessingHandler
{
    protected TelegramMonitorService $telegram;

    public function __construct($level = Level::Debug, bool $bubble = true)
    {
        parent::__construct($level, $bubble);
        // Resolve usage of service inside handler
    }

    protected function write(LogRecord $record): void
    {
        // Avoid infinite loops if telegram service logs something
        if (str_contains($record->message, 'Telegram:')) {
            return;
        }

        try {
            $telegram = app(TelegramMonitorService::class);

            if (!$telegram->isEnabled()) {
                return;
            }

            // Determine Emoji based on level
            $levelName = $record->level->getName();
            $emoji = match ($levelName) {
                'EMERGENCY', 'ALERT', 'CRITICAL' => '🚨',
                'ERROR' => '🔴',
                'WARNING' => '⚠️',
                'NOTICE' => '📢',
                'INFO' => 'ℹ️',
                'DEBUG' => '🐛',
                default => '📝',
            };

            // Format message like a terminal log
            $message = "{$emoji} <b>[{$levelName}]</b>\n"
                     . "<code>{$record->message}</code>\n";

            // Add context if exists (like user agent, ip, etc if passed)
            if (!empty($record->context)) {
                $context = json_encode($record->context, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
                // Limit context length
                if (strlen($context) > 500) $context = substr($context, 0, 500) . '...';
                $message .= "\n<pre>{$context}</pre>";
            }

            $message .= "\n⏰ " . $record->datetime->format('H:i:s');

            // Send via service
            $telegram->send($message);

        } catch (\Throwable $e) {
            // Do nothing if fails to avoid crashing app
        }
    }
}
