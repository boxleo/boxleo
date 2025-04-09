<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class HodDepartment extends Model
{
    use HasFactory;


    protected $fillable = ['user_id', 'department_id'];

    // Relationship with the User (HOD)
    public function hod()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    // Relationship with the Department
    public function department()
    {
        return $this->belongsTo(Department::class, 'department_id');
    }
}
