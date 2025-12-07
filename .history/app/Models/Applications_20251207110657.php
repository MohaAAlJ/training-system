<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Spatie\Translatable\HasTranslations;

class Applications extends Model
{
    use HasFactory, SoftDeletes;
    
    protected $table = 'applications';
    protected $fillable = [
        'trainee_id',
        'department_id',
        'start_date',
        'end_date',
        'status',
        'letter_image_path',
        'accepted_at',
        'tags',
    ];
    
    protected $dates = ['start_date', 'end_date', 'accepted_at'];

    public function trainee()
    {
        return $this->belongsTo(Trainees::class, 'trainee_id');
    }

    public function department()
    {
        return $this->belongsTo(Departments::class, 'department_id');
    }
}
