<?php

namespace App\Filament\Resources\GeneralSettings\Schemas;

use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Schema;

class GeneralSettingForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('قواعد النظام العام')
                    ->description('إعدادات تتحكم في القواعد الأساسية للقبول والتسجيل.')
                    ->icon('heroicon-o-cog-6-tooth')
                    ->schema([
                        Toggle::make('hide_full_sections')
                            ->label('إخفاء الأقسام المكتملة')
                            ->helperText('عند التفعيل، لن تظهر الأقسام التي وصلت لسعتها القصوى في طلب الالتحاق. عند التعطيل، ستظهر كافة الأقسام وسيسمح بالتقديم فيها.')
                            ->default(true)
                            ->live(),

                        Toggle::make('is_public_form_enabled')
                            ->label('تفعيل نموذج الالتحاق العام')
                            ->helperText('عند تفعيل هذا الخيار، سيتمكن المتدربون من تقديم الطلبات عبر البوابة العامة. عند التعطيل، سيتم إغلاق البوابة أمام الطلبات الجديدة.')
                            ->default(true)
                            ->live(),

                        Toggle::make('enable_training_type_practice')
                            ->label('تفعيل تدريب المزاولة')
                            ->helperText('إتاحة خيار تدريب المزاولة في نموذج الالتحاق.')
                            ->default(true)
                            ->live(),

                        Toggle::make('enable_training_type_university')
                            ->label('تفعيل تدريب الجامعات')
                            ->helperText('إتاحة خيار تدريب الجامعات في نموذج الالتحاق.')
                            ->default(true)
                            ->live(),
                    ]),

                Section::make('صلاحيات رؤساء الوحدات الإدارية (HOA)')
                    ->description('إعدادات التحكم في ما يمكن لرؤساء الوحدات الإدارية القيام به تجاه الأقسام التابعة لهم.')
                    ->icon('heroicon-o-user-group')
                    ->schema([
                        Grid::make(2)
                            ->schema([
                                Toggle::make('hoa_can_edit_section')
                                    ->label('تعديل بيانات القسم')
                                    ->helperText('السماح بتعديل الاسم، السعة، وغيرها')
                                    ->default(false)
                                    ->live(),

                                Toggle::make('hoa_can_enable_section')
                                    ->label('تفعيل/تعطيل القسم')
                                    ->helperText('السماح بتغيير حالة القسم (نشط/غير نشط)')
                                    ->default(false)
                                    ->live(),
                            ]),
                    ]),

                Section::make('صلاحيات رؤساء الدوائر (Department Heads)')
                    ->description('إعدادات التحكم في ما يمكن لرؤساء الدوائر القيام به تجاه الأقسام التابعة لهم.')
                    ->icon('heroicon-o-building-office-2')
                    ->schema([
                        Grid::make(2)
                            ->schema([
                                Toggle::make('dept_head_can_edit_section')
                                    ->label('تعديل بيانات القسم')
                                    ->helperText('السماح بتعديل الاسم، السعة، وغيرها')
                                    ->default(false)
                                    ->live(),

                                Toggle::make('dept_head_can_enable_section')
                                    ->label('تفعيل/تعطيل القسم')
                                    ->helperText('السماح بتغيير حالة القسم (نشط/غير نشط)')
                                    ->default(false)
                                    ->live(),
                            ]),
                    ]),
            ]);
    }
}
