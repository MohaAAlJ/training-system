<?php

namespace App\Filament\Resources\Users\Schemas;

use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Schema;
use App\Helpers\Constans;

class UsersInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('name')
                    ->label('الاسم')
                    ->disabled(),

                TextInput::make('email')
                    ->label('البريد الإلكتروني')
                    ->disabled(),

                Select::make('role')
                    ->label('الدور')
                    ->options(Constans::ROLE_LABELS)
                    ->disabled(),

                Toggle::make('status')
                    ->label('نشط')
                    ->disabled(),
            ]);
    }
}
