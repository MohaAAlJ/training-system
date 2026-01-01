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
        // Get some seeded data
        $shifa = Administrative::where('title', 'مستشفى الشفاء الطبي')->first();
        $amal = Administrative::where('title', 'مستشفى الأمل التخصصي')->first();
        $itDept = Departments::where('title', 'تكنولوجيا المعلومات')->first();
        $pharmacyDept = Departments::where('title', 'الصيدلة')->first();
        $nursingDept = Departments::where('title', 'التمريض')->first();

        // Check if data exists to avoid errors if seeders run individually or out of order
        if (!$shifa || !$pharmacyDept) return;

        $sections = [
            // Shifa Hospital Sections
            [
                'name_location' => 'صيدلية العيادات الخارجية',
                'admin_id' => $shifa->id,
                'dept_id' => $pharmacyDept->id,
                'hos_name' => 'د. صيدلي عيادات',
                'capacity' => 5,
            ],
            [
                'name_location' => 'صيدلية الطوارئ',
                'admin_id' => $shifa->id,
                'dept_id' => $pharmacyDept->id,
                'hos_name' => 'د. صيدلي طوارئ',
                'capacity' => 3,
            ],
            [
                'name_location' => 'قسم باطنة رجال',
                'admin_id' => $shifa->id,
                'dept_id' => $nursingDept->id,
                'hos_name' => 'أ. حكيم باطنة',
                'capacity' => 10,
            ],
            [
                'name_location' => 'وحدة تكنولوجيا المعلومات - الشفاء',
                'admin_id' => $shifa->id,
                'dept_id' => $itDept->id,
                'hos_name' => 'م. دعم فني',
                'capacity' => 2,
            ],

            // Amal Hospital Sections
            [
                'name_location' => 'الصيدلية المركزية - الأمل',
                'admin_id' => $amal->id,
                'dept_id' => $pharmacyDept->id,
                'hos_name' => 'د. صيدلي أمل',
                'capacity' => 4,
            ],
            [
                'name_location' => 'قسم الجراحة العامة',
                'admin_id' => $amal->id,
                'dept_id' => $nursingDept->id,
                'hos_name' => 'أ. حكيم جراحة',
                'capacity' => 8,
            ],
        ];

        foreach ($sections as $secData) {
            $hos = User::create([
                'name' => $secData['hos_name'],
                'email' => 'hos_' . uniqid() . '@system.com',
                'password' => Hash::make('password'),
                'role' => Constans::ROLE_SECTION_HEAD,
                'status' => 'active',
            ]);

            Sections::create([
                'name_location' => $secData['name_location'],
                'administrative_id' => $secData['admin_id'],
                'department_id' => $secData['dept_id'],
                'hos' => $hos->id,
                'total_capacity' => $secData['capacity'],
                'status' => true,
            ]);
        }
    }
}
