<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Carbon\Carbon;

class CountryClockinSetting extends Model
{
    protected $fillable = [
        'country', 'country_code', 'default_clockin_time',
        'default_clockout_time', 'grace_minutes', 'timezone', 'is_active',
    ];

    protected $casts = ['is_active' => 'boolean'];

    public function overrides(): HasMany
    {
        return $this->hasMany(CountryClockinOverride::class);
    }

    /**
     * Get the effective clock-in time for a given date.
     * Returns override if one exists for that date, else the default.
     */
    public function effectiveClockinTimeFor(string $date): string
    {
        $override = $this->overrides()
            ->where('override_date', $date)
            ->where('is_active', true)
            ->first();

        return $override ? $override->clockin_time : $this->default_clockin_time;
    }

    public function todayClockinTime(): string
    {
        return $this->effectiveClockinTimeFor(now()->toDateString());
    }

    public function hasTodayOverride(): bool
    {
        return $this->overrides()
            ->where('override_date', now()->toDateString())
            ->where('is_active', true)
            ->exists();
    }

    /**
     * Evaluate whether a given clock-in datetime is On Time, Late, or Absent.
     * This mirrors the same logic used in your existing Attendance status column.
     */
    public function evaluateStatus(string $clockinDatetime): string
    {
        $date          = Carbon::parse($clockinDatetime)->toDateString();
        $effectiveTime = $this->effectiveClockinTimeFor($date);
        $deadline      = Carbon::parse("{$date} {$effectiveTime}")->addMinutes($this->grace_minutes);
        $actual        = Carbon::parse($clockinDatetime);

        return $actual->lte($deadline) ? 'In Time' : 'Late';
    }
}
