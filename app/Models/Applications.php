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
    ];

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

    protected static function booted()
    {
        static::created(function (self $application) {
            // gather recipients according to rules
            $recipients = collect();

            // Always notify ROLE_HOA users, superadmins, GTM, and all administratives
            $hoaUsers = User::where('role', Constans::ROLE_HOA)->get();
            $recipients = $recipients->merge($hoaUsers);

            $superAdmins = User::where('role', Constans::ROLE_ADMIN)->get();
            $recipients = $recipients->merge($superAdmins);

            $gtmUsers = User::where('role', Constans::ROLE_GTM)->get();
            $recipients = $recipients->merge($gtmUsers);

            // users attached to Administrative records (if any)
            $administratives = Administrative::with('user')->get()->map->user->filter();
            $recipients = $recipients->merge($administratives);

            $department = $application->department;
            if (! $department) {
                return;
            }

            // If department is medical: notify administrative's medical head (if set) and department head (if exist)
            $admin = $department->administrative ?? null;
            if ($admin && ! empty($admin->is_medical) && $admin->is_medical) {
                // administrative's designated medical head user (nullable)
                $medicalHead = $admin->medicalHead;
                if ($medicalHead) {
                    $recipients->push($medicalHead);
                }

                // department head: only add if relation returns a valid user
                $deptHead = $department->headOfDepartment;
                if ($deptHead && $deptHead->id) {
                    $recipients->push($deptHead);
                }

                // ensure application assigned under this administrative (do not notify admin owner specially)
                $application->administrative_id = $admin->id;
                $application->saveQuietly();
            } else {
                // Non-medical: notify department head if relation returns a valid user
                $deptHead = $department->headOfDepartment;
                if ($deptHead && $deptHead->id) {
                    $recipients->push($deptHead);
                }
            }

            // unique and valid users
            $recipients = $recipients->unique('id')->filter()->values();

            if ($recipients->isEmpty()) {
                return;
            }

            // send notification
            foreach ($recipients as $user) {
                try {
                    $user->notify(new ApplicationCreatedNotification($application));
                } catch (\Throwable $e) {
                    // swallow to avoid breaking creation flow; log if needed
                    // logger()->error('Failed to notify user: ' . $e->getMessage());
                }
            }
        });
    }
}
