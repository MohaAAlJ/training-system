<?php

namespace App\Filament\Resources\Applications\Schemas;

use App\Models\Institution;
use App\Models\College;
use App\Models\Major;
use App\Models\Sections;
use App\Models\Administrative;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Hidden;
use Filament\Actions\Action;
use Filament\Schemas\Components\Fieldset;
use Filament\Schemas\Schema;
use Illuminate\Support\Facades\Auth;
use App\Helpers\Constans;

class ApplicationsForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Hidden::make('trainee_id'),

                Fieldset::make('البيانات الشخصية للمتدرب')
                    ->schema([
                        TextInput::make('full_name')
                            ->label('الاسم الكامل')
                            ->formatStateUsing(fn($record) => $record?->trainee?->full_name)
                            ->disabled(fn($context) => $context === 'edit')
                            ->dehydrated(fn($context) => $context === 'create')
                            ->required()
                            ->suffixAction(
                                Action::make('edit_trainee_details')
                                    ->icon('heroicon-m-pencil-square')
                                    ->tooltip('تعديل بيانات المتدرب الأصلية')
                                    ->label('تعديل')
                                    ->visible(fn($context) => $context === 'edit' && (Auth::user()->isAdmin() || Auth::user()->isCollegeSupervisor()))
                                    ->modalHeading('تعديل بيانات المتدرب')
                                    ->mountUsing(fn($record, $form) => $form->fill([
                                        'full_name' => $record->trainee->full_name,
                                        'national_id' => $record->trainee->national_id,
                                        'phone_number' => $record->trainee->phone_number,
                                        'dob' => $record->trainee->dob,
                                        'address' => $record->trainee->address,
                                        'institution_id' => $record->trainee->institution_id,
                                        'college_id' => $record->trainee->college_id,
                                        'major_id' => $record->trainee->major_id,
                                    ]))
                                    // حقول النافذة المنبثقة
                                    ->form([
                                        TextInput::make('full_name')->label('الاسم الكامل')->required(),
                                        TextInput::make('national_id')->label('رقم الهوية')->required(),
                                        TextInput::make('phone_number')->label('رقم الهاتف')->required(),
                                        TextInput::make('address')->label('العنوان'),
                                        DatePicker::make('dob')->label('تاريخ الميلاد')->native(false),
                                        // يمكن إضافة اختيار التخصص هنا أيضاً
                                        Select::make('major_id')
                                            ->label('التخصص')
                                            ->options(Major::all()->pluck('name', 'id'))
                                            ->searchable()
                                            ->preload(),
                                    ])
                                    ->action(function ($record, $data) {
                                        $record->trainee->update($data);

                                        \Filament\Notifications\Notification::make()
                                            ->title('تم تحديث بيانات المتدرب بنجاح')
                                            ->success()
                                            ->send();
                                    })
                            ),

                        TextInput::make('national_id')
                            ->label('رقم الهوية')
                            ->formatStateUsing(fn($record) => $record?->trainee?->national_id)
                            ->disabled(fn($context) => $context === 'edit')
                            ->dehydrated(fn($context) => $context === 'create')
                            ->required(),

                        TextInput::make('phone_number')
                            ->label('رقم الهاتف')
                            ->formatStateUsing(fn($record) => $record?->trainee?->phone_number)
                            ->disabled(fn($context) => $context === 'edit')
                            ->dehydrated(fn($context) => $context === 'create')
                            ->required(),

                        TextInput::make('address')
                            ->label('العنوان')
                            ->formatStateUsing(fn($record) => $record?->trainee?->address)
                            ->disabled(fn($context) => $context === 'edit')
                            ->dehydrated(fn($context) => $context === 'create')
                            ->required(),

                        DatePicker::make('dob')
                            ->label('تاريخ الميلاد')
                            ->formatStateUsing(fn($record) => $record?->trainee?->dob)
                            ->native(false)
                            ->disabled(fn($context) => $context === 'edit')
                            ->dehydrated(fn($context) => $context === 'create')
                            ->required(),

                        Select::make('institution_id')
                            ->label('المؤسسة التعليمية')
                            ->options(fn() => Institution::all()->pluck('name', 'id'))
                            ->default(fn() => Auth::user()->isCollegeSupervisor() ? Auth::user()->college?->institution_id : null)
                            ->formatStateUsing(fn($record) => $record?->trainee?->institution_id)
                            ->disabled(fn() => Auth::user()->isCollegeSupervisor() || request()->routeIs('*.edit'))
                            ->dehydrated(fn($context) => $context === 'create')
                            ->required(fn() => Auth::user()->isAdmin())
                            ->reactive(),

                        Select::make('college_id')
                            ->label('الكلية')
                            ->options(function (callable $get) {
                                if (Auth::user()->isCollegeSupervisor() && Auth::user()->college) {
                                    return College::where('institution_id', Auth::user()->college->institution_id)
                                        ->pluck('name', 'id');
                                }

                                $institutionId = $get('institution_id');
                                if ($institutionId) {
                                    return College::where('institution_id', $institutionId)->pluck('name', 'id');
                                }
                                return [];
                            })
                            ->default(fn() => Auth::user()->isCollegeSupervisor() ? Auth::user()->college_id : null)
                            ->formatStateUsing(fn($record) => $record?->trainee?->college_id)
                            ->disabled(fn() => Auth::user()->isCollegeSupervisor() || request()->routeIs('*.edit'))
                            ->dehydrated(fn($context) => $context === 'create')
                            ->required(fn() => Auth::user()->isAdmin())
                            ->reactive(),

                        Select::make('major_id')
                            ->label('التخصص')
                            ->options(function (callable $get) {
                                if (Auth::user()->isCollegeSupervisor() && Auth::user()->college) {
                                    return Major::whereHas('colleges', function ($q) {
                                        $q->where('colleges.id', Auth::user()->college_id);
                                    })->pluck('name', 'id');
                                }
                                $collegeId = $get('college_id');
                                if ($collegeId) {
                                    return Major::whereHas('colleges', fn($q) => $q->where('colleges.id', $collegeId))->pluck('name', 'id');
                                }
                                return [];
                            })
                            ->formatStateUsing(fn($record) => $record?->trainee?->major_id)
                            ->disabled(fn($context) => $context === 'edit')
                            ->dehydrated(fn($context) => $context === 'create')
                            ->searchable()
                            ->required()
                            ->preload(),
                    ])->columns(2),

                Fieldset::make('تفاصيل الطلب')
                    ->schema([
                        Select::make('training_type')
                            ->label('نوع التدريب')
                            ->options(['cooperative' => 'تدريب جامعي', 'professional' => 'مزاولة مهنة'])
                            ->default(function () {
                                if (Auth::user()->isCollegeSupervisor()) return 'cooperative';
                                if (Auth::user()->isMinistry()) return 'professional';
                                return null;
                            })
                            ->disabled(fn() => ! Auth::user()->isAdmin())
                            ->dehydrated()
                            ->required(),

                        TextInput::make('duration')
                            ->label('مدة التدريب (بالساعات)')
                            ->numeric()
                            ->default(100)
                            ->suffix('ساعة')
                            ->disabled(fn() => ! Auth::user()->isAdmin())
                            ->required(),

                        Select::make('department_id')
                            ->label('الدائرة')
                            ->options(fn() => Administrative::all()->pluck('name', 'id'))
                            ->searchable()
                            ->preload()
                            ->disabled(fn() => ! Auth::user()->isAdmin())
                            ->required(),

                        Select::make('section_id')
                            ->label('القسم')
                            ->options(fn() => Sections::all()->pluck('name_location', 'id'))
                            ->searchable()
                            ->preload()
                            ->disabled(fn() => ! Auth::user()->isAdmin() && ! Auth::user()->isCollegeSupervisor())
                            ->required(),

                        DatePicker::make('start_date')
                            ->label('تاريخ البدء')
                            ->native(false)
                            ->disabled(fn() => ! Auth::user()->isAdmin())
                            ->required(fn() => Auth::user()->isAdmin())
                            ->dehydrated(),

                        DatePicker::make('end_date')
                            ->label('تاريخ الانتهاء')
                            ->native(false)
                            ->disabled(fn() => ! Auth::user()->isAdmin())
                            ->required(fn() => Auth::user()->isAdmin())
                            ->afterOrEqual('start_date')
                            ->dehydrated(),

                        Select::make('status')
                            ->label('الحالة')
                            ->options(fn () => array_combine(
                                Constans::STATUSES,
                                array_map(fn($s) => \Illuminate\Support\Facades\Lang::get("translation.status.$s", [], 'ar'), Constans::STATUSES)
                            ))
                            ->default(\App\Helpers\Constans::STATUS_PENDING)
                            ->required()
                            ->live()
                            ->afterStateUpdated(fn($state, $set) => $state === 'active' ? $set('accepted_at', now()) : null),
                    ])->columns(2),

                Fieldset::make('المستندات والملاحظات')
                    ->schema([
                        FileUpload::make('application_letter')
                            ->label('صورة خطاب التدريب')
                            ->image()
                            ->disk('public')
                            ->directory('application-letters')
                            ->visibility('public')
                            ->columnSpanFull(),

                        TextInput::make('tags')
                            ->label('الوسوم')
                            ->columnSpanFull(),
                    ])->columns(1),
            ]);
    }
}
