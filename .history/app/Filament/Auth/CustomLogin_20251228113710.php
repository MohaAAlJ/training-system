<?php

namespace App\Filament\Auth;
use Filament\Auth\Pages\Login;
use Filament\Auth\Pages\PasswordReset;
use Filament\Schemas\Schema;
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
                $this->getUserNameFormComponent(),
                $this->getPasswordFormComponent(),
                $this->getRememberFormComponent(),
            ]);
    }
}
