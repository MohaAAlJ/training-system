<?php

use Spatie\LaravelSettings\Migrations\SettingsMigration;

return new class extends SettingsMigration
{
    public function up(): void
    {
        if (! $this->migrator->exists('training.ai_search_enabled')) {
            $this->migrator->add('training.ai_search_enabled', false);
        }

        if (! $this->migrator->exists('training.ai_search_allowed_roles')) {
            $this->migrator->add('training.ai_search_allowed_roles', []);
        }
    }

    public function down(): void
    {
        if ($this->migrator->exists('training.ai_search_enabled')) {
            $this->migrator->delete('training.ai_search_enabled');
        }

        if ($this->migrator->exists('training.ai_search_allowed_roles')) {
            $this->migrator->delete('training.ai_search_allowed_roles');
        }
    }
};
