<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Governorate;

class GovernoratesSeeder extends Seeder
{
    public function run(): void
    {
        $governorates = [
            ['name' => 'غزة'],
            ['name' => 'شمال غزة'],
            ['name' => 'خانيونس'],
            ['name' => 'رفح'],
            ['name' => 'دير البلح'],
        ];

        foreach ($governorates as $gov) {
            Governorate::firstOrCreate($gov);
        }
    }
}
