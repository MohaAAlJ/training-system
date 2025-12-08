<?php

namespace App\Filament\Resources\Applications\Schemas;

use Filament\Infolist\Components\TextEntry;
use Filament\Infolist\Components\ImageEntry;
use Filament\Infolist\Components\Section;
use Filament\Infolist\Infolist;

class ApplicationsInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('معلومات المتدرب')
                    ->columns(2)
                    ->schema([
                        TextEntry::make('trainee.full_name')
                            ->label('الاسم الكامل'),
                        TextEntry::make('trainee.national_id')
                            ->label('رقم الهوية'),
                        TextEntry::make('trainee.phone_number')
                            ->label('رقم الهاتف'),
                        TextEntry::make('trainee.institution.name')
                            ->label('المؤسسة التعليمية'),
                    ]),
                Section::make('تفاصيل التدريب')
                    ->columns(2)
                    ->schema([
                        TextEntry::make('department.name_location')
                            ->label('القسم'),
                        TextEntry::make('status')
                            ->label('الحالة')
                            ->badge()
                            ->color(fn (string $state): string => match ($state) {
                                'pending' => 'warning',
                                'approved' => 'success',
                                'rejected' => 'danger',
                                'completed' => 'info',
                                default => 'gray',
                            })
                            ->formatStateUsing(fn (string $state): string => match ($state) {
                                'pending' => 'قيد الانتظار',
                                'approved' => 'مقبول',
                                'rejected' => 'مرفوض',
                                'completed' => 'مكتمل',
                                default => $state,
                            }),
                        TextEntry::make('start_date')
                            ->label('تاريخ البدء')
                            ->date('Y-m-d'),
                        TextEntry::make('end_date')
                            ->label('تاريخ الانتهاء')
                            ->date('Y-m-d'),
                        TextEntry::make('accepted_at')
                            ->label('تاريخ القبول')
                            ->dateTime('Y-m-d H:i'),
                        TextEntry::make('tags')
                            ->label('الوسوم'),
                    ]),
                Section::make('المستندات')
                    ->schema([
                        ImageEntry::make('letter_image_path')
                            ->label('صورة خطاب التدريب'),
                    ]),
            ]);
    }
}
