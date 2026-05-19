<?php

namespace App\Filament\Resources\Administratives\Schemas;

use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Section;

class AdministrativeForm
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
                            ->placeholder('مثال: الإدارة العامة للرعاية الأولية')
                            ->required()
                            ->maxLength(255)
                            ->columnSpan(1),
                        TextInput::make('address')
                            ->label('العنوان التفصيلي')
                            ->placeholder('مثال: شارع الوحدة، بجانب مسجد الإيمان')
                            ->maxLength(255)
                            ->columnSpan(1),
                        \Filament\Forms\Components\Placeholder::make('active')
                            ->label('الحالة')
                            ->content(fn($record) => new \Illuminate\Support\HtmlString(\Illuminate\Support\Facades\Blade::render(
                                '<x-filament::badge color="' . ($record && $record->active ? 'success' : 'danger') . '">' .
                                    ($record && $record->active ? 'نشط' : 'غير نشط') .
                                    '</x-filament::badge>'
                            )))
                            ->visible(fn($context) => $context === 'edit')
                            ->columnSpan(1),
                        Select::make('governorate_id')
                            ->label('المحافظة')
                            ->relationship('governorate', 'name')
                            ->searchable()
                            ->preload()
                            ->required()
                            ->columnSpan(1),
                        Toggle::make('is_medical')
                            ->label('إدارة طبية')
                            ->helperText('حدد إذا كانت هذه إدارة طبية أم لا')
                            ->onIcon('heroicon-m-check-circle')
                            ->offIcon('heroicon-m-x-circle')
                            ->onColor('success')
                            ->offColor('danger')
                            ->live(),
                    ]),
                Section::make('المستخدمون المسؤولون')
                    ->columns(2)
                    ->schema([
                        Select::make('user_id')
                            ->label('رئيس الإدارة')
                            ->relationship('user', 'name', fn($query) => $query->where('role', \App\Models\User::ROLE_HOA))
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
                                    ->default(\App\Models\User::ROLE_HOA),
                            ])
                            ->searchable()
                            ->preload()
                            ->nullable(),
                        Select::make('medical_head_user_id')
                            ->label('رئيس الإدارة الطبية')
                            ->relationship('medicalHead', 'name', fn($query) => $query->where('role', \App\Models\User::ROLE_HOM))
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
                                    ->default(\App\Models\User::ROLE_HOM),
                            ])
                            ->searchable()
                            ->preload()
                            ->visible(fn($get) => $get('is_medical'))
                            ->required(fn($get, string $operation): bool => $get('is_medical') && $operation !== 'edit'),
                    ]),
            ]);
    }
}
