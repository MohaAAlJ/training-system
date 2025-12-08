<?php

namespace Database\Seeders;

use App\Models\Trainee;
use Illuminate\Database\Seeder;

class TraineesSeeder extends Seeder
{
    public function run(): void
    {
        // Create 50 random trainees
        Trainee::factory()->count(50)->create();
    }
}