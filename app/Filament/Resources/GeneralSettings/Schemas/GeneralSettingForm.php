<?php

namespace App\Filament\Resources\GeneralSettings\Schemas;

use Filament\Forms\Components\Toggle;
use Filament\Schemas\Schema;

class GeneralSettingForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Toggle::make('hoa_can_edit_section')
                    ->label('رئيس الوحدة الإدارية (HOA) يمكنه تعديل بيانات القسم')
                    ->helperText('السماح بتعديل الاسم، السعة، وغيرها')
                    ->default(false),

                Toggle::make('hoa_can_enable_section')
                    ->label('رئيس الوحدة الإدارية (HOA) يمكنه تفعيل/تعطيل القسم')
                    ->helperText('السماح بتغيير حالة القسم (نشط/غير نشط)')
                    ->default(false),

                Toggle::make('dept_head_can_edit_section')
                    ->label('رئيس الدائرة (Department Head) يمكنه تعديل بيانات القسم')
                    ->default(false),

                Toggle::make('dept_head_can_enable_section')
                    ->label('رئيس الدائرة (Department Head) يمكنه تفعيل/تعطيل القسم')
                    ->default(false),
            ]);
    }
}
