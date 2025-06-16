<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Deduction extends Model
{
    use SoftDeletes;
    protected $fillable = [
        "user_id",
        "deduction_type",
        "amount",
        "type",
    ] ;
}
