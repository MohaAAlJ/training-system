<?php

namespace Database\Seeders;

use App\Models\Institution;
use Illuminate\Database\Seeder;

class InstitutionsSeeder extends Seeder
{
    public function run(): void
    {
        Institution::factory()->count(5)->create();
    }
}