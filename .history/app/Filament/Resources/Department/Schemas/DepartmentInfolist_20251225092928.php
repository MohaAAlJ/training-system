<?php

namespace App\Filament\Resources\Department\Schemas;

use Filament\Schemas\Schema;
use Filament\Schemas\Components\Section;
use Filament\Infolists\Components\TextEntry;
use Filament\Infolists\Components\IconEntry;

class DepartmentsInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('معلومات الإدارة')
                    ->schema([
                        TextEntry::make('title')
                            ->label('اسم الإدارة'),
                        TextEntry::make('user.name')
                            ->label('رئيس الإدارة'),
                        IconEntry::make('is_medical')
                            ->label('إدارة طبية')
                            ->boolean(),
                        TextEntry::make('Section_count')
                            ->label('عدد الأقسام')
                            ->getStateUsing(fn ($record) => $record->Section()->count()),
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






