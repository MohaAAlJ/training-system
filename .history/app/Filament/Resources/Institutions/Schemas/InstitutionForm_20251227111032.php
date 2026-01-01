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
            ]);
    }
}
