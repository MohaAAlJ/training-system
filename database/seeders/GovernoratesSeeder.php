<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class GovernoratesSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        DB::table('governorates')->insert([
            [
                'id' => 1,
                'name_en' => 'Gaza City',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'id' => 2,
                'name_en' => 'North Gaza',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'id' => 3,
                'name_en' => 'Deir Al-Balah',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'id' => 4,
                'name_en' => 'Khan Yunis',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'id' => 5,
                'name_en' => 'Rafah',
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ]);
    }
}
