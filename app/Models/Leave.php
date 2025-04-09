<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Notifications\Notifiable;

class Leave extends Model
{
    use Notifiable, Notifiable;
    protected $fillable = [
        'leave_type_id',
        'user_id',
        'phone',
        'document',
        'days',
        'from',
        'to',
        'comment',
        'status',
        'manager_approval',
        'hod_approval',
        'hr_approval'
    ];

    protected $dates = ['from', 'to', 'created_at', 'updated_at'];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function leave_type()
    {
        return $this->belongsTo(LeaveType::class, 'leave_type_id');
    }


    public function l($value)
    {
        return Carbon::parse($value)->toDateString();
    }

    public function routeNotificationForMail()
    {
        return 'itservices@boxleocourier.com';
    }

    public function logs()
    {
        return $this->hasMany(LeaveLog::class);
    }




    public function getCreatedAtAttribute($value)

    {
        return Carbon::parse($value)->format('Y-m-d H:i:s'); // Change format as needed
    }
}
