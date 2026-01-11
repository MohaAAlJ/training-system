<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class TrainingSettingsSeeder extends Seeder
{
    public function run(): void
    {
        DB::table('settings')->updateOrInsert(
            ['group' => 'training', 'name' => 'training'],
            [
                'payload' => json_encode([
                    'hoa_can_edit_section' => false,
                    'hoa_can_enable_section' => false,
                    'dept_head_can_edit_section' => false,
                    'dept_head_can_enable_section' => false,
                    'hide_full_sections' => true,
                    'is_public_form_enabled' => true,
                    'enable_training_type_practice' => true,
                    'enable_training_type_university' => true,
                    'can_university_reapply' => false,
                    'can_practice_reapply' => false,
                ]),
                'created_at' => now(),
                'updated_at' => now(),
            ]
        );
    }
}
