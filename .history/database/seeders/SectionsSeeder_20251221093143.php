<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Sections;
use App\Models\Administratives;
use App\Models\Departments;
use App\Models\User;
use App\Helpers\Constans;
use Illuminate\Support\Facades\Hash;

class SectionsSeeder extends Seeder
{
    public function run(): void
    {
        // Helper to get ID by partial name match
        $getAdmin = fn($name) => Administratives::where('title', 'LIKE', "%$name%")->first();
        $getDept = fn($name) => Departments::where('title', 'LIKE', "%$name%")->first();

        $adminGeneral = $getAdmin('General Hospital');
        $adminSurgery = $getAdmin('Surgery Center');
        $adminHQ = $getAdmin('Central');

        $deptPharmacy = $getDept('Pharmacy');
        $deptNursing = $getDept('Nursing');
        $deptIT = $getDept('Technology');
        $deptHR = $getDept('Resources');

        // 1. General Hospital -> Pharmacy (Inpatient)
        if ($adminGeneral && $deptPharmacy) {
            $hos1 = User::create([
                'name' => 'Ph. Kareem (Inpatient Pharmacy HOS)',
                'email' => 'hos_inpatient@test.com',
                'password' => Hash::make('password'),
                'role' => Constans::ROLE_SECTION_HEAD,
                'status' => 'active',
            ]);

            Sections::create([
                'name_location' => 'Inpatient Pharmacy',
                'administrative_id' => $adminGeneral->id,
                'department_id' => $deptPharmacy->id,
                'hos' => $hos1->id,
                'total_capacity' => 10,
                'status' => true,
            ]);
        }

        // 2. General Hospital -> Pharmacy (Outpatient)
        if ($adminGeneral && $deptPharmacy) {
            $hos2 = User::create([
                'name' => 'Ph. Mona (Outpatient Pharmacy HOS)',
                'email' => 'hos_outpatient@test.com',
                'password' => Hash::make('password'),
                'role' => Constans::ROLE_SECTION_HEAD,
                'status' => 'active',
            ]);

            Sections::create([
                'name_location' => 'Outpatient Pharmacy',
                'administrative_id' => $adminGeneral->id,
                'department_id' => $deptPharmacy->id,
                'hos' => $hos2->id,
                'total_capacity' => 15,
                'status' => true,
            ]);
        }

        // 3. General Hospital -> Nursing (ER)
        if ($adminGeneral && $deptNursing) {
            $hos3 = User::create([
                'name' => 'Nurse Hoda (ER Nursing HOS)',
                'email' => 'hos_er_nursing@test.com',
                'password' => Hash::make('password'),
                'role' => Constans::ROLE_SECTION_HEAD,
                'status' => 'active',
            ]);

            Sections::create([
                'name_location' => 'Emergency Room Nursing',
                'administrative_id' => $adminGeneral->id,
                'department_id' => $deptNursing->id,
                'hos' => $hos3->id,
                'total_capacity' => 20,
                'status' => true,
            ]);
        }

        // 4. Surgery Center -> Nursing (OR)
        if ($adminSurgery && $deptNursing) {
            $hos4 = User::create([
                'name' => 'Nurse Ali (OR Nursing HOS)',
                'email' => 'hos_or_nursing@test.com',
                'password' => Hash::make('password'),
                'role' => Constans::ROLE_SECTION_HEAD,
                'status' => 'active',
            ]);

            Sections::create([
                'name_location' => 'Operating Room Nursing',
                'administrative_id' => $adminSurgery->id,
                'department_id' => $deptNursing->id,
                'hos' => $hos4->id,
                'total_capacity' => 8,
                'status' => true,
            ]);
        }

        // 5. Central HQ -> IT
        if ($adminHQ && $deptIT) {
            $hos5 = User::create([
                'name' => 'Eng. Sameh (HQ IT Support HOS)',
                'email' => 'hos_hq_it@test.com',
                'password' => Hash::make('password'),
                'role' => Constans::ROLE_SECTION_HEAD,
                'status' => 'active',
            ]);

            Sections::create([
                'name_location' => 'HQ Technical Support',
                'administrative_id' => $adminHQ->id,
                'department_id' => $deptIT->id,
                'hos' => $hos5->id,
                'total_capacity' => 5,
                'status' => true,
            ]);
        }
    }
}
