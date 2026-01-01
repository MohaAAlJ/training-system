<?php

namespace App\Filament\Resources\Department\Schemas;

use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Schema;
use Filament\Schemas\Components\Section;

class DepartmentForm
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
                            ->placeholder('مثال: دائرة الصيدلة')
                            ->required()
                            ->maxLength(255)
                            ->columnSpanFull(),
                        Toggle::make('is_medical')
                            ->label('دائرة طبية')
                            ->helperText('حدد إذا كانت هذه دائرة طبية أم لا')
                            ->onColor('success')
                            ->offColor('danger')
                            ->default(false),
                        Toggle::make('status')
                            ->label('نشطة')
                            ->helperText('حدد إذا كانت الدائرة نشطة أم لا')
                            ->onColor('success')
                            ->offColor('danger')
                            ->default(true),
                    ]),
                Section::make('المستخدمون المسؤولون')
                    ->columns(2)
                    ->schema([
                        Select::make('user_id')
                            ->label('رئيس الدائرة')
                            ->helperText('اختر رئيس الدائرة (اختياري)')
                            ->options(fn($record) => \App\Models\User::getHeadOptions(\App\Models\User::ROLE_DEPARTMENT, $record?->user_id))
                            ->disableOptionWhen(fn($value, $record) => !\App\Models\User::where('id', $value)->free($record?->user_id)->exists())
                            ->searchable()
                            ->preload()
                            ->nullable(),
                    ]),
            ]);
    }
}
