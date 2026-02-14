<?php
require __DIR__ . '/../../../vendor/autoload.php';
$app = require_once __DIR__ . '/../../../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\Trainee;
use App\Models\Application;
use App\Models\Section;
use App\Models\Administrative;
use Illuminate\Support\Facades\DB;

$administrative_id = 1; // Administrative ID
$section_id = 1;        // Section ID

// Get the section
$section = Section::find($section_id);

if (!$section) {
    die("Section $section_id not found\n");
}

// Get the administrative
$administrative = Administrative::find($administrative_id);

if (!$administrative) {
    die("Administrative $administrative_id not found\n");
}

echo "Starting to generate 10 applications\n";
echo "Administrative: " . $administrative->name . " (ID: $administrative_id)\n";
echo "Section: " . $section->name . " (ID: $section_id)\n";
echo "Section Capacity: " . $section->capacity . "\n\n";

try {
    DB::beginTransaction();

    for ($i = 1; $i <= 10; $i++) {
        // Generate unique random national ID (9 digits)
        do {
            $national_id = str_pad(random_int(100000000, 999999999), 9, '0', STR_PAD_LEFT);
        } while (Trainee::where('national_id', $national_id)->exists());

        // Generate unique phone number
        do {
            $phone_number = '9705' . str_pad(random_int(90000000, 99999999), 8, '0', STR_PAD_LEFT);
        } while (Trainee::where('phone_number', $phone_number)->exists());

        $trainee = Trainee::create([
            'full_name' => "Test Trainee $i (Random " . uniqid() . ")",
            'national_id' => $national_id,
            'phone_number' => $phone_number,
            'governorate_id' => 1,
            'street' => 'Test Street ' . $i,
            'dob' => now()->subYears(random_int(20, 35))->format('Y-m-d'),
            'institution_id' => 1,
            'college_id' => 1,
            'major_id' => 1,
        ]);

        Application::create([
            'trainee_id' => $trainee->id,
            'section_id' => $section_id,
            'training_type' => Application::UNIVERSITY,
            'status' => Application::STATUS_STARTED_TRAINING, // Status 5 counts towards capacity
            'start_date' => now(),
            'end_date' => now()->addMonths(3),
        ]);

        echo "✓ Created application $i - National ID: $national_id\n";
    }

    DB::commit();
    echo "\n✓ Successfully created 10 applications!\n";

    $stats = $section->getCapacityStats();
    echo "\nCapacity Stats:\n";
    echo "  - Total: " . $stats['total'] . "\n";
    echo "  - Used: " . $stats['used'] . "\n";
    echo "  - Available: " . $stats['available'] . "\n";
    echo "  - Is Full: " . ($stats['is_full'] ? 'YES' : 'NO') . "\n";
} catch (\Exception $e) {
    DB::rollBack();
    echo "✗ Error: " . $e->getMessage() . "\n";
    echo "  File: " . $e->getFile() . " Line: " . $e->getLine() . "\n";
}
