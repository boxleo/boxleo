<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class ManagerDepartment extends Model
{
    use HasFactory, SoftDeletes;


    protected $fillable = ['user_id', 'department_id'];


    public function ManagerDepartement ()
    {
     

        return $this->belongsTo(User::class,'user_id');

    }

    public function department()
    {
        return $this->belongsTo(Department::class, 'department_id');
    }

}
