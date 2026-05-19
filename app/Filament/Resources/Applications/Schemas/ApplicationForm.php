<?php

declare(strict_types=1);

namespace App\Filament\Resources\Applications\Schemas;

use App\Models\Administrative;
use App\Models\Application;
use App\Models\College;
use App\Models\Department;
use App\Models\Governorate;
use App\Models\Institution;
use App\Models\Major;
use App\Models\Section;
use App\Rules\PalestinianId;
use App\Settings\TrainingSettings;
use Carbon\Carbon;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Hidden;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\CheckboxList;
use Filament\Forms\Components\Textarea;
use App\Rules\ValidMultipleApplications;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section as FormSection;
use Filament\Schemas\Schema;
use Illuminate\Support\Facades\Auth;
use Filament\Notifications\Notification;

class ApplicationForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->columns(1)
            ->components([
                Hidden::make('trainee_id'),
                Hidden::make('is_existing_trainee')->default(false),

                // ── Personal Data ─────────────────────────────────────────
                self::getPersonalDataSection(),

                // ── Application Details ───────────────────────────────────
                self::getApplicationDetailsSection(),

                // ── Documents & Notes ─────────────────────────────────────
                self::getDocumentsAndNotesSection(),
            ]);
    }

    public static function getPersonalDataSection(): FormSection
    {
        return FormSection::make('البيانات الشخصية للمتدرب')
            ->columnSpanFull()
            ->description('الرجاء إدخال جميع البيانات الشخصية بدقة')
            ->icon('heroicon-o-user-circle')
            ->iconColor('primary')
            ->collapsible()
            ->schema([

                Grid::make(2)
                    ->schema([
                        TextInput::make('national_id')
                            ->label('رقم الهوية')
                            ->placeholder('123456789')
                            ->formatStateUsing(fn($record) => $record?->trainee?->national_id)
                            ->disabled(fn($record) => $record !== null)
                            ->dehydrated()
                            ->numeric()
                            ->minLength(9)
                            ->maxLength(9)
                            ->regex('/^\d{9}$/')
                            ->helperText('9 أرقام فقط')
                            ->live(onBlur: true)
                            ->validationMessages([
                                'regex'      => trans('validation.custom.national_id.regex', [], 'ar'),
                                'max' => trans('validation.custom.national_id.max', [], 'ar'),
                                'min' => trans('validation.custom.national_id.min', [], 'ar'),
                            ])
                            ->afterStateUpdated(function ($state, callable $set) {
                                $trainee = \App\Models\Trainee::where('national_id', $state)->first();

                                $set('trainee_id',        $trainee?->id);
                                $set('is_existing_trainee', $trainee !== null);

                                if ($trainee) {
                                    $set('full_name',      $trainee->full_name);
                                    $set('gender',         $trainee->gender);
                                    $set('phone_number',   $trainee->phone_number);
                                    $set('governorate_id', $trainee->governorate_id);
                                    $set('street',         $trainee->street);
                                    $set(
                                        'dob',
                                        $trainee->dob
                                            ? Carbon::parse($trainee->dob)->format('Y-m-d')
                                            : null
                                    );

                                    if (! Auth::user()?->isMinistry()) {
                                        $query = Application::where('trainee_id', $trainee->id)
                                            ->whereNotNull('institution_id');

                                        if (Auth::user()?->isCollegeSupervisor()) {
                                            $query->where('college_id', Auth::user()->college?->id);
                                        }

                                        $lastApp = $query->latest()->first();

                                        if ($lastApp) {
                                            if (! Auth::user()?->isCollegeSupervisor()) {
                                                $set('institution_id',    $lastApp->institution_id);
                                                $set('college_id',        $lastApp->college_id);
                                            } else {
                                                $set('institution_id',    Auth::user()->college?->institution_id);
                                                $set('college_id',        Auth::user()->college?->id);
                                            }
                                            $set('major_id',          $lastApp->major_id);
                                            $set('university_number', $lastApp->university_number);
                                            $set('training_hours',    $lastApp->training_hours);
                                        }
                                    }

                                    Notification::make()
                                        ->title('تم استرجاع بيانات المتدرب')
                                        ->body('تم تعبئة البيانات الشخصية من السجلات السابقة تلقائياً.')
                                        ->success()
                                        ->send();
                                } else {
                                    $fieldsToReset = [
                                        'full_name',
                                        'gender',
                                        'phone_number',
                                        'governorate_id',
                                        'street',
                                        'dob',
                                        'major_id',
                                        'university_number',
                                        'training_hours'
                                    ];

                                    if (! Auth::user()?->isCollegeSupervisor()) {
                                        $fieldsToReset[] = 'institution_id';
                                        $fieldsToReset[] = 'college_id';
                                    }

                                    foreach ($fieldsToReset as $field) {
                                        $set($field, null);
                                    }

                                    if (Auth::user()?->isCollegeSupervisor()) {
                                        $set('institution_id', Auth::user()->college?->institution_id);
                                        $set('college_id', Auth::user()->college?->id);
                                    }
                                }
                            })
                            ->rules(fn($record) => [
                                new PalestinianId(),
                                new ValidMultipleApplications($record?->id),
                            ])
                            ->required()
                            ->columnSpan(1),

                        DatePicker::make('dob')
                            ->label('تاريخ الميلاد')
                            ->placeholder('أدخل تاريخ الميلاد')
                            ->native(false)
                            ->displayFormat('Y/m/d')
                            ->formatStateUsing(fn($record) => $record?->trainee?->dob)
                            ->disabled(fn($context, callable $get) => $context === 'edit' || ($context === 'create' && $get('is_existing_trainee')))
                            ->dehydrated(fn($context) => $context === 'create')
                            ->maxDate(now()->subYears(18))
                            ->minDate(now()->subYears(60))
                            ->validationMessages([
                                'before_or_equal' => trans('validation.custom.dob.before_or_equal', [], 'ar'),
                                'after_or_equal'  => trans('validation.custom.dob.after_or_equal', [], 'ar'),
                            ])
                            ->required()
                            ->columnSpan(1),
                    ]),

                Grid::make(3)
                    ->schema([
                        TextInput::make('full_name')
                            ->label('الاسم الكامل')
                            ->placeholder('أدخل الاسم الكامل')
                            ->formatStateUsing(fn($record) => $record?->trainee?->full_name)
                            ->disabled(fn($context, callable $get) => $context === 'edit' || ($context === 'create' && $get('is_existing_trainee')))
                            ->dehydrated(fn($context) => $context === 'create')
                            ->regex('/^[A-Za-z\p{Arabic}\s]+$/u')
                            ->maxLength(255)
                            ->validationMessages([
                                'required' => trans('validation.custom.full_name.required', [], 'ar'),
                                'regex'    => trans('validation.custom.full_name.regex', [], 'ar'),
                                'max'      => trans('validation.custom.full_name.max', [], 'ar'),
                            ])
                            ->required(fn($context) => $context === 'create')
                            ->columnSpan(2),

                        Select::make('gender')
                            ->label('الجنس')
                            ->options(\App\Enums\Gender::class)
                            ->formatStateUsing(fn($record) => $record?->trainee?->gender)
                            ->disabled(fn($context, callable $get) => $context === 'edit' || ($context === 'create' && $get('is_existing_trainee')))
                            ->dehydrated(fn($context) => $context === 'create')
                            ->required()
                            ->columnSpan(1),
                    ]),

                Grid::make(3)
                    ->schema([
                        TextInput::make('phone_number')
                            ->label('رقم الجوال')
                            ->placeholder('970591234567')
                            ->formatStateUsing(fn($record) => $record?->trainee?->phone_number)
                            ->disabled(fn($context) => $context === 'edit')
                            ->dehydrated(fn($context) => $context === 'create')
                            ->tel()
                            ->regex('/^97(0|2)5\d{8}$/')
                            ->minLength(12)
                            ->maxLength(12)
                            ->helperText('مثال: 970591234567 أو 972561234567')
                            ->validationMessages([
                                'regex' => trans('validation.custom.phone_number.regex', [], 'ar'),
                            ])
                            ->required()
                            ->columnSpan(1),

                        Select::make('governorate_id')
                            ->label('المحافظة')
                            ->placeholder('اختر المحافظة')
                            ->options(Governorate::pluck('name', 'id'))
                            ->formatStateUsing(fn($record) => $record?->trainee?->governorate_id)
                            ->disabled(fn($context) => $context === 'edit')
                            ->dehydrated(fn($context) => $context === 'create')
                            ->searchable()
                            ->preload()
                            ->required()
                            ->columnSpan(1),

                        TextInput::make('street')
                            ->label('الشارع')
                            ->placeholder('الشارع')
                            ->formatStateUsing(fn($record) => $record?->trainee?->street)
                            ->disabled(fn($context) => $context === 'edit')
                            ->dehydrated(fn($context) => $context === 'create')
                            ->regex('/^[A-Za-z\p{Arabic}0-9\s\-\.,#\/_]+$/u')
                            ->maxLength(255)
                            ->nullable()
                            ->columnSpan(1),
                    ]),
            ]);
    }

    public static function getApplicationDetailsSection(): FormSection
    {
        return FormSection::make('تفاصيل الطلب')
            ->columnSpanFull()
            ->description('تفاصيل طلب التدريب والجهة المستقبلة')
            ->icon('heroicon-o-clipboard-document-check')
            ->iconColor('success')
            ->collapsible()
            ->schema([
                self::getTrainingTypeGrid(),
                self::getUniversityInformationGrid(),
                self::getUniversityNumberGrid(),
                self::getTrainingLocationGrid(),
                self::getTrainingDatesAndStatusGrid(),
            ]);
    }

    private static function getTrainingTypeGrid(): Grid
    {
        return Grid::make(3)
            ->schema([
                Select::make('training_type')
                    ->label('نوع التدريب')
                    ->placeholder('اختر نوع التدريب')
                    ->options([
                        Application::UNIVERSITY => Application::getTrainingTypeLabel(Application::UNIVERSITY),
                        Application::PRACTICE   => Application::getTrainingTypeLabel(Application::PRACTICE),
                    ])
                    ->default(Application::PRACTICE)
                    ->visible(fn() => Auth::user()->isAdmin() || Auth::user()->isTrainingManagerLike())
                    ->dehydrated()
                    ->live()
                    ->required()
                    ->columnSpan(1),

                Hidden::make('training_type')
                    ->default(Application::UNIVERSITY)
                    ->visible(fn() => Auth::user()->isCollegeSupervisor())
                    ->dehydrated(),

                Hidden::make('training_type')
                    ->default(Application::PRACTICE)
                    ->visible(fn() => Auth::user()->isMinistry())
                    ->dehydrated(),

                TextInput::make('training_hours')
                    ->label('ساعات التدريب المطلوبة')
                    ->placeholder('120')
                    ->extraInputAttributes([
                        'inputmode' => 'numeric',
                        'pattern'   => '[0-9]*',
                        'oninput'   => 'this.value = this.value.replace(/[^0-9]/g, "")',
                        'maxlength' => '3',
                    ])
                    ->suffix('ساعة')
                    ->helperText('الساعات الأكاديمية المطلوبة (أقل من 1000)')
                    ->required(fn() => Auth::user()->isCollegeSupervisor())
                    ->columnSpan(1),
            ]);
    }

    private static function getUniversityInformationGrid(): Grid
    {
        return Grid::make(3)
            ->visible(fn() => ! Auth::user()->isMinistry())
            ->schema([
                Hidden::make('institution_id')
                    ->default(fn() => Auth::user()->college?->institution_id)
                    ->visible(fn() => Auth::user()->isCollegeSupervisor())
                    ->dehydrated(),

                Hidden::make('college_id')
                    ->default(fn() => Auth::user()->college?->id)
                    ->visible(fn() => Auth::user()->isCollegeSupervisor())
                    ->dehydrated(),

                Select::make('institution_id')
                    ->label('المؤسسة التعليمية')
                    ->placeholder('اختر المؤسسة')
                    ->options(fn() => Institution::active()->pluck('name', 'id')->toArray())
                    ->default(fn() => Auth::user()->isCollegeSupervisor() ? Auth::user()->college?->institution_id : null)
                    ->disabled(fn(callable $get) => request()->routeIs('*.edit') && (int) $get('training_type') !== Application::UNIVERSITY)
                    ->visible(fn(callable $get) => ! Auth::user()->isMinistry() && ! Auth::user()->isCollegeSupervisor() && (int) $get('training_type') === Application::UNIVERSITY)
                    ->dehydrated()
                    ->searchable()
                    ->preload()
                    ->validationMessages([
                        'required' => trans('validation.custom.institution_id.required', [], 'ar'),
                    ])
                    ->required(fn() => Auth::user()->isAdmin() || Auth::user()->isTrainingManagerLike())
                    ->live()
                    ->afterStateUpdated(function ($set) {
                        $set('college_id', null);
                        $set('major_id', null);
                    })
                    ->columnSpan(1),

                Select::make('college_id')
                    ->label('الكلية')
                    ->placeholder('اختر الكلية')
                    ->options(function (callable $get) {
                        if (Auth::user()->isCollegeSupervisor()) {
                            return College::where('id', Auth::user()->college?->id)
                                ->pluck('name', 'id')
                                ->toArray();
                        }
                        $institutionId = $get('institution_id');
                        if ($institutionId) {
                            return College::active()
                                ->where('institution_id', $institutionId)
                                ->pluck('name', 'id')
                                ->toArray();
                        }
                        return [];
                    })
                    ->default(fn() => Auth::user()->isCollegeSupervisor() ? Auth::user()->college?->id : null)
                    ->disabled(fn(callable $get) => request()->routeIs('*.edit') && (int) $get('training_type') !== Application::UNIVERSITY)
                    ->visible(fn(callable $get) => ! Auth::user()->isMinistry() && ! Auth::user()->isCollegeSupervisor() && (int) $get('training_type') === Application::UNIVERSITY)
                    ->dehydrated()
                    ->searchable()
                    ->preload()
                    ->validationMessages([
                        'required' => trans('validation.custom.college_id.required', [], 'ar'),
                    ])
                    ->required(fn() => Auth::user()->isAdmin() || Auth::user()->isTrainingManagerLike())
                    ->live()
                    ->afterStateUpdated(function ($set) {
                        $set('major_id', null);
                    })
                    ->getOptionLabelUsing(fn($value): ?string => College::find($value)?->name)
                    ->columnSpan(1),

                Select::make('major_id')
                    ->label('التخصص')
                    ->placeholder('اختر التخصص')
                    ->options(function (callable $get) {
                        if (Auth::user()->isCollegeSupervisor() && Auth::user()->college?->id) {
                            return Major::whereHas(
                                'colleges',
                                fn($q) => $q
                                    ->where('colleges.id', Auth::user()->college->id)
                                    ->where('college_major.active', true)
                            )
                                ->pluck('name', 'id')
                                ->toArray();
                        }
                        $collegeId = $get('college_id');
                        if ($collegeId) {
                            return Major::whereHas(
                                'colleges',
                                fn($q) => $q
                                    ->where('colleges.id', $collegeId)
                                    ->where('college_major.active', true)
                            )
                                ->pluck('name', 'id')
                                ->toArray();
                        }
                        return [];
                    })
                    ->disabled(fn($context, callable $get) => $context === 'edit' && (int) $get('training_type') !== Application::UNIVERSITY)
                    ->visible(function (callable $get) {
                        if (Auth::user()->isMinistry()) {
                            return false;
                        }
                        if (Auth::user()->isCollegeSupervisor()) {
                            return true;
                        }
                        $trainingType = $get('training_type');
                        if ($trainingType === null || $trainingType === '') {
                            return false;
                        }
                        return (int) $trainingType === Application::UNIVERSITY;
                    })
                    ->dehydrated()
                    ->searchable()
                    ->preload()
                    ->validationMessages([
                        'required' => trans('validation.custom.major_id.required', [], 'ar'),
                    ])
                    ->required()
                    ->getOptionLabelUsing(fn($value): ?string => Major::find($value)?->name)
                    ->columnSpan(1),
            ]);
    }

    private static function getUniversityNumberGrid(): Grid
    {
        return Grid::make(3)
            ->visible(fn() => ! Auth::user()->isMinistry())
            ->schema([
                TextInput::make('university_number')
                    ->label('الرقم الجامعي')
                    ->placeholder('أدخل الرقم الجامعي')
                    ->dehydrated()
                    ->maxLength(255)
                    ->numeric()
                    ->type('number')
                    ->helperText('الرقم الجامعي')
                    ->visible(function (callable $get) {
                        $user = Auth::user();
                        if ($user->isAdmin()) {
                            return (int) $get('training_type') === Application::UNIVERSITY;
                        }
                        return $user->isCollegeSupervisor();
                    })
                    ->required(function (callable $get) {
                        $user = Auth::user();
                        if ($user->isAdmin()) {
                            return (int) $get('training_type') === Application::UNIVERSITY;
                        }
                        return $user->isCollegeSupervisor();
                    })
                    ->columnSpan(1),
            ]);
    }

    private static function getTrainingLocationGrid(): Grid
    {
        return Grid::make(3)
            ->schema([
                Select::make('administrative_id')
                    ->label('الإدارة')
                    ->placeholder('اختر الإدارة')
                    ->options(function () {
                        $query = Administrative::query();
                        if (!Auth::user()->isAdmin()) {
                            $query->active();
                        }

                        if (Auth::user()->isMinistry()) {
                            $query->whereHas('sections', fn($q) => $q->whereHas(
                                'departments',
                                fn($d) => $d->where('is_medical', true)->when(!Auth::user()->isAdmin(), fn($d2) => $d2->active()->visible())
                            ));
                        }

                        if (Auth::user()->isAssistantTrainingManager()) {
                            $query->whereHas('sections.departments', fn($d) => $d->whereIn('departments.id', Auth::user()->managedDepartmentIds()));
                        }

                        return $query->pluck('name', 'id');
                    })
                    ->default(function () {
                        $user = Auth::user();
                        if ($user->isMinistry() && $user->mohDepartment) {
                            $section = Section::whereHas(
                                'departments',
                                fn($q) => $q->where('departments.id', $user->mohDepartment->id)
                            )->first();

                            return $section?->administrative_id;
                        }
                        return null;
                    })
                    ->formatStateUsing(fn($record) => $record?->section?->administrative_id)
                    ->searchable()
                    ->preload()
                    ->reactive()
                    ->afterStateUpdated(function ($set) {
                        if (! Auth::user()->isMinistry()) {
                            $set('department_id', null);
                        }
                        $set('section_id', null);
                    })
                    ->disabled(fn() => ! (Auth::user()->isAdmin() || Auth::user()->isTrainingManagerLike() || Auth::user()->isCollegeSupervisor() || Auth::user()->isMinistry()))
                    ->dehydrated(false)
                    ->required()
                    ->columnSpan(1),

                Select::make('department_id')
                    ->label('الدائرة')
                    ->placeholder('اختر الدائرة')
                    ->options(function (callable $get) {
                        $adminId = $get('administrative_id');
                        $user = Auth::user();

                        $query = Department::query();
                        if (!$user->isAdmin()) {
                            $query->active()->visible();
                        }

                        if ($user->isMinistry() && ! $user->mohDepartment) {
                            $query->where('is_medical', true);
                        }

                        if (! $adminId) {
                            return $query->pluck('name', 'id');
                        }

                        return $query->whereHas('sections', fn($q) => $q->when(!$user->isAdmin(), fn($s) => $s->active())->where('administrative_id', $adminId))
                            ->pluck('name', 'id');
                    })
                    ->formatStateUsing(fn($record) => $record?->section?->departments->first()?->id)
                    ->default(function () {
                        $user = Auth::user();
                        if ($user->isMinistry() && $user->mohDepartment) {
                            return $user->mohDepartment->id;
                        }
                        return null;
                    })
                    ->searchable()
                    ->preload()
                    ->reactive()
                    ->live()
                    ->afterStateUpdated(function ($set) {
                        $set('section_id', null);
                    })
                    ->disabled(function (callable $get) {
                        $user = Auth::user();
                        if ($user->isMinistry() && $user->mohDepartment) {
                            return true;
                        }
                        return (! $user->isAdmin() && ! $user->isTrainingManagerLike() && ! $user->isCollegeSupervisor() && ! $user->isMinistry())
                            || ! $get('administrative_id');
                    })
                    ->visible(function () {
                        $user = Auth::user();
                        if ($user->isMinistry() && $user->mohDepartment) {
                            return false;
                        }
                        return true;
                    })
                    ->dehydrated(false)
                    ->required(function () {
                        $user = Auth::user();
                        if ($user->isMinistry() && $user->mohDepartment) {
                            return false;
                        }
                        return true;
                    })
                    ->columnSpan(1),

                Hidden::make('department_id')
                    ->default(function () {
                        $user = Auth::user();
                        if ($user->isMinistry() && $user->mohDepartment) {
                            return $user->mohDepartment->id;
                        }
                        return null;
                    })
                    ->visible(function () {
                        $user = Auth::user();
                        return $user->isMinistry() && $user->mohDepartment !== null;
                    })
                    ->dehydrated(false)
                    ->reactive()
                    ->live(),

                Select::make('section_id')
                    ->label('القسم')
                    ->placeholder('اختر القسم')
                    ->options(function (callable $get) {
                        $user    = Auth::user();
                        $adminId = $get('administrative_id');

                        $deptId = ($user->isMinistry() && $user->mohDepartment)
                            ? $user->mohDepartment->id
                            : $get('department_id');

                        $query = Section::query();
                        if (!Auth::user()->isAdmin()) {
                            $query->active();
                        }

                        if ($adminId) {
                            $query->where('administrative_id', $adminId);
                        }

                        if ($deptId) {
                            $query->whereHas('departments', fn($q) => $q->where('departments.id', $deptId)->when(!Auth::user()->isAdmin(), fn($d) => $d->active()->visible()));
                        }

                        $settings   = app(TrainingSettings::class);
                        $showFull   = $settings->hide_full_sections;
                        $isAdmin    = $user->isAdmin();
                        $shouldHide = ! $showFull && ! $isAdmin;

                        $sections = $query->get();

                        if ($shouldHide) {
                            $sections = $sections->reject(fn(Section $sec) => $sec->isFull());
                        }

                        return $sections->mapWithKeys(function (Section $sec) {
                            $stats = $sec->getCapacityStats();
                            $label = $sec->name . ($stats['is_full'] ? ' (ممتلئ)' : '');
                            return [$sec->id => $label];
                        });
                    })
                    ->getOptionLabelUsing(fn($value): ?string => Section::find($value)?->name)
                    ->disableOptionWhen(function (string $value) {
                        if (Auth::user()->isAdmin()) {
                            return false;
                        }
                        $section = Section::find($value);
                        return $section && $section->isFull();
                    })
                    ->searchable()
                    ->preload()
                    ->disabled(function (callable $get) {
                        $user = Auth::user();

                        if (! $user->isAdmin() && ! $user->isTrainingManagerLike() && ! $user->isCollegeSupervisor() && ! $user->isMinistry()) {
                            return true;
                        }

                        $deptId = ($user->isMinistry() && $user->mohDepartment)
                            ? $user->mohDepartment->id
                            : $get('department_id');

                        return ! $get('administrative_id') || ! $deptId;
                    })
                    ->required()
                    ->columnSpan(1),
            ]);
    }

    private static function getTrainingDatesAndStatusGrid(): Grid
    {
        return Grid::make(3)
            ->schema([
                DatePicker::make('start_date')
                    ->label('تاريخ البدء')
                    ->placeholder('اختر تاريخ البدء')
                    ->native(false)
                    ->displayFormat('d/m/Y')
                    ->disabled(fn() => ! (Auth::user()->isAdmin() || Auth::user()->isTrainingManagerLike()))
                    ->visible(fn(callable $get) => ! Auth::user()->isCollegeSupervisor() && in_array((int) $get('status'), [Application::STATUS_STARTED_TRAINING]))
                    ->required(fn(callable $get) => (Auth::user()->isAdmin() || Auth::user()->isTrainingManagerLike()) && in_array((int) $get('status'), [Application::STATUS_STARTED_TRAINING]))
                    ->dehydrated()
                    ->columnSpan(1),

                DatePicker::make('end_date')
                    ->label('تاريخ الانتهاء')
                    ->placeholder('اختر تاريخ الانتهاء')
                    ->native(false)
                    ->displayFormat('d/m/Y')
                    ->disabled(fn() => ! (Auth::user()->isAdmin() || Auth::user()->isTrainingManagerLike()))
                    ->visible(fn(callable $get) => ! Auth::user()->isCollegeSupervisor() && in_array((int) $get('status'), [Application::STATUS_STARTED_TRAINING]))
                    ->required(fn(callable $get) => (Auth::user()->isAdmin() || Auth::user()->isTrainingManagerLike()) && in_array((int) $get('status'), [Application::STATUS_STARTED_TRAINING]))
                    ->afterOrEqual('start_date')
                    ->dehydrated()
                    ->columnSpan(1),

                Select::make('status')
                    ->label('حالة الطلب')
                    ->placeholder('اختر الحالة')
                    ->options(Application::getStatuses())
                    ->default(fn() => (Auth::user()->isCollegeSupervisor() || Auth::user()->isMinistry())
                        ? Application::STATUS_CONFIRMATION
                        : Application::STATUS_NEW)
                    ->disabled(fn() => ! (Auth::user()->isAdmin() || Auth::user()->isTrainingManagerLike()))
                    ->visible(fn() => Auth::user()->isAdmin() || Auth::user()->isTrainingManagerLike())
                    ->afterStateUpdated(fn($state, $set) => (int) $state === Application::STATUS_CONFIRMATION ? $set('accepted_at', now()) : null)
                    ->dehydrated()
                    ->required()
                    ->live()
                    ->columnSpan(1),

                CheckboxList::make('days_note.training_days')
                    ->label('أيام التدريب')
                    ->options(Application::ALL_DAYS)
                    ->columns(5)
                    ->afterStateHydrated(fn($component, $state) => $component->state(array_map('intval', (array) $state)))
                    ->dehydrateStateUsing(fn($state) => array_map('intval', (array) $state))
                    ->visible(fn(callable $get) => (Auth::user()->isAdmin() || Auth::user()->isTrainingManagerLike()) && (int) $get('status') === Application::STATUS_STARTED_TRAINING)
                    ->columnSpanFull(),

                Textarea::make('days_note.note')
                    ->label('ملاحظات التدريب')
                    ->rows(3)
                    ->visible(fn(callable $get) => (Auth::user()->isAdmin() || Auth::user()->isTrainingManagerLike()) && (int) $get('status') === Application::STATUS_STARTED_TRAINING)
                    ->columnSpanFull(),
            ]);
    }

    public static function getDocumentsAndNotesSection(): FormSection
    {
        return FormSection::make('المستندات والملاحظات')
            ->columnSpanFull()
            ->description('رفع المستندات المطلوبة وإضافة ملاحظات')
            ->icon('heroicon-o-paper-clip')
            ->iconColor('warning')
            ->collapsible()
            ->collapsed()
            ->schema([
                FileUpload::make('application_letter')
                    ->label('صورة خطاب التدريب')
                    ->helperText('يرجى رفع صورة واضحة لخطاب التدريب (PNG, JPG, JPEG)')
                    ->image()
                    ->disk('public')
                    ->directory('application-letters')
                    ->visibility('public')
                    ->imageEditor()
                    ->imageEditorAspectRatios([
                        null,
                        '16:9',
                        '4:3',
                        '1:1',
                    ])
                    ->maxSize(5120)
                    ->acceptedFileTypes(['image/png', 'image/jpeg', 'image/jpg'])
                    ->columnSpanFull(),

                TextInput::make('tags')
                    ->label('الوسوم')
                    ->placeholder('أدخل الوسوم مفصولة بفواصل')
                    ->helperText('يمكنك إضافة وسوم لتسهيل البحث والتصنيف')
                    ->columnSpanFull(),
            ]);
    }
}
