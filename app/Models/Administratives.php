<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Administratives extends Model
{
    //
    use SoftDeletes,HasFactory;
    protected $table = "administratives";
    protected $fillable = [
        'id',
        'name',
        'position',
        'head_of_administrative',
        'location',
        'status',

    ];
    public function administratives(): BelongsTo
{
    return $this->belongsTo(User::class);
}

}
