<?php

namespace App\Filament\Resources\Administratives\Schemas;

use Filament\Schemas\Schema;
use Filament\Schemas\Components\Section;
use Filament\Infolists\Components\TextEntry;
use Filament\Infolists\Components\IconEntry;

class AdministrativeInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->columns(1)
            ->components([
                Section::make('معلومات الإدارة')
                    ->description('التفاصيل الأساسية للإدارة')
                    ->icon('heroicon-o-building-office')
                    ->schema([
                        TextEntry::make('name')
                            ->label('اسم الإدارة')
                            ->icon('heroicon-o-building-office-2')
                            ->copyable()
                            ->weight('bold')
                            ->size('lg')
                            ->color('primary'),

                        TextEntry::make('address')
                            ->label('العنوان التفصيلي')
                            ->icon('heroicon-o-map-pin')
                            ->copyable()
                            ->placeholder('غير محدد'),

                        TextEntry::make('user.name')
                            ->label('رئيس الإدارة')
                            ->icon('heroicon-o-user-circle')
                            ->copyable()
                            ->badge()
                            ->color('info')
                            ->placeholder('غير محدد'),

                        IconEntry::make('is_medical')
                            ->label('إدارة طبية')
                            ->boolean()
                            ->trueIcon('heroicon-o-shield-check')
                            ->falseIcon('heroicon-o-x-circle')
                            ->trueColor('success')
                            ->falseColor('gray'),

                        TextEntry::make('medicalHead.name')
                            ->label('رئيس الإدارة الطبية')
                            ->icon('heroicon-o-shield-check')
                            ->badge()
                            ->color('success')
                            ->placeholder('لا يوجد')
                            ->visible(fn($record) => $record->is_medical),

                        TextEntry::make('sections_count')
                            ->label('عدد الأقسام')
                            ->icon('heroicon-o-rectangle-stack')
                            ->getStateUsing(fn($record) => $record->sections()->count())
                            ->badge()
                            ->color('warning')
                            ->suffix(' قسم'),

                        TextEntry::make('active')
                            ->label('حالة الإدارة')
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
