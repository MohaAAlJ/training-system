<?php

namespace App\Console\Commands;

use App\Services\Telegram\TelegramMonitorService;
use Illuminate\Console\Command;

class TelegramTest extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'telegram:test';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Test Telegram bot connection and send a test message';

    /**
     * Execute the console command.
     */
    public function handle(TelegramMonitorService $telegram): int
    {
        $this->info('Testing Telegram bot connection...');

        // Test connection
        $result = $telegram->testConnection();

        if (!$result['success']) {
            $this->error('Connection failed: ' . ($result['error'] ?? 'Unknown error'));
            return Command::FAILURE;
        }

        $this->info("✓ Connected to bot: @{$result['bot_username']} ({$result['bot_name']})");

        // Test Live Feed Logging
        $this->info('Testing Live Log Feed...');
        \Illuminate\Support\Facades\Log::info('تجربة نظام المراقبة الحية (Live Feed) - هذا السطر تم إرساله تلقائياً عند تسجيل Log في النظام.');

        // Send test message
        $this->info('Sending test message...');

        $success = $telegram->notify(
            'اختبار الاتصال',
            "✅ تم الاتصال بنجاح!\n\n"
            . "🤖 البوت يعمل بشكل صحيح\n"
            . "📡 جميع الإشعارات مفعّلة\n\n"
            . "🔧 الإعدادات:\n"
            . "   • مراقبة الأخطاء: " . (config('telegram.log_errors') ? '✅' : '❌') . "\n"
            . "   • مراقبة النشاط: " . (config('telegram.log_activities') ? '✅' : '❌') . "\n"
            . "   • التقرير اليومي: " . (config('telegram.daily_report') ? '✅' : '❌'),
            '🚀'
        );

        if ($success) {
            $this->info('✓ Test message sent successfully!');
            $this->newLine();
            $this->info('Check your Telegram to see the message.');
            return Command::SUCCESS;
        }

        $this->error('Failed to send test message');
        return Command::FAILURE;
    }
}
