<?php

declare(strict_types=1);

namespace App\Filament\Pages;

use App\Models\User;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Schema;
use Filament\Schemas\Components\Section;
use Filament\Pages\Page;
use Illuminate\Support\Facades\Auth;
use Filament\Infolists\Concerns\InteractsWithInfolists;
use Filament\Infolists\Contracts\HasInfolists;

class ViewProfile extends Page implements HasInfolists
{
    use InteractsWithInfolists;

    protected static string | \BackedEnum | null $navigationIcon = 'heroicon-o-user-circle';
    protected static ?int $navigationSort = 1;
    protected static ?string $slug = 'profile';
    protected static ?string $title = '';
    protected string $view = 'filament.pages.view-profile';
    protected static string | \UnitEnum | null $navigationGroup = 'الإعدادات';

    public static function getNavigationLabel(): string
    {
        return 'بيانات الحساب';
    }

    public ?array $data = [];

    public function mount(): void
    {
        $user = Auth::user();
        $this->data = [
            'name'            => $user->name,
            'email'           => $user->email,
            'phone_number'    => $user->phone_number ?? 'غير محدد',
            'created_at'      => $user->created_at ? $user->created_at->format('Y/m/d') : 'غير محدد',
            'created_at_time' => $user->created_at ? $user->created_at->format('H:i') : '',
        ];
    }

    public function form(Schema $schema): Schema
    {
        return $schema
            ->record(Auth::user())
            ->schema([
                Section::make('بيانات الحساب')
                    ->description('التفاصيل الخاصة بحسابك في النظام')
                    ->icon('heroicon-o-identification')
                    ->schema([
                        TextEntry::make('name')
                            ->label('الاسم الكامل')
                            ->icon('heroicon-m-user')
                            ->iconColor('primary')
                            ->weight('bold'),

                        TextEntry::make('email')
                            ->label('البريد الإلكتروني')
                            ->icon('heroicon-m-envelope')
                            ->iconColor('primary')
                            ->weight('bold'),

                        TextEntry::make('phone_number')
                            ->label('رقم الهاتف')
                            ->icon('heroicon-m-phone')
                            ->iconColor('primary')
                            ->weight('bold'),

                        // TextEntry::make('role')
                        //     ->label('الدور (الصلاحية)')
                        //     ->icon('heroicon-m-shield-check')
                        //     ->iconColor('primary')
                        //     ->weight('bold'),

                        TextEntry::make('created_at')
                            ->label('تاريخ الانضمام')
                            ->icon('heroicon-m-calendar-days')
                            ->iconColor('primary')
                            ->weight('bold'),
                    ])
                    ->columns(2),
            ]);
    }
}
