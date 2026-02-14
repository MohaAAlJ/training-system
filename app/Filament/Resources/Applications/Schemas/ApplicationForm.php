<?php

declare(strict_types=1);

namespace App\Filament\Resources\Applications\Schemas;

// use App\Enums\ApplicationStatus;
// use App\Enums\TrainingType;
use App\Models\Application;
use App\Models\Administrative;
use App\Models\College;
use App\Models\Institution;
use App\Models\Major;
use App\Models\Governorate;
use App\Models\Section;
use App\Models\Trainee;
use App\Rules\PalestinianId;
use App\Settings\TrainingSettings;
use Carbon\Carbon;
use Filament\Actions\Action as FormAction;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\FileUpload;
use Filament\Schemas\Components\Grid;
use Filament\Forms\Components\Hidden;
use Filament\Schemas\Components\Section as FormSection;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Notifications\Notification;
use Filament\Schemas\Schema;
use Illuminate\Support\Facades\Auth;
use Illuminate\Database\Eloquent\Builder;
use Livewire\Component;

class ApplicationForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->columns(1) // Force single column layout
            ->components([
                Hidden::make('trainee_id'),

                // البيانات الشخصية للمتدرب - Enhanced Section
                FormSection::make('البيانات الشخصية للمتدرب')
                    ->columnSpanFull()
                    ->description('الرجاء إدخال جميع البيانات الشخصية بدقة')
                    ->icon('heroicon-o-user-circle')
                    ->iconColor('primary')
                    ->collapsible()
                    ->schema([
                        // Personal Information Grid
                        Grid::make(3)
                            ->schema([
                                TextInput::make('full_name')
                                    ->label('الاسم الكامل')
                                    ->placeholder('أدخل الاسم الكامل')
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
                                    ->required(fn($context) => $context === 'create')
                                    ->columnSpan(2)
                                    ->suffixAction(
                                        FormAction::make('edit_trainee_details')
                                            ->icon('heroicon-m-pencil-square')
                                            ->tooltip('تعديل بيانات المتدرب الأصلية')
                                            ->label('تعديل')
                                            ->visible(fn($context) => $context === 'edit' && Auth::user()->isAdmin())
                                            ->modalHeading('تعديل بيانات المتدرب')
                                            ->modalDescription('يمكنك تعديل جميع بيانات المتدرب من هنا')
                                            ->modalWidth('3xl')
                                            ->mountUsing(fn($record, $form) => $form->fill([
                                                'full_name' => $record->trainee->full_name,
                                                'national_id' => $record->trainee->national_id,
                                                'phone_number' => $record->trainee->phone_number,
                                                'dob' => $record->trainee->dob,
                                                'street' => $record->trainee->street,
                                                'institution_id' => $record->trainee->institution_id,
                                                'college_id' => $record->trainee->college_id,
                                                'major_id' => $record->trainee->major_id,
                                                'governorate_id' => $record->trainee->governorate_id,
                                                'gender' => $record->trainee->gender,
                                            ]))
                                            ->form([
                                                Grid::make(2)->schema([
                                                    TextInput::make('full_name')
                                                        ->label('الاسم الكامل')
                                                        ->required(),

                                                    TextInput::make('national_id')
                                                        ->label('رقم الهوية')
                                                        ->required()
                                                        ->unique('trainees', 'national_id', ignorable: fn($record) => $record->trainee)
                                                        ->rules([
                                                            new PalestinianId(),
                                                        ]),

                                                    TextInput::make('phone_number')
                                                        ->label('رقم الهاتف')
                                                        ->required(),

                                                    TextInput::make('street')
                                                        ->label('الشارع'),

                                                    DatePicker::make('dob')
                                                        ->label('تاريخ الميلاد')
                                                        ->native(false)
                                                        ->displayFormat('d/m/Y')
                                                        ->required(),

                                                    Select::make('gender')
                                                        ->label('الجنس')
                                                        ->options(\App\Enums\Gender::class)
                                                        ->required(),

                                                    Select::make('governorate_id')
                                                        ->label('المحافظة')
                                                        ->options(Governorate::pluck('name', 'id'))
                                                        ->searchable()
                                                        ->preload()
                                                        ->required(),
                                                ]),
                                            ])
                                            ->action(function ($record, array $data, Component $livewire) {
                                                $record->trainee->update($data);

                                                $livewire->data['full_name'] = $data['full_name'];
                                                $livewire->data['national_id'] = $data['national_id'];
                                                $livewire->data['phone_number'] = $data['phone_number'];
                                                $livewire->data['street'] = $data['street'];
                                                $livewire->data['governorate_id'] = $data['governorate_id'];
                                                $livewire->data['gender'] = $data['gender'];

                                                if (!empty($data['dob'])) {
                                                    $livewire->data['dob'] = $data['dob'];
                                                }

                                                Notification::make()
                                                    ->title('تم تحديث بيانات المتدرب بنجاح')
                                                    ->success()
                                                    ->send();
                                            })
                                    ),

                                TextInput::make('national_id')
                                    ->label('رقم الهوية')
                                    ->placeholder('123456789')
                                    ->formatStateUsing(fn($record) => $record?->trainee?->national_id)
                                    ->disabled(fn($context) => $context === 'edit')
                                    ->dehydrated(fn($context) => $context === 'create')
                                    ->numeric()
                                    ->minLength(9)
                                    ->maxLength(9)
                                    ->regex('/^\d{9}$/')
                                    ->helperText('9 أرقام فقط')
                                    ->live(onBlur: true)
                                    ->validationMessages([
                                        'regex' => __('validation.custom.national_id.regex'),
                                        'digits' => __('validation.custom.national_id.digits'),
                                        'unique' => 'رقم الهوية مسجل مسبقاً في النظام',
                                    ])
                                    ->rules([
                                        new PalestinianId(),
                                        fn($context) => $context === 'create' ? 'unique:trainees,national_id' : null,
                                    ])
                                    ->required()
                                    ->columnSpan(1),
                            ]),

                        Grid::make(4)
                            ->schema([
                                Select::make('gender')
                                    ->label('الجنس')
                                    ->options(\App\Enums\Gender::class)
                                    ->formatStateUsing(fn($record) => $record?->trainee?->gender)
                                    ->disabled(fn($context) => $context === 'edit')
                                    ->dehydrated(fn($context) => $context === 'create')
                                    ->required()
                                    ->columnSpan(1),

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
                                        'regex' => 'صيغة رقم الجوال غير صحيحة. استخدم 9705XXXXXXXX أو 9725XXXXXXXX',
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

                        DatePicker::make('dob')
                            ->label('تاريخ الميلاد')
                            ->placeholder('أدخل تاريخ الميلاد')
                            ->native(false)
                            ->displayFormat('Y/m/d')
                            ->formatStateUsing(fn($record) => $record?->trainee?->dob)
                            ->disabled(fn($context) => $context === 'edit')
                            ->dehydrated(fn($context) => $context === 'create')
                            ->required(),
                    ]),

                // تفاصيل الطلب - Enhanced Section
                FormSection::make('تفاصيل الطلب')
                    ->columnSpanFull()
                    ->description('تفاصيل طلب التدريب والجهة المستقبلة')
                    ->icon('heroicon-o-clipboard-document-check')
                    ->iconColor('success')
                    ->collapsible()
                    ->schema([
                        // Training Type and Educational Information
                        Grid::make(3)
                            ->schema([
                                Select::make('training_type')
                                    ->label('نوع التدريب')
                                    ->placeholder('اختر نوع التدريب')
                                    ->options([
                                        Application::UNIVERSITY => Application::getTrainingTypeLabel(Application::UNIVERSITY),
                                        Application::PRACTICE => Application::getTrainingTypeLabel(Application::PRACTICE),
                                    ])
                                    ->default(function () {
                                        if (Auth::user()->isCollegeSupervisor()) {
                                            return Application::UNIVERSITY;
                                        }
                                        return Application::PRACTICE;
                                    })
                                    ->disabled(fn() => !Auth::user()->isAdmin())
                                    ->visible(fn() => !Auth::user()->isCollegeSupervisor())
                                    ->dehydrated()
                                    ->live()
                                    ->required()
                                    ->columnSpan(1),

                                Hidden::make('training_type')
                                    ->default(Application::UNIVERSITY)
                                    ->visible(fn() => Auth::user()->isCollegeSupervisor())
                                    ->dehydrated(),

                                TextInput::make('training_hours')
                                    ->label('ساعات التدريب المطلوبة')
                                    ->placeholder('120')
                                    ->formatStateUsing(fn($record) => $record?->trainee?->training_hours)
                                    ->dehydrated(fn($context) => $context === 'create')
                                    ->numeric()
                                    ->suffix('ساعة')
                                    ->helperText('الساعات الأكاديمية المطلوبة')
                                    ->required(fn() => Auth::user()->isCollegeSupervisor())
                                    ->columnSpan(1),
                            ]),

                        // University Information (Conditional)
                        Grid::make(3)
                            ->schema([
                                Select::make('institution_id')
                                    ->label('المؤسسة التعليمية')
                                    ->placeholder('اختر المؤسسة')
                                    ->options(fn() => Institution::active()->pluck('name', 'id')->toArray())
                                    ->default(fn() => Auth::user()->isCollegeSupervisor() ? Auth::user()->college?->institution_id : null)
                                    ->formatStateUsing(fn($record) => $record?->trainee?->institution_id)
                                    ->disabled(fn(callable $get) => request()->routeIs('*.edit') && (int)$get('training_type') !== Application::UNIVERSITY)
                                    ->visible(fn(callable $get) => !Auth::user()->isCollegeSupervisor() && (int)$get('training_type') === Application::UNIVERSITY)
                                    ->dehydrated()
                                    ->searchable()
                                    ->preload()
                                    ->validationMessages([
                                        'required' => __('validation.custom.institution_id.required'),
                                    ])
                                    ->required(fn() => Auth::user()->isAdmin())
                                    ->reactive()
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
                                            return College::active()->where('institution_id', $institutionId)
                                                ->pluck('name', 'id')
                                                ->toArray();
                                        }
                                        return [];
                                    })
                                    ->default(fn() => Auth::user()->isCollegeSupervisor() ? Auth::user()->college?->id : null)
                                    ->formatStateUsing(fn($record) => $record?->trainee?->college_id)
                                    ->disabled(fn(callable $get) => request()->routeIs('*.edit') && (int)$get('training_type') !== Application::UNIVERSITY)
                                    ->visible(fn(callable $get) => !Auth::user()->isCollegeSupervisor() && (int)$get('training_type') === Application::UNIVERSITY)
                                    ->dehydrated()
                                    ->searchable()
                                    ->preload()
                                    ->validationMessages([
                                        'required' => __('validation.custom.college_id.required'),
                                    ])
                                    ->required(fn() => Auth::user()->isAdmin())
                                    ->reactive()
                                    ->getOptionLabelUsing(fn($value): ?string => College::find($value)?->name)
                                    ->columnSpan(1),

                                Select::make('major_id')
                                    ->label('التخصص')
                                    ->placeholder('اختر التخصص')
                                    ->options(function (callable $get) {
                                        if (Auth::user()->isCollegeSupervisor() && Auth::user()->college?->id) {
                                            return Major::whereHas('colleges', fn($q) => $q->where('colleges.id', Auth::user()->college->id))
                                                ->pluck('name', 'id')
                                                ->toArray();
                                        }
                                        $collegeId = $get('college_id');
                                        if ($collegeId) {
                                            return Major::whereHas('colleges', fn($q) => $q->where('colleges.id', $collegeId))
                                                ->pluck('name', 'id')
                                                ->toArray();
                                        }
                                        return [];
                                    })
                                    ->formatStateUsing(fn($record) => $record?->trainee?->major_id)
                                    ->disabled(fn($context, callable $get) => $context === 'edit' && (int)$get('training_type') !== Application::UNIVERSITY)
                                    ->visible(fn(callable $get) => (int)$get('training_type') === Application::UNIVERSITY)
                                    ->dehydrated()
                                    ->searchable()
                                    ->preload()
                                    ->validationMessages([
                                        'required' => __('validation.custom.major_id.required'),
                                    ])
                                    ->required()
                                    ->getOptionLabelUsing(fn($value): ?string => Major::find($value)?->name)
                                    ->columnSpan(1),

                                TextInput::make('university_number')
                                    ->label('الرقم الجامعي')
                                    ->placeholder('أدخل الرقم الجامعي')
                                    ->formatStateUsing(fn($record) => $record?->university_number)
                                    ->dehydrated()
                                    ->maxLength(255)
                                    ->helperText('الرقم الجامعي')
                                    ->visible(function (callable $get) {
                                        $trainingType = $get('training_type');
                                        // Check against both enum instance and value
                                        return (int)$trainingType === Application::UNIVERSITY;
                                    })
                                    ->required(function (callable $get) {
                                        $trainingType = $get('training_type');
                                        return (int)$trainingType === Application::UNIVERSITY;
                                    })
                                    ->columnSpan(1),
                            ]),

                        // Training Location
                        Grid::make(3)
                            ->schema([

                                Select::make('administrative_id')
                                    ->label('الإدارة')
                                    ->placeholder('اختر الإدارة')
                                    ->options(fn() => Administrative::active()->pluck('name', 'id'))
                                    ->formatStateUsing(fn($record) => $record?->section?->administrative_id)
                                    ->searchable()
                                    ->preload()
                                    ->reactive()
                                    ->afterStateUpdated(function ($set) {
                                        $set('department_id', null);
                                        $set('section_id', null);
                                    })
                                    ->disabled(fn() => !Auth::user()->isAdmin() && !Auth::user()->isCollegeSupervisor())
                                    ->dehydrated(false) // Don't save - just for filtering
                                    ->required()
                                    ->columnSpan(1),

                                Select::make('department_id')
                                    ->getOptionLabelUsing(fn($value): ?string => \App\Models\Department::find($value)?->name)
                                    ->label('الدائرة')
                                    ->placeholder('اختر الدائرة')
                                    ->options(function (callable $get) {
                                        $adminId = $get('administrative_id');
                                        if (!$adminId) {
                                            return \App\Models\Department::active()->pluck('name', 'id');
                                        }
                                        return \App\Models\Department::active()->whereHas('sections', fn($q) => $q->active()->where('administrative_id', $adminId))
                                            ->pluck('name', 'id');
                                    })
                                    ->formatStateUsing(fn($record) => $record?->section?->department_id)
                                    ->searchable()
                                    ->preload()
                                    ->reactive()
                                    ->afterStateUpdated(function ($set) {
                                        $set('section_id', null);
                                    })
                                    ->disabled(fn(callable $get) => (!Auth::user()->isAdmin() && !Auth::user()->isCollegeSupervisor()) || !$get('administrative_id'))
                                    ->dehydrated(false) // Don't save - just for filtering
                                    ->required()
                                    ->columnSpan(1),

                                Select::make('section_id')
                                    ->label('القسم')
                                    ->placeholder('اختر القسم')
                                    ->options(function (callable $get) {
                                        $query = Section::query()->active();

                                        if ($adminId = $get('administrative_id')) {
                                            $query->where('administrative_id', $adminId);
                                        }

                                        if ($deptId = $get('department_id')) {
                                            $query->where('department_id', $deptId);
                                        }

                                        $settings = app(TrainingSettings::class);
                                        $showFull = $settings->hide_full_sections;
                                        $isAdmin = Auth::user()->isAdmin();

                                        $shouldHide = !$showFull && !$isAdmin;

                                        $sections = $query->get();

                                        if ($shouldHide) {
                                            $sections = $sections->reject(fn(Section $sec) => $sec->getCapacityStats()['is_full'] ?? false);
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
                                        return $section && ($section->getCapacityStats()['is_full'] ?? false);
                                    })
                                    ->searchable()
                                    ->preload()
                                    ->disabled(fn(callable $get) => (!Auth::user()->isAdmin() && !Auth::user()->isCollegeSupervisor()) || !$get('department_id'))
                                    ->required()
                                    ->columnSpan(1),
                            ]),

                        // Training Dates and Status
                        Grid::make(3)
                            ->schema([
                                DatePicker::make('start_date')
                                    ->label('تاريخ البدء')
                                    ->placeholder('اختر تاريخ البدء')
                                    ->native(false)
                                    ->displayFormat('d/m/Y')
                                    ->disabled(fn() => !Auth::user()->isAdmin())
                                    ->visible(fn(callable $get) => !Auth::user()->isCollegeSupervisor() && in_array((int)$get('status'), [Application::STATUS_STARTED_TRAINING]))
                                    ->required(fn(callable $get) => Auth::user()->isAdmin() && in_array((int)$get('status'), [Application::STATUS_STARTED_TRAINING]))
                                    ->dehydrated()
                                    ->columnSpan(1),

                                DatePicker::make('end_date')
                                    ->label('تاريخ الانتهاء')
                                    ->placeholder('اختر تاريخ الانتهاء')
                                    ->native(false)
                                    ->displayFormat('d/m/Y')
                                    ->disabled(fn() => !Auth::user()->isAdmin())
                                    ->visible(fn(callable $get) => !Auth::user()->isCollegeSupervisor() && in_array((int)$get('status'), [Application::STATUS_STARTED_TRAINING]))
                                    ->required(fn(callable $get) => Auth::user()->isAdmin() && in_array((int)$get('status'), [Application::STATUS_STARTED_TRAINING]))
                                    ->afterOrEqual('start_date')
                                    ->dehydrated()
                                    ->columnSpan(1),

                                Select::make('status')
                                    ->label('حالة الطلب')
                                    ->placeholder('اختر الحالة')
                                    ->options(Application::getStatuses())
                                    ->default(fn() => (Auth::user()->isCollegeSupervisor() || Auth::user()->isMinistry()) ? Application::STATUS_CONFIRMATION : Application::STATUS_NEW)
                                    ->disabled(fn() => !Auth::user()->isAdmin())
                                    ->visible(fn() => !Auth::user()->isCollegeSupervisor())
                                    ->afterStateUpdated(fn($state, $set) => (int)$state === Application::STATUS_CONFIRMATION ? $set('accepted_at', now()) : null)
                                    ->dehydrated()
                                    ->required()
                                    ->live()
                                    ->columnSpan(1),
                            ]),
                    ]),

                // المستندات والملاحظات - Enhanced Section
                FormSection::make('المستندات والملاحظات')
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
                    ]),
            ]);
    }
}
