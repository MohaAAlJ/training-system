<?php

namespace App\Filament\Resources\Users\Schemas;

use App\Helpers\Constans;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Schema;

class UsersForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('name')
                    ->label('الاسم')
                    ->required()
                    ->maxLength(255),

                TextInput::make('email')
                    ->label('البريد الإلكتروني')
                    ->email()
                    ->required()
                    ->maxLength(255),

                TextInput::make('password')
                    ->label('كلمة المرور')
                    ->password()
                    ->dehydrated(fn ($state) => filled($state))
                    ->maxLength(255),

                Select::make('role')
                    ->label('الدور')
                    ->options(Constans::ROLE_LABELS)
                    ->required(),

                Toggle::make('status')
                    ->label('نشط')
                    ->inline(false),
            ]);
    }
}
