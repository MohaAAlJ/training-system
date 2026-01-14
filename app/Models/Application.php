<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use App\Models\Department;
use App\Models\Administrative;
use App\Models\User;
use App\Models\Trainee;
use App\Models\Section;
use App\Models\Institution;
use App\Models\Major;
use App\Models\College;
use App\Notifications\ApplicationCreated as ApplicationCreatedNotification;
use App\Notifications\InitialApprovalNotification;

class Application extends Model
{
    use HasFactory, SoftDeletes;

    /**
     * Application status constants
     */
    public const STATUS_NEW = 1;
    public const STATUS_INITIAL_APPROVE = 2;
    public const STATUS_CONFIRMATION = 3;
    public const STATUS_WAITING_LIST = 4;
    public const STATUS_STARTED_TRAINING = 5;
    public const STATUS_ENDED_TRAINING = 6;
    public const STATUS_REJECTED = 7;
    public const STATUS_DROPPED = 8;
    public const STATUS_UNKNOWN = 9;

    public const STATUSES = [
        self::STATUS_NEW,
        self::STATUS_INITIAL_APPROVE,
        self::STATUS_CONFIRMATION,
        self::STATUS_WAITING_LIST,
        self::STATUS_STARTED_TRAINING,
        self::STATUS_ENDED_TRAINING,
        self::STATUS_REJECTED,
        self::STATUS_DROPPED,
        self::STATUS_UNKNOWN,
    ];

    /**
     * Training Type constants
     */
    public const TRAINING_TYPE_UNIVERSITY = 1;
    public const TRAINING_TYPE_PRACTICE = 2;

    public const TRAINING_TYPES = [
        self::TRAINING_TYPE_UNIVERSITY => 'تدريب جامعي',
        self::TRAINING_TYPE_PRACTICE => 'مزاولة مهنة',
    ];

    /**
     * Status messages in Arabic - used for both frontend and API responses
     * Note: STATUS_INITIAL_APPROVE message varies by training type - see STATUS_INITIAL_APPROVE_MESSAGES
     */
    public const STATUS_MESSAGES = [
        self::STATUS_NEW => 'لديك طلب قيد الانتظار',
        self::STATUS_INITIAL_APPROVE => 'لديك طلب في انتظار القبول الجامعي',
        self::STATUS_CONFIRMATION => 'لديك طلب في انتظار التأكيد',
        self::STATUS_WAITING_LIST => 'لديك طلب في قائمة الانتظار',
        self::STATUS_STARTED_TRAINING => 'لديك تدريب نشط',
        self::STATUS_ENDED_TRAINING => 'لديك طلب منتهي',
        self::STATUS_REJECTED => 'لديك طلب سابق لايمكنك اصادر طلب جديد',
        self::STATUS_DROPPED => 'لديك طلب منسحب',
    ];

    /**
     * Training type-specific messages for STATUS_INITIAL_APPROVE
     */
    public const STATUS_INITIAL_APPROVE_MESSAGES = [
        self::TRAINING_TYPE_UNIVERSITY => 'لديك طلب في انتظار القبول الجامعي',
        self::TRAINING_TYPE_PRACTICE => 'لديك طلب في انتظار قبول الوزارة',
    ];

    protected $table = 'applications';
    protected $fillable = [
        'id',
        'uuid',
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
    ];
    protected $casts = [
        'start_date' => 'date',
        'end_date' => 'date',
        'accepted_at' => 'datetime',
        'status' => 'integer',
        'duration' => 'integer',
        'training_type' => 'integer',
    ];

    public function getTrainingTypeLabelAttribute(): string
    {
        return self::TRAINING_TYPES[$this->training_type] ?? 'غير محدد';
    }

    /**
     * Get status message in Arabic
     */
    public function getStatusMessageAttribute(): string
    {
        return self::STATUS_MESSAGES[$this->status] ?? 'لا يمكنك تقديم طلب جديد في هذا الوقت';
    }

    /**
     * Static helper to get status message by status code
     * For STATUS_INITIAL_APPROVE, provide training type to get the correct message
     */
    public static function getStatusMessage(int $status, ?int $trainingType = null): string
    {
        // Special handling for STATUS_INITIAL_APPROVE with training type
        if ($status === self::STATUS_INITIAL_APPROVE && $trainingType !== null) {
            return self::STATUS_INITIAL_APPROVE_MESSAGES[$trainingType]
                ?? self::STATUS_MESSAGES[$status]
                ?? 'لا يمكنك تقديم طلب جديد في هذا الوقت';
        }

        return self::STATUS_MESSAGES[$status] ?? 'لا يمكنك تقديم طلب جديد في هذا الوقت';
    }

    public function trainee()
    {
        return $this->belongsTo(Trainee::class, 'trainee_id');
    }

