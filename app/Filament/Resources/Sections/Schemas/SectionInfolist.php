<?php

namespace App\Filament\Resources\Sections\Schemas;

use Filament\Schemas\Schema;
use Filament\Schemas\Components\Section;
use Filament\Infolists\Components\TextEntry;

class SectionInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->columns(1)
            ->components([
                Section::make('معلومات القسم')
                    ->description('التفاصيل الأساسية للقسم')
                    ->icon('heroicon-o-rectangle-group')
                    ->schema([
                        TextEntry::make('name')
                            ->label('اسم القسم')
                            ->icon('heroicon-o-tag')
                            ->copyable()
                            ->weight('bold')
                            ->size('lg')
                            ->color('primary'),

                        TextEntry::make('administrative.name')
                            ->label('الإدارة')
                            ->icon('heroicon-o-building-office')
                            ->copyable()
                            ->badge()
                            ->color('info')
                            ->placeholder('غير محدد'),

                        TextEntry::make('departments.name')
                            ->label('الدائرة')
                            ->icon('heroicon-o-building-office-2')
                            ->copyable()
                            ->badge()
                            ->color('purple')
                            ->placeholder('غير محدد'),

                        TextEntry::make('user.name')
                            ->label('المسؤول عن القسم')
                            ->icon('heroicon-o-user-circle')
                            ->copyable()
                            ->badge()
                            ->color('fuchsia')
                            ->placeholder('غير محدد'),

                        TextEntry::make('capacity')
                            ->label('السعة الكلية')
                            ->icon('heroicon-o-users')
                            ->badge()
                            ->color('warning')
                            ->suffix(' متدرب'),

                        TextEntry::make('active')
                            ->label('حالة القسم')
                            ->badge()
                            ->icon(fn(int $state) => $state ? 'heroicon-o-check-circle' : 'heroicon-o-x-circle')
                            ->size('lg')
                            ->color(fn(int $state): string => \App\Enums\GeneralConst::getStatusColor($state))
                            ->formatStateUsing(fn(int $state): string => \App\Enums\GeneralConst::getStatusLabel($state)),
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
