?php

require __DIR__ . '/vendor/autoload.php';

$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

try {
    $reflection = new ReflectionMethod('Filament\Pages\SettingsPage', 'form');
    echo "Method: " . $reflection->getName() . "\n";
    echo "Return Type: " . ($reflection->getReturnType() ?? 'None') . "\n";
    echo "Parameters: \n";
    foreach ($reflection->getParameters() as $param) {
        echo "  " . $param->getName() . ": " . ($param->getType() ?? 'None') . "\n";
    }
} catch (\Exception $e) {
    echo "Error: " . $e->getMessage();
}
