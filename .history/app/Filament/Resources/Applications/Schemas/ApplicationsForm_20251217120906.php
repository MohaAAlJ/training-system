<?php

namespace App\Filament\Resources\Applications\Schemas;

use App\Models\Institution;
use App\Models\College;
use App\Models\Major;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Fieldset;
use Filament\Schemas\Schema;
use Illuminate\Support\Facades\Auth;
use Illuminate\Contracts\Auth\Authenticatable;
use Closure;

class ApplicationsForm
{
    // Constants for labels and options
    private const PERSONAL_DATA_LABEL = 'البيانات الشخصية للمتدرب';
    private const REQUEST_DETAILS_LABEL = 'تفاصيل الطلب';
    private const DOCUMENTS_LABEL = 'المستندات والملاحظات';

    private const TRAINING_TYPES = [
        'cooperative' => 'تدريب جامعي',
        'professional' => 'مزاولة مهنة',
    ];

    private const APPLICATION_STATUSES = [
        'pending' => 'طلب جديد',
        'approved' => 'استيعاب',
        'waiting' => 'لم يستلم عمل بعد',
        'active' => 'بدء العمل',
        'completed' => 'انتهى',
        'rejected' => 'مرفوض',
        'paused' => 'منقطع',
    ];

    private const TRAINEE_FIELDS = [
        'national_id',
        'full_name',
        'phone_number',
        'address',
    ];

    private const DURATION_CONSTRAINTS = [
        'min' => 20,
        'max' => 1000,
        'default' => 100,
    ];

    private const FILE_UPLOAD_CONFIG = [
        'disk' => 'public',
        'directory' => 'application-letters',
        'visibility' => 'public',
    ];

    /**
     * Check if current user is admin or college supervisor
     */
    private static function isEditableByUser(): Closure
    {
        return static fn() => Auth::user()?->isAdmin() || Auth::user()?->isCollegeSupervisor() ?? false;
    }

    /**
     * Check if current user is admin only
     */
    private static function isAdminOnly(): Closure
    {
        return static fn() => Auth::user()?->isAdmin() ?? false;
    }

    /**
     * Get default institution for college supervisors
     */
    private static function getDefaultInstitution(): ?int
    {
        $user = Auth::user();
        return $user?->isCollegeSupervisor() ? $user->college?->institution_id : null;
    }

    /**
     * Get default college for college supervisors
     */
    private static function getDefaultCollege(): ?int
    {
        $user = Auth::user();
        return $user?->isCollegeSupervisor() ? $user->college_id : null;
    }

    /**
     * Get default training type based on user role
     */
    private static function getDefaultTrainingType(): ?string
    {
        $user = Auth::user();
        
        if ($user?->isCollegeSupervisor()) {
            return 'cooperative';
        }
        
        if ($user?->isMinistry()) {
            return 'professional';
        }
        
        return null;
    }

    /**
     * Check if college supervisor has institution locked
     */
    private static function isCollegeSupervisor(): Closure
    {
        return static fn() => Auth::user()?->isCollegeSupervisor() ?? false;
    }

    public static function configure(Schema $schema): Schema
    {
        return $schema->components([
            self::personalDataFieldset(),
            self::requestDetailsFieldset(),
            self::documentsFieldset(),
        ]);
    }

    /**
     * Personal data section for trainee information
     */
    private static function personalDataFieldset(): Fieldset
    {
        return Fieldset::make(self::PERSONAL_DATA_LABEL)
            ->schema([
                ...self::buildTraineeFields(),
                self::institutionSelect(),
                self::collegeSelect(),
                self::majorSelect(),
            ])
            ->columns(2);
    }

    /**
     * Request details section (training type, duration, department, dates, status)
     */
    private static function requestDetailsFieldset(): Fieldset
    {
        return Fieldset::make(self::REQUEST_DETAILS_LABEL)
            ->schema([
                self::trainingTypeSelect(),
                self::durationInput(),
                self::administrativeSelect(),
                self::departmentSelect(),
                self::startDatePicker(),
                self::endDatePicker(),
                self::statusSelect(),
            ])
            ->columns(2);
    }

    /**
     * Documents and notes section
     */
    private static function documentsFieldset(): Fieldset
    {
        return Fieldset::make(self::DOCUMENTS_LABEL)
            ->schema([
                self::applicationLetterUpload(),
                self::tagsInput(),
                self::acceptedAtPicker(),
            ])
            ->columns(1);
    }

    /**
     * Build all trainee personal information fields
     */
    private static function buildTraineeFields(): array
    {
        $fields = [];
        
        foreach (self::TRAINEE_FIELDS as $field) {
            $fields[] = self::traineeField($field);
        }
        
        return $fields;
    }

    /**
     * Create a trainee field (national_id, full_name, phone_number, address)
     */
    private static function traineeField(string $fieldName): TextInput
    {
        $labels = [
            'national_id' => 'رقم الهوية',
            'full_name' => 'الاسم الكامل',
            'phone_number' => 'رقم الهاتف',
            'address' => 'العنوان',
        ];

        return TextInput::make($fieldName)
            ->label($labels[$fieldName] ?? $fieldName)
            ->formatStateUsing(fn($record) => $record?->trainee?->{$fieldName})
            ->disabled(static fn() => !self::isEditableByUser()())
            ->required()
            ->dehydrated();
    }

