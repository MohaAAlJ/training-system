<?php

namespace App\Filament\Resources\Applications\Schemas;

use App\Models\Institution;
use App\Models\College;
use App\Models\Major;
use App\Models\Trainees;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Hidden;
use Filament\Schemas\Components\Fieldset;
use Filament\Schemas\Schema;
use Illuminate\Support\Facades\Auth;
use Filament\Actions\Action;

class ApplicationsForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema

            ->components([
                Fieldset::make('البيانات الشخصية للمتدرب')
                    ->schema([
                        // Instead of open inputs, we show the Trainee Name as a Read-Only field
                        // BUT we add an "Edit" button next to it.
                        TextInput::make('trainee_name_display')
                            ->label('اسم المتدرب')
                            // Load data directly from relationship
                            ->formatStateUsing(fn($record) => $record?->trainee?->full_name)
                            ->disabled()
                            ->dehydrated(false) // Don't save this field
                            ->columnSpan(2)
                            // THE MAGIC: Add an Action to edit the Trainee
                            ->suffixAction(
                                Action::make('edit_trainee')
                                    ->icon('heroicon-m-pencil-square')
                                    ->label('تعديل بيانات المتدرب')
                                    ->visible(fn() => Auth::user()->isAdmin() || Auth::user()->isCollegeSupervisor())
                                    ->mountUsing(fn($record, $form) => $form->fill([
                                        'full_name' => $record->trainee->full_name,
                                        'national_id' => $record->trainee->national_id,
                                        'phone_number' => $record->trainee->phone_number,
                                        'dob' => $record->trainee->dob,
                                        'address' => $record->trainee->address,
                                        'major_id' => $record->trainee->major_id,
                                    ]))
                                    ->form([
                                        TextInput::make('full_name')->label('الاسم')->required(),
                                        TextInput::make('national_id')->label('الهوية')->required(),
                                        TextInput::make('phone_number')->label('الجوال'),
                                        DatePicker::make('dob')->label('تاريخ الميلاد'),
                                        TextInput::make('address')->label('العنوان'),
                                        // Add Major Select here if needed
                                    ])
                                    ->action(function ($record, $data) {
                                        $record->trainee->update($data);
                                        // Send notification
                                        \Filament\Notifications\Notification::make()
                                            ->title('تم تحديث بيانات المتدرب')
                                            ->success()
                                            ->send();
                                    })
                            ),

                        // Display other details as Read-Only context
                        TextInput::make('trainee_national_id_display')
                            ->label('رقم الهوية')
                            ->formatStateUsing(fn($record) => $record?->trainee?->national_id)
                            ->disabled()
                            ->dehydrated(false),

                        TextInput::make('trainee_major_display')
                            ->label('التخصص')
                            ->formatStateUsing(fn($record) => $record?->trainee?->major?->name) // Assuming relationship
                            ->disabled()
                            ->dehydrated(false),
                    ])->columns(2),

                // 3. APPLICATION DETAILS (Standard Filament)
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
