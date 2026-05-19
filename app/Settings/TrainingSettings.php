<?php

namespace App\Settings;

use Spatie\LaravelSettings\Settings;
use DateTime;

class TrainingSettings extends Settings
{
    // Section permissions
    public bool $hoa_can_edit_section;
    public bool $hoa_can_enable_section;
    public bool $dept_head_can_edit_section;
    public bool $dept_head_can_enable_section;

    // Training form settings
    public bool $hide_full_sections;
    public bool $is_public_form_enabled;
    public bool $enable_training_type_practice;
    public bool $enable_training_type_university;
    public bool $can_university_reapply;
    public bool $can_practice_reapply;
    public bool $enable_change_password = false;
    public bool $ai_search_enabled = false;
    public array $ai_search_allowed_roles = [];

    // Maintenance mode
    public bool $is_maintenance_mode;
    public string $maintenance_title;
    public string $maintenance_message;
    public array $maintenance_roles;

    // Error pages
    public string $not_found_title;
    public string $not_found_message;

    // Backup settings
    public ?string $last_backup_at;

    // WhatsApp settings
    public bool $whatsapp_notifications_enabled; // Added
    public bool $whatsapp_initial_approve;
    public bool $whatsapp_start_training;
    public bool $whatsapp_end_training;
    public int $whatsapp_end_training_days;

    // Role-based WhatsApp Settings
    public bool $whatsapp_gtm_new_application;
    public bool $whatsapp_hoa_started_training;
    public bool $whatsapp_hom_started_training;
    public bool $whatsapp_department_started_training;
    public bool $whatsapp_section_started_training;
    public bool $whatsapp_college_approved_application;

    // Telegram settings
    public ?string $telegram_bot_token;
    public ?string $telegram_chat_ids; // Stored as comma-separated string
    public ?string $telegram_access_code;
    public bool $telegram_enabled;
    public bool $telegram_new_applications; // Added
    public bool $telegram_status_changes; // Added
    public bool $telegram_daily_report;
    public ?string $telegram_daily_report_time;
    public bool $telegram_log_errors;
    public bool $telegram_log_activities;

    public static function group(): string
    {
        return 'training';
    }
}
