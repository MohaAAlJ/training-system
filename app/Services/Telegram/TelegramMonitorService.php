<?php

namespace App\Services\Telegram;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Cache;
use App\Models\Application;
// use App\Enums\ApplicationStatus;
use App\Settings\TrainingSettings;
use Throwable;

class TelegramMonitorService
{
    protected string $botToken;
    protected array $chatIds;
    // protected bool $enabled; // Use settings directly
    protected string $baseUrl = 'https://api.telegram.org/bot';

    public function __construct(protected TrainingSettings $settings)
    {
        $this->botToken = $settings->telegram_bot_token ?? '';
        $this->chatIds = array_filter(array_map('trim', explode(',', $settings->telegram_chat_ids ?? '')));
        $this->enabled = $settings->telegram_enabled;
    }

    /**
     * Check if the service is properly configured and enabled
     */
    public function isEnabled(): bool
    {
        return $this->settings->telegram_enabled && !empty($this->botToken);
    }

    /**
     * Send a general message to all configured chats and active subscribers
     *
     * @param string $message
     * @param string $type 'general' | 'error' | 'activity'
     */
    public function send(string $message, string $type = 'general'): bool
    {
        if (!$this->isEnabled()) {
            return false;
        }

        // 1. Get Hardcoded IDs from Config
        $recipients = collect($this->chatIds);

        // 2. Get Database Subscribers
        try {
            $query = \App\Models\TelegramSubscriber::where('is_active', true);

            // If it's an activity message, only send to those who want activities
            if ($type === 'activity') {
                $query->where('wants_activities', true);
            }
            // If it's an error message, only send to those who want errors
            elseif ($type === 'error') {
                $query->where('wants_errors', true);
            }

            // For general messages (like daily reports), we send to everyone active

            $subscribers = $query->pluck('chat_id');
            $recipients = $recipients->merge($subscribers);
        } catch (\Throwable $e) {
            // Fallback: If DB fails (migration not run yet), just use config IDs
            // Log::warning('Telegram Subscriber DB fetch failed: ' . $e->getMessage());
        }

        // Unique IDs to avoid duplicates
        $uniqueRecipients = $recipients->unique()->values();

        if ($uniqueRecipients->isEmpty()) {
            return false;
        }

        $success = true;
        $parseMode = 'HTML';

        foreach ($uniqueRecipients as $chatId) {
            try {
                $response = Http::withoutVerifying()->timeout(10)->post($this->baseUrl . $this->botToken . '/sendMessage', [
                    'chat_id' => trim($chatId),
                    'text' => $message,
                    'parse_mode' => $parseMode,
                    'disable_web_page_preview' => true,
                ]);

                if (!$response->successful()) {
                    Log::error('Telegram API Error: ' . $response->body());
                    $success = false;
                }
            } catch (Throwable $e) {
                Log::error('Telegram Send Error: ' . $e->getMessage());
                $success = false;
            }
        }

        $this->incrementRateLimit();

        return $success;
    }

    /**
     * Send an error notification
     */
    public function sendError(Throwable $exception, ?array $context = []): bool
    {
        if (!$this->settings->telegram_log_errors) {
            return false;
        }

        // Check if this exception type should be ignored
        foreach (config('telegram.ignored_exceptions', []) as $ignoredException) {
            if ($exception instanceof $ignoredException) {
                return false;
            }
        }

        $user = auth()->user();
        $request = request();

        $message = $this->formatErrorMessage([
            'type' => get_class($exception),
            'message' => $exception->getMessage(),
            'code' => $exception->getCode(),
            'file' => $exception->getFile(),
            'line' => $exception->getLine(),
            'user_id' => $user?->id ?? 'Guest',
            'user_name' => $user?->name ?? 'Guest',
            'ip' => $request->ip(),
            'url' => $request->fullUrl(),
            'method' => $request->method(),
            'user_agent' => $request->userAgent(),
            'context' => $context,
        ]);

        return $this->send($message);
    }

