<?php

namespace App\Filament\Resources\Applications\Schemas;

use Filament\Infolists\Components\TextEntry;
use Filament\Infolists\Components\ImageEntry;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Illuminate\Support\Facades\Auth;

class ApplicationInfolist
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
                        TextEntry::make('trainee.governorate.name')
                            ->label('المحافظة'),
                        TextEntry::make('trainee.street')
                            ->label('الشارع'),
                        TextEntry::make('trainee.institution.name')
                            ->label('المؤسسة التعليمية')
                            ->visible(fn($record) => $record->training_type !== \App\Models\Application::TRAINING_TYPE_PRACTICE && Auth::check() && (
                                Auth::user()->isAdmin() ||
                                Auth::user()->isDepartment() ||
                                Auth::user()->isHOA() ||
                                Auth::user()->isGeneralTrainingManager()
                            )),
                        TextEntry::make('trainee.major.name')
                            ->label('التخصص')
                            ->visible(fn($record) => $record->training_type !== \App\Models\Application::TRAINING_TYPE_PRACTICE && Auth::check() && (
                                Auth::user()->isAdmin() ||
                                Auth::user()->isDepartment() ||
                                Auth::user()->isHOA() ||
                                Auth::user()->isGeneralTrainingManager()
                            )),
                    ]),

                Section::make('تفاصيل التدريب')
                    ->columns(2)
                    ->schema([
                        TextEntry::make('department.title')
                            ->label('القسم / الدائرة'),
                        TextEntry::make('section.name_location')
                            ->label('المكان / الشعبة'),
                        TextEntry::make('administrative.title')
                            ->label('الإدارة'),
                        TextEntry::make('trainee.training_hours')
                            ->label('عدد ساعات التدريب'),
                        TextEntry::make('training_type')
                            ->label('نوع التدريب')
                            ->formatStateUsing(fn(?string $state): string => match ($state) {
                                'cooperative' => 'تدريب جامعي',
                                'professional' => 'مزاولة مهنة',
                                default => $state ?? '-',
                            }),
                        TextEntry::make('status')
                            ->label('الحالة')
                            ->badge()
                            ->color(fn($state): string => match ((int)$state) {
                                1 => 'info',
                                2 => 'primary',
                                3 => 'primary',
                                4 => 'warning',
                                5 => 'success',
                                6 => 'gray',
                                7 => 'danger',
                                8 => 'danger',
                                default => 'gray',
                            })
                            ->formatStateUsing(fn($state): string => (function ($state) {
                                $key = 'translation.status.' . $state;
                                $translated = \Illuminate\Support\Facades\Lang::get($key, [], 'ar');
                                return $translated === $key ? $state : $translated;
                            })($state)),
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
