<?php

namespace App\Providers;

use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;
use App\Models\User;
use App\Models\Applications;
use App\Models\Trainees;
use App\Models\Departments;
use App\Models\Administratives;
use App\Models\College;
use App\Models\Institution;
use App\Models\Major;
use App\Policies\UserPolicy;

class AuthServiceProvider extends ServiceProvider
{
    /**
     * The policy mappings for the application.
     *
     * @var array<class-string, class-string>
     */
    // Policies disabled temporarily. Original mappings kept commented for reference.
    // protected $policies = [
    //     User::class => UserPolicy::class,
    //     \App\Models\Applications::class => UserPolicy::class,
    // ];

    protected $policies = [
        User::class => UserPolicy::class,
        Applications::class => UserPolicy::class,
        Trainees::class => UserPolicy::class,
        Departments::class => \App\Policies\DepartmentPolicy::class,
        Administratives::class => \App\Policies\AdministrativePolicy::class,
        College::class => UserPolicy::class,
        Institution::class => UserPolicy::class,
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
