<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Gate;
use App\Models\Administratives;
use App\Policies\AdministrativePolicy;
use App\Models\Departments;
use App\Policies\DepartmentPolicy;

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
        Gate::policy(Administratives::class, AdministrativePolicy::class);
        Gate::policy(Departments::class, DepartmentPolicy::class);
        Gate::policy(Applications::class, ApplicationPolicy::class);
    }
}
