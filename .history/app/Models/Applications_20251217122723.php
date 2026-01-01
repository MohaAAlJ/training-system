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
        'training_type',
        'duration',
        'administrative_id',
        'department_id',
        'start_date',
        'end_date',
        'status',
        'trainee_id',
        'application_letter',
        'tags',
        'accepted_at'
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
