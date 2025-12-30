<?php

namespace App\Filament\Resources\Sections\Schemas;

use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;

class SectionForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('name_location')
                    ->label('اسم القسم والموقع')
                    ->required()
                    ->maxLength(255)
                    ->columnSpanFull(),
                Select::make('administrative_id')
                    ->label('الإدارة')
                    ->relationship('administrative', 'title')
                    ->searchable()
                    ->preload()
                    ->required(),
                Select::make('department_id')
                    ->label('القسم')
                    ->relationship('department', 'title')
                    ->searchable()
                    ->preload()
                    ->required(),
                Select::make('user_id')
                    ->label('المسؤول')
                    ->options(fn($record) => \App\Models\User::getHeadOptions(\App\Models\User::ROLE_SECTION, $record?->user_id))
                    ->disableOptionWhen(fn($value, $record) => !\App\Models\User::where('id', $value)->free($record?->user_id)->exists())
                    ->searchable()
                    ->preload()
                    ->required(),
                TextInput::make('capacity')
                    ->label('السعة الكلية')
                    ->numeric()
                    ->minValue(1)
                    ->default(10)
                    ->required(),
                Select::make('status')
                    ->label('الحالة')
                    ->options([
                        'active' => 'نشط',
                        'inactive' => 'غير نشط',
                    ])
                    ->default('active')
                    ->required(),
            ]);
    }
}
