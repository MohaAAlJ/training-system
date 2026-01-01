<?php

namespace App\Filament\Resources\Colleges\Schemas;

use Filament\Schemas\Schema;

class CollegesForm
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
                            ->relationship('user', 'name', function ($query, $get) {
                                // Filter users to only show College Supervisors (Role 5) and free users
                                return $query->where('role', \App\Helpers\Constans::ROLE_COLLEGE)
                                    ->free($get('user_id'));
                            })
                            ->searchable()
                            ->preload()
                            ->helperText('المستخدم الذي سيقوم بإدارة شؤون هذه الكلية في النظام'),
                    ]),
            ]);
    }
}
