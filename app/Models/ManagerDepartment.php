<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ManagerDepartment extends Model
{
    use HasFactory;



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
