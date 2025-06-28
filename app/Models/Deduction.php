<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Deduction extends Model
{
    use SoftDeletes;
    protected $fillable = [
        "deduction_type",
        "is_recurring",
        'label',
    ] ;


    protected $casts = [
    'is_recurring' => 'boolean',
];

}
