<?php

use Spatie\LaravelSettings\Migrations\SettingsMigration;

return new class extends SettingsMigration
{
    public function up(): void
    {
        $this->migrator->add('training.hoa_can_edit_section', false);
        $this->migrator->add('training.hoa_can_enable_section', false);
        $this->migrator->add('training.dept_head_can_edit_section', false);
        $this->migrator->add('training.dept_head_can_enable_section', false);
        $this->migrator->add('training.hide_full_sections', true);
        $this->migrator->add('training.is_public_form_enabled', true);
        $this->migrator->add('training.enable_training_type_practice', true);
        $this->migrator->add('training.enable_training_type_university', true);
        $this->migrator->add('training.can_university_reapply', false);
        $this->migrator->add('training.can_practice_reapply', false);
        $this->migrator->add('training.is_maintenance_mode', false);
        $this->migrator->add('training.maintenance_title', 'الموقع تحت الصيانة');
        $this->migrator->add('training.maintenance_message', 'الموقع تحت الصيانة حالياً. سنعود قريباً.');
        $this->migrator->add('training.maintenance_roles', []);
        // Error pages
        $this->migrator->add('training.not_found_title', 'الصفحة غير موجودة');
        $this->migrator->add(
            'training.not_found_message',
            'عذراً، لم نتمكن من العثور على الصفحة التي تبحث عنها. ربما تم نقلها أو حذفها أو أن الرابط غير صحيح.'
        );
        // Backup settings
        $this->migrator->add('training.last_backup_at', null);

        // Whatsapp Settings
        $this->migrator->add('training.whatsapp_notifications_enabled', false);
        $this->migrator->add('training.whatsapp_initial_approve', false);
        $this->migrator->add('training.whatsapp_start_training', false);
        $this->migrator->add('training.whatsapp_end_training', false);
        $this->migrator->add('training.whatsapp_end_training_days', 3);
        $this->migrator->add('training.whatsapp_gtm_new_application', false);
        $this->migrator->add('training.whatsapp_hoa_started_training', false);
        $this->migrator->add('training.whatsapp_hom_started_training', false);
        $this->migrator->add('training.whatsapp_department_started_training', false);
        $this->migrator->add('training.whatsapp_section_started_training', false);
        $this->migrator->add('training.whatsapp_college_approved_application', false);

        // Telegram Settings
        $this->migrator->add('training.telegram_enabled', env('TELEGRAM_ENABLED', false));
        $this->migrator->add('training.telegram_log_errors', env('TELEGRAM_LOG_ERRORS', false));
        $this->migrator->add('training.telegram_log_activities', env('TELEGRAM_LOG_ACTIVITIES', false));
        $this->migrator->add('training.telegram_daily_report', env('TELEGRAM_DAILY_REPORT', false));
        $this->migrator->add('training.telegram_bot_token', env('TELEGRAM_BOT_TOKEN', ''));
        $this->migrator->add('training.telegram_chat_ids', env('TELEGRAM_CHAT_IDS', ''));
        $this->migrator->add('training.telegram_access_code', env('TELEGRAM_ACCESS_CODE', ''));
        $this->migrator->add('training.telegram_daily_report_time', env('TELEGRAM_DAILY_REPORT_TIME', ''));
        $this->migrator->add('training.telegram_new_applications', false);
        $this->migrator->add('training.telegram_status_changes', false);
    }

    public function down(): void
    {
        // حذف إعدادات الصلاحيات والتحكم
        $this->migrator->delete('training.hoa_can_edit_section');
        $this->migrator->delete('training.hoa_can_enable_section');
        $this->migrator->delete('training.dept_head_can_edit_section');
        $this->migrator->delete('training.dept_head_can_enable_section');
        $this->migrator->delete('training.hide_full_sections');
        $this->migrator->delete('training.is_public_form_enabled');
        $this->migrator->delete('training.enable_training_type_practice');
        $this->migrator->delete('training.enable_training_type_university');
        $this->migrator->delete('training.can_university_reapply');
        $this->migrator->delete('training.can_practice_reapply');
        $this->migrator->delete('training.is_maintenance_mode');
        $this->migrator->delete('training.maintenance_title');
        $this->migrator->delete('training.maintenance_message');
        $this->migrator->delete('training.maintenance_roles');

        // حذف إعدادات صفحات الخطأ
        $this->migrator->delete('training.not_found_title');
        $this->migrator->delete('training.not_found_message');

        // حذف إعدادات النسخ الاحتياطي
        $this->migrator->delete('training.last_backup_at');

        // حذف إعدادات الواتساب
        $this->migrator->delete('training.whatsapp_notifications_enabled');
        $this->migrator->delete('training.whatsapp_initial_approve');
        $this->migrator->delete('training.whatsapp_start_training');
        $this->migrator->delete('training.whatsapp_end_training');
        $this->migrator->delete('training.whatsapp_end_training_days');
        $this->migrator->delete('training.whatsapp_gtm_new_application');
        $this->migrator->delete('training.whatsapp_hoa_started_training');
        $this->migrator->delete('training.whatsapp_hom_started_training');
        $this->migrator->delete('training.whatsapp_department_started_training');
        $this->migrator->delete('training.whatsapp_section_started_training');
        $this->migrator->delete('training.whatsapp_college_approved_application');

        // حذف إعدادات تليجرام
        $this->migrator->delete('training.telegram_enabled');
        $this->migrator->delete('training.telegram_log_errors');
        $this->migrator->delete('training.telegram_log_activities');
        $this->migrator->delete('training.telegram_daily_report');
        $this->migrator->delete('training.telegram_bot_token');
        $this->migrator->delete('training.telegram_chat_ids');
        $this->migrator->delete('training.telegram_access_code');
        $this->migrator->delete('training.telegram_daily_report_time');
        $this->migrator->delete('training.telegram_new_applications');
        $this->migrator->delete('training.telegram_status_changes');
    }
};
