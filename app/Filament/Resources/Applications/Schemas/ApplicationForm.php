<?php

declare(strict_types=1);

namespace App\Filament\Resources\Applications\Schemas;

use App\Enums\ApplicationStatus;
use App\Enums\TrainingType;
use App\Models\Administrative;
use App\Models\College;
use App\Models\Institution;
use App\Models\Major;
use App\Models\Section;
use App\Models\Trainee;
use App\Rules\PalestinianId;
use App\Settings\TrainingSettings;
use Carbon\Carbon;
use Filament\Actions\Action as FormAction;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\FileUpload;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Group;
use Filament\Forms\Components\Hidden;
use Filament\Forms\Components\Placeholder;
use Filament\Schemas\Components\Section as FormSection;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Notifications\Notification;
use Filament\Schemas\Schema;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Support\Facades\Auth;
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
                                                        ->label('المنطقة / الشارع'),

                                                    DatePicker::make('dob')
                                                        ->label('تاريخ الميلاد')
                                                        ->native(false)
                                                        ->displayFormat('d/m/Y'),

                                                    Select::make('major_id')
                                                        ->label('التخصص')
                                                        ->options(Major::pluck('name', 'id'))
                                                        ->searchable()
                                                        ->preload(),
                                                ]),
                                            ])
                                            ->action(function ($record, array $data, Component $livewire) {
                                                $record->trainee->update($data);

                                                $livewire->data['full_name'] = $data['full_name'];
                                                $livewire->data['national_id'] = $data['national_id'];
                                                $livewire->data['phone_number'] = $data['phone_number'];
                                                $livewire->data['street'] = $data['street'];
                                                $livewire->data['major_id'] = $data['major_id'];

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
                                    ->label('رقم الهوية الوطنية')
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
                                    ->afterStateUpdated(function ($state, callable $set) {
                                        if (empty($state) || strlen($state) < 9) {
                                            return;
                                        }

                                        if (validatePalestinianId($state) !== 'valid') {
                                            Notification::make()
                                                ->title('رقم الهوية الوطنية غير صحيح')
                                                ->danger()
                                                ->send();
                                            $set('national_id', null);
                                            return;
                                        }

                                        $trainee = Trainee::where('national_id', $state)->first();

                                        if ($trainee) {
                                            $set('trainee_id', $trainee->id);
                                            $set('full_name', $trainee->full_name);
                                            $set('phone_number', $trainee->phone_number);
                                            $set('street', $trainee->street);
                                            $set('institution_id', $trainee->institution_id);
                                            $set('college_id', $trainee->college_id);
                                            $set('major_id', $trainee->major_id);
                                            $set('training_hours', $trainee->training_hours);

                                            if ($trainee->institution_id || $trainee->college_id || $trainee->major_id) {
                                                $set('training_type', TrainingType::UNIVERSITY);
                                            } else {
                                                $set('training_type', TrainingType::PRACTICE);
                                            }

                                            if ($trainee->dob) {
                                                $set('dob', Carbon::parse($trainee->dob)->format('Y-m-d'));
                                            }

                                            Notification::make()
                                                ->title('تم العثور على بيانات المتدرب')
                                                ->body('تم تعبئة الحقول تلقائياً')
                                                ->success()
                                                ->send();
                                        } else {
                                            $set('trainee_id', null);
                                            Notification::make()
                                                ->title('المتدرب غير موجود')
                                                ->body('يمكنك إضافة متدرب جديد')
                                                ->warning()
                                                ->send();
                                        }
                                    })
                                    ->validationMessages([
                                        'required' => __('validation.custom.national_id.required'),
                                        'regex' => __('validation.custom.national_id.regex'),
                                        'digits' => __('validation.custom.national_id.digits'),
                                    ])
                                    ->rules([
                                        new PalestinianId(),
                                    ])
                                    ->required(fn($context) => $context === 'create')
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
                                    ->regex('/^97[02]5[69]\d{7}$/')
                                    ->minLength(12)
                                    ->maxLength(12)
                                    ->helperText('مثال: 970591234567 أو 972561234567')
                                    ->validationMessages([
                                        'required' => __('validation.custom.phone_number.required'),
                                        'regex' => __('validation.custom.phone_number.regex'),
                                    ])
                                    ->required(fn($context) => $context === 'create')
                                    ->columnSpan(1),

                                TextInput::make('street')
                                    ->label('المنطقة / الشارع')
                                    ->placeholder('المنطقة أو الشارع')
                                    ->formatStateUsing(fn($record) => $record?->trainee?->street)
                                    ->disabled(fn($context) => $context === 'edit')
                                    ->dehydrated(fn($context) => $context === 'create')
                                    ->regex('/^[A-Za-z\p{Arabic}0-9\s\-\.,#\/]+$/u')
                                    ->maxLength(255)
                                    ->required(fn($context) => $context === 'create')
                                    ->columnSpan(2),
                            ]),

                        DatePicker::make('dob')
                            ->label('تاريخ الميلاد')
                            ->placeholder('أدخل تاريخ الميلاد')
                            ->native(false)
                            ->displayFormat('Y/m/d')
                            ->formatStateUsing(fn($record) => $record?->trainee?->dob)
                            ->disabled(fn($context) => $context === 'edit')
                            ->dehydrated(fn($context) => $context === 'create')
                            ->required(fn($context) => $context === 'create'),
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
                                    ->options(TrainingType::class)
                                    ->default(function () {
                                        if (Auth::user()->isCollegeSupervisor()) {
                                            return TrainingType::UNIVERSITY;
                                        }
                                        return TrainingType::PRACTICE;
                                    })
                                    ->disabled(fn() => !Auth::user()->isAdmin())
                                    ->visible(fn() => !Auth::user()->isCollegeSupervisor())
                                    ->dehydrated()
                                    ->live()
                                    ->required()
                                    ->columnSpan(1),

                                TextInput::make('training_hours')
                                    ->label('ساعات التدريب المطلوبة')
                                    ->placeholder('120')
                                    ->formatStateUsing(fn($record) => $record?->trainee?->training_hours)
                                    ->dehydrated(fn($context) => $context === 'create')
                                    ->numeric()
                                    ->suffix('ساعة')
                                    ->helperText('الساعات الأكاديمية المطلوبة')
                                    ->columnSpan(1),
                            ]),

                        // University Information (Conditional)
                        Grid::make(3)
                            ->schema([
                                Select::make('institution_id')
                                    ->label('المؤسسة التعليمية')
                                    ->placeholder('اختر المؤسسة')
                                    ->options(fn() => Institution::pluck('name', 'id')->toArray())
                                    ->default(fn() => Auth::user()->isCollegeSupervisor() ? Auth::user()->college?->institution_id : null)
                                    ->formatStateUsing(fn($record) => $record?->trainee?->institution_id)
                                    ->disabled(fn(callable $get) => request()->routeIs('*.edit') && $get('training_type') != TrainingType::UNIVERSITY->value)
                                    ->visible(fn(callable $get) => !Auth::user()->isCollegeSupervisor() && $get('training_type') == TrainingType::UNIVERSITY->value)
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
                                            return College::where('institution_id', $institutionId)
                                                ->pluck('name', 'id')
                                                ->toArray();
                                        }
                                        return [];
                                    })
                                    ->default(fn() => Auth::user()->isCollegeSupervisor() ? Auth::user()->college?->id : null)
                                    ->formatStateUsing(fn($record) => $record?->trainee?->college_id)
                                    ->disabled(fn(callable $get) => request()->routeIs('*.edit') && $get('training_type') != TrainingType::UNIVERSITY->value)
                                    ->visible(fn(callable $get) => !Auth::user()->isCollegeSupervisor() && $get('training_type') == TrainingType::UNIVERSITY->value)
                                    ->dehydrated()
                                    ->searchable()
                                    ->preload()
                                    ->validationMessages([
                                        'required' => __('validation.custom.college_id.required'),
                                    ])
                                    ->required(fn() => Auth::user()->isAdmin())
                                    ->reactive()
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
                                    ->disabled(fn($context, callable $get) => $context === 'edit' && $get('training_type') != TrainingType::UNIVERSITY->value)
                                    ->visible(fn(callable $get) => $get('training_type') == TrainingType::UNIVERSITY->value)
                                    ->dehydrated()
                                    ->searchable()
                                    ->preload()
                                    ->validationMessages([
                                        'required' => __('validation.custom.major_id.required'),
                                    ])
                                    ->required(fn($context) => $context === 'create')
                                    ->columnSpan(1),
                            ]),

                        // Training Location
                        Grid::make(3)
                            ->schema([
                                Select::make('administrative_id')
                                    ->label('الإدارة')
                                    ->placeholder('اختر الإدارة')
                                    ->relationship('administrative', 'title')
                                    ->getOptionLabelFromRecordUsing(fn(Administrative $record) => $record->name_with_governorate)
                                    ->searchable()
                                    ->preload()
                                    ->reactive()
                                    ->disabled(fn() => !Auth::user()->isAdmin() && !Auth::user()->isCollegeSupervisor())
                                    ->required()
                                    ->columnSpan(1),

                                Select::make('department_id')
                                    ->label('الدائرة')
                                    ->placeholder('اختر الدائرة')
                                    ->options(function (callable $get) {
                                        $adminId = $get('administrative_id');
                                        if (!$adminId) {
                                            return \App\Models\Department::pluck('title', 'id');
                                        }
                                        return \App\Models\Department::whereHas('sections', fn($q) => $q->where('administrative_id', $adminId))
                                            ->pluck('title', 'id');
                                    })
                                    ->searchable()
                                    ->preload()
                                    ->reactive()
                                    ->disabled(fn(callable $get) => (!Auth::user()->isAdmin() && !Auth::user()->isCollegeSupervisor()) || !$get('administrative_id'))
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
                                            $label = $sec->name_location . ($stats['is_full'] ? ' (ممتلئ)' : '');
                                            return [$sec->id => $label];
                                        });
                                    })
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
                                    ->visible(fn() => !Auth::user()->isCollegeSupervisor())
                                    ->required(fn(callable $get) => Auth::user()->isAdmin() && $get('status') === ApplicationStatus::STARTED_TRAINING->value)
                                    ->dehydrated()
                                    ->columnSpan(1),

                                DatePicker::make('end_date')
                                    ->label('تاريخ الانتهاء')
                                    ->placeholder('اختر تاريخ الانتهاء')
                                    ->native(false)
                                    ->displayFormat('d/m/Y')
                                    ->disabled(fn() => !Auth::user()->isAdmin())
                                    ->visible(fn() => !Auth::user()->isCollegeSupervisor())
                                    ->required(fn(callable $get) => Auth::user()->isAdmin() && $get('status') === ApplicationStatus::STARTED_TRAINING->value)
                                    ->afterOrEqual('start_date')
                                    ->dehydrated()
                                    ->columnSpan(1),

                                Select::make('status')
                                    ->label('حالة الطلب')
                                    ->placeholder('اختر الحالة')
                                    ->options(ApplicationStatus::class)
                                    ->default(fn() => (Auth::user()->isCollegeSupervisor() || Auth::user()->isMinistry()) ? ApplicationStatus::CONFIRMATION : ApplicationStatus::NEW)
                                    ->disabled(fn() => !Auth::user()->isAdmin())
                                    ->visible(fn() => !Auth::user()->isCollegeSupervisor())
                                    ->afterStateUpdated(fn($state, $set) => $state === ApplicationStatus::CONFIRMATION->value ? $set('accepted_at', now()) : null)
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
