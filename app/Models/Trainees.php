<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;
class Trainees extends Model
{
    //
    use SoftDeletes,HasFactory;
    protected $table = "trainees";
    public function studentPersonal()
    {

    }
    protected $fillable = [
        'id',
        'national_id',
        'date_of_birth',
        'name',
        'institution_id',
        'major',
        'email',
        'phone',
        'department_id',
        'administrative_id',
        'accepted_at',
        'start_date',
        'finish_date',
        'status',
        'supervisor_id',
        'address',
        'reason_for_rejection',
        'cover_letter',
    ];

}
