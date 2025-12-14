<?php

namespace Database\Factories;

use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use App\Helpers\Constans;

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
            'role' => Constans::ROLE_MOH,
            'remember_token' => Str::random(10),
        ];
    }

    public function admin(): static
    {
        return $this->state(fn(array $attributes) => [
            'role' => Constans::ROLE_ADMIN,
        ]);
    }

    /**
     * Set user role to Administrative
     */
    public function administrative(): static
    {
        return $this->state(fn(array $attributes) => [
            'role' => Constans::ROLE_ADMINISTRATIVE,
        ]);
    }

    /**
     * Set user role to Department
     */
    public function department(): static
    {
        return $this->state(fn(array $attributes) => [
            'role' => Constans::ROLE_DEPARTMENT,
        ]);
    }

    /**
     * Set user role to MOH
     */
    public function moh(): static
    {
        return $this->state(fn(array $attributes) => [
            'role' => Constans::ROLE_MOH,
        ]);
    }

    /**
     * Set user role to Institution
     */
    public function institutionSupervisor(): static
    {
        return $this->state(fn(array $attributes) => [
            'role' => Constans::ROLE_COLLAGE,
        ]);

    }

    /**
     * Set user role to Collage
     */
    public function collage(): static
    {
        return $this->state(fn(array $attributes) => [
            'role' => Constans::ROLE_COLLAGE,
        ]);
    }
}
