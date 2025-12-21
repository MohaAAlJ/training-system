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

        $iugSupervisor = User::create([
            'name' => 'iug supervisor',
            'email' => 'iug@college.com',
            'password' => Hash::make('123'),
            'role' => 5,
            'status' => 'active',
        ]);

        // Link IUG supervisor (Using college ID 1 for example)
        \App\Models\College::where('id', 1)->update(['user_id' => $iugSupervisor->id]);

        $augSupervisor = User::create([
            'name' => 'aug supervisor',
            'email' => 'aug@college.com',
            'password' => Hash::make('123'),
            'role' => 5,
            'status' => 'active',
        ]);

        // Link AUG supervisor (Using Al-Azhar college ID 14 for example)
        \App\Models\College::where('id', 14)->update(['user_id' => $augSupervisor->id]);

        User::create([
            'name' => 'section head',
            'email' => 'de@section.com',
            'password' => Hash::make('123'),
            'role' => 4,
            'status' => 'active',
        ]);

        User::create([
            'name' => 'department head',
            'email' => 'ad@department.com',
            'password' => Hash::make('123'),
            'role' => 3,
            'status' => 'active',
        ]);
    }
}


