<?php

namespace App\Filament\Resources\Departments\Schemas;

use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Schema;
use Filament\Schemas\Components\Section;
use App\Models\User;

class DepartmentForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('البيانات الأساسية')
                    ->columns(2)
                    ->schema([
                        TextInput::make('name')
                            ->label('اسم الدائرة')
                            ->placeholder('مثال: دائرة الصيدلة')
                            ->required()
                            ->maxLength(255)
                            ->columnSpanFull(),
                        Toggle::make('is_medical')
                            ->label('دائرة طبية')
                            ->helperText('حدد إذا كانت هذه دائرة طبية أم لا')
                            ->onIcon('heroicon-m-check-circle')
                            ->offIcon('heroicon-m-x-circle')
                            ->onColor('success')
                            ->offColor('danger')
                            ->default(false),
                        Toggle::make('active')
                            ->label('نشطة')
                            ->helperText('حدد إذا كانت الدائرة نشطة أم لا')
                            ->onIcon('heroicon-m-check-circle')
                            ->offIcon('heroicon-m-x-circle')
                            ->onColor('success')
                            ->offColor('danger')
                            ->default(true)
                            ->required(),
                        Toggle::make('visible')
                            ->label('مرئية')
                            ->helperText('حدد إذا كانت الدائرة تظهر للمتدربين والمشرفين. قم بإلغائها لإخفائها مع إبقائها نشطة.')
                            ->onIcon('heroicon-m-eye')
                            ->offIcon('heroicon-m-eye-slash')
                            ->onColor('success')
                            ->offColor('danger')
                            ->default(true)
                            ->required(),
                    ]),
                Section::make('المستخدمون المسؤولون')
                    ->columns(2)
                    ->schema([
                        Select::make('user_id')
                            ->label('رئيس الدائرة')
                            ->relationship('user', 'name', fn($query) => $query->where('role', User::ROLE_DEPARTMENT))
                            ->createOptionForm([
                                TextInput::make('name')
                                    ->label('الاسم')
                                    ->required(),
                                TextInput::make('user_name')
                                    ->label('اسم المستخدم')
                                    ->required()
                                    ->unique('users', 'user_name'),
                                TextInput::make('email')
                                    ->label('البريد الإلكتروني')
                                    ->required()
                                    ->email()
                                    ->unique('users', 'email'),
                                TextInput::make('password')
                                    ->label('كلمة المرور')
                                    ->password()
                                    ->revealable()
                                    ->required(),
                                \Filament\Forms\Components\Hidden::make('role')
                                    ->default(User::ROLE_DEPARTMENT),
                            ]),

                        Select::make('moh_dept_user_id')
                            ->label('مشرف دائرة - الصحة')
                            ->relationship('mohUser', 'name', fn($query) => $query->where('role', User::ROLE_MOH))
                            ->createOptionForm([
                                TextInput::make('name')
                                    ->label('الاسم')
                                    ->required(),
                                TextInput::make('user_name')
                                    ->label('اسم المستخدم')
                                    ->required()
                                    ->unique('users', 'user_name'),
                                TextInput::make('email')
                                    ->label('البريد الإلكتروني')
                                    ->required()
                                    ->email()
                                    ->unique('users', 'email'),
                                TextInput::make('password')
                                    ->label('كلمة المرور')
                                    ->password()
                                    ->revealable()
                                    ->required(),
                                \Filament\Forms\Components\Hidden::make('role')
                                    ->default(User::ROLE_MOH),
                            ])
                            ->searchable()
                            ->preload()
                            ->nullable(),
                    ]),
            ]);
    }
}
