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
    }

    public function down(): void
    {
        $this->migrator->delete('general.hoa_can_edit_section');
        $this->migrator->delete('general.hoa_can_enable_section');
        $this->migrator->delete('general.dept_head_can_edit_section');
        $this->migrator->delete('general.dept_head_can_enable_section');
        $this->migrator->delete('general.hide_full_sections');
        $this->migrator->delete('general.is_public_form_enabled');
        $this->migrator->delete('general.enable_training_type_practice');
        $this->migrator->delete('general.enable_training_type_university');
        $this->migrator->delete('general.can_university_reapply');
        $this->migrator->delete('general.can_practice_reapply');
    }
};
