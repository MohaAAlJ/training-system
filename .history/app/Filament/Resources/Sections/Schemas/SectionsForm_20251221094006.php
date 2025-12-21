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
                    ->label('اسم القسم')
                    ->required()
                    ->maxLength(255)
                    ->columnSpanFull(),
                Select::make('administrative_id')
                    ->label('الادارة التابع لها')
                    ->relationship('administrative', 'title')
                    ->searchable()
                    ->preload()
                    ->required(),
                Select::make('department_id')
                    ->label('الدائرة الفنية')
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
                    ->label('رئيس القسم')
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
