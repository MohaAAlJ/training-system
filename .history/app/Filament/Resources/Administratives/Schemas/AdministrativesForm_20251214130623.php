<?php

namespace App\Filament\Resources\Administratives\Schemas;

use Filament\Forms\Components\Section;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;
use Filament\Forms\Components\Toggle;

class AdministrativesForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('البيانات الأساسية')
                    ->columns(2)
                    ->schema([
                        TextInput::make('title')
                            ->label('اسم الإدارة')
                            ->placeholder('مثال: الإدارة العامة للرعاية الأولية')
                            ->required()
                            ->maxLength(255)
                            ->columnSpanFull(),
                        TextInput::make('head_of_administrative')
                            ->label('رئيس الإدارة')
                            ->placeholder('أدخل اسم رئيس الإدارة')
                            ->required()
                            ->maxLength(255),
                        Toggle::make('is_medical')
                            ->label('إدارة طبية')
                            ->helperText('حدد إذا كانت هذه إدارة طبية أم لا')
                            ->onColor('success')
                            ->offColor('danger'),
                    ]),
                Section::make('المستخدم المسؤول')
                    ->schema([
                        Select::make('user_id')
                            ->label('المستخدم المسؤول')
                            ->helperText('اختر المستخدم الذي سيكون مسؤولاً عن هذه الإدارة')
                            ->relationship('user', 'name')
                            ->searchable()
                            ->preload()
                            ->required(),
                    ]),
            ]);
    }
}
