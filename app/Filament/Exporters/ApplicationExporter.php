<?php

namespace App\Filament\Exporters;

use App\Models\Application;
use Filament\Actions\Exports\ExportColumn;
use Filament\Actions\Exports\Exporter;
use Filament\Actions\Exports\Models\Export;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;

class ApplicationExporter extends Exporter
{
    protected static ?string $model = Application::class;

    public static function getColumns(): array
    {
        $translation = require resource_path('lang/ar/translation.php');
        return [
            ExportColumn::make('id')
                ->label('رقم الطلب'),
            ExportColumn::make('trainee.full_name')
                ->label('اسم المتدرب'),
            ExportColumn::make('trainee.national_id')
                ->label('رقم الهوية'),
            ExportColumn::make('trainee.institution.name')
                ->label('المؤسسة'),
            ExportColumn::make('trainee.major.name')
                ->label('التخصص'),
            ExportColumn::make('training_type_label')
                ->label('نوع التدريب'),
            ExportColumn::make('administrative.title')
                ->label('الإدارة'),
            ExportColumn::make('department.title')
                ->label('الدائرة'),
            ExportColumn::make('section.name_location')
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
                    $actualState = $state instanceof \App\Enums\ApplicationStatus ? $state->value : (int)$state;
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
        $timestamp = Carbon::now()->format('ymd_His');
        $baseFileName = "المتدربين_{$timestamp}";

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
                return "{$baseFileName}_{$department->title}";
            }
        } elseif ($user->isAdministrative()) {
            $administrative = $user->administrative;
            if ($administrative) {
                return "{$baseFileName}_{$administrative->title}";
            }
        } elseif ($user->isSectionHead()) {
            $section = $user->section;
            if ($section) {
                return "{$baseFileName}_{$section->name_location}";
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
