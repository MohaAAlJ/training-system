<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class GeneralSetting extends Model
{
    protected $table = 'general_settings';

    protected $fillable = [
        'hoa_can_edit_section',
        'hoa_can_enable_section',
        'dept_head_can_edit_section',
        'dept_head_can_enable_section',
        'hide_full_sections',
        'is_public_form_enabled',
        'enable_training_type_practice',
        'enable_training_type_university',
    ];

    protected $casts = [
        'hoa_can_edit_section' => 'boolean',
        'hoa_can_enable_section' => 'boolean',
        'dept_head_can_edit_section' => 'boolean',
        'dept_head_can_enable_section' => 'boolean',
        'hide_full_sections' => 'boolean',
        'is_public_form_enabled' => 'boolean',
        'enable_training_type_practice' => 'boolean',
        'enable_training_type_university' => 'boolean',
    ];

    /**
     * Get the singleton settings instance
     */
    public static function instance()
    {
        return static::first() ?? static::create([]);
    }
}
