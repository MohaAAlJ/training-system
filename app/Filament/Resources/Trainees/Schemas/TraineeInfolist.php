<?php

namespace App\Filament\Resources\Trainees\Schemas;

use Filament\Schemas\Components\Section;
use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Schema;
use Illuminate\Support\Facades\Auth;
use Filament\Infolists\Components\RepeatableEntry;
use App\Models\Application;

class TraineeInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->columns(1)
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
                            ->formatStateUsing(fn($state) => $state ? \Carbon\Carbon::parse($state)->format('d/m/Y') : null)
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

                        RepeatableEntry::make('applications')
                            ->label('سجل التعليم والتدريب')
                            ->state(fn($record) => $record->applications()->where('training_type', '!=', Application::PRACTICE)->get())
                            ->schema([
                                TextEntry::make('institution.name')
                                    ->label('المؤسسة')
                                    ->icon('heroicon-o-building-library')
                                    ->color('purple'),
                                TextEntry::make('college.name')
                                    ->label('الكلية')
                                    ->visible(fn($record) => $record->training_type === Application::UNIVERSITY),
                                TextEntry::make('major.name')
                                    ->label('التخصص')
                                    ->icon('heroicon-o-academic-cap'),
                                TextEntry::make('university_number')
                                    ->label('الرقم الجامعي')
                                    ->icon('heroicon-o-identification')
                                    ->visible(fn($record) => $record->training_type === Application::UNIVERSITY),
                                TextEntry::make('status')
                                    ->label('الحالة')
                                    ->badge()
                                    ->formatStateUsing(fn($state) => Application::getStatusLabel($state))
                                    ->color(fn($state) => Application::getStatusColor($state)),
                                TextEntry::make('start_date')
                                    ->label('تاريخ البدء')
                                    ->formatStateUsing(fn($state) => $state ? \Carbon\Carbon::parse($state)->format('d/m/Y') : null)
                                    ->placeholder('غير محدد'),
                                TextEntry::make('end_date')
                                    ->label('تاريخ الانتهاء')
                                    ->formatStateUsing(fn($state) => $state ? \Carbon\Carbon::parse($state)->format('d/m/Y') : null)
                                    ->placeholder('غير محدد'),
                            ])
                            ->columns(3)
                            ->columnSpanFull()
                            ->visible(fn($record) => Auth::check() && (
                                Auth::user()->isAdmin() ||
                                Auth::user()->isDepartment() ||
                                Auth::user()->isHOA() ||
                                Auth::user()->isTrainingManagerLike() ||
                                Auth::user()->isCollegeSupervisor() ||
                                Auth::user()->isMonitor()
                            ) && $record->applications()->where('training_type', Application::UNIVERSITY)->exists()),
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
