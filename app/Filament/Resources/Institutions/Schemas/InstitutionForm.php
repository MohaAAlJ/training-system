<?php

namespace App\Filament\Resources\Institutions\Schemas;

use Filament\Schemas\Schema;

class InstitutionForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                \Filament\Forms\Components\TextInput::make('name')
                    ->label('اسم المؤسسة')
                    ->required()
                    ->maxLength(255)
                    ->columnSpan('full'),
                \Filament\Forms\Components\Toggle::make('is_active')
                    ->label('نشط')
                    ->default(true),
                \Filament\Forms\Components\Toggle::make('Can_add_Application')
                    ->label('السماح بإضافة طلبات')
                    ->default(true),
            ]);
    }
}
