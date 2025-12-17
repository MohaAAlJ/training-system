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
        return $schema

            ->components([
                Fieldset::make('البيانات الشخصية للمتدرب')
                    ->schema([
                        TextInput::make('national_id')
                            ->label('رقم الهوية')
                            ->formatStateUsing(fn($record) => $record?->trainee?->national_id)
                            ->disabled(fn() => ! Auth::user()->isAdmin() && ! Auth::user()->isCollegeSupervisor())
                            ->required()
                            ->dehydrated(),

                        TextInput::make('full_name')
                            ->label('الاسم الكامل')
                            ->formatStateUsing(fn($record) => $record?->trainee?->full_name)
                            ->disabled(fn() => ! Auth::user()->isAdmin() && ! Auth::user()->isCollegeSupervisor())
                            ->required()
                            ->dehydrated(),

                        TextInput::make('phone_number')
                            ->label('رقم الهاتف')
                            ->formatStateUsing(fn($record) => $record?->trainee?->phone_number)
                            ->disabled(fn() => ! Auth::user()->isAdmin() && ! Auth::user()->isCollegeSupervisor())
                            ->required()
                            ->dehydrated(),

                        TextInput::make('address')
                            ->label('العنوان')
                            ->formatStateUsing(fn($record) => $record?->trainee?->address)
                            ->disabled(fn() => ! Auth::user()->isAdmin() && ! Auth::user()->isCollegeSupervisor())
                            ->required()
                            ->dehydrated(),

                        Select::make('institution_id')
                            ->label('المؤسسة التعليمية')
                            ->options(fn() => Institution::all()->pluck('name', 'id'))
                            ->default(function () {
                                if (Auth::user()->isCollegeSupervisor()) {
                                    return Auth::user()->college?->institution_id;
                                }
                                return null;
                            })
                            ->disabled(fn() => Auth::user()->isCollegeSupervisor())
                            ->dehydrated()
                            ->required()
                            ->reactive(),

                        Select::make('college_id')
                            ->label('الكلية')
                            ->options(function (callable $get) {
                                $institutionId = $get('institution_id');
                                if ($institutionId) {
                                    return College::where('institution_id', $institutionId)->pluck('name', 'id');
                                }
                                return [];
                            })
                            ->default(function () {
                                if (Auth::user()->isCollegeSupervisor()) {
                                    return Auth::user()->college_id;
                                }
                                return null;
                            })
                            ->disabled(fn() => Auth::user()->isCollegeSupervisor())
                            ->dehydrated()
                            ->required()
                            ->reactive(),

                        Select::make('major_id')
                            ->label('التخصص')
                            ->options(function (callable $get) {
                                $collegeId = $get('college_id');

                                if ($collegeId) {
                                    return Major::whereHas('colleges', function ($q) use ($collegeId) {
                                        $q->where('colleges.id', $collegeId);
                                    })->pluck('name', 'id');
                                }
                                return [];
                            })
                            ->searchable()
                            ->required()
                            ->preload(),
                    ])->columns(2),

                Fieldset::make('تفاصيل الطلب')
                    ->schema([
                        Select::make('training_type')
                            ->label('نوع التدريب')
                            ->options([
                                'cooperative' => 'تدريب جامعي',
                                'professional' => 'مزاولة مهنة',
                            ])
                            ->default(function () {
                                $user = Auth::user();
                                if ($user->isCollegeSupervisor()) {
                                    return 'cooperative';
                                }
                                if ($user->isMinistry()) {
                                    return 'professional';
                                }
                                return null;
                            })
                            ->disabled(fn() => ! Auth::user()->isAdmin())
                            ->dehydrated()
                            ->required(),

                        TextInput::make('duration')
                            ->label('مدة التدريب (بالساعات)')
                            ->numeric()
                            ->minValue(20)
                            ->maxValue(1000)
                            ->default(100)
                            ->suffix('ساعة')
                            ->disabled(fn() => ! Auth::user()->isAdmin() && ! Auth::user()->isCollegeSupervisor())
                            ->required(),

                        Select::make('administrative_id')
                            ->label('الادارة')
                            ->relationship('administrative', 'title')
                            ->preload()
                            ->disabled(fn() => ! Auth::user()->isAdmin() && ! Auth::user()->isCollegeSupervisor())
                            ->required(),

                        Select::make('department_id')
                            ->label('القسم')
                            ->relationship('department', 'name_location')
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
                            ->options([
                                'pending' => 'طلب جديد',
                                'approved' => 'استيعاب',
                                'waiting' => 'لم يستلم عمل بعد',
                                'active' => 'بدء العمل',
                                'completed' => 'انتهى',
                                'rejected' => 'مرفوض',
                                'paused' => 'منقطع',
                            ])
                            ->default('pending')
                            ->hidden(fn() => Auth::user()->isCollegeSupervisor())
                            ->dehydrated()
                            ->required()
                            ->live()
                            ->afterStateUpdated(function ($state, $set) {
                                if ($state === 'active') {
                                    $set('accepted_at', now());
                                }
                            }),
                    ])->columns(2),

                Fieldset::make('المستندات والملاحظات')
                    ->schema([
                        FileUpload::make('application_letter')
                            ->label('صورة خطاب التدريب')
                            ->image()
                            ->disk('public')
                            ->directory('application-letters')
                            ->visibility('public')
                            ->disabled(fn() => ! Auth::user()->isAdmin() && ! Auth::user()->isCollegeSupervisor())
                            ->columnSpanFull(),

                        TextInput::make('tags')
                            ->label('الوسوم')
                            ->disabled(fn() => ! Auth::user()->isAdmin() && ! Auth::user()->isCollegeSupervisor())
                            ->columnSpanFull(),

                        DatePicker::make('accepted_at')
                            ->label('تاريخ القبول')
                            ->native(false)
                            ->disabled()
                            ->dehydrated()
                            ->visible(fn($get) => $get('status') === 'active'),
                    ])->columns(1),
            ]);
    }
}
