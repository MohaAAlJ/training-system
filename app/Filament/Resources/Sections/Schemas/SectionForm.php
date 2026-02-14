<?php

namespace App\Filament\Resources\Sections\Schemas;

use Filament\Forms\Components\Hidden;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Schema;
use Illuminate\Support\Facades\Auth;

class SectionForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('name')
                    ->label('اسم القسم')
                    ->required()
                    ->maxLength(255)
                    ->columnSpanFull(),
                Select::make('administrative_id')
                    ->label('الإدارة')
                    ->relationship('administrative', 'name', fn($query) => $query->active())
                    ->getOptionLabelUsing(fn($value) => \App\Models\Administrative::find($value)?->name)
                    ->searchable()
                    ->preload()
                    ->required(),
                Select::make('department_id')
                    ->label('الدائرة')
                    ->relationship('department', 'name', fn($query) => $query->active())
                    ->getOptionLabelUsing(fn($value) => \App\Models\Department::find($value)?->name)
                    ->searchable()
                    ->preload()
                    ->required(),
                Select::make('user_id')
                    ->label('المسؤول')
                    ->relationship('user', 'name', fn($query) => $query->where('role', \App\Models\User::ROLE_SECTION))
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
                        Hidden::make('role')
                            ->default(\App\Models\User::ROLE_SECTION),
                    ])
                    ->searchable()
                    ->preload()
                    ->nullable(),
                TextInput::make('capacity')
                    ->label('السعة الكلية')
                    ->numeric()
                    ->minValue(1)
                    ->default(10)
                    ->required(),
                Toggle::make('active')
                    ->label('الحالة')
                    ->onIcon('heroicon-m-check-circle')
                    ->offIcon('heroicon-m-x-circle')
                    ->onColor('success')
                    ->offColor('danger')
                    ->default(true)
                    ->disabled(fn($record) => !Auth::user()->can('toggleActive', $record ?? new \App\Models\Section()))
                    ->required(),
            ]);
    }
}
