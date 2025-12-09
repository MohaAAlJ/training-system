<?php

namespace App\Filament\Resources\Trainees\Schemas;

use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\DatePicker;
use Filament\Schemas\Schema;

class TraineesForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('national_id')
                    ->label('رقم الهوية')
                    ->required()
                    ->maxLength(255)
                    ->unique(ignoreRecord: true),
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
                TextInput::make('address')
                    ->label('العنوان')
                    ->maxLength(255),
                Select::make('institution_id')
                    ->label('المؤسسة التعليمية')
                    ->relationship('institution', 'name')
                    ->searchable()
                    ->preload()
                    ->required(),
                Select::make('major_id')
                    ->label('التخصص')
                    ->relationship('major', 'name')
                    ->searchable()
                    ->preload()
                    ->required(),
            ]);
    }
}
