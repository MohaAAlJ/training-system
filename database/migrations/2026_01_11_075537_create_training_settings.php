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
        $this->migrator->add('training.not_found_message',
            'عذراً، لم نتمكن من العثور على الصفحة التي تبحث عنها. ربما تم نقلها أو حذفها أو أن الرابط غير صحيح.'
        );
        // Backup settings
        $this->migrator->add('training.last_backup_at', null);
    }

    public function down(): void
    {
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

        $this->migrator->delete('training.not_found_title');
        $this->migrator->delete('training.not_found_message');
        $this->migrator->delete('training.last_backup_at');
    }
};
