<?php

namespace App\Filament\Pages;

use App\Settings\GeneralSettings;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Pages\SettingsPage;

class ManageGeneralSettings extends SettingsPage
{
    protected static ?string $navigationIcon = 'heroicon-o-cog-6-tooth';

    protected static string $settings = GeneralSettings::class;

    protected static ?string $navigationLabel = 'اعدادات النظام';

    protected static ?string $title = 'اعدادات النظام';

    protected static ?int $navigationSort = 100;

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('General Configuration')
                    ->label('اعدادات عامة')
                    ->schema([
                        Forms\Components\Toggle::make('hide_full_sections')
                            ->label('اخفاء الشعب الممتلئة')
                            ->helperText('عند التفعيل لن تظهر الشعب الممتلئة في فورم التسجيل'),
                        Forms\Components\Toggle::make('is_public_form_enabled')
                            ->label('تفعيل التسجيل العام')
                            ->helperText('اذا تم التعطيل لن يتمكن اي احد من تقديم طلب جديد'),
                    ])->columns(2),

                Forms\Components\Section::make('Permissions')
                    ->label('صلاحيات رؤساء الأقسام والشعَب')
                    ->schema([
                        Forms\Components\Toggle::make('hoa_can_edit_section')
                            ->label('رئيس القسم (HOA) يمكنه تعديل الشعبة'),
                        Forms\Components\Toggle::make('hoa_can_enable_section')
                            ->label('رئيس القسم (HOA) يمكنه تفعيل الشعبة'),
                        Forms\Components\Toggle::make('dept_head_can_edit_section')
                            ->label('رئيس الدائرة الطبية يمكنه تعديل الشعبة'),
                        Forms\Components\Toggle::make('dept_head_can_enable_section')
                            ->label('رئيس الدائرة الطبية يمكنه تفعيل الشعبة'),
                    ])->columns(2),

                Forms\Components\Section::make('Training Types')
                    ->label('أنواع التدريب المتاحة')
                    ->schema([
                        Forms\Components\Toggle::make('enable_training_type_practice')
                            ->label('تفعيل تدريب مزاولة المهنة'),
                        Forms\Components\Toggle::make('enable_training_type_university')
                            ->label('تفعيل التدريب الجامعي'),
                        Forms\Components\Toggle::make('can_university_reapply')
                            ->label('امكانية تقديم طلبات اخرى لمتدربين جامعيين أتمو تدريبهم'),
                        Forms\Components\Toggle::make('can_practice_reapply')
                            ->label('امكانية تقديم طلبات اخرى لمتدربين مزاولة مهنة أتمو تدريبهم'),
                    ])->columns(2),
            ]);
    }
}
