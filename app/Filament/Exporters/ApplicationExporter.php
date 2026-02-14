<?php

namespace App\Filament\Exporters;

use App\Models\Application;
use Filament\Actions\Exports\ExportColumn;
use Filament\Actions\Exports\Exporter;
use Filament\Actions\Exports\Models\Export;
use Filament\Actions\Exports\Enums\ExportFormat;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;

class ApplicationExporter extends Exporter
{
    protected static ?string $model = Application::class;

    public static function getLabel(): string
    {
        return 'طلبات التدريب';
    }

    public function getFormats(): array
    {
        if (Auth::user()?->isAdmin()) {
            return [ExportFormat::Xlsx, ExportFormat::Csv,];
        }
        return [ExportFormat::Xlsx,];
    }

    public static function getColumns(): array
    {
        $translation = require resource_path('lang/ar/translation.php');
        return [
            ExportColumn::make('id')
                ->label('رقم الطلب'),
            ExportColumn::make('trainee.full_name')
                ->label('اسم المتدرب'),
            ExportColumn::make('trainee.gender')
                ->label('الجنس'),
            ExportColumn::make('trainee.national_id')
                ->label('رقم الهوية'),
            ExportColumn::make('trainee.institution.name')
                ->label('المؤسسة'),
            ExportColumn::make('trainee.major.name')
                ->label('التخصص'),
            ExportColumn::make('training_type_label')
                ->label('نوع التدريب'),
            ExportColumn::make('section.administrative.name')
                ->label('الإدارة'),
            ExportColumn::make('section.department.name')
                ->label('الدائرة'),
            ExportColumn::make('section.name')
                ->label('القسم'),
            ExportColumn::make('start_date')
                ->label('تاريخ البدء')
                ->formatStateUsing(fn($state) => $state ? $state->format('d/m/Y') : ''),
            ExportColumn::make('end_date')
                ->label('تاريخ الانتهاء')
                ->formatStateUsing(fn($state) => $state ? $state->format('d/m/Y') : ''),
            ExportColumn::make('status')
                ->label('الحالة')
                ->formatStateUsing(function ($state) use ($translation) {
                    $actualState = (int)$state;
                    return $translation['status'][$actualState] ?? 'غير محدد';
                }),
            ExportColumn::make('trainee.training_hours')
                ->label('ساعات التدريب'),
            ExportColumn::make('accepted_at')
                ->label('تاريخ القبول')
                ->formatStateUsing(fn($state) => $state ? $state->format('d/m/Y') : ''),
            ExportColumn::make('created_at')
                ->label('تاريخ الإنشاء')
                ->formatStateUsing(fn($state) => $state ? $state->format('d/m/Y') : ''),
            ExportColumn::make('end_date')
                ->label('تاريخ الانتهاء')
                ->formatStateUsing(fn($state) => $state ? $state->format('d/m/Y') : ''),
        ];
    }

    public static function getCompletedNotificationBody(Export $export): string
    {
        $translation = require resource_path('lang/ar/translation.php');
        $body = 'تم تصدير' . ' ' . number_format($export->successful_rows) . ' ' . 'طلب بنجاح.';

        if ($failedRowsCount = $export->getFailedRowsCount()) {
            $body .= ' ' . number_format($failedRowsCount) . ' ' . 'صفوف فشلت في التصدير.';
        }

        return $body;
    }

    public function getFileName(Export $export): string
    {
        $user = Auth::user();
        $date = Carbon::now()->format('Y-m-d');
        $time = Carbon::now()->format('H-i');
        $baseFileName = "طلبات_التدريب_تاريخ_{$date}_وقت_{$time}";

        if (!$user) {
            return $baseFileName;
        }

        // Check user role and add relevant context
        if ($user->isAdmin()) {
            // Admin users get the system context
            return "{$baseFileName}";
        } elseif ($user->isGeneralTrainingManager()) {
            // GTM users get training context
            return "{$baseFileName}_مدير_التدريب";
        } elseif ($user->isDepartmentHead()) {
            $department = $user->department;
            if ($department) {
                return "{$baseFileName}_{$department->name}";
            }
        } elseif ($user->isAdministrative()) {
            $administrative = $user->administrative;
            if ($administrative) {
                return "{$baseFileName}_{$administrative->name}";
            }
        } elseif ($user->isSectionHead()) {
            $section = $user->section;
            if ($section) {
                return "{$baseFileName}_{$section->name}";
            }
        } elseif ($user->isCollegeSupervisor()) {
            $college = $user->college;
            if ($college) {
                return "{$baseFileName}_{$college->name}";
            }
        }

        // Default for any other users
        return $baseFileName;
    }
}
