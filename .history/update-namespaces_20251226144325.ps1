# PowerShell script to update Filament v4 namespaces
# This script replaces old namespace patterns with new Resource-suffixed patterns

$basePath = "c:\Users\adels\Herd\training-system\app\Filament\Resources"

# Define namespace mappings (old pattern => new pattern)
$namespaceMappings = @{
    "App\\Filament\\Resources\\Department\\" = "App\\Filament\\Resources\\DepartmentResource\\"
    "App\\Filament\\Resources\\Section\\" = "App\\Filament\\Resources\\SectionResource\\"
    "App\\Filament\\Resources\\Trainee\\" = "App\\Filament\\Resources\\TraineeResource\\"
    "App\\Filament\\Resources\\User\\" = "App\\Filament\\Resources\\UserResource\\"
}

# Function to update namespaces in a file
function Update-Namespaces {
    param(
        [string]$FilePath
    )
    
    if (Test-Path $FilePath) {
        $content = Get-Content $FilePath -Raw
        $originalContent = $content
        $updated = $false
        
        foreach ($old in $namespaceMappings.Keys) {
            $new = $namespaceMappings[$old]
            if ($content -match [regex]::Escape($old)) {
                $content = $content -replace [regex]::Escape($old), $new
                $updated = $true
            }
        }
        
        if ($updated) {
            Set-Content -Path $FilePath -Value $content -NoNewline
            Write-Host "Updated: $FilePath" -ForegroundColor Green
        }
    }
}

# Get all PHP files in the Resources directory
$phpFiles = Get-ChildItem -Path $basePath -Filter "*.php" -Recurse

Write-Host "Found $($phpFiles.Count) PHP files to process..." -ForegroundColor Cyan
Write-Host ""

foreach ($file in $phpFiles) {
    Update-Namespaces -FilePath $file.FullName
}

Write-Host ""
Write-Host "Namespace updates complete!" -ForegroundColor Green
Write-Host "Running composer dump-autoload..." -ForegroundColor Cyan
