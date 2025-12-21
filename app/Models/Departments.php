<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
class Departments extends Model
{
    use SoftDeletes, HasFactory;

    protected $table = 'departments';
    protected $fillable = [
        'title',
        'user_id',
        'head_of_department',
        'medical_head_user_id',
        'is_medical',
        'administrative_id',
        'status',
        'total_capacity',
        'current_capacity',
        'location',
    ];
    protected $casts = [
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function medicalHead()
    {
        return $this->belongsTo(User::class, 'medical_head_user_id');
    }

    public function departments()
    {
        return $this->hasMany(Departments::class, 'department_id');
    }

    public function applications()
    {
        return $this->hasMany(Applications::class, 'department_id');
    }
    public function sections()
    {
        return $this->hasMany(Sections::class, 'department_id');

    }
    public function headOfDepartment()
    {
        return $this->belongsTo(User::class, 'head_of_department');
    }

    /**
     * Safely notify the head of department if set.
     */
    public function notifyHead($notification): void
    {
        $user = $this->headOfDepartment;

        if (! $user || ! ($user->id ?? null)) {
            return;
        }

        $user->notify($notification);
    }
    public function scopeMedical($query)
    {
        return $query->where('is_medical', true);
    }
}


// namespace App\Models;

// use Illuminate\Database\Eloquent\Model;
// use Illuminate\Database\Eloquent\Builder;
// use Illuminate\Database\Eloquent\Factories\HasFactory;
// use Illuminate\Database\Eloquent\SoftDeletes;

// class Departments extends Model
// {
//     use SoftDeletes, HasFactory;

//     protected $table = 'departments';
//     protected $fillable = [
//         'name_location',
//         'status',
//         'total_capacity',
//         'user_id',
//         'administrative_id',
//         'is_medical',
//     ];
//     protected $casts = [
//         'status' => 'string',
//         'is_medical' => 'boolean',
//     ];

//     /**
//      * Scope a query to only include active departments.
//      */
//     public function scopeActive(Builder $query): void
//     {
//         $query->where('status', 'active');
//     }

//     // Accessor to convert status string to boolean for ToggleColumn
//     protected function getStatusAttribute($value)
//     {
//         return $value === 'active';
//     }

//     // Mutator to convert boolean back to status string
//     protected function setStatusAttribute($value)
//     {
//         $this->attributes['status'] = is_bool($value) ? ($value ? 'active' : 'inactive') : $value;
//     }

//     public function user()
//     {
//         return $this->belongsTo(User::class);
//     }

//     public function applications()
//     {
//         return $this->hasMany(Applications::class, 'department_id');
//     }
// }
