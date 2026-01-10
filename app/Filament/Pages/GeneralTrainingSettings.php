<?php

namespace App\Filament\Pages;

use BackedEnum;
use Filament\Pages\SettingsPage;
use Filament\Forms;
use Filament\Support\Icons\Heroicon;
use App\Settings\TrainingSettings;

class GeneralTrainingSettings extends SettingsPage
{
    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedCog6Tooth;

    protected static string $settings = TrainingSettings::class;

    public static function getNavigationLabel(): string
    {
        return 'إعدادات التدريب';
    }

    public function getTitle(): string
    {
        return 'إعدادات التدريب';
    }

    protected function getFormSchema(): array
    {
        return [
                Forms\Components\Section::make('قواعد النظام العام')
                    ->description('إعدادات تتحكم في القواعد الأساسية للقبول والتسجيل وإمكانية إعادة التقديم.')
                    ->icon('heroicon-o-cog-6-tooth')
                    ->schema([
                        Forms\Components\Toggle::make('hide_full_sections')
                            ->label('إخفاء الأقسام المكتملة')
                            ->helperText('عند التفعيل، لن تظهر الأقسام التي وصلت لسعتها القصوى في طلب الالتحاق. عند التعطيل، ستظهر كافة الأقسام وسيسمح بالتقديم فيها.')
                            ->default(true)
                            ->live(),

                        Forms\Components\Toggle::make('is_public_form_enabled')
                            ->label('تفعيل نموذج الالتحاق العام')
                            ->helperText('عند تفعيل هذا الخيار، سيتمكن المتدربون من تقديم الطلبات عبر البوابة العامة. عند التعطيل، سيتم إغلاق البوابة أمام الطلبات الجديدة.')
                            ->default(true)
                            ->live(),

                        Forms\Components\Grid::make(2)
                            ->schema([
                                Forms\Components\Toggle::make('enable_training_type_practice')
                                    ->label('تفعيل تدريب المزاولة')
                                    ->helperText('إتاحة خيار تدريب المزاولة في نموذج الالتحاق.')
                                    ->default(true)
                                    ->live(),

                                Forms\Components\Toggle::make('enable_training_type_university')
                                    ->label('تفعيل التدريب الجامعي')
                                    ->helperText('إتاحة خيار التدريب الجامعي في نموذج الالتحاق.')
                                    ->default(true)
                                    ->live(),
                            ]),

                        Forms\Components\Grid::make(2)
                            ->schema([
                                Forms\Components\Toggle::make('can_practice_reapply')
                                    ->label('السماح بإعادة التقديم لتدريب المزاولة')
                                    ->helperText('تفعيل هذا الخيار سيسمح للمتدربين الذين تم رفضهم سابقاً بإعادة التقديم.')
                                    ->default(false)
                                    ->live(),

                                Forms\Components\Toggle::make('can_university_reapply')
                                    ->label('السماح بإعادة التقديم للتدريب الجامعي')
                                    ->helperText('تفعيل هذا الخيار سيسمح للمتدربين الذين تم رفضهم سابقاً بإعادة التقديم.')
                                    ->default(false)
                                    ->live(),
                            ]),
                    ]),

                Forms\Components\Section::make('صلاحيات رؤساء الجهات')
                    ->description('إدارة صلاحيات رؤساء الجهات الحكومية في النظام.')
                    ->icon('heroicon-o-shield-check')
                    ->schema([
                        Forms\Components\Grid::make(2)
                            ->schema([
                                Forms\Components\Toggle::make('hoa_can_edit_section')
                                    ->label('السماح برئيس الجهة بتعديل بيانات الأقسام')
                                    ->helperText('إذا تم تفعيل هذا الخيار، سيتمكن رئيس الجهة من تعديل بيانات الأقسام.')
                                    ->default(false)
                                    ->live(),

                                Forms\Components\Toggle::make('hoa_can_enable_section')
                                    ->label('السماح برئيس الجهة بتفعيل/تعطيل الأقسام')
                                    ->helperText('إذا تم تفعيل هذا الخيار، سيتمكن رئيس الجهة من تفعيل أو تعطيل الأقسام.')
                                    ->default(false)
                                    ->live(),
                            ]),
                    ]),

                Forms\Components\Section::make('صلاحيات رؤساء الأقسام')
                    ->description('إدارة صلاحيات رؤساء الأقسام في النظام.')
                    ->icon('heroicon-o-shield-check')
                    ->schema([
                        Forms\Components\Grid::make(2)
                            ->schema([
                                Forms\Components\Toggle::make('dept_head_can_edit_section')
                                    ->label('السماح برئيس القسم بتعديل بيانات القسم')
                                    ->helperText('إذا تم تفعيل هذا الخيار، سيتمكن رئيس القسم من تعديل بيانات قسمه.')
                                    ->default(false)
                                    ->live(),

                                Forms\Components\Toggle::make('dept_head_can_enable_section')
                                    ->label('السماح برئيس القسم بتفعيل/تعطيل القسم')
                                    ->helperText('إذا تم تفعيل هذا الخيار، سيتمكن رئيس القسم من تفعيل أو تعطيل قسمه.')
                                    ->default(false)
                                    ->live(),
                            ]),
                    ]),
        ];
    }
}
