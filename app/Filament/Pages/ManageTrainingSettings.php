<?php

namespace App\Filament\Pages;

use App\Models\User;
use App\Settings\TrainingSettings;
use Filament\Schemas\Schema;
use Filament\Pages\SettingsPage;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Tabs;
use Filament\Forms\Components\Toggle;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\CheckboxList;
use Filament\Forms\Components\Placeholder;
use Filament\Actions\Action;
use Closure;
use Filament\Notifications\Notification;

use UnitEnum;

class ManageTrainingSettings extends SettingsPage
{
    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-cog-6-tooth';
    protected static ?string $navigationLabel = 'اعدادات التدريب';
    protected static ?string $title = 'اعدادات التدريب';
    protected static string|\UnitEnum|null $navigationGroup = 'الإعدادات';

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
                                            ->onIcon('heroicon-m-check-circle')
                                            ->offIcon('heroicon-m-x-circle')
                                            ->onColor('success')
                                            ->offColor('danger')
                                            ->inline()
                                            ->columnSpan(1)
                                            ->live(),

                                        Toggle::make('hide_full_sections')
                                            ->label('إظهار الأقسام المكتملة')
                                            ->helperText('عرض الأقسام التي وصلت لسعتها القصوى')
                                            ->default(true)
                                            ->onIcon('heroicon-m-check-circle')
                                            ->offIcon('heroicon-m-x-circle')
                                            ->onColor('success')
                                            ->offColor('danger')
                                            ->inline()
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
                                            ->onIcon('heroicon-m-check-circle')
                                            ->offIcon('heroicon-m-x-circle')
                                            ->onColor('success')
                                            ->offColor('danger')
                                            ->inline()
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
                                            ->onIcon('heroicon-m-check-circle')
                                            ->offIcon('heroicon-m-x-circle')
                                            ->onColor('success')
                                            ->offColor('danger')
                                            ->inline()
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
                                            ->onIcon('heroicon-m-check-circle')
                                            ->offIcon('heroicon-m-x-circle')
                                            ->onColor('success')
                                            ->offColor('danger')
                                            ->inline()
                                            ->columnSpan(1)
                                            ->live(),