    /**
     * Send an activity notification
     */
    /**
     * Handle Application Created Event
     */
    public function handleApplicationCreated(Application $application): void
    {
        Log::info("TelegramMonitor: New Application Created Event fired for ID: {$application->id}");

        if (!$this->settings->telegram_new_applications) {
            Log::info("TelegramMonitor: Skipping notification - telegram_new_applications is disabled.");
            return;
        }

        $user = auth()->user();

        // Determine the source
        $actorName = $user ? $user->name : 'نموذج خارجي (Outside Form)';

        // We manually construct the message to bypass sendActivity's log check
        $message = $this->formatActivityMessage([
            'action' => 'created',
            'model' => 'Application',
            'user_id' => $user?->id ?? 'Guest',
            'user_name' => $actorName,
            'ip' => request()->ip(),
            'details' => [
                'رقم الطلب' => $application->id,
                'المتدرب' => $application->trainee?->full_name ?? 'غير معروف',
                'المصدر' => $actorName,
            ],
        ]);

        $this->send($message, 'general');
    }

    /**
     * Handle Application Updated Event
     */
    public function handleApplicationUpdated(Application $application): void
    {
        $user = auth()->user();
        $actorName = $user ? $user->name : 'النظام الآلي (Auto/System)';

        // Check if status was changed
        if ($application->isDirty('status')) {
            if (!$this->settings->telegram_status_changes) {
                return;
            }

            $oldStatusValue = $application->getOriginal('status');
            $newStatus = $application->status;

            // Get status labels using the enum
            $oldLabel = Application::getStatusLabel((int)$oldStatusValue);

            $newLabel = Application::getStatusLabel((int)$newStatus);

            $this->sendStatusChange(
                $application->id,
                $application->trainee?->full_name ?? 'غير معروف',
                $oldLabel,
                $newLabel
            );
        } else {
            // Other updates - respect log_activities
            if (!$this->settings->telegram_log_activities) return;

            $changes = $application->getChanges();
            unset($changes['updated_at']);

            if (!empty($changes)) {
                $this->sendActivity('updated', 'Application', [
                    'رقم الطلب' => $application->id,
                    'التغييرات' => implode(', ', array_keys($changes)),
                    'بواسطة' => $actorName
                ]);
            }
        }
    }

    /**
     * Handle Application Deleted Event
     */
    public function handleApplicationDeleted(Application $application): void
    {
        $this->sendActivity('deleted', 'Application', [
            'رقم الطلب' => $application->id,
            'المتدرب' => $application->trainee?->full_name ?? 'غير معروف',
        ]);
    }

    /**
     * Handle Application Restored Event
     */
    public function handleApplicationRestored(Application $application): void
    {
        $this->sendActivity('restored', 'Application', [
            'رقم الطلب' => $application->id,
            'المتدرب' => $application->trainee?->full_name ?? 'غير معروف',
        ]);
    }

    /**
     * Handle User Created Event
     */
    public function handleUserCreated(\App\Models\User $user): void
    {
        $this->sendActivity('created', 'User', [
            'الاسم' => $user->name,
            'البريد' => $user->email,
            'الدور' => \App\Models\User::ROLE_LABELS[$user->role] ?? $user->role,
        ]);
    }

    /**
     * Handle User Updated Event
     */
    public function handleUserUpdated(\App\Models\User $user): void
    {
        $changes = $user->getChanges();

        // Remove sensitive and irrelevant fields
        unset($changes['updated_at'], $changes['password'], $changes['remember_token']);

        if (empty($changes)) {
            return;
        }

        // Check for role change
        if ($user->isDirty('role')) {
            $oldRole = \App\Models\User::ROLE_LABELS[$user->getOriginal('role')] ?? $user->getOriginal('role');
            $newRole = \App\Models\User::ROLE_LABELS[$user->role] ?? $user->role;

            $this->notify(
                'تغيير صلاحيات مستخدم',
                "👤 المستخدم: {$user->name}\n"
                    . "🔄 الدور: {$oldRole} ← {$newRole}",
                '🔐'
            );
            return;
        }

        // Check for status change
        if ($user->isDirty('status')) {
            $status = $user->status ? 'مفعّل ✅' : 'معطّل ❌';
            $this->notify(
                'تغيير حالة مستخدم',
                "👤 المستخدم: {$user->name}\n📊 الحالة: {$status}",
                '👤'
            );
            return;
        }

        // Other changes
        $this->sendActivity('updated', 'User', [
            'الاسم' => $user->name,
            'التغييرات' => implode(', ', array_keys($changes)),
        ]);
    }

