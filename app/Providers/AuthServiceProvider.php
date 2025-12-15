<?php

namespace App\Providers;

use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;
use App\Models\User;
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
        // No policies registered while debugging.
        //  User::class => UserPolicy::class,
    ];

    /**
     * Register any authentication / authorization services.
     */
    public function boot(): void
    {
        $this->registerPolicies();
    }
}
