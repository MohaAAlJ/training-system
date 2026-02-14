<?php

namespace App\Services;

use App\Models\Administrative;
use App\Models\Application;
use App\Models\Governorate;
use App\Models\Trainee;
use App\Models\Major;
// use App\Enums\ApplicationStatus;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Auth;
use PhpOffice\PhpSpreadsheet\IOFactory;

class ExcelImportService
{
    // Column Index Constants (0-indexed)
    private const COL_FULL_NAME = 0;
    private const COL_NATIONAL_ID = 1;
    private const COL_GENDER = 2;
    private const COL_UNI_NUMBER = 3;
    private const COL_GOVERNORATE = 4;
    private const COL_PHONE = 5;
    private const COL_DOB = 6;
    private const COL_ADMIN_UNIT = 7;

    /**
     * Parse file and return rows as associative arrays for preview.
     */
    public function getRowsForPreview(string $filePath): array
    {
        try {
            $spreadsheet = IOFactory::load($filePath);
            $rows = $spreadsheet->getSheet(0)->toArray();
            $data = [];

            // Skip Header (Row 1)
            for ($i = 1; $i < count($rows); $i++) {
                $row = $rows[$i];
                if (empty($row[self::COL_FULL_NAME]) && empty($row[self::COL_NATIONAL_ID])) {
                    continue;
                }

                $data[] = $this->mapRawRowToData($row);
            }

            return $data;
        } catch (\Exception $e) {
            Log::error("Failed to parse import file: " . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Process multiple rows and return a summary.
     */
    public function import(array $rows): array
    {
        $summary = ['success' => 0, 'failed' => 0, 'errors' => []];

        foreach ($rows as $index => $row) {
            try {
                $this->processRow($row);
                $summary['success']++;
            } catch (\Exception $e) {
                $summary['failed']++;
                $summary['errors'][] = "سطر " . ($index + 1) . " (" . ($row['full_name'] ?? '؟') . "): " . $e->getMessage();
                Log::warning("Import row failed: " . $e->getMessage(), ['row' => $row]);
            }
        }

        return $summary;
    }

    /**
     * Process a single import row within a transaction.
     */
    protected function processRow(array $row): void
    {
        DB::transaction(function () use ($row) {
            $trainee = $this->upsertTrainee($row);
            $this->upsertApplication($trainee, $row);
        });
    }

    /**
     * Create or update student record.
     */
    protected function upsertTrainee(array $row): Trainee
    {
        $nationalId = $row['national_id'] ?? null;
        if (!$nationalId) {
            throw new \Exception("رقم الهوية مفقود");
        }

        $traineeData = [
            'full_name' => $row['full_name'],
            'phone_number' => $row['phone'] ?? null,
            'dob' => $row['dob'] ?? null,
            'gender' => $row['gender'] ?? null,
            'national_id' => $nationalId,
            'university_number' => $row['university_number'] ?? null,
            'major_id' => $row['major_id'] ?? null,
            'governorate_id' => $row['governorate_id'] ?? null,
        ];

        // Apply College Supervisor restrictions if applicable
        $user = Auth::user();
        if ($user && $user->isCollegeSupervisor() && $user->college) {
            $traineeData['college_id'] = $user->college->id;
            $traineeData['institution_id'] = $user->college->institution_id;
        }

        return Trainee::updateOrCreate(
            ['national_id' => $nationalId],
            $traineeData
        );
    }

    /**
     * Handle application logic (check conflicts, upgrade existing or create new).
     */
    protected function upsertApplication(Trainee $trainee, array $row): void
    {
        // 1. Check for Active Conflicts
        $conflicting = Application::where('trainee_id', $trainee->id)
            ->whereIn('status', [
                Application::STATUS_CONFIRMATION,
                Application::STATUS_WAITING_LIST,
                Application::STATUS_STARTED_TRAINING,
                Application::STATUS_ENDED_TRAINING,
            ])->first();

        if ($conflicting) {
            throw new \Exception("الطالب لديه طلب نشط بالفعل (حالة: " . $conflicting->status_message . ")");
        }

        // 2. Prepare Application Data (only section_id - no administrative_id/department_id)
        $appData = [
            'trainee_id' => $trainee->id,
            'section_id' => $row['section_id'] ?? null,
            'major_id' => $row['major_id'] ?? null,
            'status' => $row['status'] ?? Application::STATUS_CONFIRMATION,
            'university_number' => $row['university_number'] ?? null,
            'training_type' => Application::UNIVERSITY,
        ];

        // 3. Find upgradable "New" or "Initial" requests
        $upgradable = Application::where('trainee_id', $trainee->id)
            ->whereIn('status', [
                Application::STATUS_NEW,
                Application::STATUS_INITIAL_APPROVE,
            ])->first();

        if ($upgradable) {
            $upgradable->update($appData);
        } else {
            Application::create($appData);
        }
    }

    /**
     * Map a raw Excel row to our internal associative array structure.
     */
    protected function mapRawRowToData(array $row): array
    {
        $adminUnit = trim((string)($row[self::COL_ADMIN_UNIT] ?? ''));

        return [
            'full_name' => trim((string)($row[self::COL_FULL_NAME] ?? '')),
            'national_id' => trim((string)($row[self::COL_NATIONAL_ID] ?? '')),
            'gender' => $this->resolveGender(trim((string)($row[self::COL_GENDER] ?? ''))),
            'university_number' => trim((string)($row[self::COL_UNI_NUMBER] ?? '')),
            'phone' => trim((string)($row[self::COL_PHONE] ?? '')),
            'governorate_id' => $this->resolveGovernorateId(trim((string)($row[self::COL_GOVERNORATE] ?? ''))),
            'dob' => trim((string)($row[self::COL_DOB] ?? '')),
            'administrative_unit' => $adminUnit,
            'administrative_id' => $this->resolveAdministrativeId($adminUnit),
            'major_id' => null,
            'department_id' => null,
            'section_id' => null,
            'status' => Application::STATUS_CONFIRMATION,
        ];
    }

    /**
     * Resolve Gender enum from text value.
     */
    private function resolveGender(?string $value): ?int
    {
        if (empty($value)) return null;

        return match (trim($value)) {
            'ذكر' => \App\Enums\Gender::MALE->value,
            'أنثى' => \App\Enums\Gender::FEMALE->value,
            default => null,
        };
    }

    /**
     * Resolve Major ID by name exact match.
     */
    private function resolveMajorId(?string $name): ?int
    {
        if (empty($name)) return null;
        return Major::where('name', $name)->value('id');
    }

    /**
     * Resolve Governorate ID by name exact match.
     */
    private function resolveGovernorateId(?string $name): ?int
    {
        if (empty($name)) return null;
        return Governorate::where('name', $name)->value('id');
    }

    /**
     * Resolve Administrative ID by title (handles "Title - Governorate" format).
     */
    private function resolveAdministrativeId(?string $nameString): ?int
    {
        if (empty($nameString)) return null;

        // Split "Name - Governorate" if present
        $parts = explode(' - ', $nameString);
        $name = trim($parts[0]);

        // Attempt exact name match
        $adminId = Administrative::where('name', $name)->value('id');

        // Final fallback for the whole string
        return $adminId ?? Administrative::where('name', trim($nameString))->value('id');
    }
}
