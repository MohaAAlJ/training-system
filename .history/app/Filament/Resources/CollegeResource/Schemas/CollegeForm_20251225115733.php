<?php

namespace App\Filament\Resources\College\Schemas;

use Filament\Schemas\Schema;

class CollegeForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                \Filament\Schemas\Components\Section::make('معلومات الكلية')
                    ->columns(2)
                    ->schema([
                        \Filament\Forms\Components\TextInput::make('name')
                            ->label('اسم الكلية')
                            ->required()
                            ->maxLength(255)
                            ->columnSpanFull(),
                        \Filament\Forms\Components\Select::make('institution_id')
                            ->label('الجامعة / المؤسسة')
                            ->relationship('institution', 'name')
                            ->searchable()
                            ->preload()
                            ->required(),
                        \Filament\Forms\Components\Select::make('user_id')
                            ->label('مشرف الكلية')
                            ->options(fn($record) => \App\Models\User::getHeadOptions(\App\Models\User::ROLE_COLLEGE, $record?->user_id))
                            ->disableOptionWhen(fn($value, $record) => !\App\Models\User::where('id', $value)->free($record?->user_id)->exists())
                            ->searchable()
                            ->preload()
                            ->helperText('المستخدم الذي سيقوم بإدارة شؤون هذه الكلية في النظام'),
                    ]),
            ]);
    }
}
