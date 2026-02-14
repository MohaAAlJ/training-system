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
                \Filament\Forms\Components\Toggle::make('active')
                    ->label('نشط')
                    ->onIcon('heroicon-m-check-circle')
                    ->offIcon('heroicon-m-x-circle')
                    ->onColor('success')
                    ->offColor('danger')
                    ->default(true),
                \Filament\Forms\Components\Toggle::make('add_application')
                    ->label('السماح بإضافة طلبات')
                    ->onIcon('heroicon-m-check-circle')
                    ->offIcon('heroicon-m-x-circle')
                    ->onColor('success')
                    ->offColor('danger')
                    ->default(true),
            ]);
    }
}
