<?php

namespace App\Filament\Resources\Applications\Schemas;

use App\Models\Departments;
use App\Models\Trainees;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Fieldset;
use Filament\Schemas\Schema;
use Illuminate\Support\Facades\Auth;

class ApplicationsForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema

            ->components([
                //     Fieldset::make('معلومات المتدرب')
                //         ->schema([
                //                     Select::make('trainee_id')
                //                 ->label('المتدرب')
                //                 ->relationship('trainee', 'full_name')
                //                 ->searchable()
                //                 ->preload()
                //                 ->required()
                //             ]),
                Fieldset::make('البيانات الشخصية للمتدرب')
                    ->schema([
                        TextInput::make('national_id')
                            ->label('رقم الهوية')
                            ->required()
                            ->maxLength(9)
                            ->inputMode('numeric')
                            ->extraAttributes([
                                'oninput' => "this.value = this.value.replace(/[^0-9]/g,'').slice(0,9)",
                                'pattern' => '[0-9]*',
                            ])
                            ->rules(['digits:9']),
                        TextInput::make('full_name')
                            ->label('الاسم الكامل')
                            ->required()
                            ->maxLength(100)
                            ->extraAttributes([
                                'oninput' => "this.value = this.value.replace(/[^A-Za-z\\u0600-\\u06FF ]/g,'').slice(0,150)",
                            ])
                            ->rules(['string', 'max:150', 'regex:/^[A-Za-z\x{0600}-\x{06FF}\s]+$/u']),
                        TextInput::make('phone_number')
                            ->label('رقم الهاتف')
                            ->tel()
                            ->required()
                            ->rules(['regex:/^97[02]5[69]\d{7}$/']),
                        Fieldset::make('تاريخ الميلاد')
                            ->columns(3)
                            ->schema([
                                Select::make('dob_day')
                                    ->label('اليوم')
                                    ->options(fn() => array_combine(range(1, 31), range(1, 31)))
                                    ->required()
                                    ->rules(['integer', 'min:1', 'max:31']),
                                Select::make('dob_month')
                                    ->label('الشهر')
                                    ->options(fn() => [
                                        1 => 'يناير',
                                        2 => 'فبراير',
                                        3 => 'مارس',
                                        4 => 'أبريل',
                                        5 => 'مايو',
                                        6 => 'يونيو',
                                        7 => 'يوليو',
                                        8 => 'أغسطس',
                                        9 => 'سبتمبر',
                                        10 => 'أكتوبر',
                                        11 => 'نوفمبر',
                                        12 => 'ديسمبر',
                                    ])
                                    ->required()
                                    ->rules(['integer', 'min:1', 'max:12']),
                                Select::make('dob_year')
                                    ->label('السنة')
                                    ->options(function () {
                                        $max = now()->year - 20;
                                        $min = now()->year - 60;
                                        $years = [];
                                        for ($y = $max; $y >= $min; $y--) {
                                            $years[$y] = (string) $y;
                                        }
                                        return $years;
                                    })
                                    ->required()
                                    ->rules(['integer']),
                            ]),
                        TextInput::make('address')
                            ->label('العنوان'),
                        Select::make('institution_id')
                            ->label('المؤسسة التعليمية')
                            ->options(fn() => \App\Models\Institution::all()->mapWithKeys(fn($i) => [$i->id => $i->getTranslation('name', 'ar')])->toArray())
                            ->searchable()
                            ->reactive()
                            ->required()
                            ->visible(function () {
                                $user = Auth::user();
                                return ! ($user instanceof \App\Models\User && $user->isCollegeSupervisor());
                            })
                            ->rules(['exists:institutions,id']),
                        Select::make('college_id')
                            ->label('الكلية')
                            ->options(fn(callable $get) => $get('institution_id') ? \App\Models\College::where('institution_id', $get('institution_id'))->get()->mapWithKeys(fn($c) => [$c->id => $c->getTranslation('name', 'ar')])->toArray() : [])
                            ->searchable()
                            ->reactive()
                            ->required()
                            ->visible(function () {
                                $user = Auth::user();
                                return ! ($user instanceof \App\Models\User && $user->isCollege());
                            })
                            ->rules(['exists:colleges,id']),
                        Select::make('major_id')
                            ->label('التخصص')
                            ->options(function (callable $get) {
                                $user = Auth::user();
                                if ($user instanceof \App\Models\User && $user->isCollege()) {
                                    $collegeId = $user->college->id ?? 0;
                                    return \App\Models\Major::whereHas('colleges', function ($q) use ($collegeId) {
                                        $q->where('colleges.id', $collegeId);
                                    })->get()->mapWithKeys(fn($m) => [$m->id => $m->getTranslation('name', 'ar')])->toArray();
                                }

                                return $get('college_id') ? \App\Models\Major::whereHas('colleges', function ($q) use ($get) {
                                    $q->where('colleges.id', $get('college_id'));
                                })->get()->mapWithKeys(fn($m) => [$m->id => $m->getTranslation('name', 'ar')])->toArray() : [];
                            })
                            ->searchable()
                            ->required()
                            ->rules(['exists:majors,id']),


                    ])->columns(1),

                // ...existing code...

                Fieldset::make('تفاصيل الطلب')
                    ->schema([
                        Select::make('training_type')
                            ->label('نوع التدريب')
                            ->options([
                                'professional' => 'مزاولة مهنة',
                                'cooperative' => 'تدريب جامعي',
                            ]),

                        // enforce allowed values
                        TextInput::make('duration')
                            ->label('مدة التدريب (بالساعات)')
                            ->numeric()
                            ->minValue(20)
                            ->maxValue(1000)
                            ->required()
                            ->default(100)
                            ->suffix('ساعة')
                            ->rules(['integer', 'min:20', 'max:1000']),

                        Select::make('administrative_id')
                            ->label('الادارة')
                            ->relationship('administrative', 'title')
                            ->searchable()
                            ->preload()
                            ->required(),
                        Select::make('department_id')
                            ->label('القسم')
                            ->relationship('department', 'name_location')
                            ->searchable()
                            ->preload()
                            ->required()
                            ->rules(['exists:departments,id']),
                        // DatePicker::make('start_date')
                        //     ->label('تاريخ البدء')
                        //     ->required()
                        //     ->native(false),
                        // DatePicker::make('end_date')
                        //     ->label('تاريخ الانتهاء')
                        //     ->required()
                        //     ->native(false)
                        //     ->afterOrEqual('start_date'),

                        // Add validation rules for dates
                        DatePicker::make('start_date')
                            ->label('تاريخ البدء')
                            ->required()
                            ->native(false)
                            ->rules(['date']),
                        DatePicker::make('end_date')
                            ->label('تاريخ الانتهاء')
                            ->required()
                            ->native(false)
                            ->afterOrEqual('start_date')
                            ->rules(['date', 'after_or_equal:start_date']),
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
                            ->required()
                            ->live()
                            ->afterStateUpdated(function ($state, $set) {
                                // Auto-fill accepted_at when status changes to active
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
                            ->getUploadedFileNameForStorageUsing(function ($file, $get): string {
                                $extension = $file->getClientOriginalExtension();
                                $applicationId = $get('id') ?? 'new';
                                $traineeId = $get('trainee_id') ?? 'unknown';
                                return "{$applicationId}_{$traineeId}.{$extension}";
                            })
                            ->columnSpanFull(),
                        TextInput::make('tags')
                            ->label('الوسوم')
                            ->placeholder('أدخل الوسوم مفصولة بفواصل')
                            ->columnSpanFull(),
                        DatePicker::make('accepted_at')
                            ->label('تاريخ القبول')
                            ->native(false)
                            ->visible(fn($get) => $get('status') === 'active'),
                    ])->columns(1),
            ]);
    }
}
