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
        'name',
        'institution_id',
        'major',
        'email',
        'phone',
        'department_id',
        'administrative_id',
        'start_date',
        'end_date',
        'status',
    ];

}
