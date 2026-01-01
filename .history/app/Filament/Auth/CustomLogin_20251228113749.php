<?php

namespace App\Filament\Auth;
use Filament\Auth\Pages\Login;
use Filament\Schemas\Schema;
use Filame  nt\Forms\Components\TextInput;
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

    protected function getEmailFormComponent(): Component
    {
        return TextInput::make('email')
            ->label(__('filament-panels::auth/pages/login.form.email.label'))
            ->email()
            ->required()
            ->autocomplete()
            ->autofocus()
            ->extraInputAttributes(['tabindex' => 1]);
    }
}
