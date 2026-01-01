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
                Fieldset::make('البيانات الشخصية للمتدرب')
                    ->schema([
                        TextInput::make('national_id')
                            ->label('رقم الهوية')
                            ->formatStateUsing(fn($record) => $record?->trainee?->national_id)
                            ->disabled()
                            ->dehydrated(false),

                        TextInput::make('full_name')
                            ->label('الاسم الكامل')
                            ->formatStateUsing(fn($record) => $record?->trainee?->full_name)
                            ->disabled()
                            ->dehydrated(false),

                        TextInput::make('phone_number')
                            ->label('رقم الهاتف')
                            ->formatStateUsing(fn($record) => $record?->trainee?->phone_number)
                            ->disabled()
                            ->dehydrated(false),

                        TextInput::make('address')
                            ->label('العنوان')
                            ->formatStateUsing(fn($record) => $record?->trainee?->address)
                            ->disabled()
                            ->dehydrated(false),

                        Select::make('institution_id')
                            ->label('المؤسسة التعليمية')
                            ->options(fn() => \App\Models\Institution::all()->pluck('name', 'id'))
                            ->formatStateUsing(fn($record) => $record?->trainee?->institution_id)
                            ->disabled()
                            ->dehydrated(false),

                        Select::make('college_id')
                            ->label('الكلية')
                            ->options(fn() => \App\Models\College::all()->pluck('name', 'id'))
                            ->formatStateUsing(fn($record) => $record?->trainee?->college_id)
                            ->disabled()
                            ->dehydrated(false),

                        Select::make('major_id')
                            ->label('التخصص')
                            ->options(fn() => \App\Models\Major::all()->pluck('name', 'id'))
                            ->formatStateUsing(fn($record) => $record?->trainee?->major_id)
                            ->disabled()
                            ->dehydrated(false),
                    ])->columns(2),

                Fieldset::make('تفاصيل الطلب')
                    ->schema([
                        Select::make('training_type')
                            ->label('نوع التدريب')
                            ->options([
                                'professional' => 'مزاولة مهنة',
                                'cooperative' => 'تدريب جامعي',
                            ])
                            ->disabled(fn() => ! Auth::user()->isAdmin() && ! Auth::user()->isCollegeSupervisor())
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
                            // تعطيل الحقل إذا لم يكن أدمن أو مشرف كلية
                            ->disabled(fn() => ! Auth::user()->isAdmin() && ! Auth::user()->isCollegeSupervisor())
                            ->required(),

                        DatePicker::make('end_date')
                            ->label('تاريخ الانتهاء')
                            ->native(false)
                            // تعطيل الحقل إذا لم يكن أدمن أو مشرف كلية
                            ->disabled(fn() => ! Auth::user()->isAdmin() && ! Auth::user()->isCollegeSupervisor())
                            ->required(),

                        // --- حقل الحالة (Status) ---
                        // هذا الحقل الوحيد الذي يبقى مفعلاً للجميع (لتغيير الحالة)
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
