<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use App\Models\Departments;
use App\Models\Administrative;
use App\Models\User;
use App\Helpers\Constans;
use App\Notifications\ApplicationCreated as ApplicationCreatedNotification;

class Applications extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'applications';
    protected $fillable = [
        'id',
        'training_type',
        'duration',
        'section_id',
        'department_id',
        'administrative_id',
        'start_date',
        'end_date',
        'status',
        'trainee_id',
        'application_letter',
        'tags',
        'accepted_at',
        'street',
        'duration',
    ];
    protected $casts = [
        'start_date' => 'date',
        'end_date' => 'date',
        'accepted_at' => 'datetime',
        'status' => 'string',
        'duration' => 'integer',
        'training_type' => 'integer',
    ];

    public function getTrainingTypeLabelAttribute(): string
    {
        return Constans::TRAINING_TYPES[$this->training_type] ?? 'غير محدد';
    }

    public function trainee()
    {
        return $this->belongsTo(Trainees::class, 'trainee_id');
    }

    public function section()
    {
        return $this->belongsTo(Sections::class, 'section_id');
    }

    public function department()
    {
        return $this->belongsTo(Departments::class, 'department_id');
    }

    public function administrative()
    {
        return $this->belongsTo(Administrative::class, 'administrative_id');
    }

    public function institution()
    {
        return $this->belongsTo(Institution::class);
    }

    public function major()
    {
        return $this->belongsTo(Major::class);
    }

    /**
     * Notification recipients by role:
     * - ROLE_ADMIN (1): Gets notifications for EVERYTHING
     * - ROLE_DEPARTMENT (2): Gets notified when application is for THEIR department
     * - ROLE_SECTION (3): Gets notified when application is for THEIR section
     * - ROLE_HOA (6): Gets notified for anything in THEIR administrative
     * - ROLE_HOM (7): Gets notified when anything in MEDICAL department gets added
     * - ROLE_GTM (8): Gets notifications for ANY application added
     */
    protected static function booted()
    {
        static::created(function (self $application) {
            // Gather recipients according to role-based rules
            $recipients = collect();

            // ============================================================
            // ROLE_ADMIN (1): Gets notifications for EVERYTHING
            // ============================================================
            $superAdmins = User::where('role', Constans::ROLE_ADMIN)->get();
            $recipients = $recipients->merge($superAdmins);

            // ============================================================
            // ROLE_GTM (8): Gets notifications for ANY application added
            // ============================================================
            $gtmUsers = User::where('role', Constans::ROLE_GTM)->get();
            $recipients = $recipients->merge($gtmUsers);

            // Get related entities
            $section = $application->section;
            $department = $application->department;
            $administrative = $application->administrative;

            // ============================================================
            // ROLE_SECTION (3): Gets notified when application is for THEIR section
            // ============================================================
            if ($section && $section->user_id) {
                $sectionHead = $section->user;
                if ($sectionHead && $sectionHead->role === Constans::ROLE_SECTION) {
                    $recipients->push($sectionHead);
                }
            }

            // ============================================================
            // ROLE_DEPARTMENT (2): Gets notified when application is for THEIR department
            // ============================================================
            if ($department) {
                // Check head_of_department relationship
                $deptHead = $department->headOfDepartment;
                if ($deptHead && $deptHead->id && $deptHead->role === Constans::ROLE_DEPARTMENT) {
                    $recipients->push($deptHead);
                }

                // Also check user_id on department (some departments use this)
                if ($department->user_id) {
                    $deptUser = $department->user;
                    if ($deptUser && $deptUser->role === Constans::ROLE_DEPARTMENT) {
                        $recipients->push($deptUser);
                    }
                }
            }

            // ============================================================
            // ROLE_HOA (6): Gets notified for anything in THEIR administrative
            // ============================================================
            if ($administrative && $administrative->user_id) {
                $hoaUser = $administrative->user;
                if ($hoaUser && $hoaUser->role === Constans::ROLE_HOA) {
                    $recipients->push($hoaUser);
                }
            }

            // Also get all HOA users who are linked to the administrative via the section
            if ($section && $section->administrative_id) {
                $sectionAdmin = $section->administrative;
                if ($sectionAdmin && $sectionAdmin->user_id) {
                    $hoaUser = $sectionAdmin->user;
                    if ($hoaUser && $hoaUser->role === Constans::ROLE_HOA) {
                        $recipients->push($hoaUser);
                    }
                }
            }

            // ============================================================
            // ROLE_HOM (7): Gets notified when anything in MEDICAL department gets added
            // ============================================================
            $isMedical = false;

            // Check if department is medical
            if ($department && $department->is_medical) {
                $isMedical = true;
            }

            // Check if administrative is medical
            if ($administrative && $administrative->is_medical) {
                $isMedical = true;
            }

            if ($isMedical) {
                // Notify all HOM users
                $homUsers = User::where('role', Constans::ROLE_HOM)->get();
                $recipients = $recipients->merge($homUsers);

                // Also notify the specific medical head if set on administrative
                if ($administrative && $administrative->medical_head_user_id) {
                    $medicalHead = $administrative->medicalHead;
                    if ($medicalHead) {
                        $recipients->push($medicalHead);
                    }
                }

                // Notify medical head on department if set
                if ($department && $department->medical_head_user_id) {
                    $deptMedicalHead = $department->medicalHead;
                    if ($deptMedicalHead) {
                        $recipients->push($deptMedicalHead);
                    }
                }
            }

            // ============================================================
            // Deduplicate and filter valid users
            // ============================================================
            $recipients = $recipients->unique('id')->filter(function ($user) {
                return $user && $user->id && $user->status === 'active';
            })->values();

            if ($recipients->isEmpty()) {
                return;
            }

            // ============================================================
            // Send notifications
            // ============================================================
            foreach ($recipients as $user) {
                try {
                    $user->notify(new ApplicationCreatedNotification($application));
                } catch (\Throwable $e) {
                    // Log error to help diagnose notification issues
                    logger()->error('Failed to notify user ' . $user->id . ': ' . $e->getMessage());
                }
            }
        });
    }
}
