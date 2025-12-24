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

        static::updated(function (self $application) {
            // Check if status changed
            if ($application->isDirty('status')) {
                $newStatus = (int) $application->status;

                // Notification to send
                $notificationToSend = null;

                // Status 5 : Started Training
                if ($newStatus === Constans::STATUS_STRATED_TRAINING) {
                    $notificationToSend = new \App\Notifications\TraineeStartedNotification($application);
                }
                // Status 6 : Ended Training
                elseif ($newStatus === Constans::STATUS_ENDED_TRAINING) {
                    $notificationToSend = new \App\Notifications\TraineeFinishedNotification($application);
                }
                // Status 3 : Confirmed (Notify GTM and Admin)
                elseif ($newStatus === Constans::STATUS_CONFIRMATION) {
                    $notification = new \App\Notifications\ApplicationConfirmedNotification($application);
                    $recipients = User::whereIn('role', [Constans::ROLE_GTM, Constans::ROLE_ADMIN])
                        ->where('status', 'active')
                        ->get();

                    foreach ($recipients as $user) {
                        try {
                            $user->notify($notification);
                        } catch (\Throwable $e) {
                            logger()->error('Failed to notify GTM/Admin for confirmation: ' . $e->getMessage());
                        }
                    }
                }

                if ($notificationToSend) {
                     // Gather recipients: HOA, HOM, Dept Head, Section Head
                     $recipients = collect();

                     // Get related entities
                     $section = $application->section;
                     $department = $application->department;
                     $administrative = $application->administrative;

                     // 1. Role Section
                     if ($section && $section->user_id) {
                         $sectionHead = $section->user;
                         if ($sectionHead && $sectionHead->role === Constans::ROLE_SECTION) {
                             $recipients->push($sectionHead);
                         }
                     }

                     // 2. Role Department
                     if ($department) {
                         $deptHead = $department->headOfDepartment;
                         if ($deptHead && $deptHead->id && $deptHead->role === Constans::ROLE_DEPARTMENT) {
                             $recipients->push($deptHead);
                         }
                         if ($department->user_id) {
                            $deptUser = $department->user;
                            if ($deptUser && $deptUser->role === Constans::ROLE_DEPARTMENT) {
                                $recipients->push($deptUser);
                            }
                         }
                     }

                     // 3. Role HOA (Head of Administration)
                     // If defined on administrative
                     if ($administrative && $administrative->user_id) {
                         $hoaUser = $administrative->user;
                         if ($hoaUser && $hoaUser->role === Constans::ROLE_HOA) {
                             $recipients->push($hoaUser);
                         }
                     }
                     // If Section -> Administrative -> User
                     if ($section && $section->administrative_id) {
                        $sectionAdmin = $section->administrative;
                        if ($sectionAdmin && $sectionAdmin->user_id) {
                            $hoaUser = $sectionAdmin->user;
                            if ($hoaUser && $hoaUser->role === Constans::ROLE_HOA) {
                                $recipients->push($hoaUser);
                            }
                        }
                    }

                     // 4. Role HOM (Head of Medical) - ONLY if medical Department/Administrative
                     $isMedical = false;
                     if ($department && $department->is_medical) $isMedical = true;
                     if ($administrative && $administrative->is_medical) $isMedical = true;

                     if ($isMedical) {
                         // Notify all HOM users (Broad notification like created?)
                         // Request says: "ROLE_HOM gets a notification for all the section in a medical department"
                         // This implies broadly notifying HOMs about activity in their domain.
                         $homUsers = User::where('role', Constans::ROLE_HOM)->get();
                         $recipients = $recipients->merge($homUsers);

                         if ($administrative && $administrative->medical_head_user_id) {
                             $medicalHead = $administrative->medicalHead;
                             if ($medicalHead) $recipients->push($medicalHead);
                         }
                         if ($department && $department->medical_head_user_id) {
                            $deptMedicalHead = $department->medicalHead;
                            if ($deptMedicalHead) $recipients->push($deptMedicalHead);
                        }
                     }

                     // Deduplicate and filter
                     $recipients = $recipients->unique('id')->filter(function ($user) {
                        return $user && $user->id && $user->status === 'active';
                     })->values();

                     // Send
                     foreach ($recipients as $user) {
                        try {
                            $user->notify($notificationToSend);
                        } catch (\Throwable $e) {
                            logger()->error('Failed to notify user for status change ' . $user->id . ': ' . $e->getMessage());
                        }
                     }
                }
            }
        });
}
}
