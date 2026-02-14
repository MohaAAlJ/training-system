<?php

namespace App\Exports;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\StreamedResponse;

class BackupExport
{
    /**
     * Export database using PHP-native approach (no mysqldump required)
     * Works reliably across all environments: Laragon, Herd, cPanel
     */
    public function export(): StreamedResponse
    {
        $database = config('database.connections.mysql.database');
        $timestamp = now()->format('Y-m-d_H-i-s');
        $filename = "backup_{$database}_{$timestamp}.sql";

        return response()->streamDownload(function () use ($database) {
            // Output SQL header
            echo "-- =============================================\n";
            echo "-- Database Backup: {$database}\n";
            echo "-- Generated: " . now()->format('Y-m-d H:i:s') . "\n";
            echo "-- PHP Native Export (No mysqldump required)\n";
            echo "-- =============================================\n\n";
            echo "SET NAMES utf8mb4;\n";
            echo "SET FOREIGN_KEY_CHECKS = 0;\n";
            echo "SET SQL_MODE = 'NO_AUTO_VALUE_ON_ZERO';\n\n";

            // Get all tables
            $tables = DB::select('SHOW TABLES');
            $tableKey = "Tables_in_{$database}";

            foreach ($tables as $table) {
                $tableName = $table->$tableKey;

                // Get CREATE TABLE statement
                $createTable = DB::select("SHOW CREATE TABLE `{$tableName}`");
                $createStatement = $createTable[0]->{'Create Table'} ?? '';

                echo "-- ----------------------------------------\n";
                echo "-- Table: {$tableName}\n";
                echo "-- ----------------------------------------\n";
                echo "DROP TABLE IF EXISTS `{$tableName}`;\n";
                echo $createStatement . ";\n\n";

                // Get table data in chunks to handle large tables
                $count = DB::table($tableName)->count();

                if ($count > 0) {
                    $chunkSize = 500;
                    $offset = 0;

                    while ($offset < $count) {
                        $rows = DB::table($tableName)->offset($offset)->limit($chunkSize)->get();

                        foreach ($rows as $row) {
                            $values = [];
                            foreach ((array)$row as $value) {
                                if (is_null($value)) {
                                    $values[] = 'NULL';
                                } else {
                                    $values[] = "'" . addslashes((string)$value) . "'";
                                }
                            }

                            $columns = array_keys((array)$row);
                            $columnList = '`' . implode('`, `', $columns) . '`';
                            $valueList = implode(', ', $values);

                            echo "INSERT INTO `{$tableName}` ({$columnList}) VALUES ({$valueList});\n";
                        }

                        $offset += $chunkSize;
                    }
                    echo "\n";
                }
            }

            echo "SET FOREIGN_KEY_CHECKS = 1;\n";
            echo "\n-- =============================================\n";
            echo "-- End of backup\n";
            echo "-- =============================================\n";

        }, $filename, [
            'Content-Type' => 'application/sql',
            'Content-Disposition' => "attachment; filename=\"{$filename}\"",
        ]);
    }

    /**
     * Save backup to storage disk instead of downloading
     */
    public function saveToStorage(): string
    {
        $database = config('database.connections.mysql.database');
        $timestamp = now()->format('Y-m-d_H-i-s');
        $filename = "backup_{$database}_{$timestamp}.sql";

        $sql = $this->generateBackupSql($database);

        Storage::disk('backup')->put($filename, $sql);

        return $filename;
    }

    /**
     * Generate backup SQL as a string
     */
    protected function generateBackupSql(string $database): string
    {
        $output = "";

        // SQL header
        $output .= "-- =============================================\n";
        $output .= "-- Database Backup: {$database}\n";
        $output .= "-- Generated: " . now()->format('Y-m-d H:i:s') . "\n";
        $output .= "-- PHP Native Export (No mysqldump required)\n";
        $output .= "-- =============================================\n\n";
        $output .= "SET NAMES utf8mb4;\n";
        $output .= "SET FOREIGN_KEY_CHECKS = 0;\n";
        $output .= "SET SQL_MODE = 'NO_AUTO_VALUE_ON_ZERO';\n\n";

        // Get all tables
        $tables = DB::select('SHOW TABLES');
        $tableKey = "Tables_in_{$database}";

        foreach ($tables as $table) {
            $tableName = $table->$tableKey;

            // Get CREATE TABLE statement
            $createTable = DB::select("SHOW CREATE TABLE `{$tableName}`");
            $createStatement = $createTable[0]->{'Create Table'} ?? '';

            $output .= "-- ----------------------------------------\n";
            $output .= "-- Table: {$tableName}\n";
            $output .= "-- ----------------------------------------\n";
            $output .= "DROP TABLE IF EXISTS `{$tableName}`;\n";
            $output .= $createStatement . ";\n\n";

            // Get table data
            $rows = DB::table($tableName)->get();

            foreach ($rows as $row) {
                $values = [];
                foreach ((array)$row as $value) {
                    if (is_null($value)) {
                        $values[] = 'NULL';
                    } else {
                        $values[] = "'" . addslashes((string)$value) . "'";
                    }
                }

                $columns = array_keys((array)$row);
                $columnList = '`' . implode('`, `', $columns) . '`';
                $valueList = implode(', ', $values);

                $output .= "INSERT INTO `{$tableName}` ({$columnList}) VALUES ({$valueList});\n";
            }

            if (count($rows) > 0) {
                $output .= "\n";
            }
        }

        $output .= "SET FOREIGN_KEY_CHECKS = 1;\n";
        $output .= "\n-- =============================================\n";
        $output .= "-- End of backup\n";
        $output .= "-- =============================================\n";

        return $output;
    }
}
