<?php

namespace Database\Seeders;

use App\Models\Trainees;
use Illuminate\Database\Seeder;

class TraineesSeeder extends Seeder
{
    public function run(): void
    {
        // Create 50 random trainees
        Trainees::factory()->count(50)->create();
    }
}