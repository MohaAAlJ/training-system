<?php

namespace App\Http\Controllers;

use App\Models\TelegramSubscriber;
use App\Services\Telegram\TelegramMonitorService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Cache;

class TelegramWebhookController extends Controller
{
    public function handle(Request $request, TelegramMonitorService $telegram, \App\Settings\TrainingSettings $settings)
    {
        // Telegram sends the update as JSON in the request body
        $update = $request->all();

        if (!isset($update['message'])) {
            return response()->json(['status' => 'ok']); // Acknowledge receipt
        }

        $message = $update['message'];
        $chatId = $message['chat']['id'] ?? null;
        $text = $message['text'] ?? '';
        $username = $message['from']['username'] ?? 'Unknown';
        $firstName = $message['from']['first_name'] ?? 'Unknown';

        if (!$chatId) return response()->json(['status' => 'ok']);

        // Register or retrieve subscriber
        $subscriber = TelegramSubscriber::firstOrCreate(
            ['chat_id' => $chatId],
            ['name' => $firstName, 'username' => $username]
        );

        Log::info("Telegram Webhook: Received '{$text}' from {$firstName}");

        // AUTO-AUTH: Prioritize Settings, fallback to Config
        $settingsChatIds = array_filter(explode(',', $settings->telegram_chat_ids ?? ''));
        $configChatIds = config('telegram.chat_ids', []);

        $allAllowedChatIds = array_merge($settingsChatIds, $configChatIds);

        if (in_array((string)$chatId, array_map('trim', array_map('strval', $allAllowedChatIds)))) {
            if (!$subscriber->is_active) {
                $subscriber->update(['is_active' => true]);
                // Log::info("Telegram: Auto-authorized admin {$firstName}");
            }
        }

        // Check if user is active/authorized
        $isActive = $subscriber->is_active;

        // Handle Commands
        $command = strtolower(trim($text));

        // AUTHENTICATION LOGIC
        if (str_starts_with($command, '/auth')) {
            $providedCode = trim(str_replace('/auth', '', $text));
            $correctCode = $settings->telegram_access_code ?: config('telegram.access_code');

            if ($providedCode === $correctCode) {
                $subscriber->update(['is_active' => true]);
                $this->reply($chatId, "✅ <b>تم التحقق بنجاح!</b>\n\nأهلاً بك يا {$firstName}، لقد تم تفعيل حسابك لاستقبال إشعارات النظام.");

                // Notify admin/logs that someone authenticated
                Log::info("Telegram User Authenticated: {$firstName} ({$chatId})");
            } else {
                $this->reply($chatId, "❌ <b>كود الدخول خاطئ!</b>\n\nيرجى التأكد من الكود والمحاولة مرة أخرى.\nمثال: <code>/auth 123456</code>");
            }
            return response()->json(['status' => 'ok']);
        }

        // START COMMAND
        if ($command === '/start') {
            if ($isActive) {
                $this->reply($chatId, "👋 أهلاً بك مجدداً يا {$firstName}.\nحسابك مفعل وجاهز.");
            } else {
                $this->reply($chatId, "🔒 <b>مرحباً بك في بوت المراقبة</b>\n\nلأسباب أمنية، يجب تفعيل حسابك أولاً.\n\nالرجاء إرسال كود الدخول باستخدام الأمر:\n<code>/auth YOUR_CODE</code>");
            }
            return response()->json(['status' => 'ok']);
        }

        // SECURITY GATE: Block everything else if not active
        if (!$isActive) {
            $this->reply($chatId, "⛔ <b>غير مصرح لك</b>\n\nيجب عليك تفعيل حسابك أولاً باستخدام /auth.");
            return response()->json(['status' => 'ok']);
        }

        // AUTHORIZED COMMANDS
        switch ($command) {
            case '/help':
                $helpMsg = "ℹ️ <b>قائمة الأوامر:</b>\n\n"
                    . "/summary - 📊 ملخص سريع للطلبات والنشاط\n"
                    . "/find - 🔍 بحث عن طلب برقم الهوية أو الطلب\n"
                    . "/status_on - 🔔 تفعيل إشعارات حالة الطلبات\n"
                    . "/status_off - 🔕 إيقاف إشعارات حالة الطلبات\n"
                    . "/errors_on - 🔴 تفعيل سجلات الأخطاء البرمجية\n"
                    . "/errors_off - ⚪ إيقاف سجلات الأخطاء البرمجية\n";
                $this->reply($chatId, $helpMsg);
                break;

            case '/summary':
                $this->sendSummary($chatId);
                break;

            // TOGGLE ACTIVITIES (Application Status)
            case '/status_off':
                $subscriber->update(['wants_activities' => false]);
                $this->reply($chatId, "🔕 <b>تم إيقاف إشعارات حالة الطلبات</b>\n\nلن تصلك تحديثات الطلبات الجديدة أو تغيير الحالات.");
                break;

            case '/status_on':
                $subscriber->update(['wants_activities' => true]);
                $this->reply($chatId, "🔔 <b>تم تفعيل إشعارات حالة الطلبات</b>\n\nستصلك جميع الأنشطة.");
                break;

            // TOGGLE ERRORS (System Logs)
            case '/errors_on':
            case '/logs_on':
                $subscriber->update(['wants_errors' => true]);
                $this->reply($chatId, "🔴 <b>تم تفعيل سجلات الأخطاء</b>\n\nستصلك رسائل الأخطاء البرمجية للنظام.");
                break;

            case '/errors_off':
            case '/logs_off':
                $subscriber->update(['wants_errors' => false]);
                $this->reply($chatId, "⚪ <b>تم إيقاف سجلات الأخطاء</b>\n\nلن تصلك رسائل الأخطاء البرمجية.");
                break;

            // FIND APPLICATION
            default:
                if (str_starts_with($command, '/find')) {
                    $query = trim(str_replace('/find', '', $text));
                    if (empty($query)) {
                        $this->reply($chatId, "🔍 <b>بحث عن طلب</b>\n\nالرجاء كتابة رقم الطلب أو الهوية.\nمثال: <code>/find 1050</code>");
                        break;
                    }

                    // Search by ID or National ID
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

                        $this->reply($chatId, $msg);
                    } else {
                        $this->reply($chatId, "❌ لم يتم العثور على طلب بهذا الرقم.");
                    }
                } elseif (str_starts_with($text, '/')) {
                    $this->reply($chatId, "❌ أمر غير معروف. استخدم /help للمساعدة.");
                }
                break;
        }

