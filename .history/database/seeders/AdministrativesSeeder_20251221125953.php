<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Administrative;
use App\Models\User;
use App\Helpers\Constans;
use Illuminate\Support\Facades\Hash;

class AdministrativesSeeder extends Seeder
{
    public function run(): void
    {
        $admins = [
            [
                'title' => 'مستشفى الشفاء الطبي',
                'is_medical' => true,
                'hoa_name' => 'د. محمد المدير',
                'medical_hoa_name' => 'د. سمير الطبي',
            ],
            [
                'title' => 'مستشفى الأمل التخصصي',
                'is_medical' => true,
                'hoa_name' => 'د. خالد أمل',
                'medical_hoa_name' => 'د. يوسف طبي',
            ],
            [
                'title' => 'مديرية الصحة - غزة',
                'is_medical' => false,
                'hoa_name' => 'أ. أحمد الإداري',
                'medical_hoa_name' => null,
            ],
            [
                'title' => 'مركز النور للرعاية الأولية',
                'is_medical' => true,
                'hoa_name' => 'د. محمود نور',
                'medical_hoa_name' => 'د. علي رعاية',
            ],
        ];

        foreach ($admins as $adminData) {
            // Create HOA User
            $hoa = User::create([
                'name' => $adminData['hoa_name'],
                'email' => 'hoa_' . uniqid() . '@system.com',
                'password' => Hash::make('password'),
                'role' => Constans::ROLE_HOA,
                'status' => 'active',
            ]);

            $medicalHoaId = null;
            if ($adminData['medical_hoa_name']) {
                $medicalHoa = User::create([
                    'name' => $adminData['medical_hoa_name'],
                    'email' => 'med_hoa_' . uniqid() . '@system.com',
                    'password' => Hash::make('password'),
                    'role' => Constans::ROLE_HOM,
                    'status' => 'active',
                ]);
                $medicalHoaId = $medicalHoa->id;
            }

            Administrative::create([
                'title' => $adminData['title'],
                'is_medical' => $adminData['is_medical'],
                'hoa' => $hoa->id,
                'medical_hoa' => $medicalHoaId,
            ]);
        }
    }
}
