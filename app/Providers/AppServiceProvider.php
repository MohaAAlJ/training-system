<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Gate;
use App\Policies\DepartmentPolicy;
use App\Models\Department;
use App\Models\Section;
use App\Models\Application;
use App\Models\User;
use App\Policies\SectionPolicy;
// use App\Observers\ApplicationObserver;
// use App\Observers\UserObserver;
use Filament\Actions\Exports\Models\Export;
use App\Filament\Exporters\StyleExportFile;

use Illuminate\Support\Facades\Event;
use Illuminate\Auth\Events\Login;
use Illuminate\Support\Facades\Session;

class AppServiceProvider extends ServiceProvider
{
    // Register any application services
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        Event::listen(Login::class, function ($event) {
            Session::put('login_time', now());
            $event->user->update(['last_login_at' => now()]);
        });

        Gate::policy(Department::class, DepartmentPolicy::class);
        Gate::policy(Section::class, SectionPolicy::class);

        // Define Rate Limiter for WhatsApp (1 message per minute)
        \Illuminate\Support\Facades\RateLimiter::for('whatsapp', function () {
            return \Illuminate\Cache\RateLimiting\Limit::perMinute(1);
        });

        // // Register Telegram monitoring observers
        // Application::observe(ApplicationObserver::class);
        // User::observe(UserObserver::class);

        // Apply styling after export is completed
        Export::updated(function (Export $export) {
            if ($export->wasChanged('completed_at') && $export->completed_at && $export->file_name) {
                $filePath = "filament_exports/{$export->id}/{$export->file_name}.xlsx";
                StyleExportFile::dispatch($filePath, $export->file_disk);
            }
        });
    }
}
