<?php

namespace App\Filament\Pages;

use App\Settings\TrainingSettings;
use Filament\Schemas\Schema;
use Filament\Pages\SettingsPage;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Tabs;
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
                Tabs::make('Settings')
                    ->tabs([
                        Tabs\Tab::make('الإعدادات العامة')
                            ->icon('heroicon-o-adjustments-horizontal')
                            ->schema([
                                Section::make('نموذج الالتحاق والتسجيل')
                                    ->description('التحكم في إتاحة النظام والأقسام للمتدربين')
                                    ->icon('heroicon-o-clipboard-document-list')
                                    ->columns(2)
                                    ->schema([
                                        Toggle::make('is_public_form_enabled')
                                            ->label('تفعيل نموذج الالتحاق العام')
                                            ->helperText('إتاحة تقديم الطلبات عبر البوابة العامة')
                                            ->default(true)
                                            ->inline(false)
                                            ->columnSpan(1)
                                            ->live(),

                                        Toggle::make('hide_full_sections')
                                            ->label('إظهار الأقسام المكتملة')
                                            ->helperText('عرض الأقسام التي وصلت لسعتها القصوى')
                                            ->default(true)
                                            ->inline(false)
                                            ->columnSpan(1)
                                            ->live(),
                                    ]),

                                Section::make('أنواع التدريب المتاحة')
                                    ->description('تحديد أنواع التدريب التي يمكن التقديم عليها')
                                    ->icon('heroicon-o-academic-cap')
                                    ->columns(2)
                                    ->schema([
                                        Toggle::make('enable_training_type_practice')
                                            ->label('تدريب المزاولة')
                                            ->helperText('للممارسين الراغبين في الحصول على ترخيص مزاولة المهنة')
                                            ->default(true)
                                            ->inline(false)
                                            ->columnSpan(1)
                                            ->rules([
                                                fn(callable $get) => function (string $attribute, $value, Closure $fail) use ($get) {
                                                    if (!$value && !$get('enable_training_type_university')) {
                                                        $fail('يجب تفعيل نوع واحد على الأقل من التدريب.');
                                                    }
                                                },
                                            ])
                                            ->live(),

                                        Toggle::make('enable_training_type_university')
                                            ->label('تدريب الجامعات')
                                            ->helperText('لطلاب الجامعات ضمن متطلبات التخرج')
                                            ->default(true)
                                            ->inline(false)
                                            ->columnSpan(1)
                                            ->rules([
                                                fn(callable $get) => function (string $attribute, $value, Closure $fail) use ($get) {
                                                    if (!$value && !$get('enable_training_type_practice')) {
                                                        $fail('يجب تفعيل نوع واحد على الأقل من التدريب.');
                                                    }
                                                },
                                            ])
                                            ->live(),
                                    ]),

                                Section::make('سياسة إعادة التقديم')
                                    ->description('السماح للمتدربين السابقين بتقديم طلبات جديدة')
                                    ->icon('heroicon-o-arrow-path')
                                    ->columns(2)
                                    ->schema([
                                        Toggle::make('can_practice_reapply')
                                            ->label('إعادة التقديم لتدريب المزاولة')
                                            ->helperText('السماح لمتدربي المزاولة الذين أتموا تدريبهم بالتقديم مجدداً')
                                            ->default(false)
                                            ->inline(false)
                                            ->columnSpan(1)
                                            ->live(),

                                        Toggle::make('can_university_reapply')
                                            ->label('إعادة التقديم للتدريب الجامعي')
                                            ->helperText('السماح للمتدربين الجامعيين الذين أتموا تدريبهم بالتقديم مجدداً')
                                            ->default(false)
                                            ->inline(false)
                                            ->columnSpan(1)
                                            ->live(),
                                    ]),
                            ]),

                        Tabs\Tab::make('الصلاحيات')
                            ->icon('heroicon-o-shield-check')
                            ->schema([
                                Grid::make(2)
                                    ->schema([
                                        Section::make('رؤساء الوحدات الإدارية (HOA)')
                                            ->description('الصلاحيات الممنوحة لرؤساء الوحدات')
                                            ->icon('heroicon-o-user-group')
                                            ->schema([
                                                Toggle::make('hoa_can_edit_section')
                                                    ->label('تعديل بيانات الأقسام')
                                                    ->helperText('الاسم، السعة، والتفاصيل الأخرى')
                                                    ->default(false)
                                                    ->inline(false)
                                                    ->live(),

                                                Toggle::make('hoa_can_enable_section')
                                                    ->label('تفعيل/تعطيل الأقسام')
                                                    ->helperText('تغيير حالة القسم (نشط/غير نشط)')
                                                    ->default(false)
                                                    ->inline(false)
                                                    ->live(),
                                            ]),

                                        Section::make('رؤساء الدوائر')
                                            ->description('الصلاحيات الممنوحة لرؤساء الدوائر')
                                            ->icon('heroicon-o-building-office-2')
                                            ->schema([
                                                Toggle::make('dept_head_can_edit_section')
                                                    ->label('تعديل بيانات الأقسام')
                                                    ->helperText('الاسم، السعة، والتفاصيل الأخرى')
                                                    ->default(false)
                                                    ->inline(false)
                                                    ->live(),

                                                Toggle::make('dept_head_can_enable_section')
                                                    ->label('تفعيل/تعطيل الأقسام')
                                                    ->helperText('تغيير حالة القسم (نشط/غير نشط)')
                                                    ->default(false)
                                                    ->inline(false)
                                                    ->live(),
                                            ]),
                                    ]),
                            ]),

                        Tabs\Tab::make('وضع الصيانة')
                            ->icon('heroicon-o-wrench-screwdriver')
                            ->badge(fn($get) => $get('is_maintenance_mode') ? 'نشط' : null)
                            ->badgeColor('danger')
                            ->schema([
                                Section::make()
                                    ->description('تعطيل مؤقت للنظام مع عرض رسالة للمستخدمين')
                                    ->schema([
                                        Toggle::make('is_maintenance_mode')
                                            ->label('تفعيل وضع الصيانة')
                                            ->helperText('سيتم منع المستخدمين المحددين من الوصول إلى النظام')
                                            ->inline(false)
                                            ->live(),

                                        Grid::make(1)
                                            ->schema([
                                                Textarea::make('maintenance_message')
                                                    ->label('رسالة الصيانة')
                                                    ->placeholder('مثال: النظام قيد الصيانة حالياً. سيتم استعادة الخدمة قريباً.')
                                                    ->rows(4)
                                                    ->maxLength(500)
                                                    ->visible(fn($get) => $get('is_maintenance_mode'))
                                                    ->required(),

                                                CheckboxList::make('maintenance_roles')
                                                    ->label('الأدوار المتأثرة بالصيانة')
                                                    ->helperText('حدد الأدوار التي سيتم منعها من الدخول أثناء الصيانة')
                                                    ->options([
                                                        \App\Models\User::ROLE_COLLEGE => 'مشرف كلية',
                                                        \App\Models\User::ROLE_MOH => 'وزارة الصحة',
                                                        \App\Models\User::ROLE_GTM => 'مدير التدريب العام',
                                                    ])
                                                    ->visible(fn($get) => $get('is_maintenance_mode'))
                                                    ->columns(1)
                                                    ->bulkToggleable(),
                                            ])
                                            ->visible(fn($get) => $get('is_maintenance_mode')),
                                    ]),
                            ]),
                    ])
                    ->columnSpanFull()
                    ->persistTabInQueryString(),
            ]);
    }
}