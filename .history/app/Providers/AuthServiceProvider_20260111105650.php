<?php

namespace App\Providers;

use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;
use App\Models\User;
use App\Models\Application;
use App\Models\Trainee;
use App\Models\Department;
use App\Models\College;
use App\Models\Institution;
use App\Models\Major;
use App\Models\Section;
use App\Policies\UserPolicy;
use App\Policies\ApplicationPolicy;
use App\Policies\TraineePolicy;
use App\Policies\DepartmentPolicy;
use App\Policies\SectionPolicy;


class AuthServiceProvider extends ServiceProvider
{
    /**
     * The policy mappings for the application.
     *
     * @var array<class-string, class-string>
     */
    protected $policies = [
        User::class => UserPolicy::class,
        Application::class => ApplicationPolicy::class,
        Trainee::class => TraineePolicy::class,
        Department::class => DepartmentPolicy::class,
        Section::class => SectionPolicy::class,
        College::class => \App\Policies\CollegePolicy::class,
        Institution::class => \App\Policies\InstitutionPolicy::class,
        Major::class => UserPolicy::class,

    ];

    /**
     * Register any authentication / authorization services.
     */
    public function boot(): void
    {
        $this->registerPolicies();
    }
}
