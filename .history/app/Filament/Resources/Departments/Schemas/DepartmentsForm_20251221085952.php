<?php

namespace App\Filament\Resources\Departments\Schemas;

use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Schema;

class DepartmentsForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('title')
                    ->label('اسم القسم')
                    ->required()
                    ->maxLength(255)
                    ->columnSpanFull(),
                Toggle::make('is_medical')
                    ->label('قسم طبي')
                    ->onColor('success')
                    ->offColor('danger')
                    ->default(false),
                Select::make('hod')
                    ->label('رئيس القسم')
                    ->relationship('hodUser', 'name')
                    ->searchable()
                    ->preload()
                    ->required(),
            ]);
    }
}
