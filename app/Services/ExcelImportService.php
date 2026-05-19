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
    // ── College/Admin template column indices (0-indexed) ─────────────────
    private const COL_FULL_NAME      = 0;
    private const COL_NATIONAL_ID    = 1;
    private const COL_GENDER         = 2;
    private const COL_UNI_NUMBER     = 3;
    private const COL_MAJOR          = 4;
    private const COL_TRAINING_HOURS = 5;
    private const COL_GOVERNORATE    = 6;
    private const COL_STREET         = 7;
    private const COL_PHONE          = 8;
    private const COL_DOB            = 9;
    private const COL_ADMIN_UNIT     = 10;

    // ── MOH template column indices (0-indexed, no university_number) ──────
    // A=full_name, B=national_id, C=gender, D=training_hours,
    // E=governorate, F=street, G=phone, H=dob, I=admin_unit
    private const MOH_COL_FULL_NAME      = 0;
    private const MOH_COL_NATIONAL_ID    = 1;
    private const MOH_COL_GENDER         = 2;
    private const MOH_COL_TRAINING_HOURS = 3;
    private const MOH_COL_GOVERNORATE    = 4;
    private const MOH_COL_STREET         = 5;
    private const MOH_COL_PHONE          = 6;
    private const MOH_COL_DOB            = 7;
    private const MOH_COL_ADMIN_UNIT     = 8;

    /**
     * Parse file and return rows as associative arrays for preview.
     * If a trainee's national_id already exists in the DB, their stored
     * personal data is merged into the row so the repeater shows accurate info.
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

            // ── Merge existing trainee data ──────────────────────────────────
            // Bulk-load any trainees that already exist in the DB by national_id.
            // We then override personal fields so the preview shows the DB values
            // (same logic as the single-form's afterStateUpdated callback).
            $nationalIds = array_filter(array_column($data, 'national_id'));

            if (! empty($nationalIds)) {
                $existingTrainees = Trainee::whereIn('national_id', $nationalIds)
                    ->get()
                    ->keyBy('national_id');

                $user = Auth::user();

                foreach ($data as &$row) {
                    $nid     = $row['national_id'] ?? null;
                    $trainee = $nid ? ($existingTrainees[$nid] ?? null) : null;

                    if (! $trainee) {
                        $row['is_existing_trainee'] = false;
                        continue;
                    }

                    // Mark this row as having been enriched from the DB
                    $row['is_existing_trainee'] = true;

                    // Always override locked personal fields from DB.
                    $row['full_name']     = $trainee->full_name;
                    $row['gender']        = $trainee->gender;
                    $row['phone']         = $trainee->phone_number;
                    $row['street']        = $trainee->street;
                    $row['governorate_id'] = $trainee->governorate_id;
                    $row['dob']           = $trainee->dob
                        ? \Carbon\Carbon::parse($trainee->dob)->format('Y-m-d')
                        : $row['dob'];

                    // For non-MOH: also prefill educational info from their last application.
                    if (! $user?->isMinistry()) {
                        $query = Application::where('trainee_id', $trainee->id)
                            ->whereNotNull('institution_id');

                        if ($user?->isCollegeSupervisor()) {
                            $query->where('college_id', $user->college?->id);
                        }

                        $lastApp = $query->latest()->first();

                        if ($lastApp) {
                            // Only overwrite if the Excel row left the field empty.
                            $row['major_id']          = $row['major_id']          ?? $lastApp->major_id;
                            $row['university_number']  = $row['university_number']  ?? $lastApp->university_number;
                            $row['training_hours']     = $row['training_hours']     ?? $lastApp->training_hours;
                        }
                    }
                }
                unset($row); // break reference
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
    public function import(array $rows, int $chunkSize = 5): array
    {
        $summary = ['success' => 0, 'failed' => 0, 'errors' => []];
        $chunks = array_chunk($rows, $chunkSize);

        foreach ($chunks as $chunkIndex => $chunk) {
            foreach ($chunk as $indexInChunk => $row) {
                // Calculate absolute index (1-based because Excel starts at row 2 usually,
                // but the collector might vary. We use absolute index + 1 for user readability).
                $absoluteIndex = ($chunkIndex * $chunkSize) + $indexInChunk;

                try {
                    $this->processRow($row);
                    $summary['success']++;
                } catch (\Exception $e) {
                    $summary['failed']++;
                    $summary['errors'][] = "سطر " . ($absoluteIndex + 1) . " (" . ($row['full_name'] ?? '؟') . "): " . $e->getMessage();
                    Log::warning("Import row failed at absolute index " . $absoluteIndex, ['row' => $row, 'error' => $e->getMessage()]);
                }
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
            'street' => $row['street'] ?? null,
            'dob' => $row['dob'] ?? null,
            'gender' => $row['gender'] ?? null,
            'national_id' => $nationalId,
            'governorate_id' => $row['governorate_id'] ?? null,
        ];

        // Apply College Supervisor restrictions if applicable
        // Apply College Supervisor restrictions if applicable
        // Note: Institution/College are now on Application, not Trainee
        // We will pass these to the application in the next step
        $user = Auth::user();
        if ($user && $user->isCollegeSupervisor() && $user->college) {
            // Context passed via row or separate mechanism if needed,
            // but for now we just clean up Trainee
        }

        return Trainee::updateOrCreate(
            ['national_id' => $nationalId],
            $traineeData
        );
    }

    protected function upsertApplication(Trainee $trainee, array $row): void
    {
        $user = Auth::user();

        // Determine training type based on user role or data
        $trainingType = Application::PRACTICE;
        if ($user && ($user->isCollegeSupervisor() || !empty($row['university_number']))) {
            $trainingType = Application::UNIVERSITY;
        }

        // 3. Find upgradable "New" or "Initial" requests
        $upgradable = Application::where('trainee_id', $trainee->id)
            ->whereIn('status', [
                Application::STATUS_NEW,
                Application::STATUS_INITIAL_APPROVE,
            ])->first();

        // Prepare Application Data early to check college
        $institutionId = null;
        $collegeId = null;

        if ($user && $user->isCollegeSupervisor() && $user->college) {
            $institutionId = $user->college->institution_id;
            $collegeId = $user->college->id;

            if ($upgradable) {
                if ($upgradable->college_id !== $collegeId) {
                    throw new \Exception("هذا المتدرب لديه طلب تدريب مسجل من جامعة أو كلية أخرى.");
                }
            }
        }

        // 1. Check eligibility using the same service as the form (respects reapply settings)
        $service = app(\App\Services\Application\ApplicationStatusService::class);
        $eligibility = $service->checkEligibilityByTraineeId($trainee->id, $trainingType, $upgradable?->id);

        if (! $eligibility['can_apply']) {
            throw new \Exception("الطالب لديه طلب نشط بالفعل (حالة: " . $eligibility['message'] . ")");
        }

        // 2. Prepare remaining Application Data
        $appData = [
            'trainee_id'      => $trainee->id,
            'section_id'      => $row['section_id'] ?? null,
            'street'          => $row['street'] ?? null,
            'major_id'        => $row['major_id'] ?? null,
            'institution_id'  => $institutionId,
            'college_id'      => $collegeId,
            'status'          => $row['status'] ?? Application::STATUS_CONFIRMATION,
            'university_number' => $row['university_number'] ?? null,
            'training_hours'  => $row['training_hours'] ?? null,
            'training_type'   => $row['training_type'] ?? $trainingType,
            'days_note'       => $this->normalizeDaysNote($row['days_note'] ?? null),
        ];

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
        $user = Auth::user();

        if ($user?->isMinistry()) {
            return $this->mapMohRow($row);
        }

        return $this->mapCollegeRow($row);
    }

    /**
     * Map a raw row from the MOH template.
     */
    private function mapMohRow(array $row): array
    {
        $user      = Auth::user();
        $hasStreetColumn = array_key_exists(self::MOH_COL_ADMIN_UNIT, $row);
        $streetIndex = $hasStreetColumn ? self::MOH_COL_STREET : null;
        $phoneIndex = $hasStreetColumn ? self::MOH_COL_PHONE : 5;
        $dobIndex = $hasStreetColumn ? self::MOH_COL_DOB : 6;
        $adminIndex = $hasStreetColumn ? self::MOH_COL_ADMIN_UNIT : 7;
        $adminUnit = trim((string)($row[$adminIndex] ?? ''));

        // For linked MOH users inject their department so the repeater shows it pre-selected.
        // ->default() on a repeater field is overridden by explicit row data, so we set it here.
        $linkedDeptId = $user?->mohDepartment?->id;

        return [
            'full_name'           => trim((string)($row[self::MOH_COL_FULL_NAME] ?? '')),
            'national_id'         => trim((string)($row[self::MOH_COL_NATIONAL_ID] ?? '')),
            'gender'              => $this->resolveGender(trim((string)($row[self::MOH_COL_GENDER] ?? ''))),
            'university_number'   => null,
            'training_hours'      => trim((string)($row[self::MOH_COL_TRAINING_HOURS] ?? '')),
            'governorate_id'      => $this->resolveGovernorateId(trim((string)($row[self::MOH_COL_GOVERNORATE] ?? ''))),
            'street'              => $streetIndex === null ? null : trim((string)($row[$streetIndex] ?? '')),
            'phone'               => trim((string)($row[$phoneIndex] ?? '')),
            'dob'                 => trim((string)($row[$dobIndex] ?? '')),
            'administrative_unit' => $adminUnit,
            'administrative_id'   => $this->resolveAdministrativeId($adminUnit),
            'major_id'            => null,
            'department_id'       => $linkedDeptId,   // pre-fill for linked MOH
            'section_id'          => null,
            'status'              => Application::STATUS_CONFIRMATION,
            'days_note'           => ['training_days' => [], 'note' => null],
        ];
    }

    /**
     * Map a raw row from the College/Admin template.
     */
    private function mapCollegeRow(array $row): array
    {
        $hasStreetColumn = array_key_exists(self::COL_ADMIN_UNIT, $row);
        $streetIndex = $hasStreetColumn ? self::COL_STREET : null;
        $phoneIndex = $hasStreetColumn ? self::COL_PHONE : 7;
        $dobIndex = $hasStreetColumn ? self::COL_DOB : 8;
        $adminIndex = $hasStreetColumn ? self::COL_ADMIN_UNIT : 9;
        $adminUnit = trim((string)($row[$adminIndex] ?? ''));

        return [
            'full_name'         => trim((string)($row[self::COL_FULL_NAME] ?? '')),
            'national_id'       => trim((string)($row[self::COL_NATIONAL_ID] ?? '')),
            'gender'            => $this->resolveGender(trim((string)($row[self::COL_GENDER] ?? ''))),
            'university_number' => trim((string)($row[self::COL_UNI_NUMBER] ?? '')),
            'training_hours'    => trim((string)($row[self::COL_TRAINING_HOURS] ?? '')),
            'governorate_id'    => $this->resolveGovernorateId(trim((string)($row[self::COL_GOVERNORATE] ?? ''))),
            'street'            => $streetIndex === null ? null : trim((string)($row[$streetIndex] ?? '')),
            'phone'             => trim((string)($row[$phoneIndex] ?? '')),
            'dob'               => trim((string)($row[$dobIndex] ?? '')),
            'administrative_unit' => $adminUnit,
            'administrative_id' => $this->resolveAdministrativeId($adminUnit),
            'major_id'          => $this->resolveMajorId(trim((string)($row[self::COL_MAJOR] ?? ''))),
            'department_id'     => null,
            'section_id'        => null,
            'status'            => Application::STATUS_CONFIRMATION,
            'days_note'         => ['training_days' => [], 'note' => null],
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
        return Major::where('name', trim($name))->value('id');
    }

    /**
     * Resolve Governorate ID by name exact match.
     */
    private function resolveGovernorateId(?string $name): ?int
    {
        if (empty($name)) return null;
        return Governorate::where('name', trim($name))->value('id');
    }

    /**
     * Resolve Administrative ID by title (handles "Title - Governorate" format).
     */
    private function resolveAdministrativeId(?string $nameString): ?int
    {
        if (empty($nameString)) return null;
        $nameString = trim($nameString);

        // Split "Name - Governorate" if present
        $parts = explode(' - ', $nameString);
        $name = trim($parts[0]);

        // Attempt exact name match
        $adminId = Administrative::where('name', $name)->value('id');

        // Final fallback for the whole string
        return $adminId ?? Administrative::where('name', trim($nameString))->value('id');
    }

    /**
     * Normalize the days_note value from the repeater.
     * Returns null when no days were selected and no note was written,
     * otherwise returns the structured array for JSON storage.
     */
    private function normalizeDaysNote(mixed $value): ?array
    {
        if (! is_array($value)) {
            return null;
        }

        $days = array_values(array_filter(
            (array) ($value['training_days'] ?? []),
            fn($d) => $d !== null && $d !== ''
        ));

        $note = isset($value['note']) && $value['note'] !== '' ? $value['note'] : null;

        if (empty($days) && $note === null) {
            return null;
        }

        return [
            'training_days' => $days,
            'note'          => $note,
        ];
    }
}
