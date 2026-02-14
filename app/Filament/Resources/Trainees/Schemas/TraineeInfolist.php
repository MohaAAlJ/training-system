<?php

namespace App\Filament\Resources\Trainees\Schemas;

use Filament\Schemas\Components\Section;
use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Schema;
use Illuminate\Support\Facades\Auth;

class TraineeInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('المعلومات الشخصية')
                    ->description('البيانات الأساسية للمتدرب')
                    ->icon('heroicon-o-user')
                    ->schema([
                        TextEntry::make('national_id')
                            ->label('رقم الهوية')
                            ->icon('heroicon-o-identification')
                            ->copyable()
                            ->badge()
                            ->color('primary')
                            ->weight('bold'),

                        TextEntry::make('full_name')
                            ->label('الاسم الكامل')
                            ->icon('heroicon-o-user-circle')
                            ->copyable()
                            ->weight('bold')
                            ->size('lg')
                            ->color('info'),

                        TextEntry::make('phone_number')
                            ->label('رقم الهاتف')
                            ->icon('heroicon-o-phone')
                            ->copyable()
                            ->url(fn($record) => $record->phone_number ? "tel:{$record->phone_number}" : null)
                            ->badge()
                            ->color('success'),

                        TextEntry::make('dob')
                            ->label('تاريخ الميلاد')
                            ->icon('heroicon-o-calendar')
                            ->date('d/m/Y')
                            ->badge()
                            ->color('gray'),

                        TextEntry::make('gender')
                            ->label('الجنس')
                            ->icon('heroicon-o-user')
                            ->badge()
                            ->color(fn($state) => $state === 'ذكر' ? 'info' : 'fuchsia'),

                        TextEntry::make('street')
                            ->label('المنطقة')
                            ->icon('heroicon-o-map-pin')
                            ->copyable()
                            ->badge()
                            ->color('warning'),

                        TextEntry::make('institution.name')
                            ->label('المؤسسة التعليمية')
                            ->icon('heroicon-o-building-library')
                            ->copyable()
                            ->badge()
                            ->color('purple')
                            ->visible(fn() => Auth::check() && (
                                Auth::user()->isAdmin() ||
                                Auth::user()->isDepartment() ||
                                Auth::user()->isHOA() ||
                                Auth::user()->isGeneralTrainingManager()
                            ))
                            ->formatStateUsing(fn($state) => is_array($state) ? ($state['ar'] ?? $state['en'] ?? reset($state)) : $state),

                        TextEntry::make('major.name')
                            ->label('التخصص')
                            ->icon('heroicon-o-academic-cap')
                            ->copyable()
                            ->badge()
                            ->color('indigo')
                            ->visible(fn() => Auth::check() && (
                                Auth::user()->isAdmin() ||
                                Auth::user()->isDepartment() ||
                                Auth::user()->isHOA() ||
                                Auth::user()->isGeneralTrainingManager() ||
                                Auth::user()->isCollegeSupervisor()
                            ))
                            ->formatStateUsing(fn($state) => is_array($state) ? ($state['ar'] ?? $state['en'] ?? reset($state)) : $state),
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
