<?php

namespace App\Filament\Resources\Departments\Schemas;

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
                        TextInput::make('name')
                            ->label('اسم الدائرة')
                            ->placeholder('مثال: دائرة الصيدلة')
                            ->required()
                            ->maxLength(255)
                            ->columnSpanFull(),
                        Toggle::make('is_medical')
                            ->label('دائرة طبية')
                            ->helperText('حدد إذا كانت هذه دائرة طبية أم لا')
                            ->onIcon('heroicon-m-check-circle')
                            ->offIcon('heroicon-m-x-circle')
                            ->onColor('success')
                            ->offColor('danger')
                            ->default(false),
                        Toggle::make('active')
                            ->label('نشطة')
                            ->helperText('حدد إذا كانت الدائرة نشطة أم لا')
                            ->onIcon('heroicon-m-check-circle')
                            ->offIcon('heroicon-m-x-circle')
                            ->onColor('success')
                            ->offColor('danger')
                            ->default(true),
                    ]),
                Section::make('المستخدمون المسؤولون')
                    ->columns(2)
                    ->schema([
                        Select::make('user_id')
                            ->label('رئيس الدائرة')
                            ->relationship('user', 'name', fn($query) => $query->where('role', \App\Models\User::ROLE_DEPARTMENT))
                            ->createOptionForm([
                                TextInput::make('name')
                                    ->label('الاسم')
                                    ->required(),
                                TextInput::make('user_name')
                                    ->label('اسم المستخدم')
                                    ->required()
                                    ->unique('users', 'user_name'),
                                TextInput::make('email')
                                    ->label('البريد الإلكتروني')
                                    ->required()
                                    ->email()
                                    ->unique('users', 'email'),
                                TextInput::make('password')
                                    ->label('كلمة المرور')
                                    ->password()
                                    ->required(),
                                \Filament\Forms\Components\Hidden::make('role')
                                    ->default(\App\Models\User::ROLE_DEPARTMENT),
                            ])
                            ->searchable()
                            ->preload()
                            ->nullable(),
                    ]),
            ]);
    }
}
