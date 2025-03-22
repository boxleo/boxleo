<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ComplaintDepartment extends Model
{
    use HasFactory;




    protected $table = 'complaint_department';
    
    protected $fillable = ['complaint_id', 'department_id'];
    
    public $timestamps = true;
}
