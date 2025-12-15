<?php

namespace App\Filament\Resources\Users\Schemas;

use Filament\Schemas\Schema;
use Filament\Schemas\Components\Section;
use Filament\Infolists\Components\TextEntry;

class UsersInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('معلومات الحساب')
                    ->schema([
                        TextEntry::make('name')
                            ->label('الاسم'),

                        TextEntry::make('email')
                            ->label('البريد الإلكتروني'),

                        TextEntry::make('role')
                            ->label('الدور')
                            ->getStateUsing(
                                fn ($record) => \App\Helpers\Constans::ROLE_LABELS[$record->role] ?? $record->role
                            )
                            ->badge()
                            ->color('info'),

                        TextEntry::make('status')
                            ->label('الحالة')
                            ->badge()
                            ->color(fn (string $state) => match ($state) {
                                'active' => 'success',
                                'pending' => 'warning',
                                'blocked' => 'danger',
                                default => 'gray',
                            }),
                    ])
                    ->columns(2),

                Section::make('معلومات النظام')
                    ->schema([
                        TextEntry::make('created_at')
                            ->label('تاريخ الإنشاء')
                            ->dateTime('Y-m-d H:i'),

                        TextEntry::make('updated_at')
                            ->label('آخر تحديث')
                            ->dateTime('Y-m-d H:i'),
                    ])
                    ->columns(2)
                    ->collapsed(),
            ]);
    }
}
