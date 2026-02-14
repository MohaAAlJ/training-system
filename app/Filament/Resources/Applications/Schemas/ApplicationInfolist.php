<?php

declare(strict_types=1);

namespace App\Filament\Resources\Applications\Schemas;

// use App\Enums\TrainingType;
use App\Models\Application;
use Filament\Infolists\Components\ImageEntry;
use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Schema;
use Illuminate\Support\Facades\Auth;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Support\Enums\TextSize;

class ApplicationInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->columns(1)
            ->components([
                // معلومات المتدرب - Enhanced Section
                Section::make('معلومات المتدرب')
                    ->description('البيانات الشخصية الكاملة للمتدرب')
                    ->icon('heroicon-o-user-circle')
                    ->iconColor('primary')
                    ->collapsible()
                    ->columnSpanFull()
                    ->schema([
                        Grid::make(3)
                            ->schema([
                                TextEntry::make('trainee.full_name')
                                    ->label('الاسم الكامل')
                                    ->icon('heroicon-m-user')
                                    ->iconColor('primary')
                                    ->weight('bold')
                                    ->size(TextSize::Medium)
                                    ->copyable()
                                    ->copyMessage('تم نسخ الاسم')
                                    ->copyMessageDuration(1500),

                                TextEntry::make('trainee.national_id')
                                    ->label('رقم الهوية')
                                    ->icon('heroicon-m-identification')
                                    ->iconColor('success')
                                    ->copyable()
                                    ->copyMessage('تم نسخ رقم الهوية')
                                    ->copyMessageDuration(1500),

                                TextEntry::make('trainee.phone_number')
                                    ->label('رقم الهاتف')
                                    ->icon('heroicon-m-phone')
                                    ->iconColor('info')
                                    ->copyable()
                                    ->copyMessage('تم نسخ رقم الهاتف')
                                    ->copyMessageDuration(1500)
                                    ->url(fn($state) => $state ? 'tel:' . $state : null),
                            ]),

                        Grid::make(4)
                            ->schema([
                                TextEntry::make('trainee.gender')
                                    ->label('الجنس'),
                                TextEntry::make('trainee.dob')
                                    ->label('تاريخ الميلاد')
                                    ->icon('heroicon-m-cake')
                                    ->date('d/m/Y')
                                    ->placeholder('غير محدد'),

                                TextEntry::make('trainee.governorate.name')
                                    ->label('المحافظة')
                                    ->icon('heroicon-m-map-pin')
                                    ->badge()
                                    ->color('gray')
                                    ->placeholder('غير محدد'),

                                TextEntry::make('trainee.street')
                                    ->label('العنوان')
                                    ->icon('heroicon-m-home')
                                    ->placeholder('غير محدد'),
                            ]),

                        Grid::make(2)
                            ->schema([
                                TextEntry::make('trainee.institution.name')
                                    ->label('المؤسسة التعليمية')
                                    ->icon('heroicon-m-building-library')
                                    ->iconColor('warning')
                                    ->badge()
                                    ->color('warning')
                                    ->placeholder('غير محدد')
                                    ->visible(fn($record) => $record->training_type !== Application::PRACTICE && Auth::check() && (
                                        Auth::user()->isAdmin() ||
                                        Auth::user()->isDepartment() ||
                                        Auth::user()->isHOA() ||
                                        Auth::user()->isGeneralTrainingManager()
                                    )),

                                TextEntry::make('trainee.major.name')
                                    ->label('التخصص الأكاديمي')
                                    ->icon('heroicon-m-academic-cap')
                                    ->iconColor('success')
                                    ->badge()
                                    ->color('success')
                                    ->placeholder('غير محدد')
                                    ->visible(fn($record) => $record->training_type !== Application::PRACTICE && Auth::check() && (
                                        Auth::user()->isAdmin() ||
                                        Auth::user()->isDepartment() ||
                                        Auth::user()->isHOA() ||
                                        Auth::user()->isGeneralTrainingManager()
                                    )),
                            ]),
                    ]),

                // تفاصيل التدريب - Enhanced Section
                Section::make('تفاصيل التدريب')
                    ->description('معلومات كاملة عن برنامج التدريب والجهة المستقبلة')
                    ->icon('heroicon-o-clipboard-document-check')
                    ->iconColor('success')
                    ->collapsible()
                    ->columnSpanFull()
                    ->schema([
                        // Training Location
                        Grid::make(3)
                            ->schema([
                                TextEntry::make('section.administrative.name')
                                    ->label('الإدارة')
                                    ->icon('heroicon-m-building-office-2')
                                    ->iconColor('primary')
                                    ->weight('semibold')
                                    ->badge()
                                    ->color('primary'),

                                TextEntry::make('section.department.name')
                                    ->label('الدائرة')
                                    ->icon('heroicon-m-rectangle-group')
                                    ->iconColor('info')
                                    ->badge()
                                    ->color('info'),

                                TextEntry::make('section.name')
                                    ->label('القسم')
                                    ->icon('heroicon-m-squares-2x2')
                                    ->iconColor('success')
                                    ->badge()
                                    ->color('success'),
                            ]),

                        // Training Details
                        Grid::make(3)
                            ->schema([
                                TextEntry::make('trainee.training_hours')
                                    ->label('عدد ساعات التدريب المطلوبة')
                                    ->icon('heroicon-m-clock')
                                    ->iconColor('warning')
                                    ->suffix(' ساعة')
                                    ->weight('bold')
                                    ->size(TextSize::Medium)
                                    ->placeholder('غير محدد'),

                                TextEntry::make('training_type')
                                    ->label('نوع التدريب')
                                    ->icon('heroicon-m-academic-cap')
                                    ->badge()
                                    ->color(fn(int $state): string => Application::getTrainingTypeColor($state))
                                    ->formatStateUsing(fn(int $state): string => Application::getTrainingTypeLabel($state))
                                    ->size(TextSize::Medium),

                                TextEntry::make('status')
                                    ->label('حالة الطلب')
                                    ->icon('heroicon-m-check-badge')
                                    ->badge()
                                    ->color(fn(int $state): string => Application::getStatusColor($state))
                                    ->formatStateUsing(fn(int $state): string => Application::getStatusLabel($state))
                                    ->size(TextSize::Medium),
                            ]),

                        // Training Timeline
                        Grid::make(3)
                            ->schema([
                                TextEntry::make('start_date')
                                    ->label('تاريخ البدء')
                                    ->icon('heroicon-m-calendar')
                                    ->iconColor('success')
                                    ->date('d/m/Y')
                                    ->placeholder('لم يبدأ بعد')
                                    ->color('success'),

                                TextEntry::make('end_date')
                                    ->label('تاريخ الانتهاء')
                                    ->icon('heroicon-m-calendar')
                                    ->iconColor('danger')
                                    ->date('d/m/Y')
                                    ->placeholder('غير محدد')
                                    ->color('danger'),

                                TextEntry::make('accepted_at')
                                    ->label('تاريخ القبول')
                                    ->icon('heroicon-m-check-circle')
                                    ->iconColor('success')
                                    ->dateTime('d/m/Y - h:i A')
                                    ->placeholder('غير محدد')
                                    ->visible(fn($record) => $record->accepted_at !== null),
                            ]),

                        // Tags
                        TextEntry::make('tags')
                            ->label('الوسوم')
                            ->icon('heroicon-m-tag')
                            ->iconColor('gray')
                            ->badge()
                            ->color('gray')
                            ->separator(',')
                            ->placeholder('لا توجد وسوم')
                            ->columnSpanFull()
                            ->visible(fn($record) => !empty($record->tags)),
                    ]),

                // المستندات - Enhanced Section
                Section::make('المستندات المرفقة')
                    ->description('الملفات والمستندات الخاصة بطلب التدريب')
                    ->icon('heroicon-o-paper-clip')
                    ->iconColor('warning')
                    ->collapsible()
                    ->collapsed()
                    ->columnSpanFull()
                    ->schema([
                        ImageEntry::make('application_letter')
                            ->label('صورة خطاب التدريب')
                            ->disk('public')
                            ->height(400)
                            ->width('100%')
                            ->extraImgAttributes([
                                'class' => 'rounded-lg border-2 border-gray-200 dark:border-gray-700',
                            ])
                            ->visible(fn($record) => !empty($record->application_letter))
                            ->columnSpanFull(),

                        TextEntry::make('application_letter')
                            ->label('')
                            ->formatStateUsing(fn() => 'لم يتم رفع خطاب التدريب بعد')
                            ->icon('heroicon-m-exclamation-triangle')
                            ->iconColor('warning')
                            ->color('warning')
                            ->visible(fn($record) => empty($record->application_letter))
                            ->columnSpanFull(),
                    ]),

                // معلومات إضافية - System Info Section
                Section::make('معلومات النظام')
                    ->description('البيانات التقنية والتواريخ')
                    ->icon('heroicon-o-information-circle')
                    ->iconColor('gray')
                    ->collapsible()
                    ->collapsed()
                    ->columnSpanFull()
                    ->schema([
                        Grid::make(3)
                            ->schema([
                                TextEntry::make('created_at')
                                    ->label('تاريخ إنشاء الطلب')
                                    ->icon('heroicon-m-clock')
                                    ->dateTime('d/m/Y - h:i A')
                                    ->color('gray'),

                                TextEntry::make('updated_at')
                                    ->label('آخر تحديث')
                                    ->icon('heroicon-m-arrow-path')
                                    ->dateTime('d/m/Y - h:i A')
                                    ->color('gray')
                                    ->since(),

                                TextEntry::make('id')
                                    ->label('رقم الطلب')
                                    ->icon('heroicon-m-hashtag')
                                    ->badge()
                                    ->color('gray')
                                    ->copyable()
                                    ->copyMessage('تم نسخ رقم الطلب')
                                    ->copyMessageDuration(1500),
                            ]),
                    ]),
            ]);
    }
}
