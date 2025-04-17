<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Attendance extends Model
{
    use SoftDeletes;
    protected $fillable = [
        'attendance_date','user_id','clock_in_time','clock_out_time','status','is_present','notes','overtime_hours','hours_worked','status'
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

}
