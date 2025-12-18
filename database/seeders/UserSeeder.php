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
            'password' => Hash::make('123'),
            'role' => 1,
            'status' => 'active',
        ]);
        User::create([
            'name' => 'admin',
            'email' => 'a@admin.com',
            'password' => Hash::make('123'),
            'role' => 1,
            'status' => 'active',
        ]);
        User::create([
            'name' => 'iug supervisor',
            'email' => 'iug@college.com',
            'password' => Hash::make('123'),
            'role' => 5,
            'college_id' => 1,
            'institution_id' => 1,
            'status' => 'active',
        ]);
        User::create([
            'name' => 'aug supervisor',
            'email' => 'aug@college.com',
            'password' => Hash::make('123'),
            'role' => 5,
            'college_id' => 2,
            'institution_id' => 2,
            'status' => 'active',
        ]);
        User::create([
            'name' => 'section head',
            'email' => 'de@section.com',
            'password' => Hash::make('123'),
            'role' => 4,
        ]);
        User::create([
            'name' => 'department head',
            'email' => 'ad@department.com',
            'password' => Hash::make('123'),
            'role' => 3,
        ]);
    }
    }