    /**
     * Handle User Deleted Event
     */
    public function handleUserDeleted(\App\Models\User $user): void
    {
        $this->sendActivity('deleted', 'User', [
            'الاسم' => $user->name,
            'البريد' => $user->email,
        ]);
    }

    /**
     * Send an activity notification
     */
    public function sendActivity(string $action, string $model, array $details = []): bool
    {
        if (!$this->settings->telegram_log_activities) {
            return false;
        }

        $user = auth()->user();
        $request = request();

        $message = $this->formatActivityMessage([
            'action' => $action,
            'model' => $model,
            'user_id' => $user?->id ?? 'System',
            'user_name' => $user?->name ?? 'System',
            'ip' => $request->ip(),
            'details' => $details,
        ]);

        return $this->send($message);
    }

    /**
     * Send application status change notification
     */
    public function sendStatusChange(
        int $applicationId,
        string $traineeName,
        string $oldStatus,
        string $newStatus
    ): bool {
        $user = auth()->user();

        $message = $this->formatStatusChangeMessage([
            'application_id' => $applicationId,
            'trainee_name' => $traineeName,
            'old_status' => $oldStatus,
            'new_status' => $newStatus,
            'user_id' => $user?->id ?? 'System',
            'user_name' => $user?->name ?? 'System',
            'ip' => request()->ip(),
        ]);

        return $this->send($message);
    }

    /**
     * Send daily report
     */
    public function sendDailyReport(array $stats): bool
    {
        if (!$this->settings->telegram_daily_report) {
            return false;
        }

        $message = $this->formatDailyReportMessage($stats);
        return $this->send($message, 'general');
    }

    /**
     * Send login notification
     */
    public function sendLoginNotification(int $userId, string $userName, string $ip): bool
    {
        // Don't send login notifications to avoid spam, unless critical
        // Or keep it simple if requested
        return true;
    }

    /**
     * Send a custom notification
     */
    public function notify(string $title, string $content, string $emoji = '📢'): bool
    {
        $message = "{$emoji} <b>{$title}</b>\n\n{$content}\n\n⏰ " . now()->format('Y-m-d H:i:s');
        return $this->send($message);
    }

    /**
     * Format error message
     */
    protected function formatErrorMessage(array $data): string
    {
        // Use relative path instead of just filename
        $shortFile = str_replace(base_path() . DIRECTORY_SEPARATOR, '', $data['file']);
        $shortMessage = mb_substr($data['message'], 0, 500);
        $code = $data['code'] ? " (Code: {$data['code']})" : "";

        return "🔴 <b>خطأ في النظام</b>\n\n"
            . "📝 <b>الرسالة:</b> {$shortMessage}\n"
            . "📛 <b>النوع:</b> <code>" . class_basename($data['type']) . "{$code}</code>\n\n"
            . "👤 <b>المستخدم:</b> {$data['user_name']} (ID: {$data['user_id']})\n"
            . "🌐 <b>IP:</b> {$data['ip']}\n"
            . "📍 <b>الملف:</b> {$shortFile}:{$data['line']}\n"
            . "🔗 <b>الرابط:</b> <a href='{$data['url']}'></a>\n"
            . "👉 <code>{$data['url']}</code>\n"
            . "⏰ " . now()->format('Y-m-d H:i:s');
    }

