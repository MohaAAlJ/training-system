<?php

namespace App\Actions\Api;

use App\Models\Application;
use App\Models\Section;
use App\Models\Trainee;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class SyncMohApplicationAction
{
    public function execute(array $data, User $user): array
    {
        // 1. Resolve Department from the authenticated user
        $department = $user->mohDepartment;
        if (! $department) {
            return [
                'success' => false,
                'message' => 'Your account is not linked to any PRCS department.',
                'status_code' => 403,
            ];
        }
        $departmentId = $department->id;

        // 2. Resolve and Validate Section
        // Ensure the section exists and belongs to the department linked to this MOH user.
        $section = Section::where('id', $data['section_id'])
            ->whereHas('departments', fn($q) => $q->where('departments.id', $departmentId))
            ->first();

        if (! $section) {
            return [
                'success' => false,
                'message' => 'The provided Section ID is invalid or does not belong to your department.',
                'status_code' => 403,
            ];
        }

        if (! $section->active) {
            return [
                'success' => false,
                'message' => 'The requested section is currently inactive.',
                'status_code' => 403,
            ];
        }

        // Capacity check is intentionally bypassed for MOH integrations.
        // MOH applications are always accepted regardless of section capacity.

        try {
            // Use database transaction to ensure data integrity
            return DB::transaction(function () use ($data, $section, $user) {
                // 3. Normalize Phone Number
                $phoneNumber = $data['phone_number'];
                if (strlen($phoneNumber) === 9 && str_starts_with($phoneNumber, '5')) {
                    $phoneNumber = '970' . $phoneNumber;
                } elseif (strlen($phoneNumber) === 10 && str_starts_with($phoneNumber, '05')) {
                    $phoneNumber = '97' . $phoneNumber;
                }

                // Create or update trainee record
                $trainee = Trainee::updateOrCreate(
                    ['national_id' => $data['national_id']],
                    [
                        'full_name' => trim("{$data['first_name']} {$data['father_name']} {$data['grandfather_name']} {$data['family_name']}"),
                        'phone_number' => $phoneNumber,
                        'dob' => $data['dob'],
                        'gender' => $data['gender'],
                        'street' => $data['street'],
                    ]
                );

                $departmentId = $user->mohDepartment?->id;

                $activeApp = Application::where('trainee_id', $trainee->id)
                    ->whereIn('status', [
                        Application::STATUS_NEW,
                        Application::STATUS_INITIAL_APPROVE,
                        Application::STATUS_CONFIRMATION,
                        Application::STATUS_WAITING_LIST,
                        // Application::STATUS_STARTED_TRAINING
                    ])
                    ->whereHas('section.departments', fn ($q) => $q->where('departments.id', $departmentId))
                    ->first();

                $daysNote = [
                    'training_days' => array_keys(Application::ALL_DAYS),
                    'daily_hours' => 8,
                    'note' => 'مزامنة تلقائية - بوابة وزارة الصحة',
                ];

                if ($activeApp) {
                    $activeApp->fill([
                        'section_id' => $section->id,
                        'status' => Application::STATUS_WAITING_LIST,
                        'training_type' => Application::PRACTICE,
                        'start_date' => $data['start_date'],
                        'training_hours' => $data['training_hours'],
                        'days_note' => $daysNote,
                        'street' => $data['street'],
                    ]);
                    $activeApp->end_date = $activeApp->calculateEndDate();
                    $activeApp->save();
                    $application = $activeApp;
                } else {
                    $application = new Application([
                        'trainee_id' => $trainee->id,
                        'section_id' => $section->id,
                        'street' => $data['street'],
                        'training_type' => Application::PRACTICE,
                        'training_hours' => $data['training_hours'],
                        'start_date' => $data['start_date'],
                        'days_note' => $daysNote,
                        'status' => Application::STATUS_WAITING_LIST,
                    ]);
                    $application->end_date = $application->calculateEndDate();
                    $application->save();
                }

                return [
                    'success' => true,
                    'data' => [
                        'trainee_id' => $trainee->id,
                        'application_id' => $application->id,
                    ]
                ];
            });
        } catch (\Exception $e) {
            Log::error('MOH Sync Error: ' . $e->getMessage());
            return [
                'success' => false,
                'message' => 'حدث خطأ داخلي أثناء معالجة الطلب.',
                'status_code' => 500,
            ];
        }
    }
}
