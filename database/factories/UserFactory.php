<?php

namespace Database\Factories;

use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;


/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\User>
 */
class UserFactory extends Factory
{
    public function definition(): array
    {
        return [
            'name' => $this->faker->name(),
            'email' => $this->faker->unique()->safeEmail(),
            'email_verified_at' => now(),
            'password' => Hash::make('password'),
            'status' => 'active',
            'role' => User::ROLE_MOH,
            'remember_token' => Str::random(10),
        ];
    }

    public function admin(): static
    {
        return $this->state(fn(array $attributes) => [
            'role' => User::ROLE_ADMIN,
        ]);
    }

    /**
     * Set user role to Department
     */
    public function department(): static
    {
        return $this->state(fn(array $attributes) => [
            'role' => User::ROLE_DEPARTMENT,
        ]);
    }

    /**
     * Set user role to Department
     */
    public function section(): static
    {
        return $this->state(fn(array $attributes) => [
            'role' => User::ROLE_SECTION,
        ]);
    }

    /**
     * Set user role to MOH
     */
    public function moh(): static
    {
        return $this->state(fn(array $attributes) => [
            'role' => User::ROLE_MOH,
        ]);
    }

    /**
     * Set user role to Institution
     */
    public function institutionSupervisor(): static
    {
        return $this->state(fn(array $attributes) => [
            'role' => User::ROLE_COLLEGE,
        ]);
    }

    /**
     * Set user role to College
     */
    public function collage(): static
    {
        return $this->state(fn(array $attributes) => [
            'role' => User::ROLE_COLLEGE,
        ]);
    }
    /**
     * Set user role to Head of Administration
     */
    public function hoa(): static
    {
        return $this->state(fn(array $attributes) => [
            'role' => User::ROLE_HOA,
        ]);
    }

    /**
     * Set user role to Head of Medical
     */
    public function hom(): static
    {
        return $this->state(fn(array $attributes) => [
            'role' => User::ROLE_HOM,
        ]);
    }

    /**
     * Set user role to General Training Manager
     */
    public function gtm(): static
    {
        return $this->state(fn(array $attributes) => [
            'role' => User::ROLE_GTM,
        ]);
    }
}
