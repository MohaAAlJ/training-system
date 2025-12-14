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

class ApplicationsForm
{
    public static function configure(Schema $schema): Schema
    {
                return $schema

            ->components([
                Fieldset::make('معلومات المتدرب')
                    ->schema([
                        Select::make('trainee_id')
                            ->label('المتدرب')
                            ->relationship('trainee', 'full_name')
                            ->searchable()
                            ->preload()
                            ->required()
                            ->createOptionForm([
                                TextInput::make('national_id')
                                    ->label('رقم الهوية')
                                    ->required(),
                                TextInput::make('full_name')
                                    ->label('الاسم الكامل')
                                    ->required(),
                                TextInput::make('phone_number')
                                    ->label('رقم الهاتف')
                                    ->tel()
                                    ->required(),
                                DatePicker::make('dob')
                                    ->label('تاريخ الميلاد')
                                    ->required()
                                    ->native(false),
                                TextInput::make('address')
                                    ->label('العنوان'),
                                Select::make('institution_id')
                                    ->label('المؤسسة التعليمية')
                                    ->relationship('institution', 'name')
                                    ->searchable()
                                    ->preload(),
                                Select::make('institution_major_id')
                                    ->label('التخصص')
                                    ->relationship('institutionMajor', 'name')
                                    ->searchable()
                                    ->preload(),
                            ]),
                    ])->columns(1),

                // ...existing code...

                Fieldset::make('تفاصيل الطلب')
                    ->schema([
                        Select::make('department_id')
                            ->label('القسم')
                            ->relationship('department', 'name_location')
                            ->searchable()
                            ->preload()
                            ->required(),
                        DatePicker::make('start_date')
                            ->label('تاريخ البدء')
                            ->required()
                            ->native(false),
                        DatePicker::make('end_date')
                            ->label('تاريخ الانتهاء')
                            ->required()
                            ->native(false)
                            ->afterOrEqual('start_date'),
                        Select::make('status')
                            ->label('الحالة')
                            ->options([
                                'pending' => 'طلب جديد',
                                'waiting' => 'استيعاب',
                                'approved' => 'تأكيد الطلب',
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
                            ->visible(fn ($get) => $get('status') === 'active'),
                    ])->columns(1),
            ]);
    }
}
