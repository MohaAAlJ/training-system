<?php

namespace App\Filament\Resources\Trainees\Schemas;

use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\DatePicker;
use Filament\Schemas\Schema;
use App\Rules\PalestinianId;
use App\Models\Governorate;
use Illuminate\Support\Facades\Auth;

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
                Select::make('gender')
                    ->label('الجنس')
                    ->options(\App\Enums\Gender::class)
                    ->required(),
                TextInput::make('street')
                    ->label('المنطقة')
                    ->maxLength(255),
                Select::make('governorate_id')
                    ->label('المحافظة')
                    ->options(Governorate::pluck('name', 'id'))
                    ->searchable()
                    ->preload()
                    ->required(),

            ]);
    }
}
