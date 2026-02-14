<?php

namespace App\Filament\Resources\Users\Schemas;

use App\Models\User;

use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Schema;
use App\Models\College;
use Illuminate\Support\Facades\Auth;

class UserForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('user_name')
                    ->label('اسم المستخدم')
                    ->required()
                    ->unique('users', 'user_name', ignorable: fn($record) => $record instanceof User ? $record : null)
                    ->maxLength(255),

                TextInput::make('name')
                    ->label('الاسم')
                    ->required()
                    ->maxLength(255),

                TextInput::make('email')
                    ->label('البريد الإلكتروني')
                    ->email()
                    ->required()
                    ->maxLength(255)
                    ->unique('users', 'email', ignorable: fn($record) => $record instanceof User ? $record : null)
                    ->validationMessages([
                        'unique' => 'البريد الإلكتروني هذا مسجل مسبقاً في النظام.',
                    ]),

                TextInput::make('phone_number')
                    ->label('رقم الهاتف')
                    ->regex('/^97(0|2)5\d{8}$/')
                    ->validationMessages([
                        'regex' => 'صيغة رقم الجوال غير صحيحة. استخدم 9705XXXXXXXX أو 9725XXXXXXXX',
                    ])
                    ->nullable()
                    ->maxLength(255),

                TextInput::make('password')
                    ->label('كلمة المرور')
                    ->password()
                    ->dehydrateStateUsing(fn ($state) => \Illuminate\Support\Facades\Hash::make($state))
                    ->dehydrated(fn($state) => filled($state))
                    ->required(fn(string $context): bool => $context === 'create')
                    ->maxLength(255),

                Select::make('role')
                    ->label('الدور')
                    ->options(User::ROLE_LABELS)
                    ->required()
                    ->reactive()
                    ->afterStateUpdated(
                        fn($state, $set) =>
                        $state != User::ROLE_COLLEGE ? ($set('institution_id', null) || $set('college_id', null)) : null
                    ),

                Select::make('institution_id')
                    ->label('المؤسسة')
                    ->options(fn() => \App\Models\Institution::all()->pluck('name', 'id')->toArray())
                    ->placeholder('اختر المؤسسة')
                    ->visible(fn(callable $get) => $get('role') == User::ROLE_COLLEGE)
                    ->reactive()
                    ->required(fn(callable $get) => $get('role') == User::ROLE_COLLEGE)
                    ->default(fn() => Auth::user()->isCollegeSupervisor() ? Auth::user()->college?->institution_id : null)
                    ->disabled(fn() => Auth::user()->isCollegeSupervisor() && !Auth::user()->isAdmin())
                    ->dehydrated(false)
                    ->searchable()
                    ->columnSpanFull(),

                Select::make('college_id')
                    ->label('الكلية')
                    ->options(fn(callable $get) => $get('institution_id') ? College::where('institution_id', $get('institution_id'))->pluck('name', 'id')->toArray() : [])
                    ->placeholder('اختر الكلية')
                    ->visible(fn(callable $get) => $get('role') == User::ROLE_COLLEGE)
                    ->required(fn(callable $get) => $get('role') == User::ROLE_COLLEGE)
                    ->default(fn() => Auth::user()->isCollegeSupervisor() ? Auth::user()->college?->id : null)
                    ->disabled(fn() => Auth::user()->isCollegeSupervisor() && !Auth::user()->isAdmin())
                    ->dehydrated(false)
                    ->reactive()
                    ->afterStateUpdated(fn($state, $set) => $state ? $set('institution_id', College::find($state)?->institution_id) : null)
                    ->searchable()
                    ->columnSpanFull(),

                Toggle::make('active')
                     ->onColor('success')
                     ->offColor('danger')
                     ->onIcon('heroicon-m-check-circle')
                     ->offIcon('heroicon-m-x-circle')
                     ->helperText('حدد إذا كان المستخدم نشطاً أم لا')
                    ->label('نشط')
                    ->default(true),

            ]);
    }
}
