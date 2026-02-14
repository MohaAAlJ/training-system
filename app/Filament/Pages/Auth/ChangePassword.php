<?php

namespace App\Filament\Pages\Auth;

use Filament\Pages\Page;
use Filament\Forms\Contracts\HasForms;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Schemas\Schema;
use Filament\Forms\Components\TextInput;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Auth;
use Filament\Notifications\Notification;
use Illuminate\Validation\Rules\Password;

class ChangePassword extends Page implements HasForms
{
    use InteractsWithForms;

    protected static string | \BackedEnum | null $navigationIcon = 'heroicon-o-key';

    protected string $view = 'filament.pages.auth.change-password';

    protected static bool $shouldRegisterNavigation = false;

    public ?array $data = [];

    public bool $hasMinLength = false;
    public bool $hasLetters = false;
    public bool $hasNumbers = false;
    public bool $hasSymbols = false;
    public bool $hasMixedCase = false;

    public function mount(): void
    {
        $this->form->fill();
    }

    public static function getNavigationLabel(): string
    {
        return 'تغيير كلمة المرور';
    }

    public function getTitle(): string
    {
        return 'تغيير كلمة المرور';
    }

    public function updatedDataNewPassword($value): void
    {
        $this->validatePasswordRules($value);
    }

    protected function validatePasswordRules($password): void
    {
        $this->hasMinLength = strlen($password ?? '') >= 8;
        $this->hasLetters = (bool) preg_match('/[a-zA-Z]/', $password ?? '');
        $this->hasNumbers = (bool) preg_match('/[0-9]/', $password ?? '');
        $this->hasSymbols = (bool) preg_match('/[\W_]/', $password ?? '');
        $this->hasMixedCase = (bool) (preg_match('/[a-z]/', $password ?? '') && preg_match('/[A-Z]/', $password ?? ''));
    }

    public function form(Schema $form): Schema
    {
        return $form
            ->components([
                TextInput::make('current_password')
                    ->label('كلمة المرور الحالية')
                    ->password()
                    ->revealable()
                    ->required()
                    ->currentPassword(),

                TextInput::make('new_password')
                    ->label('كلمة المرور الجديدة')
                    ->password()
                    ->revealable()
                    ->live(debounce: 200)
                    ->required()
                    ->rule(Password::min(8)->letters()->mixedCase()->numbers()->symbols()),

                TextInput::make('new_password_confirmation')
                    ->label('تأكيد كلمة المرور الجديدة')
                    ->password()
                    ->revealable()
                    ->required()
                    ->same('new_password'),
            ])
            ->statePath('data');
    }

    public function allRulesPass(): bool
    {
        return $this->hasMinLength && $this->hasLetters && $this->hasNumbers && $this->hasSymbols && $this->hasMixedCase;
    }

    public function save()
    {
        if (!$this->allRulesPass()) {
            return;
        }

        $this->validate();

        auth()->user()->update([
            'password' => Hash::make($this->data['new_password']),
        ]);

        // Force logout and invalidate session
        Auth::logout();
        request()->session()->invalidate();
        request()->session()->regenerateToken();

        Notification::make()
            ->success()
            ->title('تم تغيير كلمة المرور بنجاح')
            ->send();

        return redirect('/login');
    }
}
