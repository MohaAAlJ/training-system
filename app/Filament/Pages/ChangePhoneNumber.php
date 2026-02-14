<?php

namespace App\Filament\Pages;

use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Illuminate\Support\Facades\Auth;
use Filament\Actions\Action;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;

class ChangePhoneNumber extends Page implements HasForms
{
    use InteractsWithForms;

    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-phone';
    protected static ?string $navigationLabel = 'تغيير رقم الهاتف';
    protected static ?string $title = 'تغيير رقم الهاتف';
    protected static ?string $slug = 'change-phone-number';
    protected static ?int $navigationSort = 100;

    protected string $view = 'filament.pages.change-phone-number';

    public ?array $data = [];

    public function mount(): void
    {
        $this->form->fill([
            'phone_number' => Auth::user()->phone_number,
        ]);
    }

    public function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                \Filament\Schemas\Components\Section::make('معلومات الاتصال')
                    ->description('قم بتحديث رقم هاتفك المسجل في النظام')
                    ->icon('heroicon-o-phone')
                    ->schema([
                        TextInput::make('phone_number')
                            ->label('رقم الهاتف')
                            ->placeholder('970591234567')
                            ->helperText('يجب أن يبدأ الرقم بـ 9705 أو 9725 متبوعاً بـ 8 أرقام')
                            ->prefix('📱')
                            ->tel()
                            ->required()
                            ->regex('/^97(0|2)5\d{8}$/')
                            ->validationMessages([
                                'regex' => 'صيغة رقم الجوال غير صحيحة. استخدم 9705XXXXXXXX أو 9725XXXXXXXX',
                                'unique' => 'رقم الهاتف هذا مسجل مسبقاً',
                            ])
                            ->unique('users', 'phone_number', ignorable: Auth::user())
                            ->maxLength(20),
                    ]),
            ])
            ->statePath('data');
    }

    public function save(): void
    {
        $data = $this->form->getState();

        Auth::user()->update([
            'phone_number' => $data['phone_number'],
        ]);

        Notification::make()
            ->success()
            ->title('تم تحديث رقم الهاتف بنجاح')
            ->send();
    }
}
