<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Applications extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'applications';
    protected $fillable = [
        'full_name',
        'dob',
        'national_id',
        'phone_number',
        'address',
        'street',
        'institution_id',
        'major_id',
        'training_hours',
        'administrative_id',
        'department_id',
        'training_type',
        'trainee_id',
        'start_date',
        'end_date',
        'status',
        'letter_image_path',
        'accepted_at',
        'tags',
        'slug',
    ];
    protected $casts = [
        'start_date' => 'date',
        'end_date' => 'date',
        'accepted_at' => 'datetime',
        'status' => 'string',
        'training_hours' => 'integer',
    ];

    public function trainee()
    {
        return $this->belongsTo(Trainees::class, 'trainee_id');
    }

    public function department()
    {
        return $this->belongsTo(Departments::class, 'department_id');
    }

    public function institution()
    {
        return $this->belongsTo(Institution::class);
    }

    public function major()
    {
        return $this->belongsTo(Major::class);
    }

    public function administrative()
    {
        return $this->belongsTo(Administratives::class, 'administrative_id');
    }
}
