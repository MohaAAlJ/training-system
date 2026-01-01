<?php

namespace App\Filament\Resources\Departments\Schemas;

use Filament\Schemas\Schema;
use Filament\Schemas\Components\Section;
use Filament\Infolists\Components\TextEntry;

class DepartmentsInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('معلومات القسم')
                    ->schema([
                        TextEntry::make('name_location')
                            ->label('اسم القسم والموقع'),
                        TextEntry::make('administrative.title')
                            ->label('المديرية'),
                        TextEntry::make('user.name')
                            ->label('المسؤول'),
                        TextEntry::make('total_capacity')
                            ->label('السعة الكلية'),
                        TextEntry::make('status')
                            ->label('الحالة')
                            ->badge()
                            ->color(fn (mixed $state): string => match ($state) {
                                true => 'success',
                                false => 'danger',
                                default => 'gray',
                            })
                            ->formatStateUsing(fn (mixed $state): string => match ($state) {
                                true => 'نشط',
                                false => 'غير نشط',
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
