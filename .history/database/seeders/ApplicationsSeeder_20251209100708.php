<?php

namespace Database\Seeders;

use App\Models\Applications;
use Illuminate\Database\Seeder;

class ApplicationsSeeder extends Seeder
{
    public function run(): void
    {
        // Create 100 random applications with mix of statuses
        Applications::factory()->count(40)->active()->create();
        Applications::factory()->count(30)->pending()->create();
        Applications::factory()->count(20)->rejected()->create();
        Applications::factory()->count(10)->completed()->create();
    }
}
