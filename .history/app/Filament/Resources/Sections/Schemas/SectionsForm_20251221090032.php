<?php

namespace App\Filament\Resources\Sections\Schemas;

use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Schema;

class SectionsForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('name_location')
                    ->label('اسم الموقع/الفرع')
                    ->required()
                    ->maxLength(255)
                    ->columnSpanFull(),
                Select::make('administrative_id')
                    ->label('المديرية التابع لها')
                    ->relationship('administrative', 'title')
                    ->searchable()
                    ->preload()
                    ->required(),
                Select::make('department_id')
                    ->label('القسم الفني')
                    ->relationship('department', 'title')
                    ->searchable()
                    ->preload()
                    ->required(),
                Select::make('governorate_id')
                    ->label('المحافظة')
                    ->relationship('governorate', 'name')
                    ->getOptionLabelFromRecordUsing(fn($record) => $record->name)
                    ->searchable()
                    ->preload(),
                Select::make('hos')
                    ->label('مسؤول الموقع/الشعبة')
                    ->relationship('hosUser', 'name')
                    ->searchable()
                    ->preload(),
                TextInput::make('total_capacity')
                    ->label('السعة الاستيعابية')
                    ->numeric()
                    ->default(0)
                    ->required(),
                Toggle::make('status')
                    ->label('الحالة')
                    ->default(true),
            ]);
    }
}
