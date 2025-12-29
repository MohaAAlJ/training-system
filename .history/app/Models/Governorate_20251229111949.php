<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Governorate extends Model
{
    use HasFactory;

    protected $table = 'governorates';

    protected $fillable = [
        'name',
    ];

    protected $casts = [
        'name' => 'array',
    ];

    function GetGovName($gov)
{
    $name = $gov->name;
    if (is_array($name)) {
        return json_encode($name, JSON_UNESCAPED_UNICODE);
    }
    return $name;
}
}