    /**
     * Institution select field
     */
    private static function institutionSelect(): Select
    {
        return Select::make('institution_id')
            ->label('المؤسسة التعليمية')
            ->options(fn() => Institution::query()->pluck('name', 'id'))
            ->default(self::getDefaultInstitution())
            ->disabled(self::isCollegeSupervisor())
            ->dehydrated()
            ->required()
            ->reactive();
    }

    /**
     * College select field with dynamic loading based on institution
     */
    private static function collegeSelect(): Select
    {
        return Select::make('college_id')
            ->label('الكلية')
            ->options(function (callable $get) {
                $institutionId = $get('institution_id');
                
                if (!$institutionId) {
                    return [];
                }
                
                return College::query()
                    ->where('institution_id', $institutionId)
                    ->pluck('name', 'id');
            })
            ->default(self::getDefaultCollege())
            ->disabled(self::isCollegeSupervisor())
            ->dehydrated()
            ->required()
            ->reactive();
    }

    /**
     * Major select field with dynamic loading based on college
     */
    private static function majorSelect(): Select
    {
        return Select::make('major_id')
            ->label('التخصص')
            ->options(function (callable $get) {
                $collegeId = $get('college_id');

                if (!$collegeId) {
                    return [];
                }

                return Major::query()
                    ->whereHas('colleges', fn($q) => $q->where('colleges.id', $collegeId))
                    ->pluck('name', 'id');
            })
            ->searchable()
            ->preload()
            ->required();
    }

    /**
     * Training type select field
     */
    private static function trainingTypeSelect(): Select
    {
        return Select::make('training_type')
            ->label('نوع التدريب')
            ->options(self::TRAINING_TYPES)
            ->default(self::getDefaultTrainingType())
            ->disabled(self::isAdminOnly())
            ->dehydrated()
            ->required();
    }

    /**
     * Training duration numeric input
     */
    private static function durationInput(): TextInput
    {
        return TextInput::make('duration')
            ->label('مدة التدريب (بالساعات)')
            ->numeric()
            ->minValue(self::DURATION_CONSTRAINTS['min'])
            ->maxValue(self::DURATION_CONSTRAINTS['max'])
            ->default(self::DURATION_CONSTRAINTS['default'])
            ->suffix('ساعة')
            ->disabled(static fn() => !self::isEditableByUser()())
            ->required();
    }

    /**
     * Administrative department select
     */
    private static function administrativeSelect(): Select
    {
        return Select::make('administrative_id')
            ->label('الادارة')
            ->relationship('administrative', 'title')
            ->preload()
            ->disabled(static fn() => !self::isEditableByUser()())
            ->required();
    }

    /**
     * Department select
     */
    private static function departmentSelect(): Select
    {
        return Select::make('department_id')
            ->label('القسم')
            ->relationship('department', 'name_location')
            ->preload()
            ->disabled(static fn() => !self::isEditableByUser()())
            ->required();
    }

    /**
     * Start date picker
     */
    private static function startDatePicker(): DatePicker
    {
        return DatePicker::make('start_date')
            ->label('تاريخ البدء')
            ->native(false)
            ->disabled(self::isAdminOnly())
            ->required(self::isAdminOnly())
            ->dehydrated();
    }

    /**
     * End date picker with validation
     */
    private static function endDatePicker(): DatePicker
    {
        return DatePicker::make('end_date')
            ->label('تاريخ الانتهاء')
            ->native(false)
            ->disabled(self::isAdminOnly())
            ->required(self::isAdminOnly())
            ->afterOrEqual('start_date')
            ->dehydrated();
    }

    /**
     * Application status select with reactive state handling
     */
    private static function statusSelect(): Select
    {
        return Select::make('status')
            ->label('الحالة')
            ->options(self::APPLICATION_STATUSES)
            ->default('pending')
            ->hidden(self::isCollegeSupervisor())
            ->dehydrated()
            ->required()
            ->live()
            ->afterStateUpdated(function ($state, $set) {
                // Set acceptance timestamp when status is changed to active
                if ($state === 'active') {
                    $set('accepted_at', now());
                }
            });
    }

    /**
     * Application letter file upload
     */
    private static function applicationLetterUpload(): FileUpload
    {
        return FileUpload::make('application_letter')
            ->label('صورة خطاب التدريب')
            ->image()
            ->disk(self::FILE_UPLOAD_CONFIG['disk'])
            ->directory(self::FILE_UPLOAD_CONFIG['directory'])
            ->visibility(self::FILE_UPLOAD_CONFIG['visibility'])
            ->disabled(static fn() => !self::isEditableByUser()())
            ->columnSpanFull();
    }

    /**
     * Tags input field
     */
    private static function tagsInput(): TextInput
    {
        return TextInput::make('tags')
            ->label('الوسوم')
            ->disabled(static fn() => !self::isEditableByUser()())
            ->columnSpanFull();
    }

    /**
     * Acceptance date display (read-only, shown only when active)
     */
    private static function acceptedAtPicker(): DatePicker
    {
        return DatePicker::make('accepted_at')
            ->label('تاريخ القبول')
            ->native(false)
            ->disabled()
            ->dehydrated()
            ->visible(fn($get) => $get('status') === 'active');
    }
}
