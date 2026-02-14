<?php

namespace App\Models;

use App\Models\Administrative;
use App\Models\College;
use App\Models\Section;
use App\Models\Trainee;
use App\Models\User;
use App\Notifications\ApplicationConfirmedNotification;
use App\Notifications\ApplicationCreated as ApplicationCreatedNotification;
use App\Notifications\InitialApprovalNotification;
use App\Notifications\TraineeFinishedNotification;
use App\Notifications\TraineeStartedNotification;
use App\Services\Telegram\TelegramMonitorService;
use App\Services\WhatsApp\WhatsAppNotificationService;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class Application extends Model
{
    use HasFactory, SoftDeletes;

    // =========================================================================
    // CONSTANTS: TRAINING TYPES
    // =========================================================================
    public const UNIVERSITY = 1;
    public const PRACTICE = 2;

    // =========================================================================
    // CONSTANTS: STATUSES
    // =========================================================================
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

    // =========================================================================
    // STATIC HELPERS (LABELS & COLORS)
    // =========================================================================

    public static function getTrainingTypeLabel(int $type): string
    {
        return match ($type) {
            self::UNIVERSITY => 'تدريب جامعي',
            self::PRACTICE => 'تدريب إمتياز',
            default => 'غير محدد',
        };
    }

    public static function getTrainingTypeColor(int $type): string
    {
        return match ($type) {
            self::UNIVERSITY => 'info',
            self::PRACTICE => 'yellow',
            default => 'gray',
        };
    }

    public static function getStatuses(): array
    {
        return [
            self::STATUS_NEW => self::getStatusLabel(self::STATUS_NEW),
            self::STATUS_INITIAL_APPROVE => self::getStatusLabel(self::STATUS_INITIAL_APPROVE),
            self::STATUS_CONFIRMATION => self::getStatusLabel(self::STATUS_CONFIRMATION),
            self::STATUS_WAITING_LIST => self::getStatusLabel(self::STATUS_WAITING_LIST),
            self::STATUS_STARTED_TRAINING => self::getStatusLabel(self::STATUS_STARTED_TRAINING),
            self::STATUS_ENDED_TRAINING => self::getStatusLabel(self::STATUS_ENDED_TRAINING),
            self::STATUS_REJECTED => self::getStatusLabel(self::STATUS_REJECTED),
            self::STATUS_DROPPED => self::getStatusLabel(self::STATUS_DROPPED),
            self::STATUS_UNKNOWN => self::getStatusLabel(self::STATUS_UNKNOWN),
        ];
    }

    public static function getStatusLabel(int $status): string
    {
        return match ($status) {
            self::STATUS_NEW => 'جديد',
            self::STATUS_INITIAL_APPROVE => 'موافقة مبدئية',
            self::STATUS_CONFIRMATION => 'مؤكد',
            self::STATUS_WAITING_LIST => 'قائمة الانتظار',
            self::STATUS_STARTED_TRAINING => 'قيد التدريب',
            self::STATUS_ENDED_TRAINING => 'أنهى التدريب',
            self::STATUS_REJECTED => 'رفض',
            self::STATUS_DROPPED => 'منسحب',
            self::STATUS_UNKNOWN => 'غير معروف',
            default => 'غير محدد',
        };
    }

    public static function getStatusColor(int $status): string
    {
        return match ($status) {
            self::STATUS_NEW => 'info',
            self::STATUS_INITIAL_APPROVE, self::STATUS_CONFIRMATION => 'primary',
            self::STATUS_WAITING_LIST => 'warning',
            self::STATUS_STARTED_TRAINING => 'success',
            self::STATUS_ENDED_TRAINING => 'gray',
            self::STATUS_REJECTED, self::STATUS_DROPPED => 'danger',
            default => 'gray',
        };
    }

    public static function getStatusMessage(int $status, int|null $trainingType = null): string
    {
        return match ($status) {
            self::STATUS_NEW => 'لديك طلب قيد الانتظار',
            self::STATUS_INITIAL_APPROVE => match ($trainingType) {
                self::UNIVERSITY => 'لديك طلب في انتظار القبول الجامعي',
                self::PRACTICE => 'لديك طلب في انتظار قبول الوزارة',
                default => 'لديك طلب في انتظار القبول المبدئي',
            },
            self::STATUS_CONFIRMATION => 'لديك طلب في انتظار التأكيد',
            self::STATUS_WAITING_LIST => 'لديك طلب في قائمة الانتظار',
            self::STATUS_STARTED_TRAINING => 'لديك تدريب نشط',
            self::STATUS_ENDED_TRAINING => 'لديك طلب تدريب منتهي',
            self::STATUS_REJECTED => 'نعتذر، لقد تم رفض طلبك السابق ولا يمكنك تقديم طلب جديد حالياً وفقاً للسياسات المعمول بها.',
            self::STATUS_DROPPED => 'لديك طلب منسحب',
            self::STATUS_UNKNOWN => 'لا يمكنك تقديم طلب جديد في هذا الوقت',
            default => 'حالة غير معروفة',
        };
    }

    // =========================================================================
    // SETUP
    // =========================================================================
    protected $table = 'applications';

    protected $fillable = [
        'uuid',
        'training_type',
        'duration',
        'section_id',
        'start_date',
        'end_date',
        'status',
        'trainee_id',
        'application_letter',
        'tags',
        'accepted_at',
        'street',
        'university_number',
    ];

    protected $casts = [
        'start_date' => 'date',
        'end_date' => 'date',
        'accepted_at' => 'datetime',
        'status' => 'integer',
        'duration' => 'integer',
        'training_type' => 'integer',
    ];

    // =========================================================================
    // ACCESSORS
    // =========================================================================

    public function getTrainingTypeLabelAttribute(): string
    {
        return self::getTrainingTypeLabel($this->training_type);
    }

    public function getStatusMessageAttribute(): string
    {
        return self::getStatusMessage($this->status, $this->training_type);
    }

    // =========================================================================
    // RELATIONSHIPS
    // =========================================================================

    public function trainee(): BelongsTo
    {
        return $this->belongsTo(Trainee::class);
    }

    public function section(): BelongsTo
    {
        return $this->belongsTo(Section::class);
    }

    // =========================================================================
    // BOOT & EVENTS
    // =========================================================================
    protected static function booted()
    {
        parent::booted();

        static::creating(function ($model) {
            $model->uuid = (string) Str::uuid();
        });

        static::created(function (self $application) {
            // 1. Telegram Notification
            try {
                app(TelegramMonitorService::class)->handleApplicationCreated($application);
            } catch (\Throwable $e) {
                Log::error('Telegram Create Notification Failed: ' . $e->getMessage());
            }

            // 2. Notify System Users (Admin, GTM, etc.)
            self::notifySystemUsersOnCreation($application);
        });

        static::updating(function (self $application) {
            // Check if status changed FROM Started Training TO something else
            if ($application->isDirty('status')) {
                if ($application->getOriginal('status') == self::STATUS_STARTED_TRAINING) {
                    $application->start_date = null;
                    $application->end_date = null;
                }
            }
        });

        static::updated(function (self $application) {
            // 1. Telegram Activity & Status Change
            try {
                app(TelegramMonitorService::class)->handleApplicationUpdated($application);
            } catch (\Throwable $e) {
                // Silent fail
            }

            // 2. Handle Status Changes
            if ($application->isDirty('status')) {
                // WhatsApp
                try {
                    app(WhatsAppNotificationService::class)->handleApplicationStatusChange($application, $application->status);
                } catch (\Throwable $e) {
                    Log::error('WhatsApp Notification Failed: ' . $e->getMessage());
                }

                // Email / System Notifications
                self::handleStatusChangeNotifications($application);
            }
        });

        static::deleted(function (self $application) {
            try {
                app(TelegramMonitorService::class)->handleApplicationDeleted($application);
            } catch (\Throwable $e) {
            }
        });

        static::restored(function (self $application) {
            try {
                app(TelegramMonitorService::class)->handleApplicationRestored($application);
            } catch (\Throwable $e) {
            }
        });
    }

    // =========================================================================
    // SCOPES
    // =========================================================================

    public function scopeForUser($query, $user)
    {
        if ($user->isAdmin() || $user->isGeneralTrainingManager()) {
            return $query;
        }

        if ($user->isCollegeSupervisor()) {
            $collegeId = College::where('user_id', $user->id)->value('id');
            return $query->where('training_type', self::UNIVERSITY)
                ->whereIn('status', [
                    self::STATUS_INITIAL_APPROVE,
                    self::STATUS_CONFIRMATION,
                    self::STATUS_WAITING_LIST,
                    self::STATUS_STARTED_TRAINING,
                    self::STATUS_ENDED_TRAINING
                ])
                ->whereHas('trainee', function ($q) use ($collegeId) {
                    $q->where('college_id', $collegeId);
                });
        }

        if ($user->isSectionHead()) {
            return $query->where('section_id', $user->section?->id)
                ->whereIn('status', [
                    self::STATUS_WAITING_LIST,
                    self::STATUS_STARTED_TRAINING,
                    self::STATUS_ENDED_TRAINING
                ]);
        }

        if ($user->isAdministrative()) {
            // Filter by applications whose section belongs to this administrative
            return $query->whereHas('section', fn($q) => $q->where('administrative_id', $user->administrative?->id))
                ->whereIn('status', [
                    self::STATUS_WAITING_LIST,
                    self::STATUS_STARTED_TRAINING,
                    self::STATUS_ENDED_TRAINING
                ]);
        }

        if ($user->isMedicalManager()) {
            $adminId = Administrative::where('medical_head_user_id', $user->id)->value('id');
            return $query->whereHas(
                'section',
                fn($q) =>
                $q->where('administrative_id', $adminId)
                    ->whereHas('department', fn($d) => $d->where('is_medical', true))
            )
                ->whereIn('status', [
                    self::STATUS_WAITING_LIST,
                    self::STATUS_STARTED_TRAINING,
                    self::STATUS_ENDED_TRAINING
                ]);
        }

        if ($user->isDepartment()) {
            // Filter by applications whose section belongs to this department
            $query->whereHas('section', fn($q) => $q->where('department_id', $user->department?->id))
                ->whereIn('status', [
                    self::STATUS_WAITING_LIST,
                    self::STATUS_STARTED_TRAINING,
                    self::STATUS_ENDED_TRAINING
                ]);

            if ($user->department?->is_medical === true) {
                // Redundant check if we are already filtering by department_id, but keeping logic
                // If department itself is medical, all its sections are generally considered part of it.
            }

            return $query;
        }

        if ($user->isMinistry()) {
            return $query->where('training_type', self::PRACTICE)
                ->whereIn('status', [
                    self::STATUS_INITIAL_APPROVE,
                    self::STATUS_CONFIRMATION,
                    self::STATUS_STARTED_TRAINING,
                    self::STATUS_ENDED_TRAINING
                ]);
        }

        return $query->whereRaw('1 = 0');
    }
    // =========================================================================
    // PRIVATE HELPERS FOR NOTIFICATIONS
    // =========================================================================

    private static function notifySystemUsersOnCreation(self $application)
    {
        // "I want the gtm && superadmin to recived the notification for all of them...
        // and the hoa and hom to role department and role section recived the notificafiton all but application created..."

        $recipients = collect();
        // 1. ROLE_ADMIN
        $recipients = $recipients->merge(User::where('role', User::ROLE_ADMIN)->active()->get());

        // 2. ROLE_GTM
        $recipients = $recipients->merge(User::where('role', User::ROLE_GTM)->active()->get());

        // Note: HOS, HOD, HOA, HOM are EXCLUDED from Creation notification as per request.

        $recipients = $recipients->unique('id')->values();

        foreach ($recipients as $user) {
            try {
                $user->notify(new ApplicationCreatedNotification($application));
            } catch (\Throwable $e) {
                Log::error('Failed to notify Admin/GTM on creation: ' . $e->getMessage());
            }
        }
    }

    private static function handleStatusChangeNotifications(self $application)
    {
        $newStatus = $application->status;
        $notificationToSend = null;

        // Determine Notification Type
        if ($newStatus === self::STATUS_STARTED_TRAINING) {
            $notificationToSend = new TraineeStartedNotification($application);
        } elseif ($newStatus === self::STATUS_ENDED_TRAINING) {
            $notificationToSend = new TraineeFinishedNotification($application);
        } elseif ($newStatus === self::STATUS_CONFIRMATION) {
            self::notifyGtmAndAdmin($application);
            return; // Exit as this is handled separately
        } elseif ($newStatus === self::STATUS_INITIAL_APPROVE) {
            self::notifyInitialApproval($application);
            return; // Exit as this is handled separately
        }

        if ($notificationToSend) {
            // "so only the started and finshed notification" -> Goes to everyone (Admin/GTM + Chain)
            self::notifyCommandChain($application, $notificationToSend);
        }
    }

    private static function notifyGtmAndAdmin(self $application)
    {
        $notification = new ApplicationConfirmedNotification($application);
        // Confirmed -> Only Admin & GTM
        $recipients = User::whereIn('role', [User::ROLE_GTM, User::ROLE_ADMIN])->active()->get();

        foreach ($recipients as $user) {
            try {
                $user->notify($notification);
            } catch (\Throwable $e) {
                Log::error('Failed to notify GTM/Admin: ' . $e->getMessage());
            }
        }
    }

    private static function notifyInitialApproval(self $application)
    {
        $notification = new InitialApprovalNotification($application);
        $recipients = collect();

        // Initial Approval -> Only Admin & GTM (implied "all but...")?
        // User said: "recived the notificafiton all but application created and applicationconfirmednotification and initiaonapprovanotificaton"
        // This implies HOA/HOM/HOD/HOS do NOT receive InitialApproval either.
        // But normally InitialApproval goes to MOH or College.
        // Keeping existing logic for MOH/College as they are external entities, not HOD/HOS/HOA/HOM.

        if ($application->training_type === self::PRACTICE) {
            $recipients = User::where('role', User::ROLE_MOH)->active()->get();
        } elseif ($application->training_type === self::UNIVERSITY) {
            $trainee = $application->trainee;
            if ($trainee && $trainee->college_id) {
                $recipients = User::where('role', User::ROLE_COLLEGE)
                    ->active()
                    ->whereHas('college', fn($q) => $q->where('id', $trainee->college_id))
                    ->get();
            }
        }

        // Also add Admin/GTM if they should receive "all of them"
        $recipients = $recipients->merge(User::whereIn('role', [User::ROLE_ADMIN, User::ROLE_GTM])->active()->get());

        $recipients = $recipients->unique('id')->values();

        foreach ($recipients as $user) {
            try {
                $user->notify($notification);
            } catch (\Throwable $e) {
                Log::error('Failed to notify users on Initial Approval: ' . $e->getMessage());
            }
        }
    }

    private static function notifyCommandChain(self $application, $notification)
    {
        $recipients = collect();

        // 1. Admin & GTM (Receive ALL)
        $recipients = $recipients->merge(User::where('role', User::ROLE_ADMIN)->active()->get());
        $recipients = $recipients->merge(User::where('role', User::ROLE_GTM)->active()->get());

        // 2. Local Management Chain (HOS, HOD, HOA, HOM)
        $section = $application->section;
        $department = $section?->department;
        $administrative = $section?->administrative;

        // Head of Section (HOS)
        if ($section && $section->user_id) {
            $user = User::where('id', $section->user_id)->active()->first();
            if ($user) $recipients->push($user);
        }

        // Head of Department (HOD)
        if ($department && $department->user_id) {
            $user = User::where('id', $department->user_id)->active()->first();
            if ($user) $recipients->push($user);
        }

        // Head of Administrative (HOA)
        if ($administrative && $administrative->user_id) {
            $user = User::where('id', $administrative->user_id)->active()->first();
            if ($user) $recipients->push($user);
        }

        // Medical Manager (HOM)
        if ($department && $department->is_medical && $administrative && $administrative->medical_head_user_id) {
            $user = User::where('id', $administrative->medical_head_user_id)->active()->first();
            if ($user) $recipients->push($user);
        }

        // 3. External Entities (College Supervisor / MOH) - "from initial approval till the end"
        if ($application->training_type === self::PRACTICE) {
            $recipients = $recipients->merge(User::where('role', User::ROLE_MOH)->active()->get());
        } elseif ($application->training_type === self::UNIVERSITY) {
            $trainee = $application->trainee;
            if ($trainee && $trainee->college_id) {
                $recipients = $recipients->merge(
                    User::where('role', User::ROLE_COLLEGE)
                        ->active()
                        ->whereHas('college', fn($q) => $q->where('id', $trainee->college_id))
                        ->get()
                );
            }
        }

        $recipients = $recipients->unique('id')->values();

        foreach ($recipients as $user) {
            try {
                $user->notify($notification);
            } catch (\Throwable $e) {
                Log::error('Failed to notify command chain user ' . $user->id . ': ' . $e->getMessage());
            }
        }
    }
}
