<?php
require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\Section;

$section = Section::find(3);
if ($section) {
    echo json_encode($section->toArray(), JSON_PRETTY_PRINT);
} else {
    echo "Section 3 not found";
}
