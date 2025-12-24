<?php

namespace App\Filament\Resources\Trainees\Schemas;

use Filament\Schemas\Components\Section;
use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Schema;
use Illuminate\Support\Facades\Auth;

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
                        TextEntry::make('street')
                            ->label('المنطقة / الشارع'),
                        TextEntry::make('institution.name')
                            ->label('المؤسسة التعليمية')
                            ->visible(fn() => Auth::check() && (
                                Auth::user()->isAdmin() ||
                                Auth::user()->isDepartment() ||
                                Auth::user()->isHOA() ||
                                Auth::user()->isGeneralTrainingManager()
                            ))
                            ->formatStateUsing(fn($state) => is_array($state) ? ($state['ar'] ?? $state['en'] ?? reset($state)) : $state),
                        TextEntry::make('major.name')
                            ->label('التخصص')
                            ->visible(fn() => Auth::check() && (
                                Auth::user()->isAdmin() ||
                                Auth::user()->isDepartment() ||
                                Auth::user()->isHOA() ||
                                Auth::user()->isGeneralTrainingManager()||
                                Auth::user()->
                            ))
                            ->formatStateUsing(fn($state) => is_array($state) ? ($state['ar'] ?? $state['en'] ?? reset($state)) : $state),
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
