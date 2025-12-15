<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    public function run(): void
    {
        User::create([
            'name' => 'Moha',
            'email' => 'Moha@admins.com',
            'password' => Hash::make('123$$'),
        ]);
        User::create([
            'name' => 'admin',
            'email' => 'a@admin.com',
            'password' => Hash::make('123'),
        ]);
    }
}
