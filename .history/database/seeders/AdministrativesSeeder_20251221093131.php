<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Administratives;
use App\Models\User;
use App\Helpers\Constans;
use Illuminate\Support\Facades\Hash;

class AdministrativesSeeder extends Seeder
{
    public function run(): void
    {
        // 1. General Hospital (Medical)
        $hoa1 = User::create([
            'name' => 'Dr. Ahmed (General Hospital Director)',
            'email' => 'hoa_general@test.com',
            'password' => Hash::make('password'),
            'role' => Constans::ROLE_ADMINISTRATIVE_MANAGER,
            'status' => 'active',
        ]);

        $medHoa1 = User::create([
            'name' => 'Dr. Sara (General Hospital Medical Dir)',
            'email' => 'med_hoa_general@test.com',
            'password' => Hash::make('password'),
            'role' => Constans::ROLE_MEDICAL_MANAGER,
            'status' => 'active',
        ]);

        Administratives::create([
            'title' => 'Gaza General Hospital',
            'hoa' => $hoa1->id,
            'is_medical' => true,
            'medical_hoa' => $medHoa1->id,
        ]);

        // 2. Specialized Surgery Center (Medical)
        $hoa2 = User::create([
            'name' => 'Dr. Khaled (Surgery Center Director)',
            'email' => 'hoa_surgery@test.com',
            'password' => Hash::make('password'),
            'role' => Constans::ROLE_ADMINISTRATIVE_MANAGER,
            'status' => 'active',
        ]);

        Administratives::create([
            'title' => 'Specialized Surgery Center',
            'hoa' => $hoa2->id,
            'is_medical' => true,
            'medical_hoa' => $medHoa1->id, // Sharing Medical Director for demo, or create new
        ]);

        // 3. Central Administration (Non-Medical)
        $hoa3 = User::create([
            'name' => 'Eng. Mahmoud (Central Admin Director)',
            'email' => 'hoa_central@test.com',
            'password' => Hash::make('password'),
            'role' => Constans::ROLE_ADMINISTRATIVE_MANAGER,
            'status' => 'active',
        ]);

        Administratives::create([
            'title' => 'Central Administration HQ',
            'hoa' => $hoa3->id,
            'is_medical' => false,
        ]);
    }
}
