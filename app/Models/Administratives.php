<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
class Administratives extends Model
{
    //
    use SoftDeletes,HasFactory;
    protected $table = "administratives";
    protected $fillable = [
        'name',
        'email',
        'phone',
        'position',
        'department',
        'office_location',
        'employee_id',
        'status',
        'notes',
    ];
}
