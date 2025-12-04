<?php

namespace App\Models;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class StudentPersonal extends Model
{
    use HasFactory,SoftDeletes;
    //
    protected $fillable = [
        'student_id',
        'department_id',
        'major_id',
        'major_level',
        'gpa',
        'graduation_year',
        '',
    ];
}
