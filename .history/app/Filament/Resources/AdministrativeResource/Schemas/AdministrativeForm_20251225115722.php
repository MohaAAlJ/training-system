<?php

namespace App\Filament\Resources\Administrative\Schemas;

use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Section;

class AdministrativeForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('البيانات الأساسية')
                    ->columns(2)
                    ->schema([
                        TextInput::make('title')
                            ->label('اسم الدائرة')
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
                            ->options(fn($record) => \App\Models\User::getHeadOptions(\App\Models\User::ROLE_HOA, $record?->user_id))
                            ->disableOptionWhen(fn($value, $record) => !\App\Models\User::where('id', $value)->free($record?->user_id)->exists())
                            ->searchable()
                            ->preload()
                            ->required(),
                        Select::make('medical_head_user_id')
                            ->label('رئيس الإدارة الطبية')
                            ->helperText('اختر رئيس الإدارة الطبية')
                            ->options(fn($record) => \App\Models\User::getHeadOptions(\App\Models\User::ROLE_HOM, $record?->medical_head_user_id))
                            ->disableOptionWhen(fn($value, $record) => !\App\Models\User::where('id', $value)->free($record?->medical_head_user_id)->exists())
                            ->searchable()
                            ->preload()
                            ->visible(fn($get) => $get('is_medical'))
                            ->required(fn($get) => $get('is_medical')),
                    ]),
            ]);
    }
}