        return response()->json(['status' => 'ok']);
    }

    protected function reply($chatId, $text)
    {
        $settings = app(\App\Settings\TrainingSettings::class);
        $botToken = $settings->telegram_bot_token ?: config('telegram.bot_token');

        if (empty($botToken)) {
            Log::error('Telegram Bot Token is missing in settings and config.');
            return;
        }

        \Illuminate\Support\Facades\Http::withoutVerifying()->post("https://api.telegram.org/bot{$botToken}/sendMessage", [
            'chat_id' => $chatId,
            'text' => $text,
            'parse_mode' => 'HTML',
        ]);
    }

    protected function sendSummary($chatId)
    {
        $today = \Carbon\Carbon::today();

        $newApps = \App\Models\Application::whereDate('created_at', $today)->count();
        $finished = \App\Models\Application::where('status', \App\Models\Application::STATUS_ENDED_TRAINING)
            ->whereDate('updated_at', $today)
            ->count();

        $online = \Illuminate\Support\Facades\DB::table('sessions')
            ->where('last_activity', '>=', now()->subHours(24)->timestamp)
            ->count();

        $errors = Cache::get('telegram_errors_today', 0);
        $date = now()->format('Y-m-d');

        $msg = "📅 <b>التقرير اليومي المختصر - {$date}</b>\n\n"
            . "📥 <b>الطلبات المضافة:</b> {$newApps}\n"
            . "✅ <b>الطلبات المكتملة (إنهاء تدريب):</b> {$finished}\n"
            . "🚨 <b>الأخطاء المسجلة:</b> {$errors}\n\n"
            . "🤖 <i>نظام المراقبة الآلي</i>";

        $this->reply($chatId, $msg);
    }
}
