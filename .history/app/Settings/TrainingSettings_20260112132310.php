<?php

namespace App\Settings;

use Spatie\LaravelSettings\Settings;

/**
 * Training Settings
 * 
 * Manages all training-related application settings using Spatie Laravel Settings.
 * Settings are stored in the database and can be modified through the Filament admin panel.
 * 
 * @package App\Settings
 */
class TrainingSettings extends Settings
{
    /**
     * Allow HOA (Head of Administration) to edit section details
     * Includes name, capacity, and other section properties
     * 
     * @var bool
     */
    public bool $hoa_can_edit_section;

    /**
     * Allow HOA (Head of Administration) to enable/disable sections
     * Controls section active status
     * 
     * @var bool
     */
    public bool $hoa_can_enable_section;

    /**
     * Allow Department Heads to edit section details
     * Includes name, capacity, and other section properties
     * 
     * @var bool
     */
    public bool $dept_head_can_edit_section;

    /**
     * Allow Department Heads to enable/disable sections
     * Controls section active status
     * 
     * @var bool
     */
    public bool $dept_head_can_enable_section;

    /**
     * Show/hide full sections in the application form
     * When true: Shows sections that have reached maximum capacity
     * When false: Hides full sections from the form
     * 
     * @var bool
     */
    public bool $hide_full_sections;

    /**
     * Enable/disable the public application form
     * When true: Trainees can submit applications via the public portal
     * When false: Public application form is closed
     * 
     * @var bool
     */
    public bool $is_public_form_enabled;

    /**
     * Enable practice training type option
     * Allows trainees to select "مزاولة" (Practice) training in the application form
     * 
     * @var bool
     */
    public bool $enable_training_type_practice;

    /**
     * Enable university training type option
     * Allows trainees to select "جامعات" (University) training in the application form
     * 
     * @var bool
     */
    public bool $enable_training_type_university;

    /**
     * Allow university trainees to reapply after completing training
     * When true: Trainees with "Ended Training" status can submit new applications
     * When false: Completed university trainees cannot reapply
     * 
     * @var bool
     */
    public bool $can_university_reapply;

    /**
     * Allow practice trainees to reapply after completing training
     * When true: Trainees with "Ended Training" status can submit new applications
     * When false: Completed practice trainees cannot reapply
     * 
     * @var bool
     */
    public bool $can_practice_reapply;

    /**
     * Enable/disable maintenance mode for specific roles
     * When true: Users with roles defined in $maintenance_roles will be redirected
     * to a maintenance page. Admins are always exempt.
     * 
     * @var bool
     */
    public bool $is_maintenance_mode;

    /**
     * Custom message displayed on the maintenance page
     * Shown to users whose roles are affected by maintenance mode
     * Should be in Arabic for consistency with the application
     * 
     * @var string
     */
    public string $maintenance_message;

    /**
     * Array of user role IDs that are affected by maintenance mode
     * Users with these roles will be redirected to the maintenance page
     * when maintenance mode is enabled.
     * 
     * Possible values:
     * - User::ROLE_COLLEGE (5): College Supervisor
     * - User::ROLE_MOH (4): Ministry of Health
     * - User::ROLE_GTM (8): General Training Manager
     * 
     * Admins (ROLE_ADMIN) are always exempt from maintenance mode
     * 
     * @var array<int, int>
     */
    public array $maintenance_roles;

    /**
     * Get the settings group name
     * Used by Spatie Laravel Settings for database storage
     * 
     * @return string
     */
    public static function group(): string
    {
        return 'training';
    }
}
