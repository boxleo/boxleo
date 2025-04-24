<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class UserDepartment extends Model
{
    use HasFactory, SoftDeletes;


    protected $table = 'user_departments';

    protected $fillable = [
        'user_id',
        'department_id',
    ];


    // Because you're using withTimestamps()
    public $timestamps = true; 
}
