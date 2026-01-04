<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Gate;
use App\Policies\DepartmentPolicy;
use App\Models\Department;
use App\Models\Section;
use App\Policies\SectionPolicy;
use Filament\Actions\Exports\Models\Export;
use App\Filament\Exporters\StyleExportFile;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        Gate::policy(Department::class, DepartmentPolicy::class);
        Gate::policy(Section::class, SectionPolicy::class);

        // Apply styling after export is completed
        Export::updated(function (Export $export) {
            if ($export->wasChanged('completed_at') && $export->completed_at && $export->file_name) {
                $filePath = "filament_exports/{$export->id}/{$export->file_name}.xlsx";
                StyleExportFile::dispatch($filePath, $export->file_disk);
            }
        });
    }
}
