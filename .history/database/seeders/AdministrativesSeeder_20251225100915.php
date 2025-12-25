<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Administrative;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

class AdministrativesSeeder extends Seeder
{
    public function run(): void
    {
        $password = Hash::make('123');

        $admins = [
            [
                'title' => 'مستشفى الأمل',
                'is_medical' => true,
                'hoa_email' => 'hoa_amal@system.com',
                'hoa_name' => 'مدير مستشفى الأمل',
                'hom_email' => 'hom_amal@system.com',
                'hom_name' => 'المدير الطبي للأمل',
            ],
            [
                'title' => 'مركز الدير',
                'is_medical' => true,
                'hoa_email' => 'hoa_deir@system.com',
                'hoa_name' => 'مدير مركز الدير',
                'hom_email' => 'hom_deir@system.com',
                'hom_name' => 'المدير الطبي للدير',
            ],
            [
                'title' => 'مدينة الأمل',
                'is_medical' => false,
                'hoa_email' => 'hoa_city@system.com',
                'hoa_name' => 'مدير مدينة الأمل',
                'hom_email' => null,
                'hom_name' => null,
            ],
        ];

        foreach ($admins as $adminData) {
            // HOA (مدير إداري)
            $hoa = User::firstOrCreate(
                ['email' => $adminData['hoa_email']],
                [
                    'name' => $adminData['hoa_name'],
                    'password' => $password,
                    'role' => User::ROLE_HOA,
                    'status' => 'active',
                ]
            );

            // HOM (مدير طبي)
            $medicalHoaId = null;
            if ($adminData['hom_email']) {
                $medicalHoa = User::firstOrCreate(
                    ['email' => $adminData['hom_email']],
                    [
                        'name' => $adminData['hom_name'],
                        'password' => $password,
                        'role' => User::ROLE_HOM,
                        'status' => 'active',
                    ]
                );
                $medicalHoaId = $medicalHoa->id;
            }

            Administrative::firstOrCreate(
                ['title' => $adminData['title']],
                [
                    'is_medical' => $adminData['is_medical'],
                    'user_id' => $hoa->id,
                    'medical_head_user_id' => $medicalHoaId,
                ]
            );
        }
    }
}
