<?php

use Spatie\LaravelSettings\Migrations\SettingsMigration;

return new class extends SettingsMigration
{
    public function up(): void
    {
        $this->migrator->add('general.hoa_can_edit_section', false);
        $this->migrator->add('general.hoa_can_enable_section', false);
        $this->migrator->add('general.dept_head_can_edit_section', false);
        $this->migrator->add('general.dept_head_can_enable_section', false);
        $this->migrator->add('general.hide_full_sections', true);
        $this->migrator->add('general.is_public_form_enabled', true);
        $this->migrator->add('general.enable_training_type_practice', true);
        $this->migrator->add('general.enable_training_type_university', true);
        $this->migrator->add('general.can_university_reapply', false);
        $this->migrator->add('general.can_practice_reapply', false);
    }
};
