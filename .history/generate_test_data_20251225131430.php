<?php
require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\Trainee;
use App\Models\Application;
use App\Models\Section;
use Illuminate\Support\Facades\DB;

$sectionId = 3;
$section = Section::find($sectionId);

if (!$section) {
    die("Section $sectionId not found\n");
}

echo "Starting to generate 10 applications for section: " . $section->name_location . " (ID: $sectionId)\n";

try {
    DB::beginTransaction();

    for ($i = 1; $i <= 10; $i++) {
        $trainee = Trainee::create([
            'full_name' => "Test Trainee $i for Capacity Test",
            'national_id' => '1000000' . str_pad($i, 2, '0', STR_PAD_LEFT),
            'phone_number' => '9705900000' . str_pad($i, 2, '0', STR_PAD_LEFT),
            'governorate_id' => 1,
            'street' => 'Test Street',
            'dob' => '2000-01-01',
            'institution_id' => 1,
            'college_id' => 1,
            'major_id' => 1,
        ]);

        Application::create([
            'trainee_id' => $trainee->id,
            'section_id' => $sectionId,
            'department_id' => $section->department_id,
            'administrative_id' => $section->administrative_id,
            'training_type' => Application::TRAINING_TYPE_UNIVERSITY,
            'status' => Application::STATUS_STARTED_TRAINING, // Status 5 counts towards capacity
            'start_date' => now(),
            'end_date' => now()->addMonths(3),
        ]);

        echo "Created application $i\n";
    }

    DB::commit();
    echo "Successfully created 10 applications. Section should now be full.\n";

    $stats = $section->getCapacityStats();
    echo "Current Stats: Used: {$stats['used']}, Total: {$stats['total']}, Full: " . ($stats['is_full'] ? 'YES' : 'NO') . "\n";
} catch (\Exception $e) {
    DB::rollBack();
    echo "Error: " . $e->getMessage() . "\n";
}
