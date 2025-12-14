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
    /**
     * The current password being used by the factory.
     */
    protected static ?string $password;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'name' => fake('ar_SA')->name(),
            'email' => fake()->unique()->safeEmail(),
            'email_verified_at' => now(),
            'password' => Hash::make('password'),
            'status' => fake()->randomElement(['active', 'inactive', 'banned']),
            'role' => Constans::ROLE_ADMIN, // Default to admin for seeding
            'remember_token' => Str::random(10),
        ];
    }

    /**
     * Indicate that the model's email address should be unverified.
     */
    public function unverified(): static
    {
        return $this->state(fn(array $attributes) => [
            'email_verified_at' => null,
        ]);
    }

    /**
     * Set user role to Admin
     */
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
    public function institution(): static
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
