<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $this->call([
            UserSeeder::class,          // لإنشاء المستخدمين والآدمن
            
            // استبدال القديم بـ RealDataSeeder
            RealDataSeeder::class,      // ينشئ الجامعات، الكليات، والتخصصات ويربطهم
            
            DepartmentsSeeder::class,   // لإنشاء الأقسام الإدارية (التي يتدرب فيها الطالب)
            
            TraineesSeeder::class,      
            
            ApplicationsSeeder::class,
        ]);
    }
}