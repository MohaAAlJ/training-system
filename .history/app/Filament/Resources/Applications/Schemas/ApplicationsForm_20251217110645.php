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
                            ->disabled(fn() => ! Auth::user()->isAdmin() && ! Auth::user()->isCollegeSupervisor())
                            ->dehydrated(false),

                        TextInput::make('full_name')
                            ->label('الاسم الكامل')
                            ->formatStateUsing(fn($record) => $record?->trainee?->full_name)
                            ->disabled(fn() => ! Auth::user()->isAdmin() && ! Auth::user()->isCollegeSupervisor())
                            ->dehydrated(false),

                        TextInput::make('phone_number')
                            ->label('رقم الهاتف')
                            ->formatStateUsing(fn($record) => $record?->trainee?->phone_number)
                            ->disabled(fn() => ! Auth::user()->isAdmin() && ! Auth::user()->isCollegeSupervisor())
                            ->dehydrated(false),

                        TextInput::make('address')
                            ->label('العنوان')
                            ->formatStateUsing(fn($record) => $record?->trainee?->address)
                            ->disabled(fn() => ! Auth::user()->isAdmin() && ! Auth::user()->isCollegeSupervisor())
                            ->dehydrated(false),

                        Select::make('institution_id')
                            ->label('المؤسسة التعليمية')
                            ->options(fn() => Institution::all()->pluck('name', 'id'))
                            ->default(function () {
                                if (Auth::user()->isCollegeSupervisor()) {
                                    return auth()->user()->college?->institution_id;
                                }
                                return null;
                            }),

                        Select::make('college_id')
                            ->label('الكلية')
                            ->options(fn() => College::all()->pluck('name', 'id'))
                            ->formatStateUsing(fn($record) => $record?->trainee?->college_id)
                            ->disabled(fn() => ! Auth::user()->isAdmin() && ! Auth::user()->isCollegeSupervisor())
                            ->dehydrated(false),

                        Select::make('major_id')
                            ->label('التخصص')
                            ->options(fn() => Major::all()->pluck('name', 'id'))
                            ->formatStateUsing(fn($record) => $record?->trainee?->major_id)
                            ->disabled(fn() => ! Auth::user()->isAdmin() && ! Auth::user()->isCollegeSupervisor())
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
                            ->disabled(fn() => ! Auth::user()->isAdmin() && ! Auth::user()->isCollegeSupervisor())
                            ->required(),

                        DatePicker::make('end_date')
                            ->label('تاريخ الانتهاء')
                            ->native(false)
                            ->disabled(fn() => ! Auth::user()->isAdmin() && ! Auth::user()->isCollegeSupervisor())
                            ->required(),

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