    public function section()
    {
        return $this->belongsTo(Section::class, 'section_id');
    }

    public function department()
    {
        return $this->belongsTo(Department::class, 'department_id');
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

    public function getRouteKeyName()
    {
        return 'uuid';
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
        parent::booted();

        static::creating(function ($model) {
            $model->uuid = (string) \Illuminate\Support\Str::uuid();
        });

        static::created(function (self $application) {
            // Gather recipients according to role-based rules
            $recipients = collect();

            // ============================================================
            // ROLE_ADMIN (1): Gets notifications for EVERYTHING
            // ============================================================
            $superAdmins = User::where('role', User::ROLE_ADMIN)->get();
            $recipients = $recipients->merge($superAdmins);

            // ============================================================
            // ROLE_GTM (8): Gets notifications for ANY application added
            // ============================================================
            $gtmUsers = User::where('role', User::ROLE_GTM)->get();
            $recipients = $recipients->merge($gtmUsers);

            // ============================================================
            // Deduplicate and filter valid users
            // ============================================================
            $recipients = $recipients->unique('id')->filter(function ($user) {
                return $user && $user->id && $user->status === true;
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
                if ($newStatus === self::STATUS_STARTED_TRAINING) {
                    $notificationToSend = new \App\Notifications\TraineeStartedNotification($application);
                }
                // Status 6 : Ended Training
                elseif ($newStatus === self::STATUS_ENDED_TRAINING) {
                    $notificationToSend = new \App\Notifications\TraineeFinishedNotification($application);
                }
                // Status 3 : Confirmed (Notify GTM and Admin)
                elseif ($newStatus === self::STATUS_CONFIRMATION) {
                    $notification = new \App\Notifications\ApplicationConfirmedNotification($application);
                    $recipients = User::whereIn('role', [User::ROLE_GTM, User::ROLE_ADMIN])
                        ->where('status', true)
                        ->get();

                    foreach ($recipients as $user) {
                        try {
                            $user->notify($notification);
                        } catch (\Throwable $e) {
                            logger()->error('Failed to notify GTM/Admin for confirmation: ' . $e->getMessage());
                        }
                    }
                }
                // Status 2 : Initial Approval (Notify MOH or College Supervisor)
                elseif ($newStatus === self::STATUS_INITIAL_APPROVE) {
                    $notification = new InitialApprovalNotification($application);
                    $recipients = collect();

                    if ($application->training_type === self::TRAINING_TYPE_PRACTICE) {
                        // Notify MOH users
                        $recipients = User::where('role', User::ROLE_MOH)
                            ->where('status', true)
                            ->get();
                    } elseif ($application->training_type === self::TRAINING_TYPE_UNIVERSITY) {
                        // Notify College Supervisor associated with the trainee's college
                        $trainee = $application->trainee;
                        if ($trainee && $trainee->college_id) {
                            $recipients = User::where('role', User::ROLE_COLLEGE)
                                ->where('status', true)
                                ->whereHas('college', fn($q) => $q->where('id', $trainee->college_id))
                                ->get();
                        }
                    }

                    foreach ($recipients as $user) {
                        try {
                            $user->notify($notification);
                        } catch (\Throwable $e) {
                            logger()->error('Failed to notify MOH/College for initial approval: ' . $e->getMessage());
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
                        if ($sectionHead && $sectionHead->role === User::ROLE_SECTION) {
                            $recipients->push($sectionHead);
                        }
                    }

                    // 2. Role Department
                    if ($department) {
                        $deptHead = $department->headOfDepartment;
                        if ($deptHead && $deptHead->id && $deptHead->role === User::ROLE_DEPARTMENT) {
                            $recipients->push($deptHead);
                        }
                        if ($department->user_id) {
                            $deptUser = $department->user;
                            if ($deptUser && $deptUser->role === User::ROLE_DEPARTMENT) {
                                $recipients->push($deptUser);
                            }
                        }
                    }

                    // 3. Role HOA (Head of Administration)
                    // If defined on administrative
                    if ($administrative && $administrative->user_id) {
                        $hoaUser = $administrative->user;
                        if ($hoaUser && $hoaUser->role === User::ROLE_HOA) {
                            $recipients->push($hoaUser);
                        }
                    }
                    // If Section -> Administrative -> User
                    if ($section && $section->administrative_id) {
                        $sectionAdmin = $section->administrative;
                        if ($sectionAdmin && $sectionAdmin->user_id) {
                            $hoaUser = $sectionAdmin->user;
                            if ($hoaUser && $hoaUser->role === User::ROLE_HOA) {
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
                        // This implies broadly notifying HOMs about activity in their domain.
                        $homUsers = User::where('role', User::ROLE_HOM)->get();
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
                        return $user && $user->id && $user->status === true;
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
