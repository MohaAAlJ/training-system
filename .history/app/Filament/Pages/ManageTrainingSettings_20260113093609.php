<?php

namespace App\Filament\Pages;

use App\Settings\TrainingSettings;
use Filament\Schemas\Schema;
use Filament\Pages\SettingsPage;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Group;
use Filament\Forms\Components\Toggle;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\CheckboxList;
use Closure;

use UnitEnum;

class ManageTrainingSettings extends SettingsPage
{
    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-cog-6-tooth';
    protected static ?string $navigationLabel = 'اعدادات التدريب';
    protected static ?string $title = 'اعدادات التدريب';
    protected static string|UnitEnum|null $navigationGroup = 'الإعدادات';

    protected static string $settings = TrainingSettings::class;

    public static function canAccess(): bool
    {
        return auth()->user()->isAdmin();
    }

    public function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                Grid::make(1)
                    ->schema([
                        Section::make('اعدادات النظام العامة')
                            ->description('إعدادات تتحكم في القواعد الأساسية للقبول والتسجيل وإمكانية إعادة التقديم.')
                            ->icon('heroicon-o-cog-6-tooth')
                            ->collapsible()
                            ->schema([
                                Grid::make(2)
                                    ->schema([
                                        Toggle::make('hide_full_sections')
                                            ->label('اظهار الأقسام المكتملة')
                                            ->helperText('عند التفعيل، سيتم اظهار الأقسام التي وصلت إلى سعتها القصوى في طلب الالتحاق.')
                                            ->default(true)
                                            ->inline(false)
                                            ->live(),

                                        Toggle::make('is_public_form_enabled')
                                            ->label('تفعيل نموذج الالتحاق العام')
                                            ->helperText('عند تفعيل هذا الخيار، سيتمكن المتدربون من تقديم الطلبات عبر البوابة العامة.')
                                            ->default(true)
                                            ->inline(false)
                                            ->live(),
                                    ]),

                                Grid::make(2)
                                    ->schema([
                                        Toggle::make('enable_training_type_practice')
                                            ->label('تفعيل تدريب المزاولة')
                                            ->helperText('إتاحة خيار تدريب المزاولة في نموذج الالتحاق.')
                                            ->default(true)
                                            ->inline(false)
                                            ->rules([
                                                fn(callable $get) => function (string $attribute, $value, Closure $fail) use ($get) {
                                                    if (!$value && !$get('enable_training_type_university')) {
                                                        $fail('يجب تفعيل نوع واحد على الأقل من التدريب.');
                                                    }
                                                },
                                            ])
                                            ->live(),

                                        Toggle::make('enable_training_type_university')
                                            ->label('تفعيل تدريب الجامعات')
                                            ->helperText('إتاحة خيار تدريب الجامعات في نموذج الالتحاق.')
                                            ->default(true)
                                            ->inline(false)
                                            ->rules([
                                                fn(callable $get) => function (string $attribute, $value, Closure $fail) use ($get) {
                                                    if (!$value && !$get('enable_training_type_practice')) {
                                                        $fail('يجب تفعيل نوع واحد على الأقل من التدريب.');
                                                    }
                                                },
                                            ])
                                            ->live(),
                                    ]),

                                Grid::make(2)
                                    ->schema([
                                        Toggle::make('can_university_reapply')
                                            ->label('إمكانية إعادة التقديم (تدريب جامعي)')
                                            ->helperText('السماح للمتدربين الجامعيين الذين أتموا تدريبهم بتقديم طلبات جديدة.')
                                            ->default(false)
                                            ->inline(false)
                                            ->live(),

                                        Toggle::make('can_practice_reapply')
                                            ->label('إمكانية إعادة التقديم (مزاولة مهنة)')
                                            ->helperText('السماح لمتدربي المزاولة الذين أتموا تدريبهم بتقديم طلبات جديدة.')
                                            ->default(false)
                                            ->inline(false)
                                            ->live(),
                                    ]),
                            ]),

                        Grid::make(2)
                            ->schema([
                                Section::make('صلاحيات رؤساء الوحدات الإدارية (HOA)')
                                    ->description('إعدادات التحكم في ما يمكن لرؤساء الوحدات الإدارية القيام به.')
                                    ->icon('heroicon-o-user-group')
                                    ->collapsible()
                                    ->schema([
                                        Toggle::make('hoa_can_edit_section')
                                            ->label('تعديل بيانات القسم')
                                            ->helperText('السماح بتعديل الاسم، السعة، وغيرها')
                                            ->default(false)
                                            ->inline(false)
                                            ->live(),

                                        Toggle::make('hoa_can_enable_section')
                                            ->label('تفعيل/تعطيل القسم')
                                            ->helperText('السماح بتغيير حالة القسم (نشط/غير نشط)')
                                            ->default(false)
                                            ->inline(false)
                                            ->live(),
                                    ]),

                                Section::make('صلاحيات رؤساء الدوائر')
                                    ->description('إعدادات التحكم في ما يمكن لرؤساء الدوائر القيام به.')
                                    ->icon('heroicon-o-building-office-2')
                                    ->collapsible()
                                    ->schema([
                                        Toggle::make('dept_head_can_edit_section')
                                            ->label('تعديل بيانات القسم')
                                            ->helperText('السماح بتعديل الاسم، السعة، وغيرها')
                                            ->default(false)
                                            ->inline(false)
                                            ->live(),

                                        Toggle::make('dept_head_can_enable_section')
                                            ->label('تفعيل/تعطيل القسم')
                                            ->helperText('السماح بتغيير حالة القسم (نشط/غير نشط)')
                                            ->default(false)
                                            ->inline(false)
                                            ->live(),
                                    ]),
                            ]),

                        Section::make('وضع الصيانة')
                            ->description('تعطيل دخول المستخدمين إلى لوحة التحكم مع عرض رسالة مخصصة.')
                            ->icon('heroicon-o-wrench-screwdriver')
                            ->collapsible()
                            ->schema([
                                Toggle::make('is_maintenance_mode')
                                    ->label('تفعيل وضع الصيانة')
                                    ->inline(false)
                                    ->live(),

                                Textarea::make('maintenance_message')
                                    ->label('رسالة الصيانة')
                                    ->rows(4)
                                    ->visible(fn($get) => $get('is_maintenance_mode'))
                                    ->required(),

                                CheckboxList::make('maintenance_roles')
                                    ->label('الأدوار المطبق عليها الصيانة')
                                    ->options([
                                        \App\Models\User::ROLE_COLLEGE => 'مشرف كلية',
                                        \App\Models\User::ROLE_MOH => 'وزارة الصحة',
                                        \App\Models\User::ROLE_GTM => 'مدير التدريب العام',
                                    ])
                                    ->visible(fn($get) => $get('is_maintenance_mode'))
                                    ->columns(3)
                                    ->gridDirection('row'),
                            ]),
                    ]),
            ]);
    }
}
