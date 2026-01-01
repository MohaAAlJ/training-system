<?php

namespace App\Filament\Resources\Users\Schemas;

use App\Models\User;

use App\Helpers\Constants;
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
                TextInput::make('name')
                    ->label('الاسم')
                    ->required()
                    ->maxLength(255),

                TextInput::make('email')
                    ->label('البريد الإلكتروني')
                    ->email()
                    ->required()
                    ->maxLength(255)
                    ->unique(ignoreRecord: true)
                    ->validationMessages([
                        'unique' => 'البريد الإلكتروني هذا مسجل مسبقاً في النظام.',
                    ]),



                TextInput::make('password')
                    ->label('كلمة المرور')
                    ->password()
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

                Toggle::make('status')
                    ->label('نشطة')
                    ->helperText('حدد إذا كانت الدائرة نشطة أم لا')
                    ->onColor('success')
                    ->offColor('danger')
                    ->default(true),

            ]);
    }
}
