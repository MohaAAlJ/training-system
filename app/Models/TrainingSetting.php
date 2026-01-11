<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TrainingSetting extends Model
{
    protected $table = 'training_settings';

    protected $fillable = [
        'hide_full_sections',
        'is_public_form_enabled',
        'enable_training_type_practice',
        'enable_training_type_university',
        'can_university_reapply',
        'can_practice_reapply',
        'hoa_can_edit_section',
        'hoa_can_enable_section',
        'dept_head_can_edit_section',
        'dept_head_can_enable_section',
    ];

    protected $casts = [
        'hide_full_sections' => 'boolean',
        'is_public_form_enabled' => 'boolean',
        'enable_training_type_practice' => 'boolean',
        'enable_training_type_university' => 'boolean',
        'can_university_reapply' => 'boolean',
        'can_practice_reapply' => 'boolean',
        'hoa_can_edit_section' => 'boolean',
        'hoa_can_enable_section' => 'boolean',
        'dept_head_can_edit_section' => 'boolean',
        'dept_head_can_enable_section' => 'boolean',
    ];

    public static function getInstance()
    {
        return self::firstOrCreate([], [
            'hide_full_sections' => true,
            'is_public_form_enabled' => true,
            'enable_training_type_practice' => true,
            'enable_training_type_university' => true,
            'can_university_reapply' => false,
            'can_practice_reapply' => false,
            'hoa_can_edit_section' => false,
            'hoa_can_enable_section' => false,
            'dept_head_can_edit_section' => false,
            'dept_head_can_enable_section' => false,
        ]);
    }
}
