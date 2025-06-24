<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class UserEarning extends Model
{
    use HasFactory;



    protected $fillable = [ 'user_id', 'earning_id', 'amount'];
}
