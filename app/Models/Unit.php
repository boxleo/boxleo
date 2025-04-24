<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Unit extends Model
{
  use SoftDeletes;


  protected $fillable = [
    'name',
    'timezone',
    'work_start_time',
    'late_threshold',
    'weekend_day',
    'weekend_clock_in_time',
    'weekend_clock_out_time',
    'weekday_threshold',
    'weekend_threshold',
    'clock_in_time',
    'clock_out_time',
    'address',
    'phone',
];

  public function users()
  {
    return $this->hasMany(User::class);
  }


  public function offices()
  {
    return $this->hasMany(Office::class);
  }

  public function getFormattedTime($time)
  {
    return date('H:i', strtotime($this->getAttribute($time)));
  }

    public function announcements()
    {
        return $this->belongsToMany(Announcement::class, 'announcement_unit');
    }
}
