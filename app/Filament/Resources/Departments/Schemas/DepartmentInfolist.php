<?php

namespace App\Filament\Resources\Departments\Schemas;

use Filament\Schemas\Schema;
use Filament\Schemas\Components\Section;
use Filament\Infolists\Components\TextEntry;
use Filament\Infolists\Components\IconEntry;

class DepartmentInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('معلومات الدائرة')
                    ->description('التفاصيل الأساسية للدائرة')
                    ->icon('heroicon-o-building-office-2')
                    ->schema([
                        TextEntry::make('name')
                            ->label('اسم الدائرة')
                            ->icon('heroicon-o-rectangle-group')
                            ->copyable()
                            ->weight('bold')
                            ->size('lg')
                            ->color('primary'),

                        TextEntry::make('user.name')
                            ->label('رئيس الدائرة')
                            ->icon('heroicon-o-user-circle')
                            ->copyable()
                            ->badge()
                            ->color('info')
                            ->placeholder('غير محدد'),

                        IconEntry::make('is_medical')
                            ->label('دائرة طبية')
                            ->boolean()
                            ->trueIcon('heroicon-o-shield-check')
                            ->falseIcon('heroicon-o-x-circle')
                            ->trueColor('success')
                            ->falseColor('gray'),

                        TextEntry::make('sections_count')
                            ->label('عدد الأقسام')
                            ->icon('heroicon-o-rectangle-stack')
                            ->getStateUsing(fn($record) => $record->sections()->count())
                            ->badge()
                            ->color('warning')
                            ->suffix(' قسم'),

                        TextEntry::make('active')
                            ->label('حالة الدائرة')
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
