<?php

namespace App\Filament\Resources\Administratives\Schemas;

use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;

class AdministrativesForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('title')
                    ->label('اسم الإدارة')
                    ->required()
                    ->maxLength(255),
                TextInput::make('head_of_administrative')
                    ->label('رئيس الإدارة')
                    ->required()
                    ->maxLength(255),
                    Toggle::make('is_medical')
                ->label('هل هذه إدارة طبية؟')
                ->onColor('success')
                ->offColor('danger')
                ->live(),
                Select::make('user_id')
                    ->label('المستخدم المسؤول')
                    ->relationship('user', 'name')
                    ->searchable()
                    ->preload()
                    ->required(),
            ]);
    }
}
