<?php

namespace App\Console\Commands;

use App\Models\TelegramSubscriber;
use App\Services\Telegram\TelegramMonitorService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class TelegramListenCommand extends Command
{
    protected $signature = 'telegram:listen';
    protected $description = 'Listen for incoming Telegram commands (Long Polling)';

    protected $offset = 0;

    public function handle(TelegramMonitorService $telegramService)
    {
        $this->info("🤖 Bot is listening for commands... (Press Ctrl+C to stop)");

        $botToken = config('telegram.bot_token');
        if (empty($botToken)) {
            $this->error('Bot token is not configured!');
            return Command::FAILURE;
        }

        while (true) {
            try {
                // Long polling request
                $response = Http::withoutVerifying()
                    ->timeout(60)
                    ->get("https://api.telegram.org/bot{$botToken}/getUpdates", [
                        'offset' => $this->offset,
                        'timeout' => 50, // Wait 50 seconds for new messages
                    ]);

                if ($response->successful()) {
                    $updates = $response->json('result', []);

                    foreach ($updates as $update) {
                        $this->offset = $update['update_id'] + 1;
                        $this->processUpdate($update, $telegramService);
                    }
                } else {
                    $this->error("Error fetching updates: " . $response->body());
                    sleep(5);
                }
            } catch (\Exception $e) {
                $this->error("Connection error: " . $e->getMessage());
                sleep(5);
            }
        }
    }

    protected function processUpdate(array $update, TelegramMonitorService $telegram)
    {
        if (!isset($update['message'])) {
            return;
        }

        $message = $update['message'];
        $chatId = $message['chat']['id'] ?? null;
        $text = $message['text'] ?? '';
        $username = $message['from']['username'] ?? 'Unknown';
        $firstName = $message['from']['first_name'] ?? 'Unknown';

        if (!$chatId) return;

        // Register or retrieve subscriber
        $subscriber = TelegramSubscriber::firstOrCreate(
            ['chat_id' => $chatId],
            ['name' => $firstName, 'username' => $username]
        );

        $this->info("Received '{$text}' from {$firstName} ({$chatId})");

        // AUTO-AUTH: If user is in config (Super Admin), activate them automatically
        $configChatIds = config('telegram.chat_ids', []);
        if (in_array((string)$chatId, array_map('strval', $configChatIds))) {
            if (!$subscriber->is_active) {
                $subscriber->update(['is_active' => true]);
                $this->info("Auto-authorized admin: {$firstName}");
            }
        }

        // Check if user is active/authorized
        $isActive = $subscriber->is_active;

        // Handle Commands
        $command = strtolower(trim($text));

        // AUTHENTICATION LOGIC
        if (str_starts_with($command, '/auth')) {
            $providedCode = trim(str_replace('/auth', '', $text));
            $correctCode = config('telegram.access_code');

            if ($providedCode === $correctCode) {
                $subscriber->update(['is_active' => true]);
                $this->reply($chatId, "✅ <b>تم التحقق بنجاح!</b>\n\nأهلاً بك يا {$firstName}، لقد تم تفعيل حسابك لاستقبال إشعارات النظام.", $telegram);

                Log::info("Telegram User Authenticated: {$firstName} ({$chatId})");
            } else {
                $this->reply($chatId, "❌ <b>كود الدخول خاطئ!</b>\n\nيرجى التأكد من الكود والمحاولة مرة أخرى.\nمثال: <code>/auth 123456</code>", $telegram);
            }
            return;
        }

        // START COMMAND
        if ($command === '/start') {
            if ($isActive) {
                $this->reply($chatId, "👋 أهلاً بك مجدداً يا {$firstName}.\nحسابك مفعل وجاهز.", $telegram);
            } else {
                $this->reply($chatId, "🔒 <b>مرحباً بك في بوت المراقبة</b>\n\nلأسباب أمنية، يجب تفعيل حسابك أولاً.\n\nالرجاء إرسال كود الدخول باستخدام الأمر:\n<code>/auth YOUR_CODE</code>", $telegram);
            }
            return;
        }

        // SECURITY GATE: Block everything else if not active
        if (!$isActive) {
            $this->reply($chatId, "⛔ <b>غير مصرح لك</b>\n\nيجب عليك تفعيل حسابك أولاً باستخدام /auth.", $telegram);
            return;
        }

        switch ($command) {
            case '/help':
                $helpMsg = "ℹ️ <b>قائمة الأوامر:</b>\n\n"
                    . "/summary - 📊 ملخص سريع للطلبات والنشاط\n"
                    . "/report - 📅 طلب التقرير اليومي فوراً\n"
                    . "/find - 🔍 بحث عن طلب برقم الهوية أو الطلب\n"
                    . "/status_on - 🔔 تفعيل إشعارات حالة الطلبات\n"
                    . "/status_off - 🔕 إيقاف إشعارات حالة الطلبات\n"
                    . "/errors_on - 🔴 تفعيل سجلات الأخطاء البرمجية\n"
                    . "/errors_off - ⚪ إيقاف سجلات الأخطاء البرمجية\n"
                    . "/users - 👥 عرض المشتركين";
                $this->reply($chatId, $helpMsg, $telegram);
                break;

            case '/users':
                $users = TelegramSubscriber::where('is_active', true)->get();
                $msg = "👥 <b>المستخدمين المصرح لهم (" . $users->count() . "):</b>\n\n";
                foreach ($users as $user) {
                    $errors = $user->wants_errors ? "🔴" : "⚪";
                    $status = $user->wants_activities ? "🔔" : "🔕";
                    $msg .= "{$status}{$errors} <b>{$user->name}</b> ({$user->username})\n<code>{$user->chat_id}</code>\n\n";
                }
                $this->reply($chatId, $msg . "\n🔴 = يستلم أخطاء | 🔔 = يستلم أنشطة", $telegram);
                break;

            case '/summary':
            case '/status':
                $this->sendSummary($chatId, $telegram);
                break;

            case '/report':
                \Illuminate\Support\Facades\Artisan::call('telegram:daily-report');
                $this->reply($chatId, "✅ تم إرسال التقرير اليومي.", $telegram);
                break;

            case '/mute':
            case '/status_off':
                $subscriber->update(['wants_activities' => false]);
                $this->reply($chatId, "🔕 <b>تم إيقاف إشعارات حالة الطلبات</b>\n\nلن تصلك تحديثات الطلبات الجديدة أو تغيير الحالات.", $telegram);
                break;

            case '/unmute':
            case '/status_on':
                $subscriber->update(['wants_activities' => true]);
                $this->reply($chatId, "🔔 <b>تم تفعيل إشعارات حالة الطلبات</b>\n\nستصلك جميع الأنشطة.", $telegram);
                break;

            case '/errors_on':
            case '/logs_on':
                $subscriber->update(['wants_errors' => true]);
                $this->reply($chatId, "🔴 <b>تم تفعيل سجلات الأخطاء</b>\n\nستصلك رسائل الأخطاء البرمجية للنظام.", $telegram);
                break;

            case '/errors_off':
            case '/logs_off':
                $subscriber->update(['wants_errors' => false]);
                $this->reply($chatId, "⚪ <b>تم إيقاف سجلات الأخطاء</b>\n\nلن تصلك رسائل الأخطاء البرمجية.", $telegram);
                break;

            default:
                if (str_starts_with($command, '/find')) {
                    $query = trim(str_replace('/find', '', $text));
                    if (empty($query)) {
                        $this->reply($chatId, "🔍 <b>بحث عن طلب</b>\n\nالرجاء كتابة رقم الطلب أو الهوية.\nمثال: <code>/find 1050</code>", $telegram);
                        break;
                    }

                    $app = \App\Models\Application::with('trainee')
                        ->where('id', $query)
                        ->orWhereHas('trainee', fn($q) => $q->where('national_id', $query))
                        ->first();

                    if ($app) {
                        $status = \App\Models\Application::getStatusLabel((int)$app->status);
                        $msg = "📄 <b>تفاصيل الطلب #{$app->id}</b>\n\n"
                            . "👤 <b>المتدرب:</b> {$app->trainee?->full_name}\n"
                            . "🆔 <b>الهوية:</b> {$app->trainee?->national_id}\n"
                            . "📊 <b>الحالة:</b> {$status}\n"
                            . "📅 <b>تاريخ الطلب:</b> {$app->created_at->format('Y-m-d')}\n";

                        $this->reply($chatId, $msg, $telegram);
                    } else {
                        $this->reply($chatId, "❌ لم يتم العثور على طلب بهذا الرقم.", $telegram);
                    }
                } elseif (str_starts_with($text, '/')) {
                    $this->reply($chatId, "❌ أمر غير معروف. استخدم /help للمساعدة.", $telegram);
                }
                break;
        }
    }

    protected function reply($chatId, $text, $telegramService)
    {
        // Use a direct call to avoid broadcasting to everyone
        $botToken = config('telegram.bot_token');
        Http::withoutVerifying()->post("https://api.telegram.org/bot{$botToken}/sendMessage", [
            'chat_id' => $chatId,
            'text' => $text,
            'parse_mode' => 'HTML',
        ]);
    }

    protected function sendSummary($chatId, $telegram)
    {
        // Calculate quick stats
        $online = \Illuminate\Support\Facades\DB::table('sessions')
            ->where('last_activity', '>=', now()->subHours(24)->timestamp)
            ->count();

        $newApps = \App\Models\Application::whereDate('created_at', now())->count();
        $errors = \Illuminate\Support\Facades\Cache::get('telegram_errors_today', 0);

        $msg = "📊 <b>ملخص النظام الحالي:</b>\n\n"
            . "👥 <b>المتصلين (24س):</b> {$online}\n"
            . "📥 <b>طلبات اليوم:</b> {$newApps}\n"
            . "🚨 <b>أخطاء اليوم:</b> {$errors}\n\n"
            . "✅ النظام يعمل بشكل جيد.";

        $this->reply($chatId, $msg, $telegram);
    }
}
