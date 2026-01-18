<?php

namespace App\Filament\Resources\Trainees\Schemas;

use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\DatePicker;
use Filament\Schemas\Schema;
use App\Rules\PalestinianId;

class TraineeForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('national_id')
                    ->label('رقم الهوية')
                    ->required()
                    ->maxLength(9)
                    ->minLength(9)
                    ->regex('/^\d+$/')
                    ->unique(ignoreRecord: true)
                    ->validationMessages([
                        'unique' => 'رقم الهوية هذا مسجل مسبقاً في النظام.',
                        'regex' => 'يجب أن يتكون رقم الهوية من 9 أرقام فقط.',
                        'minLength' => 'يجب أن يتكون رقم الهوية من 9 أرقام.',
                        'maxLength' => 'يجب أن يتكون رقم الهوية من 9 أرقام.',
                    ])
                    ->rules([
                        new PalestinianId(),
                    ]),
                TextInput::make('full_name')
                    ->label('الاسم الكامل')
                    ->required()
                    ->maxLength(255),
                TextInput::make('phone_number')
                    ->label('رقم الهاتف')
                    ->required()
                    ->maxLength(255),
                DatePicker::make('dob')
                    ->label('تاريخ الميلاد')
                    ->required(),
                TextInput::make('street')
                    ->label('المنطقة / الشارع')
                    ->maxLength(255),
                Select::make('institution_id')
                    ->label('المؤسسة التعليمية')
                    ->relationship('institution', 'name')
                    ->getOptionLabelFromRecordUsing(fn($record) => is_array($record->name) ? ($record->name['ar'] ?? $record->name['en'] ?? reset($record->name)) : $record->name)
                    ->searchable()
                    ->preload()
                    ->required(),
                Select::make('major_id')
                    ->label('التخصص')
                    ->relationship('major', 'name')
                    ->getOptionLabelFromRecordUsing(fn($record) => is_array($record->name) ? ($record->name['ar'] ?? $record->name['en'] ?? reset($record->name)) : $record->name)
                    ->searchable()
                    ->preload()
                    ->required(),
            ]);
    }
}
