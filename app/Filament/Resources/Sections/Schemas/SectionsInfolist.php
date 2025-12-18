<?php

namespace App\Filament\Resources\Sections\Schemas;

use Filament\Schemas\Schema;
use Filament\Schemas\Components\Section;
use Filament\Infolists\Components\TextEntry;

class SectionsInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('معلومات القسم')
                    ->schema([
                        TextEntry::make('name_location')
                            ->label('اسم القسم والموقع'),
                        TextEntry::make('Administrative.name')
                            ->label('الدائرة'),
                        TextEntry::make('user.name')
                            ->label('المسؤول'),
                        TextEntry::make('total_capacity')
                            ->label('السعة الكلية'),
                        TextEntry::make('status')
                            ->label('الحالة')
                            ->badge()
                            ->color(fn (string $state): string => match ($state) {
                                'active' => 'success',
                                'inactive' => 'danger',
                                default => 'gray',
                            })
                            ->formatStateUsing(fn (string $state): string => match ($state) {
                                'active' => 'نشط',
                                'inactive' => 'غير نشط',
                                default => $state,
                            }),
                    ])->columns(2),

                Section::make('معلومات النظام')
                    ->schema([
                        TextEntry::make('created_at')
                            ->label('تاريخ الإنشاء')
                            ->dateTime('Y-m-d H:i'),
                        TextEntry::make('updated_at')
                            ->label('آخر تحديث')
                            ->dateTime('Y-m-d H:i'),
                    ])->columns(2)
                    ->collapsed(),
            ]);
    }
}