                                        Toggle::make('can_university_reapply')
                                            ->label('إعادة التقديم للتدريب الجامعي')
                                            ->helperText('السماح للمتدربين الجامعيين الذين أتموا تدريبهم بالتقديم مجدداً')
                                            ->default(false)
                                            ->onIcon('heroicon-m-check-circle')
                                            ->offIcon('heroicon-m-x-circle')
                                            ->onColor('success')
                                            ->offColor('danger')
                                            ->inline()
                                            ->columnSpan(1)
                                            ->live(),
                                    ]),

                                Toggle::make('enable_change_password')
                                    ->label('تفعيل تغيير كلمة المرور')
                                    ->helperText('إظهار خيار تغيير كلمة المرور في القائمة العلوية والوصول للصفحة')
                                    ->default(false)
                                    ->onIcon('heroicon-m-check-circle')
                                    ->offIcon('heroicon-m-x-circle')
                                    ->onColor('success')
                                    ->offColor('danger')
                                    ->inline()
                                    ->live(),
                            ]),

                        Tabs\Tab::make('الصلاحيات')
                            ->icon('heroicon-o-shield-check')
                            ->schema([
                                Grid::make(2)
                                    ->schema([
                                        Section::make('رؤساء الوحدات الإدارية')
                                            ->description('الصلاحيات الممنوحة لرؤساء الوحدات')
                                            ->icon('heroicon-o-user-group')
                                            ->schema([
                                                Toggle::make('hoa_can_edit_section')
                                                    ->label('تعديل بيانات الأقسام')
                                                    ->helperText('تعديل الاسم، السعة، والتفاصيل')
                                                    ->default(false)
                                                    ->onIcon('heroicon-m-check-circle')
                                                    ->offIcon('heroicon-m-x-circle')
                                                    ->onColor('success')
                                                    ->offColor('danger')
                                                    ->inline()
                                                    ->live(),

                                                Toggle::make('hoa_can_enable_section')
                                                    ->label('التحكم في حالة الأقسام')
                                                    ->helperText('تفعيل أو تعطيل الأقسام')
                                                    ->default(false)
                                                    ->onIcon('heroicon-m-check-circle')
                                                    ->offIcon('heroicon-m-x-circle')
                                                    ->onColor('success')
                                                    ->offColor('danger')
                                                    ->inline()
                                                    ->live(),
                                            ]),

                                        Section::make('رؤساء الدوائر')
                                            ->description('الصلاحيات الممنوحة لرؤساء الدوائر')
                                            ->icon('heroicon-o-building-office-2')
                                            ->schema([
                                                Toggle::make('dept_head_can_edit_section')
                                                    ->label('تعديل بيانات الأقسام')
                                                    ->helperText('الاسم، السعة، والتفاصيل')
                                                    ->default(false)
                                                    ->onIcon('heroicon-m-check-circle')
                                                    ->offIcon('heroicon-m-x-circle')
                                                    ->onColor('success')
                                                    ->offColor('danger')
                                                    ->inline()
                                                    ->live(),

                                                Toggle::make('dept_head_can_enable_section')
                                                    ->label('التحكم في حالة الأقسام')
                                                    ->helperText('تفعيل أو تعطيل الأقسام')
                                                    ->default(false)
                                                    ->onIcon('heroicon-m-check-circle')
                                                    ->offIcon('heroicon-m-x-circle')
                                                    ->onColor('success')
                                                    ->offColor('danger')
                                                    ->inline()
                                                    ->live(),
                                            ]),
                                    ]),
                            ]),

                        Tabs\Tab::make('وضع الصيانة')
                            ->icon('heroicon-o-wrench-screwdriver')
                            ->badge(fn($get) => $get('is_maintenance_mode') ? 'نشط' : null)
                            ->badgeColor('danger')
                            ->schema([
                                Section::make('إعدادات الصيانة')
                                    ->description('تعطيل مؤقت للنظام مع عرض رسالة للمستخدمين')
                                    ->icon('heroicon-o-exclamation-triangle')
                                    ->schema([
                                        Toggle::make('is_maintenance_mode')
                                            ->label('تفعيل وضع الصيانة')
                                            ->helperText('سيتم منع المستخدمين المحددين من الوصول إلى النظام')
                                            ->onIcon('heroicon-m-check-circle')
                                            ->offIcon('heroicon-m-x-circle')
                                            ->onColor('success')
                                            ->offColor('danger')
                                            ->inline()
                                            ->live(),

                                        Grid::make(2)
                                            ->schema([
                                                TextInput::make('maintenance_title')
                                                    ->label('عنوان الصيانة')
                                                    ->placeholder('الموقع تحت الصيانة')
                                                    ->maxLength(100)
                                                    ->visible(fn($get) => $get('is_maintenance_mode'))
                                                    ->required(),

                                                TextInput::make('maintenance_message')
                                                    ->label('رسالة الصيانة')
                                                    ->placeholder('النظام قيد الصيانة. سيتم استعادة الخدمة قريباً')
                                                    ->maxLength(500)
                                                    ->columnSpanFull()
                                                    ->visible(fn($get) => $get('is_maintenance_mode'))
                                                    ->required(),

                                                CheckboxList::make('maintenance_roles')
                                                    ->label('الأدوار المتأثرة')
                                                    ->helperText('الأدوار التي سيتم منعها من الدخول')
                                                    ->options([
                                                        \App\Models\User::ROLE_COLLEGE => 'مشرف كلية',
                                                        \App\Models\User::ROLE_MOH => 'وزارة الصحة',
                                                        \App\Models\User::ROLE_GTM => 'مدير التدريب العام',
                                                        \App\Models\User::ROLE_ASSISTANT_TRAINING_MANAGER => 'مساعد مدير التدريب',
                                                        \App\Models\User::ROLE_HOA => 'رئيس الإدارة',
                                                        \App\Models\User::ROLE_HOM => 'رئيس  الإدارة الطبية',
                                                        \App\Models\User::ROLE_DEPARTMENT => 'رئيس الدائرة',
                                                        \App\Models\User::ROLE_SECTION => 'رئيس القسم',
                                                    ])
                                                    ->visible(fn($get) => $get('is_maintenance_mode'))
                                                    ->columns(3)
                                                    ->columnSpanFull()
                                                    ->bulkToggleable(),
                                            ])
                                            ->visible(fn($get) => $get('is_maintenance_mode')),
                                    ]),
                            ]),

                        Tabs\Tab::make('إعدادات الإشعارات')
                            ->icon('heroicon-o-bell')
                            ->schema([
                                // WhatsApp Notifications Section
                                Section::make('إشعارات واتساب')
                                    ->description('إدارة الرسائل التلقائية المرسلة عبر واتساب')
                                    ->icon('heroicon-o-chat-bubble-bottom-center-text')
                                    ->iconColor('success')
                                    ->collapsible()
                                    ->schema([
                                        // Master toggle for WhatsApp
                                        Toggle::make('whatsapp_notifications_enabled')
                                            ->label('تفعيل إشعارات واتساب')
                                            ->helperText('تفعيل أو تعطيل جميع رسائل واتساب للنظام')
                                            ->default(true)
                                            ->onIcon('heroicon-m-check-circle')
                                            ->offIcon('heroicon-m-x-circle')
                                            ->onColor('success')
                                            ->offColor('danger')
                                            ->inline()
                                            ->live()
                                            ->afterStateUpdated(function ($state, callable $set) {
                                                if (!$state) {
                                                    // Disable all sub-toggles
                                                    $set('whatsapp_initial_approve', false);
                                                    $set('whatsapp_start_training', false);
                                                    $set('whatsapp_end_training', false);
                                                    $set('whatsapp_gtm_new_application', false);
                                                    $set('whatsapp_hoa_started_training', false);
                                                    $set('whatsapp_hom_started_training', false);
                                                    $set('whatsapp_department_started_training', false);
                                                    $set('whatsapp_section_started_training', false);
                                                    $set('whatsapp_college_approved_application', false);
                                                }
                                            }),

                                        // Trainee Notifications
                                        Section::make('إشعارات المتدربين')
                                            ->description('الحالات التي يتم فيها إرسال إشعار للمتدرب')
                                            ->icon('heroicon-o-user')
                                            ->iconColor('primary')
                                            ->collapsed(fn($get) => !$get('whatsapp_notifications_enabled'))
                                            ->collapsible()
                                            ->schema([
                                                Grid::make(3)
                                                    ->schema([
                                                        Toggle::make('whatsapp_initial_approve')
                                                            ->label('الموافقة المبدئية')
                                                            ->helperText('عند الموافقة المبدئية')
                                                            ->default(false)
                                                            ->inline()
                                                            ->disabled(fn($get) => !$get('whatsapp_notifications_enabled'))
                                                            ->dehydrated(),

                                                        Toggle::make('whatsapp_start_training')
                                                            ->label('بدء التدريب')
                                                            ->helperText('عند بدء فترة التدريب')
                                                            ->default(false)
                                                            ->inline()
                                                            ->disabled(fn($get) => !$get('whatsapp_notifications_enabled'))
                                                            ->dehydrated(),

                                                        Toggle::make('whatsapp_end_training')
                                                            ->label('قرب الانتهاء')
                                                            ->helperText('قبل انتهاء التدريب')
                                                            ->onIcon('heroicon-m-check-circle')
                                                            ->offIcon('heroicon-m-x-circle')
                                                            ->onColor('success')
                                                            ->offColor('danger')
                                                            ->inline()
                                                            ->disabled(fn($get) => !$get('whatsapp_notifications_enabled'))
                                                            ->live()
                                                            ->dehydrated(),
                                                    ]),

                                                TextInput::make('whatsapp_end_training_days')
                                                    ->label('عدد الأيام قبل الإرسال')
                                                    ->numeric()
                                                    ->default(3)
                                                    ->minValue(1)
                                                    ->maxValue(30)
                                                    ->suffix('يوم')
                                                    ->visible(fn($get) => $get('whatsapp_end_training') && $get('whatsapp_notifications_enabled'))
                                                    ->required()
                                                    ->columnSpanFull(),
                                            ])
                                            ->visible(fn($get) => $get('whatsapp_notifications_enabled')),

                                        // System Roles Notifications
                                        Section::make('إشعارات المسؤولين')
                                            ->description('إشعارات يتم إرسالها للمسؤولين عند أحداث معينة')
                                            ->icon('heroicon-o-user-group')
                                            ->iconColor('warning')
                                            ->collapsed(fn($get) => !$get('whatsapp_notifications_enabled'))
                                            ->collapsible()
                                            ->schema([
                                                Grid::make(3)
                                                    ->schema([
                                                        Toggle::make('whatsapp_gtm_new_application')
                                                            ->label('مدير التدريب (GTM)')
                                                            ->helperText('عند طلب جديد')
                                                            ->default(false)
                                                            ->onIcon('heroicon-m-check-circle')
                                                            ->offIcon('heroicon-m-x-circle')
                                                            ->onColor('success')
                                                            ->offColor('danger')
                                                            ->inline()
                                                            ->disabled(fn($get) => !$get('whatsapp_notifications_enabled')),

                                                        Toggle::make('whatsapp_hoa_started_training')
                                                            ->label('المدير الإداري (HOA)')
                                                            ->helperText('عند بدء تدريب')
                                                            ->default(false)
                                                            ->onIcon('heroicon-m-check-circle')
                                                            ->offIcon('heroicon-m-x-circle')
                                                            ->onColor('success')
                                                            ->offColor('danger')
                                                            ->inline()
                                                            ->disabled(fn($get) => !$get('whatsapp_notifications_enabled')),

                                                        Toggle::make('whatsapp_hom_started_training')
                                                            ->label('المدير الطبي (HOM)')
                                                            ->helperText('عند بدء تدريب طبي')
                                                            ->default(false)
                                                            ->onIcon('heroicon-m-check-circle')
                                                            ->offIcon('heroicon-m-x-circle')
                                                            ->onColor('success')
                                                            ->offColor('danger')
                                                            ->inline()
                                                            ->disabled(fn($get) => !$get('whatsapp_notifications_enabled')),

                                                        Toggle::make('whatsapp_department_started_training')
                                                            ->label('مدير الدائرة')
                                                            ->helperText('عند بدء تدريب بدائرته')
                                                            ->default(false)
                                                            ->onIcon('heroicon-m-check-circle')
                                                            ->offIcon('heroicon-m-x-circle')
                                                            ->onColor('success')
                                                            ->offColor('danger')
                                                            ->inline()
                                                            ->disabled(fn($get) => !$get('whatsapp_notifications_enabled')),

                                                        Toggle::make('whatsapp_section_started_training')
                                                            ->label('مسؤول القسم')
                                                            ->helperText('عند بدء تدريب بقسمه')
                                                            ->default(false)
                                                            ->onIcon('heroicon-m-check-circle')
                                                            ->offIcon('heroicon-m-x-circle')
                                                            ->onColor('success')
                                                            ->offColor('danger')
                                                            ->inline()
                                                            ->disabled(fn($get) => !$get('whatsapp_notifications_enabled')),

                                                        Toggle::make('whatsapp_college_approved_application')
                                                            ->label('مشرف الكلية')
                                                            ->helperText('عند قبول طلب من كليته')
                                                            ->default(false)
                                                            ->onIcon('heroicon-m-check-circle')
                                                            ->offIcon('heroicon-m-x-circle')
                                                            ->onColor('success')
                                                            ->offColor('danger')
                                                            ->inline()
                                                            ->disabled(fn($get) => !$get('whatsapp_notifications_enabled')),
                                                    ]),
                                            ])
                                            ->visible(fn($get) => $get('whatsapp_notifications_enabled')),
                                    ]),

                                // Telegram Notifications Section
                                Section::make('إشعارات تيليجرام')
                                    ->description('إدارة الإشعارات والتقارير المرسلة عبر تيليجرام')
                                    ->icon('heroicon-o-paper-airplane')
                                    ->iconColor('info')
                                    ->collapsible()
                                    ->schema([
                                        // Master toggle
                                        Toggle::make('telegram_enabled')
                                            ->label('تفعيل إشعارات تيليجرام')
                                            ->helperText('تفعيل أو تعطيل جميع رسائل تيليجرام')
                                            ->default(false)
                                            ->onIcon('heroicon-m-check-circle')
                                            ->offIcon('heroicon-m-x-circle')
                                            ->onColor('success')
                                            ->offColor('danger')
                                            ->inline()
                                            ->live()
                                            ->afterStateUpdated(function ($state, callable $set) {
                                                if (!$state) {
                                                    $set('telegram_log_errors', false);
                                                    $set('telegram_log_activities', false);
                                                    $set('telegram_new_applications', false);
                                                    $set('telegram_status_changes', false);
                                                    $set('telegram_daily_report', false);
                                                }
                                            }),

                                        // Credentials Section
                                        Section::make('بيانات الاتصال')
                                            ->description('معلومات البوت والربط')
                                            ->icon('heroicon-o-key')
                                            ->collapsed()
                                            ->collapsible()
                                            ->visible(fn($get) => $get('telegram_enabled'))
                                            ->schema([
                                                Grid::make(2)
                                                    ->schema([
                                                        TextInput::make('telegram_bot_token')
                                                            ->label('Bot Token')
                                                            ->helperText('رمز البوت من @BotFather')
                                                            ->password()
                                                            ->revealable()
                                                            ->required(fn($get) => $get('telegram_enabled'))
                                                            ->columnSpanFull(),

                                                        TextInput::make('telegram_chat_ids')
                                                            ->label('Chat IDs')
                                                            ->helperText('معرفات المحادثات (مفصولة بفاصلة)')
                                                            ->placeholder('123456789, 987654321')
                                                            ->required(fn($get) => $get('telegram_enabled'))
                                                            ->columnSpan(1),

                                                        TextInput::make('telegram_access_code')
                                                            ->label('كود الحماية')
                                                            ->helperText('كود اختياري للحماية')
                                                            ->password()
                                                            ->revealable()
                                                            ->columnSpan(1),
                                                    ]),
                                            ]),

                                        // Notification Types
                                        Section::make('أنواع الإشعارات')
                                            ->icon('heroicon-o-bell-alert')
                                            ->collapsed(fn($get) => !$get('telegram_enabled'))
                                            ->collapsible()
                                            ->visible(fn($get) => $get('telegram_enabled'))
                                            ->schema([
                                                Grid::make(3)
                                                    ->schema([
                                                        Toggle::make('telegram_new_applications')
                                                            ->label('طلبات جديدة')
                                                            ->helperText('إشعار فوري')
                                                            ->default(false)
                                                            ->onIcon('heroicon-m-check-circle')
                                                            ->offIcon('heroicon-m-x-circle')
                                                            ->onColor('success')
                                                            ->offColor('danger')
                                                            ->inline()
                                                            ->disabled(fn($get) => !$get('telegram_enabled'))
                                                            ->dehydrated(),

                                                        Toggle::make('telegram_status_changes')
                                                            ->label('تغيير الحالة')
                                                            ->helperText('تحديثات الطلبات')
                                                            ->default(false)
                                                            ->onIcon('heroicon-m-check-circle')
                                                            ->offIcon('heroicon-m-x-circle')
                                                            ->onColor('success')
                                                            ->offColor('danger')
                                                            ->inline()
                                                            ->disabled(fn($get) => !$get('telegram_enabled'))
                                                            ->dehydrated(),

                                                        Toggle::make('telegram_log_errors')
                                                            ->label('الأخطاء البرمجية')
                                                            ->helperText('تفاصيل الأخطاء')
                                                            ->default(false)
                                                            ->onIcon('heroicon-m-check-circle')
                                                            ->offIcon('heroicon-m-x-circle')
                                                            ->onColor('success')
                                                            ->offColor('danger')
                                                            ->inline()
                                                            ->disabled(fn($get) => !$get('telegram_enabled'))
                                                            ->dehydrated(),

                                                        Toggle::make('telegram_log_activities')
                                                            ->label('سجل النشاطات')
                                                            ->helperText('النشاطات المهمة')
                                                            ->default(false)
                                                            ->onIcon('heroicon-m-check-circle')
                                                            ->offIcon('heroicon-m-x-circle')
                                                            ->onColor('success')
                                                            ->offColor('danger')
                                                            ->inline()
                                                            ->disabled(fn($get) => !$get('telegram_enabled'))
                                                            ->dehydrated(),

                                                        Toggle::make('telegram_daily_report')
                                                            ->label('التقرير اليومي')
                                                            ->helperText('ملخص يومي')
                                                            ->default(true)
                                                            ->onIcon('heroicon-m-check-circle')
                                                            ->offIcon('heroicon-m-x-circle')
                                                            ->onColor('success')
                                                            ->offColor('danger')
                                                            ->inline()
                                                            ->disabled(fn($get) => !$get('telegram_enabled'))
                                                            ->live()
                                                            ->dehydrated(),

                                                        TextInput::make('telegram_daily_report_time')
                                                            ->label('وقت التقرير')
                                                            ->type('time')
                                                            ->default('08:00')
                                                            ->disabled(fn($get) => !$get('telegram_enabled') || !$get('telegram_daily_report')),
                                                    ]),
                                            ]),
                                    ]),
                            ]),

                        Tabs\Tab::make('صفحات الأخطاء')
                            ->icon('heroicon-o-exclamation-triangle')
                            ->schema([
                                Section::make('صفحة 404 (غير موجود)')
                                    ->description('تخصيص رسالة الصفحة غير الموجودة')
                                    ->icon('heroicon-o-document-magnifying-glass')
                                    ->schema([
                                        TextInput::make('not_found_title')
                                            ->label('عنوان الصفحة')
                                            ->placeholder('الصفحة غير موجودة')
                                            ->maxLength(100)
                                            ->required(),

                                        Textarea::make('not_found_message')
                                            ->label('نص الرسالة')
                                            ->placeholder('عذراً، الصفحة التي تبحث عنها غير موجودة')
                                            ->rows(3)
                                            ->maxLength(500)
                                            ->required(),
                                    ]),
                            ]),

                        Tabs\Tab::make('البحث الذكي')
                            ->icon('heroicon-o-sparkles')
                            ->schema([
                                Section::make('إعدادات البحث الذكي')
                                    ->description('التحكم في إتاحة البحث الذكي في جداول Filament')
                                    ->icon('heroicon-o-cpu-chip')
                                    ->schema([
                                        Toggle::make('ai_search_enabled')
                                            ->label('تفعيل البحث الذكي')
                                            ->helperText('إظهار حقل البحث الحر في جداول الطلبات والمتدربين')
                                            ->default(false)
                                            ->onIcon('heroicon-m-check-circle')
                                            ->offIcon('heroicon-m-x-circle')
                                            ->onColor('success')
                                            ->offColor('danger')
                                            ->inline()
                                            ->live(),

                                        CheckboxList::make('ai_search_allowed_roles')
                                            ->label('الأدوار المسموح لها')
                                            ->helperText('البحث الذكي سيظهر لهذه الأدوار فقط عند تفعيله')
                                            ->options(User::ROLE_LABELS)
                                            ->columns(2)
                                            ->disabled(fn(callable $get) => ! $get('ai_search_enabled'))
                                            ->dehydrated(),
                                    ]),
                            ]),

                        Tabs\Tab::make('النسخ الاحتياطية')
                            ->icon('heroicon-o-arrow-down-tray')
                            ->schema([
                                Section::make('إدارة النسخ الاحتياطية')
                                    ->description('إنشاء واستعادة النسخ الاحتياطية من قاعدة البيانات')
                                    ->icon('heroicon-o-server-stack')
                                    ->schema([
                                        Placeholder::make('last_backup_info')
                                            ->label('معلومات آخر نسخة احتياطية')
                                            ->content(function (TrainingSettings $settings) {
                                                if (!$settings->last_backup_at) {
                                                    return 'لم يتم إنشاء نسخة احتياطية بعد.';
                                                }
                                                $date = date('Y-m-d', strtotime($settings->last_backup_at));
                                                $time = date('h:i A', strtotime($settings->last_backup_at));
                                                return "آخر نسخة احتياطية: {$date} في {$time}";
                                            })
                                            ->columnSpanFull(),
                                    ]),
                            ]),
                    ])
                    ->columnSpanFull()
                    ->persistTabInQueryString(),
            ]);
    }

    protected function getHeaderActions(): array
    {
        return [
            Action::make('backup')
                ->label('إنشاء نسخة احتياطية')
                ->icon('heroicon-o-arrow-down-tray')
                ->color('success')
                ->requiresConfirmation()
                ->modalHeading('إنشاء نسخة احتياطية')
                ->modalDescription('سيتم إنشاء نسخة احتياطية من قاعدة البيانات وتحميلها على جهازك.')
                ->modalSubmitActionLabel('إنشاء وتحميل')
                ->action(function () {
                    $settings = app(TrainingSettings::class);
                    $settings->last_backup_at = now()->toDateTimeString();
                    $settings->save();

                    Notification::make()
                        ->title('جاري تحميل النسخة الاحتياطية')
                        ->success()
                        ->send();

                    return redirect()->route('backup.download');
                }),
        ];
    }
}
