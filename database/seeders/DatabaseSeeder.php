<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // User::factory(10)->create();

        User::factory()->create([
            'name' => 'Test User',
            'email' => 'test@example.com',
        ]);
    }
}


// namespace Database\Seeders;

// use App\Models\Administratives;
// use App\Models\Applications;
// use App\Models\Departments;
// use App\Models\Institution;
// use App\Models\Major;
// use App\Models\Trainees;
// use App\Models\User;
// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
// use Illuminate\Database\Seeder;

// class DatabaseSeeder extends Seeder
// {
//     use WithoutModelEvents;

//     /**
//      * Seed the application's database.
//      */
//     public function run(): void
//     {
//         // Create admin user
//         $adminUser = User::factory()->create([
//             'name' => 'مدير النظام',
//             'email' => 'admin@example.com',
//             'status' => 'active',
//         ]);

//         // Create institutions
//         $institutions = Institution::factory(5)->create();

//         // Create majors
//         $majors = Major::factory(10)->create();

//         // Attach majors to institutions (many-to-many)
//         $institutions->each(function ($institution) use ($majors) {
//             $institution->majors()->attach(
//                 $majors->random(rand(3, 6))->pluck('id')->toArray()
//             );
//         });

//         // Create users for department heads and administrative heads
//         $users = User::factory(15)->create(['status' => 'active']);

//         // Create administratives
//         $administratives = collect();
//         foreach ($users->take(5) as $user) {
//             $administratives->push(
//                 Administratives::factory()->create(['user_id' => $user->id])
//             );
//         }

//         // Create departments
//         $departments = collect();
//         foreach ($users->skip(5)->take(10) as $user) {
//             $departments->push(
//                 Departments::factory()->create([
//                     'user_id' => $user->id,
//                     'administrative_id' => $administratives->random()->id,
//                 ])
//             );
//         }

//         // Create trainees
//         $trainees = Trainees::factory(30)->create([
//             'institution_id' => fn () => $institutions->random()->id,
//             'major_id' => fn () => $majors->random()->id,
//         ]);

//         // Create applications with various statuses
//         Applications::factory(10)->pending()->create([
//             'trainee_id' => fn () => $trainees->random()->id,
//             'department_id' => fn () => $departments->random()->id,
//         ]);

//         Applications::factory(15)->approved()->create([
//             'trainee_id' => fn () => $trainees->random()->id,
//             'department_id' => fn () => $departments->random()->id,
//         ]);

//         Applications::factory(5)->rejected()->create([
//             'trainee_id' => fn () => $trainees->random()->id,
//             'department_id' => fn () => $departments->random()->id,
//         ]);

//         Applications::factory(10)->completed()->create([
//             'trainee_id' => fn () => $trainees->random()->id,
//             'department_id' => fn () => $departments->random()->id,
//         ]);
//     }
// }
