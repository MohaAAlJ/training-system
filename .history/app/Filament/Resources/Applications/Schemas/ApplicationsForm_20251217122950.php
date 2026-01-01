<?php

namespace App\Filament\Resources\Applications\Schemas;

use App\Models\Institution;
use App\Models\College;
use App\Models\Major;
use App\Models\Trainees; // Import Trainee model
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Hidden;
use Filament\Forms\Components\Actions\Action; // For the modal
use Filament\Schemas\Components\Fieldset;
use Filament\Schemas\Schema;
use Illuminate\Support\Facades\Auth;

class ApplicationsForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                // 1. HIDDEN ID (Vital for logic)
                Hidden::make('trainee_id'),

                // 2. TRAINEE SECTION (ReadOnly Display + Edit Button)
                Fieldset::make('البيانات الشخصية للمتدرب')
                    ->schema([
                        // Instead of open inputs, we show the Trainee Name as a Read-Only field
                        // BUT we add an "Edit" button next to it.
                        TextInput::make('trainee_name_display')
                            ->label('اسم المتدرب')
                            // Load data directly from relationship
                            ->formatStateUsing(fn ($record) => $record?->trainee?->full_name)
                            ->disabled()
                            ->dehydrated(false) // Don't save this field
                            ->columnSpan(2)
                            // THE MAGIC: Add an Action to edit the Trainee
                            ->suffixAction(
                                Action::make('edit_trainee')
                                    ->icon('heroicon-m-pencil-square')
                                    ->label('تعديل بيانات المتدرب')
                                    ->visible(fn () => Auth::user()->isAdmin() || Auth::user()->isCollegeSupervisor())
                                    ->mountUsing(fn ($record, $form) => $form->fill([
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
                            ->formatStateUsing(fn ($record) => $record?->trainee?->national_id)
                            ->disabled()
                            ->dehydrated(false),

                        TextInput::make('trainee_major_display')
                            ->label('التخصص')
                            ->formatStateUsing(fn ($record) => $record?->trainee?->major?->name) // Assuming relationship
                            ->disabled()
                            ->dehydrated(false),
                    ])->columns(2),

                // 3. APPLICATION DETAILS (Standard Filament)
                Fieldset::make('تفاصيل الطلب')
                    ->schema([
                         // ... All your standard Application fields (Duration, Admin, Dept, Dates)
                         // Keep them exactly as they are in your previous code
                         // ...
                         Select::make('training_type')
                            // ... your existing logic
                            ->required(),
                         // ...
                    ])->columns(2),
            ]);
    }
}