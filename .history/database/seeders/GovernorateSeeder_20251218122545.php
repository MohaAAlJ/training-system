<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Departments;
use App\Models\Administratives;

class GovernorateSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
{
    $governorates = [
        ['ar' => 'غزة', 'en' => 'Gaza'],
        ['ar' => 'شمال غزة', 'en' => 'North Gaza'],
        ['ar' => 'خانيونس', 'en' => 'Khan Yunis'],
        ['ar' => 'رفح', 'en' => 'Rafah'],
        ['ar' => 'دير البلح', 'en' => 'Deir al-Balah'],
    ];

    foreach ($governorates as $gov) {
        \DB::table('governorates')->insert([
            'name' => json_encode($gov),
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }
}
}