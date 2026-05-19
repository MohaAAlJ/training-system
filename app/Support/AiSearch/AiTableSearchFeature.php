<?php

namespace App\Support\AiSearch;

use App\Models\User;
use App\Settings\TrainingSettings;

class AiTableSearchFeature
{
    public function __construct(
        private readonly TrainingSettings $settings,
    ) {}

    public function enabled(): bool
    {
        return (bool) $this->settings->ai_search_enabled;
    }

    public function enabledFor(?User $user): bool
    {
        if (! $user || ! $this->enabled()) {
            return false;
        }

        return in_array($user->role, $this->settings->ai_search_allowed_roles ?? [], true);
    }

    /**
     * @return array<int, string>
     */
    public function roleOptions(): array
    {
        return User::ROLE_LABELS;
    }
}
