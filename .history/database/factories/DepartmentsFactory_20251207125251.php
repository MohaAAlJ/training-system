<?php

namespace Database\Factories;

use App\Models\User;
use App\Models\Administrative;
use Illuminate\Database\Eloquent\Factories\Factory;

class DepartmentFactory extends Factory
{
    public function definition(): array
    {
        $arabicFaker = fake('ar_SA');
        
        // Random Medical Departments in Arabic
        $deptName = $arabicFaker->randomElement(['طوارئ', 'باطنة', 'جراحة', 'عناية مكثفة', 'أطفال', 'علاج طبيعي']);
        // Random Locations
        $location = $arabicFaker->randomElement(['غزة', 'خانيونس', 'رفح', 'الشمال', 'دير البلح']);

        return [
            // Format: "Emergency_Gaza" -> "طوارئ_غزة"
            'name_location' => $deptName . '_' . $location,
            
            'status' => $arabicFaker->randomElement(['active', 'inactive']),
            'total_capacity' => $arabicFaker->numberBetween(5, 50),

            // Create a user for the department head
            'user_id' => User::factory(),

            // This ensures we can link it to an Administrative unit
            'administrative_id' => Administrative::factory(),
        ];
    }
}