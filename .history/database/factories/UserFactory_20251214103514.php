<?php

namespace Database\Factories;

use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class UserFactory extends Factory
{
    public function definition(): array
    {
        return [
            'name' => $this->faker->name(),
            'email' => $this->faker->unique()->safeEmail(),
            'email_verified_at' => now(),
            'password' => Hash::make('password'), 
            'remember_token' => Str::random(10),
            'status' => 'active',
            'role' => User::ROLE_MOH, // افتراضياً موظف وزارة أو يمكنك جعله عشوائي
        ];
    }

    /**
     * حالة لإنشاء مدير نظام
     */
    public function admin(): static
    {
        return $this->state(fn (array $attributes) => [
            'role' => User::ROLE_ADMIN,
            'email' => 'admin@system.com',
        ]);
    }

    /**
     * حالة لإنشاء مشرف كلية/جامعة
     */
    public function institutionSupervisor(): static
    {
        return $this->state(fn (array $attributes) => [
            'role' => User::ROLE_INSTITUTION,
        ]);
    }
}