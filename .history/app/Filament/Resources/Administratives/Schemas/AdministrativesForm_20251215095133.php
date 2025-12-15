<?php

namespace App\Filament\Resources\Administratives\Schemas;

use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Section;

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
                        Toggle::make('is_medical')
                            ->label('إدارة طبية')
                            ->helperText('حدد إذا كانت هذه إدارة طبية أم لا')
                            ->onColor('success')
                            ->offColor('danger')
                            ->live(),
                    ]),
                Section::make('المستخدمون المسؤولون')
                    ->columns(2)
                    ->schema([
                        Select::make('user_id')
                            ->label('رئيس الإدارة')
                            ->helperText('اختر رئيس الإدارة')
                            ->relationship('user', 'name')
                            ->searchable()
                            ->preload()
                            ->required(),
                        Select::make('medical_head_user_id')
                            ->label('رئيس الإدارة الطبية')
                            ->helperText('اختر رئيس الإدارة الطبية')
                            ->relationship('medicalHead', 'name')
                            ->searchable()
                            ->preload()
                            ->visible(fn($get) => $get('is_medical'))
                            ->required(fn($get) => $get('is_medical')),
                    ]),
            ]);
    }
}
