<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Gate;
use App\Policies\DepartmentsPolicy;
use App\Models\Departments;
use App\Models\Sections;
use App\Policies\SectionPolicy;

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
        Gate::policy(Departments::class, DepartmentsPolicy::class);
        Gate::policy(Sections::class, SectionPolicy::class);
    }
}
