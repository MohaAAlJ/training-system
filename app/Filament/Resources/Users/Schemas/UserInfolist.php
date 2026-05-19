<?php

namespace App\Filament\Resources\Users\Schemas;

use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class UserInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->columns(1)
            ->components([
                Section::make('المعلومات الشخصية')
                    ->description('البيانات الأساسية للمستخدم')
                    ->icon('heroicon-o-user-circle')
                    ->schema([
                        TextEntry::make('name')
                            ->label('الاسم الكامل')
                            ->icon('heroicon-o-user')
                            ->copyable()
                            ->weight('bold')
                            ->size('lg'),

                        TextEntry::make('user_name')
                            ->label('اسم المستخدم')
                            ->icon('heroicon-o-at-symbol')
                            ->copyable()
                            ->badge()
                            ->color('gray'),

                        TextEntry::make('email')
                            ->label('البريد الإلكتروني')
                            ->icon('heroicon-o-envelope')
                            ->copyable()
                            ->url(fn($record) => "mailto:{$record->email}"),

                        TextEntry::make('phone_number')
                            ->label('رقم الهاتف')
                            ->icon('heroicon-o-phone')
                            ->placeholder('لم يتم الإضافة')
                            ->copyable()
                            ->url(fn($record) => $record->phone_number ? "tel:{$record->phone_number}" : null),
                    ])
                    ->columns(2),

                Section::make('معلومات الدور والصلاحيات')
                    ->description('دور المستخدم في النظام')
                    ->icon('heroicon-o-shield-check')
                    ->schema([
                        TextEntry::make('role')
                            ->label('الدور')
                            ->getStateUsing(
                                fn($record) => \App\Models\User::ROLE_LABELS[$record->role] ?? $record->role
                            )
                            ->badge()
                            ->icon('heroicon-o-identification')
                            ->size('lg')
                            ->color(fn($record) => match ($record->role) {
                                \App\Models\User::ROLE_ADMIN => 'danger',
                                \App\Models\User::ROLE_GTM => 'warning',
                                \App\Models\User::ROLE_ASSISTANT_TRAINING_MANAGER => 'warning',
                                \App\Models\User::ROLE_HOA => 'info',
                                \App\Models\User::ROLE_HOM => 'success',
                                \App\Models\User::ROLE_DEPARTMENT => 'purple',
                                \App\Models\User::ROLE_SECTION => 'indigo',
                                \App\Models\User::ROLE_COLLEGE => 'fuchsia',
                                default => 'gray',
                            }),

                        TextEntry::make('active')
                            ->label('حالة الحساب')
                            ->badge()
                            ->icon(fn(int $state) => $state ? 'heroicon-o-check-circle' : 'heroicon-o-x-circle')
                            ->size('lg')
                            ->color(fn(int $state): string => \App\Enums\GeneralConst::getStatusColor($state))
                            ->formatStateUsing(fn(int $state): string => \App\Enums\GeneralConst::getStatusLabel($state)),

                        TextEntry::make('college.name')
                            ->label('الكلية')
                            ->icon('heroicon-o-building-library')
                            ->badge()
                            ->color('fuchsia')
                            ->visible(fn($record) => $record->role === \App\Models\User::ROLE_COLLEGE && $record->college_id)
                            ->placeholder('غير محدد'),

                        TextEntry::make('managedDepartments.name')
                            ->label('الدوائر المُدارة')
                            ->icon('heroicon-o-building-office-2')
                            ->badge()
                            ->color('warning')
                            ->separator(',')
                            ->visible(fn($record) => $record->role === \App\Models\User::ROLE_ASSISTANT_TRAINING_MANAGER)
                            ->placeholder('غير محدد')
                            ->columnSpanFull(),
                    ])
                    ->columns(2),

                Section::make('معلومات النظام')
                    ->description('تواريخ الإنشاء والتحديث')
                    ->icon('heroicon-o-clock')
                    ->schema([
                        TextEntry::make('created_at')
                            ->label('تاريخ الإنشاء')
                            ->icon('heroicon-o-calendar')
                            ->dateTime('d/m/Y - h:i A')
                            ->since()
                            ->badge()
                            ->color('success'),

                        TextEntry::make('updated_at')
                            ->label('آخر تحديث')
                            ->icon('heroicon-o-arrow-path')
                            ->dateTime('d/m/Y - h:i A')
                            ->since()
                            ->badge()
                            ->color('warning'),
                    ])
                    ->columns(2)
                    ->collapsed(),
            ]);
    }
}
