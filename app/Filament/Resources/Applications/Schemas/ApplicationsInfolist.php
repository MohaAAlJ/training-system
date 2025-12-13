<?php

namespace App\Filament\Resources\Applications\Schemas;

use Filament\Infolists\Components\TextEntry;
use Filament\Infolists\Components\ImageEntry;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

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
                        TextEntry::make('trainee.dob')
                            ->label('تاريخ الميلاد')
                            ->date('Y-m-d'),
                        TextEntry::make('trainee.address')
                            ->label('العنوان'),
                        TextEntry::make('trainee.institution.name')
                            ->label('المؤسسة التعليمية')
                            ->formatStateUsing(fn ($state) => is_array($state) ? ($state['ar'] ?? $state['en'] ?? reset($state)) : $state),
                        TextEntry::make('trainee.major.name')
                            ->label('التخصص')
                            ->formatStateUsing(fn ($state) => is_array($state) ? ($state['ar'] ?? $state['en'] ?? reset($state)) : $state),
                    ]),
                Section::make('تفاصيل التدريب')
                    ->columns(2)
                    ->schema([
                        TextEntry::make('department.name_location')
                            ->label('القسم'),
                        TextEntry::make('administrative.title')
                            ->label('الإدارة'),
                        TextEntry::make('street')
                            ->label('الشارع'),
                        TextEntry::make('training_hours')
                            ->label('عدد ساعات التدريب'),
                        TextEntry::make('training_type')
                            ->label('نوع التدريب')
                            ->formatStateUsing(fn (?string $state): string => match ($state) {
                                'cooperative' => 'تدريب جامعي',
                                'professional' => 'مزاولة مهنة',
                                default => $state ?? '-',
                            }),
                        TextEntry::make('status')
                            ->label('الحالة')
                            ->badge()
                            ->color(fn (string $state): string => match ($state) {
                                'pending' => 'info',
                                'waiting' => 'warning',
                                'approved' => 'primary',
                                'active' => 'success',
                                'completed' => 'gray',
                                'rejected' => 'danger',
                                'paused' => 'warning',
                                default => 'gray',
                            })
                            ->formatStateUsing(fn (string $state): string => match ($state) {
                                'pending' => 'طلب جديد',
                                'waiting' => 'استيعاب',
                                'approved' => 'قبول جامعة',
                                'active' => 'بدء العمل',
                                'completed' => 'انتهى',
                                'rejected' => 'مرفوض',
                                'paused' => 'منقطع',
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
                        ImageEntry::make('application_letter')
                            ->label('صورة خطاب التدريب')
                            ->disk('public'),
                    ]),
            ]);
    }
}
