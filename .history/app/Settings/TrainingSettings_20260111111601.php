<?php

namespace App\Settings;

use Spatie\LaravelSettings\Settings;

class TrainingSettings extends Settings
{
    public bool $hoa_can_edit_section;
    public bool $hoa_can_enable_section;
    public bool $dept_head_can_edit_section;
    public bool $dept_head_can_enable_section;
    public bool $hide_full_sections;
    public bool $is_public_form_enabled;
    public bool $enable_training_type_practice = true;
    public bool $enable_training_type_university = true;
    public bool $can_university_reapply = false;
    public bool $can_practice_reapply = false;

    public static function group(): string
    {
        return 'training';
    }
}
