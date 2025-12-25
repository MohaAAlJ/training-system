<?php

namespace App\Filament\Resources\Colleges\Schemas;

use Filament\Schemas\Schema;

class CollegesInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                \Filament\Schemas\Components\Section::make('معلومات الكلية')
                    ->schema([
                        \Filament\Infolists\Components\TextEntry::make('name')
                            ->label('اسم الكلية'),
                        \Filament\Infolists\Components\TextEntry::make('institution.name')
                            ->label('الجامعة التابعة لها'),
                        \Filament\Infolists\Components\TextEntry::make('user.name')
                            ->label('مشرف الكلية')
                            ->placeholder('لم يتم الحعيين'),
                    ])->columns(3),

                \Filament\Schemas\Components\Section::make('الإحصائيات')
                    ->schema([
                        \Filament\Infolists\Components\TextEntry::make('majors_count')
                            ->label('عدد التخصصات')
                            ->getStateUsing(fn ($record) => $record->majors()->count()),
                        \Filament\Infolists\Components\TextEntry::make('Trainee_count')
                            ->label('عدد المتدربين المسجلين')
                            ->getStateUsing(fn ($record) => $record->Trainee()->count()),
                    ])->columns(2),

                \Filament\Schemas\Components\Section::make('سجل النظام')
                    ->schema([
                        \Filament\Infolists\Components\TextEntry::make('created_at')
                            ->label('تاريخ الإضافة')
                            ->dateTime('d/m/Y h:i A'),
                        \Filament\Infolists\Components\TextEntry::make('updated_at')
                            ->label('آخر تحديث')
                            ->dateTime('d/m/Y h:i A'),
                    ])->columns(2)
                    ->collapsed(),
            ]);
    }
}






