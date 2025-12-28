<?php

namespace App\Filament\Auth;
use Filament\Auth\Pages\Login;
use Filament\Schemas\Schema;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Component;
class CustomLogin extends Login
{
    /**
     * Create a new class instance.
     */
    public function __construct()
    {
        
    }

    public function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                $this->getLoginFormComponent(),
                $this->getPasswordFormComponent(),
                $this->getRememberFormComponent(),
            ]);
    }

    protected function getLoginFormComponent(): Component
    {
        return TextInput::make('login')
            ->label('اسم المستخدم / البريد الالكتروني')
            ->required()
            ->autocomplete()
            ->autofocus()
            ->extraInputAttributes(['tabindex' => 1]);
    }

    protected function getCredentialsFromFormData(array $data): array
    {

        $login_type = filter

        return [
            'email' => $data['login'],
            'password' => $data['password'],
        ];
    }
}
