<?php

namespace App\Exports;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Filament\Notifications\Notification;

class BackupExport
{
    /**
     * Create a database backup and return it for download.
     * This method uses pure PHP/PDO instead of mysqldump,
     * so it works on any machine without external binaries.
     */
    public function export(): BinaryFileResponse
    {
        $timestamp = now()->format('Y-m-d-H-i-s');
        $filename = "backup_{$timestamp}.sql";
        $backupPath = "training-system/{$filename}";

        $disk = Storage::disk('backup');

        // Ensure the directory exists
        if (!$disk->exists('training-system')) {
            $disk->makeDirectory('training-system');
        }

        try {
            // Generate the SQL dump using PHP/PDO
            $sqlContent = $this->generateDatabaseDump();

            // Save to disk
            $disk->put($backupPath, $sqlContent);

            // Create the zip file
            $zipFilename = "backup_{$timestamp}.zip";
            $zipPath = "training-system/{$zipFilename}";
            $fullZipPath = $disk->path($zipPath);
            $fullSqlPath = $disk->path($backupPath);

            $zip = new \ZipArchive();
            if ($zip->open($fullZipPath, \ZipArchive::CREATE | \ZipArchive::OVERWRITE) === true) {
                $zip->addFile($fullSqlPath, $filename);
                $zip->close();

                // Delete the raw SQL file, keep only the zip
                $disk->delete($backupPath);
            }

            // Return the zip for download
            return response()->download($fullZipPath, $zipFilename);

        } catch (\Exception $e) {
            // Log the error
            \Log::error('Backup failed: ' . $e->getMessage());

            // Show error notification
            Notification::make()
                ->title('خطأ في النسخ الاحتياطي')
                ->body('فشل إنشاء النسخة الاحتياطية: ' . $e->getMessage())
                ->danger()
                ->send();

            abort(500, 'Backup failed: ' . $e->getMessage());
        }
    }

    /**
     * Generate a SQL dump of the database using PDO.
     */
    private function generateDatabaseDump(): string
    {
        $tables = DB::select('SHOW TABLES');
        $databaseName = config('database.connections.mysql.database');
        $tableKey = "Tables_in_{$databaseName}";

        $sql = "-- Database Backup\n";
        $sql .= "-- Generated: " . now()->toDateTimeString() . "\n";
        $sql .= "-- Database: {$databaseName}\n\n";
        $sql .= "SET FOREIGN_KEY_CHECKS=0;\n\n";

        foreach ($tables as $table) {
            $tableName = $table->$tableKey;

            // Get CREATE TABLE statement
            $createTable = DB::select("SHOW CREATE TABLE `{$tableName}`");
            $createTableSql = $createTable[0]->{'Create Table'} ?? '';

            $sql .= "-- Table: {$tableName}\n";
            $sql .= "DROP TABLE IF EXISTS `{$tableName}`;\n";
            $sql .= $createTableSql . ";\n\n";

            // Get table data
            $rows = DB::table($tableName)->get();

            if ($rows->count() > 0) {
                $columns = array_keys((array) $rows->first());
                $columnList = '`' . implode('`, `', $columns) . '`';

                foreach ($rows as $row) {
                    $values = array_map(function ($value) {
                        if (is_null($value)) {
                            return 'NULL';
                        }
                        return "'" . addslashes($value) . "'";
                    }, array_values((array) $row));

                    $valueList = implode(', ', $values);
                    $sql .= "INSERT INTO `{$tableName}` ({$columnList}) VALUES ({$valueList});\n";
                }
                $sql .= "\n";
            }
        }

        $sql .= "SET FOREIGN_KEY_CHECKS=1;\n";

        return $sql;
    }
}
