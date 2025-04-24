<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Department extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'name','hod_id','manager_id'
    ];

    public function users()
    {
        return $this->hasMany(User::class);
    }


    // Many-to-Many relationship for HODs
    public function hods()
    {
        return $this->belongsToMany(User::class, 'hod_departments', 'department_id', 'user_id');
    }


    // has manager
    public function managers()
    {
        return $this->belongsToMany(User::class, 'manager_departments', 'department_id', 'user_id');
    }


    public function complaints() {
        return $this->belongsToMany(Complaint::class, 'complaint_department');
    }

    public function announcements()
    {
        return $this->belongsToMany(Announcement::class, 'announcement_department');
    }
}
