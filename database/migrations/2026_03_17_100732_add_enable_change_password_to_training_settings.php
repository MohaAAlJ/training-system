<?php

use Spatie\LaravelSettings\Migrations\SettingsMigration;

return new class extends SettingsMigration
{
    public function up(): void
    {
        $this->migrator->add('training.enable_change_password', false);
    }

    public function down(): void
    {
        $this->migrator->delete('training.enable_change_password');
    }
};