    /**
     * Format activity message
     */
    protected function formatActivityMessage(array $data): string
    {
        $actionEmoji = match ($data['action']) {
            'created' => '🆕',
            'updated' => '✏️',
            'deleted' => '🗑️',
            default => '📝',
        };

        $title = match ($data['action']) {
            'created' => 'طلب جديد تم إضافته',
            'updated' => 'تحديث في النظام',
            'deleted' => 'حذف سجل',
            default => 'نشاط جديد',
        };

        // Determine user string
        $userString = $data['user_name'];
        if ($data['user_id'] !== 'System' && $data['user_id'] !== 'Guest') {
            $userString .= " (ID: {$data['user_id']})";
        }

        $msg = "{$actionEmoji} <b>{$title}</b>\n\n";

        if (!empty($data['details'])) {
            foreach ($data['details'] as $key => $value) {
                $msg .= "🔹 <b>{$key}:</b> {$value}\n";
            }
        }

        $msg .= "\n👤 <b>بواسطة:</b> {$userString}\n";
        $msg .= "⏰ " . now()->format('H:i');

        return $msg;
    }

    /**
     * Format status change message
     */
    protected function formatStatusChangeMessage(array $data): string
    {
        // Determine user string
        $userString = $data['user_name'];
        if ($data['user_id'] !== 'System' && $data['user_id'] !== 'Auto') {
            $userString .= " (ID: {$data['user_id']})";
        }

        return "🔄 <b>تحديث حالة الطلب</b>\n\n"
            . "📋 <b>رقم الطلب:</b> #{$data['application_id']}\n"
            . "👨‍🎓 <b>المتدرب:</b> {$data['trainee_name']}\n\n"
            . "📊 <b>الحالة:</b> {$data['old_status']} ⬅️ <b>{$data['new_status']}</b>\n\n"
            . "👤 <b>بواسطة:</b> {$userString}\n"
            . "⏰ " . now()->format('H:i');
    }

    /**
     * Format daily report message
     */
    protected function formatDailyReportMessage(array $stats): string
    {
        $date = now()->format('Y-m-d');

        return "📅 <b>التقرير اليومي المختصر - {$date}</b>\n\n"
            . "📥 <b>الطلبات المضافة:</b> {$stats['new_applications']}\n"
            . "✅ <b>الطلبات المكتملة (إنهاء تدريب):</b> {$stats['finished_training']}\n"
            . "👥 <b>المستخدمين المتصلين اليوم:</b> {$stats['online_users']}\n"
            . "🚨 <b>الأخطاء المسجلة:</b> {$stats['errors_count']}\n\n"
            . "🤖 <i>نظام المراقبة الآلي</i>";
    }

    /**
     * Check if rate limited
     */
    protected function isRateLimited(): bool
    {
        $key = 'telegram_rate_limit';
        $limit = config('telegram.rate_limit.max_messages', 30);
        $current = Cache::get($key, 0);

        return $current >= $limit;
    }

    /**
     * Increment rate limit counter
     */
    protected function incrementRateLimit(): void
    {
        $key = 'telegram_rate_limit';
        $minutes = config('telegram.rate_limit.per_minutes', 1);

        $current = Cache::get($key, 0);
        Cache::put($key, $current + 1, now()->addMinutes($minutes));
    }

    /**
     * Test the bot connection
     */
    public function testConnection(): array
    {
        try {
            $response = Http::withoutVerifying()->timeout(10)->get($this->baseUrl . $this->botToken . '/getMe');

            if ($response->successful()) {
                $bot = $response->json('result');
                return [
                    'success' => true,
                    'bot_name' => $bot['first_name'] ?? 'Unknown',
                    'bot_username' => $bot['username'] ?? 'Unknown',
                ];
            }

            return [
                'success' => false,
                'error' => $response->json('description') ?? 'Unknown error',
            ];
        } catch (Throwable $e) {
            return [
                'success' => false,
                'error' => $e->getMessage(),
            ];
        }
    }
}
