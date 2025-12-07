<?php

namespace App\Filament\Resources\Trainees\Schemas;

use Filament\Schemas\Components\Section;
use Filament\Infolists\Components\TextEntry;
use Filament\Infolists\Components\RepeatableEntry;
use Filament\Infolists\Components\ImageEntry;
use Filament\Schemas\Schema;

class TraineesInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('معلومات المتدرب')
                    ->schema([
                        TextEntry::make('national_id')
                            ->label('رقم الهوية'),
                        TextEntry::make('full_name')
                            ->label('الاسم الكامل'),
                        TextEntry::make('phone_number')
                            ->label('رقم الهاتف'),
                        TextEntry::make('dob')
                            ->label('تاريخ الميلاد')
                            ->date('Y-m-d'),
                        TextEntry::make('location')
                            ->label('الموقع'),
                        TextEntry::make('institution.name')
                            ->label('المؤسسة التعليمية'),
                        TextEntry::make('institutionMajor.name')
                            ->label('التخصص'),
                    ])->columns(2),

                Section::make('الطلبات')
                    ->schema([
                        RepeatableEntry::make('applications')
                            ->label('')
                            ->schema([
                                TextEntry::make('department.name_location')
                                    ->label('القسم'),
                                TextEntry::make('start_date')
                                    ->label('تاريخ البدء')
                                    ->date('Y-m-d'),
                                TextEntry::make('end_date')
                                    ->label('تاريخ الانتهاء')
                                    ->date('Y-m-d'),
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
                                TextEntry::make('accepted_at')
                                    ->label('تاريخ القبول')
                                    ->date('Y-m-d')
                                    ->placeholder('—'),
                                TextEntry::make('tags')
                                    ->label('الوسوم')
                                    ->placeholder('—'),
                                ImageEntry::make('letter_image_path')
                                    ->label('صورة خطاب التدريب')
                                    ->columnSpanFull(),
                            ])->columns(3),
                    ]),

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
