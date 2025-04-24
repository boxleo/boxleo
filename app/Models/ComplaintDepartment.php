<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class ComplaintDepartment extends Model
{
    use HasFactory, SoftDeletes;




    protected $table = 'complaint_department';
    
    protected $fillable = ['complaint_id', 'department_id'];
    
    public $timestamps = true;
}
