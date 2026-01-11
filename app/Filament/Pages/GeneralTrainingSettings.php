<?php

namespace App\Filament\Pages;

use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Pages\Page;
use Filament\Forms;
use App\Models\TrainingSetting;
use Filament\Actions;

class GeneralTrainingSettings extends Page implements HasForms
{
    use InteractsWithForms;

    protected static ?string $title = 'إعدادات التدريب';

    public static function getNavigationLabel(): string
    {
        return 'إعدادات التدريب';
    }

    public ?array $data = [];

    public function mount(): void
    {
        $settings = TrainingSetting::getInstance();
        $this->form->fill($settings->toArray());
    }

    protected function getFormSchema(): array
    {
        return [
            Forms\Components\Fieldset::make('قواعد النظام العام')
                ->description('إعدادات تتحكم في القواعد الأساسية للقبول والتسجيل وإمكانية إعادة التقديم.')
                ->schema([
                    Forms\Components\Toggle::make('hide_full_sections')
                        ->label('إخفاء الأقسام المكتملة')
                        ->helperText('عند التفعيل، لن تظهر الأقسام التي وصلت لسعتها القصوى في طلب الالتحاق.')
                        ->default(true)
                        ->live(),

                    Forms\Components\Toggle::make('is_public_form_enabled')
                        ->label('تفعيل نموذج الالتحاق العام')
                        ->helperText('عند تفعيل هذا الخيار، سيتمكن المتدربون من تقديم الطلبات عبر البوابة العامة.')
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
                                ->helperText('تفعيل هذا الخيار سيسمح للمتدربين بإعادة التقديم.')
                                ->default(false)
                                ->live(),

                            Forms\Components\Toggle::make('can_university_reapply')
                                ->label('السماح بإعادة التقديم للتدريب الجامعي')
                                ->helperText('تفعيل هذا الخيار سيسمح للمتدربين بإعادة التقديم.')
                                ->default(false)
                                ->live(),
                        ]),
                ]),

            Forms\Components\Fieldset::make('صلاحيات رؤساء الجهات')
                ->description('إدارة صلاحيات رؤساء الجهات الحكومية في النظام.')
                ->schema([
                    Forms\Components\Grid::make(2)
                        ->schema([
                            Forms\Components\Toggle::make('hoa_can_edit_section')
                                ->label('السماح برئيس الجهة بتعديل بيانات الأقسام')
                                ->default(false)
                                ->live(),

                            Forms\Components\Toggle::make('hoa_can_enable_section')
                                ->label('السماح برئيس الجهة بتفعيل/تعطيل الأقسام')
                                ->default(false)
                                ->live(),
                        ]),
                ]),

            Forms\Components\Fieldset::make('صلاحيات رؤساء الأقسام')
                ->description('إدارة صلاحيات رؤساء الأقسام في النظام.')
                ->schema([
                    Forms\Components\Grid::make(2)
                        ->schema([
                            Forms\Components\Toggle::make('dept_head_can_edit_section')
                                ->label('السماح برئيس القسم بتعديل بيانات القسم')
                                ->default(false)
                                ->live(),

                            Forms\Components\Toggle::make('dept_head_can_enable_section')
                                ->label('السماح برئيس القسم بتفعيل/تعطيل القسم')
                                ->default(false)
                                ->live(),
                        ]),
                ]),
        ];
    }

    protected function getFormActions(): array
    {
        return [
            Actions\Action::make('save')
                ->label('حفظ')
                ->submit('save'),
        ];
    }

    public function save(): void
    {
        $data = $this->form->getState();
        $settings = TrainingSetting::getInstance();
        $settings->update($data);

        $this->notification()
            ->success()
            ->title('تم الحفظ')
            ->body('تم حفظ الإعدادات بنجاح')
            ->send();
    }
}
