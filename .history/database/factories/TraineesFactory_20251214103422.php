<?php

namespace Database\Factories;

use App\Models\College;
use App\Models\Institution;
use App\Models\Major;
use App\Models\Trainees; 
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Facades\DB;

class TraineesFactory extends Factory 
{
    public function definition(): array
    {
        $link = DB::table('college_major')->inRandomOrder()->first();

        if ($link) {
            $collegeId = $link->college_id;
            $majorId = $link->major_id;
            $institutionId = College::find($collegeId)->institution_id;
        } else {
            $college = College::factory()
                ->hasAttached(Major::factory()) 
                ->create();
            
            $collegeId = $college->id;
            $institutionId = $college->institution_id;
            $majorId = $college->majors()->first()->id;
        }

        return [
            'national_id' => $this->faker->unique()->numerify('#########'), 
            'full_name'   => $this->faker->name(), // أو arabicFaker()->name إذا كنت تستخدمه
            'phone_number' => '05' . $this->faker->numerify('#######'),
            'dob'         => $this->faker->dateTimeBetween('-24 years', '-19 years')->format('Y-m-d'),
            'address'     => $this->faker->address,
            
            // العلاقات المحدثة
            'institution_id' => $institutionId, // (اختياري، إذا كنت ما زلت تحتفظ بهذا العمود)
            'college_id'     => $collegeId,     // هذا هو الأساس الجديد
            'major_id'       => $majorId,
        ];
    }
}