<?php

declare(strict_types=1);

namespace App\Filament\Resources\Applications\Schemas;

use App\Enums\ApplicationStatus;
use App\Enums\TrainingType;
use Filament\Infolists\Components\ImageEntry;
use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Illuminate\Support\Facades\Auth;

class ApplicationInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema->components([
            Section::make('معلومات المتدرب')
                ->icon('heroicon-o-user')
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
                        ->visible(fn($record) => $record->training_type !== TrainingType::PRACTICE && Auth::check() && (
                            Auth::user()->isAdmin() ||
                            Auth::user()->isDepartment() ||
                            Auth::user()->isHOA() ||
                            Auth::user()->isGeneralTrainingManager()
                        )),

                    TextEntry::make('trainee.major.name')
                        ->label('التخصص')
                        ->visible(fn($record) => $record->training_type !== TrainingType::PRACTICE && Auth::check() && (
                            Auth::user()->isAdmin() ||
                            Auth::user()->isDepartment() ||
                            Auth::user()->isHOA() ||
                            Auth::user()->isGeneralTrainingManager()
                        )),
                ]),

            Section::make('تفاصيل التدريب')
                ->icon('heroicon-o-clipboard-document-list')
                ->columns(2)
                ->schema([
                    TextEntry::make('administrative.title')
                        ->label('الإدارة'),

                    TextEntry::make('department.title')
                        ->label('الدائرة'),

                    TextEntry::make('section.name_location')
                        ->label('القسم / الشعبة'),

                    TextEntry::make('trainee.training_hours')
                        ->label('عدد ساعات التدريب'),

                    TextEntry::make('training_type')
                        ->label('نوع التدريب')
                        ->badge(),

                    TextEntry::make('status')
                        ->label('الحالة')
                        ->badge(),

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
                ->icon('heroicon-o-document-plus')
                ->schema([
                    ImageEntry::make('application_letter')
                        ->label('صورة خطاب التدريب')
                        ->disk('public'),
                ]),
        ]);
    }
}
