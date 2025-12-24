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
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Group;
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
                            ->regex('/^[A-Za-z\p{Arabic}\s]+$/u')
                            ->maxLength(255)
                            ->validationMessages([
                                'required' => __('validation.custom.full_name.required'),
                                'regex' => __('validation.custom.full_name.regex'),
                                'max' => __('validation.custom.full_name.max'),
                            ])
                            ->required()
                            ->suffixAction(
                                Action::make('edit_trainee_details')
                                    ->icon('heroicon-m-pencil-square')
                                    ->tooltip('تعديل بيانات المتدرب الأصلية')
                                    ->label('تعديل')
                                    ->visible(fn($context) => $context === 'edit' && Auth::user()->isAdmin())
                                    ->modalHeading('تعديل بيانات المتدرب')
                                    ->mountUsing(fn($record, $form) => $form->fill([
                                        'full_name' => $record->trainee->full_name,
                                        'national_id' => $record->trainee->national_id,
                                        'phone_number' => $record->trainee->phone_number,
                                        'dob' => $record->trainee->dob,
                                        'street' => $record->trainee->street,
                                        'institution_id' => $record->trainee->institution_id,
                                        'college_id' => $record->trainee->college_id,
                                        'major_id' => $record->trainee->major_id,
                                    ]))
                                    // حقول النافذة المنبثقة
                                    ->form([
                                        TextInput::make('full_name')->label('الاسم الكامل')->required(),
                                        TextInput::make('national_id')->label('رقم الهوية')->required(),
                                        TextInput::make('phone_number')->label('رقم الهاتف')->required(),
                                        TextInput::make('street')->label('المنطقة / الشارع'),
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
                            ->numeric()
                            ->minLength(9)
                            ->maxLength(9)
                            ->regex('/^\d{9}$/')
                            ->helperText('9 أرقام فقط')
                            ->validationMessages([
                                'required' => __('validation.custom.national_id.required'),
                                'regex' => __('validation.custom.national_id.regex'),
                                'digits' => __('validation.custom.national_id.digits'),
                            ])
                            ->required(),

                        TextInput::make('phone_number')
                            ->label('رقم الجوال')
                            ->formatStateUsing(fn($record) => $record?->trainee?->phone_number)
                            ->disabled(fn($context) => $context === 'edit')
                            ->dehydrated(fn($context) => $context === 'create')
                            ->tel()
                            ->regex('/^97[02]5[69]\d{7}$/')
                            ->minLength(12)
                            ->maxLength(12)
                            ->helperText('مثال: 970591234567 أو 972561234567')
                            ->validationMessages([
                                'required' => __('validation.custom.phone_number.required'),
                                'regex' => __('validation.custom.phone_number.regex'),
                            ])
                            ->required(),

                        Select::make('governorate_id')
                            ->label('المحافظة')
                            ->formatStateUsing(fn($record) => $record?->trainee?->governorate_id)
                            ->disabled(fn($context) => $context === 'edit')
                            ->dehydrated(fn($context) => $context === 'create')
                            ->options(fn() => \Illuminate\Support\Facades\Lang::get('translation.governorates', [], 'ar'))
                            ->searchable()
                            ->preload()
                            ->required(),
                        TextInput::make('street')
                            ->label('المنطقة / الشارع')
                            ->formatStateUsing(fn($record) => $record?->trainee?->street)
                            ->disabled(fn($context) => $context === 'edit')
                            ->dehydrated(fn($context) => $context === 'create')
                            ->regex('/^[A-Za-z\p{Arabic}0-9\s\-\.,#\/]+$/u')
                            ->maxLength(255)
                            ->required(),

                        Grid::make(3)
                            ->schema([
                                Select::make('dob_day')
                                    ->label('اليوم')
                                    ->options(array_combine(range(1, 31), range(1, 31)))
                                    ->disabled(fn($context) => $context === 'edit')
                                    ->dehydrated(false)
                                    ->reactive()
                                    ->afterStateUpdated(function ($state, callable $set, callable $get) {
                                        if ($get('dob_year') && $get('dob_month') && $state) {
                                            $set('dob', sprintf('%04d-%02d-%02d', $get('dob_year'), $get('dob_month'), $state));
                                        }
                                    })
                                    ->required(),
                                Select::make('dob_month')
                                    ->label('الشهر')
                                    ->options([
                                        '01' => 'يناير',
                                        '02' => 'فبراير',
                                        '03' => 'مارس',
                                        '04' => 'أبريل',
                                        '05' => 'مايو',
                                        '06' => 'يونيو',
                                        '07' => 'يوليو',
                                        '08' => 'أغسطس',
                                        '09' => 'سبتمبر',
                                        '10' => 'أكتوبر',
                                        '11' => 'نوفمبر',
                                        '12' => 'ديسمبر'
                                    ])
                                    ->disabled(fn($context) => $context === 'edit')
                                    ->dehydrated(false)
                                    ->reactive()
                                    ->afterStateUpdated(function ($state, callable $set, callable $get) {
                                        if ($get('dob_year') && $state && $get('dob_day')) {
                                            $set('dob', sprintf('%04d-%02d-%02d', $get('dob_year'), $state, $get('dob_day')));
                                        }
                                    })
                                    ->required(),
                                Select::make('dob_year')
                                    ->label('السنة')
                                    ->options(array_combine(
                                        range(date('Y') - 20, date('Y') - 60),
                                        range(date('Y') - 20, date('Y') - 60)
                                    ))
                                    ->disabled(fn($context) => $context === 'edit')
                                    ->dehydrated(false)
                                    ->reactive()
                                    ->afterStateUpdated(function ($state, callable $set, callable $get) {
                                        if ($state && $get('dob_month') && $get('dob_day')) {
                                            $set('dob', sprintf('%04d-%02d-%02d', $state, $get('dob_month'), $get('dob_day')));
                                        }
                                    })
                                    ->required(),
                            ])
                            ->columnSpanFull(),
                        Hidden::make('dob')
                            ->dehydrated(fn($context) => $context === 'create')
                            ->formatStateUsing(fn($record) => $record?->trainee?->dob),

                        Select::make('institution_id')
                            ->label('المؤسسة التعليمية')
                            ->options(fn() => Institution::query()->get()->pluck('name', 'id')->toArray())
                            ->default(fn() => Auth::user()->isCollegeSupervisor() ? Auth::user()->college?->institution_id : null)
                            ->formatStateUsing(fn($record) => $record?->trainee?->institution_id)
                            ->disabled(fn() => Auth::user()->isCollegeSupervisor() || request()->routeIs('*.edit'))
                            ->dehydrated()
                            ->validationMessages([
                                'required' => __('validation.custom.institution_id.required'),
                            ])
                            ->required(fn() => Auth::user()->isAdmin())
                            ->reactive(),

                        Select::make('college_id')
                            ->label('الكلية')
                            ->options(function (callable $get) {
                                if (Auth::user()->isCollegeSupervisor()) {
                                    return College::where('id', Auth::user()->college?->id)
                                        ->get()
                                        ->pluck('name', 'id')
                                        ->toArray();
                                }

                                $institutionId = $get('institution_id');
                                if ($institutionId) {
                                    return College::where('institution_id', $institutionId)
                                        ->get()
                                        ->pluck('name', 'id')
                                        ->toArray();
                                }
                                return [];
                            })
                            ->default(fn() => Auth::user()->isCollegeSupervisor() ? Auth::user()->college?->id : null)
                            ->formatStateUsing(fn($record) => $record?->trainee?->college_id)
                            ->disabled(fn() => Auth::user()->isCollegeSupervisor() || request()->routeIs('*.edit'))
                            ->dehydrated()
                            ->validationMessages([
                                'required' => __('validation.custom.college_id.required'),
                            ])
                            ->required(fn() => Auth::user()->isAdmin())
                            ->reactive(),

                        Select::make('major_id')
                            ->label('التخصص')
                            ->options(function (callable $get) {
                                if (Auth::user()->isCollegeSupervisor() && Auth::user()->college?->id) {
                                    return Major::whereHas('colleges', function ($q) {
                                        $q->where('colleges.id', Auth::user()->college->id);
                                    })
                                        ->get()
                                        ->pluck('name', 'id')
                                        ->toArray();
                                }
                                $collegeId = $get('college_id');
                                if ($collegeId) {
                                    return Major::whereHas('colleges', fn($q) => $q->where('colleges.id', $collegeId))
                                        ->get()
                                        ->pluck('name', 'id')
                                        ->toArray();
                                }
                                return [];
                            })
                            ->formatStateUsing(fn($record) => $record?->trainee?->major_id)
                            ->disabled(fn($context) => $context === 'edit')
                            ->dehydrated(fn($context) => $context === 'create')
                            ->searchable()
                            ->validationMessages([
                                'required' => __('validation.custom.major_id.required'),
                            ])
                            ->required()
                            ->preload(),


                    ])->columns(2),

                Fieldset::make('تفاصيل الطلب')
                    ->schema([
                        Select::make('training_type')
                            ->label('نوع التدريب')
                            ->options(Constans::TRAINING_TYPES)
                            ->default(function () {
                                if (Auth::user()->isCollegeSupervisor()) return Constans::TRAINING_TYPE_UNIVERSITY;
                                if (Auth::user()->isMinistry()) return Constans::TRAINING_TYPE_PRACTICE;
                                return null;
                            })
                            ->disabled(fn() => ! Auth::user()->isAdmin())
                            ->dehydrated()
                            ->required(),

                        TextInput::make('training_hours')
                            ->label('ساعات التدريب المطلوبة')
                            ->formatStateUsing(fn($record) => $record?->trainee?->training_hours)
                            ->disabled(fn($context) => $context === 'edit')
                            ->dehydrated(fn($context) => $context === 'create')
                            ->numeric()
                            ->helperText('الساعات الأكاديمية المطلوبة'),

                        Select::make('administrative_id')
                            ->label('الإدارة')
                            ->relationship('administrative', 'title')
                            ->searchable()
                            ->preload()
                            ->reactive()
                            ->disabled(fn() => ! Auth::user()->isAdmin() && ! Auth::user()->isCollegeSupervisor())
                            ->required(),

                        Select::make('department_id')
                            ->label('الدائرة')
                            ->options(function (callable $get) {
                                $adminId = $get('administrative_id');
                                if (!$adminId) {
                                    return \App\Models\Departments::all()->pluck('title', 'id');
                                }
                                return \App\Models\Departments::whereHas('sections', function ($q) use ($adminId) {
                                    $q->where('administrative_id', $adminId);
                                })->pluck('title', 'id');
                            })
                            ->searchable()
                            ->preload()
                            ->reactive()
                            ->disabled(fn() => ! Auth::user()->isAdmin() && ! Auth::user()->isCollegeSupervisor())
                            ->required(),

                        Select::make('section_id')
                            ->label('القسم')
                            ->options(function (callable $get) {
                                $query = Sections::query();

                                if ($adminId = $get('administrative_id')) {
                                    $query->where('administrative_id', $adminId);
                                }

                                if ($deptId = $get('department_id')) {
                                    $query->where('department_id', $deptId);
                                }

                                return $query->pluck('name_location', 'id');
                            })
                            ->searchable()
                            ->preload()
                            ->disabled(function (callable $get) {
                                return (! Auth::user()->isAdmin() && ! Auth::user()->isCollegeSupervisor()) || (! $get('administrative_id') && ! $get('department_id'));
                            })
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
                            ->options(fn() => array_combine(
                                Constans::STATUSES,
                                array_map(fn($s) => \Illuminate\Support\Facades\Lang::get("translation.status.$s", [], 'ar'), Constans::STATUSES)
                            ))
                            ->default(fn() => Auth::user()->isCollegeSupervisor() ? Constans::STATUS_CONFIRMATION : Constans::STATUS_NEW)
                            ->disabled(fn() => ! Auth::user()->isAdmin())
                            
                            ->default(fn() => Auth::user()->isCollegeSupervisor() ? Constans::STATUS_CONFIRMATION : null)
                            ->formatStateUsing(fn($record) => $record?->trainee?->college_id)
                            ->afterStateUpdated(fn($state, $set) => (int)$state === Constans::STATUS_CONFIRMATION ? $set('accepted_at', now()) : null)
                            
                            ->dehydrated()
                            ->required()
                            ->live()
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
