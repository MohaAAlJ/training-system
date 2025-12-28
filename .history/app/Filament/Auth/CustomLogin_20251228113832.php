<?php

namespace App\Filament\Auth;
use Filament\Auth\Pages\Login;
use Filament\Schemas\Schema;
use Filament\Forms\Components\TextInput;
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
                $this->getEmailFormComponent(),
                $this->getPasswordFormComponent(),
                $this->getRememberFormComponent(),
            ]);
    }

    protected function getLoginFormComponent(): Component
    {
        return TextInput::make('email')
            ->label(__('Username / Email'))
            ->email()
            ->required()
            ->autocomplete()
            ->autofocus()
            ->extraInputAttributes(['tabindex' => 1]);
    }
}
