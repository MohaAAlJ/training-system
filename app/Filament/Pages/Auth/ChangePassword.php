<?php

namespace App\Filament\Pages\Auth;

use Filament\Pages\Page;
use Filament\Forms\Contracts\HasForms;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Schemas\Schema;
use Filament\Forms\Components\TextInput;
use Illuminate\Support\Facades\Auth;
use Filament\Notifications\Notification;
use Illuminate\Validation\Rules\Password;
use Illuminate\Support\Str;
use App\Settings\TrainingSettings;

class ChangePassword extends Page implements HasForms
{
    use InteractsWithForms;

    protected static string | \BackedEnum | null $navigationIcon = 'heroicon-o-key';

    protected string $view = 'filament.pages.auth.change-password';

    protected static bool $shouldRegisterNavigation = true;
    protected static ?int $navigationSort = 2;
    protected static string|\UnitEnum|null $navigationGroup = 'الإعدادات';

    public ?array $data = [];

    public bool $hasMinLength = false;
    public bool $hasLetters = false;
    public bool $hasNumbers = false;
    public bool $hasSymbols = false;
    public bool $hasMixedCase = false;

    public static function shouldRegisterNavigation(): bool
    {
        return app(TrainingSettings::class)->enable_change_password ?? false;
    }
    public static function canAccess(): bool
    {
        return app(TrainingSettings::class)->enable_change_password ?? false;
    }

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
        return '';
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
                    ->validationMessages([
                        'current_password' => 'كلمة المرور الحالية غير صحيحة.',
                    ])
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

        $hashedPassword = $this->shouldHashPassword($this->data['new_password'])
            ? bcrypt($this->data['new_password'])
            : $this->data['new_password'];

        auth()->user()->update([
            'password' => $hashedPassword,
            'remember_token' => Str::random(60), // Invalidate all sessions
        ]);

        // Invalidate all other active sessions for this user
        Auth::logoutOtherDevices($this->data['new_password']);

        Notification::make()
            ->success()
            ->title('تم تغيير كلمة المرور بنجاح')
            ->send();

        return redirect('/');
    }

    private function shouldHashPassword(?string $password): bool
    {
        if (!filled($password)) {
            return false;
        }

        $algo = password_get_info($password)['algo'] ?? null;

        return empty($algo);
    }
}
