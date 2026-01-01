<?php

namespace Database\Factories;

use App\Models\College;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Facades\DB;

class TraineeFactory extends Factory // تأكد أن الاسم يطابق ملفك (TraineeFactory أو TraineesFactory)
{
    public function definition(): array
    {
     
        $realLink = DB::table('college_major')->inRandomOrder()->first();

        if (!$realLink) {
            throw new \Exception('Please run "php artisan db:seed --class=RealDataSeeder" first to populate majors.');
        }

        $collegeId = $realLink->college_id;
        $majorId = $realLink->major_id;

        $institutionId = College::find($collegeId)->institution_id;

        return [
            'national_id' => $this->faker->unique()->numerify('#########'),
            'full_name'   => arabicFaker()->name, 
            'phone_number' => '05' . $this->faker->numerify('#######'),
            'dob'         => $this->faker->date('Y-m-d', '-19 years'),
            'address'     => 'Gaza Strip', 
            
            'institution_id' => $institutionId, 
            'college_id'     => $collegeId,
            'major_id'       => $majorId,
        ];
    }
}