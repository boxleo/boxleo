<?php 
namespace App\Services;

use Carbon\Carbon;
use Illuminate\Support\Facades\Log;
use App\Models\Holiday;

class AttendanceService
{
    // public function isLate($clockInTime, $unit)
    // {
    //     // ... paste the isLate function here
    // }


    public function isLate($clockInTime, $unit)
{
    // Validate input
    if (!$unit) {
        Log::error('Invalid unit provided to isLate function');
        return false;
    }

    // Convert clock-in time to unit's timezone
    $userTime = Carbon::parse($clockInTime)->setTimezone($unit->timezone);

    // Set default thresholds
    $defaultLateThreshold = $unit->late_threshold ?? '08:00';
    $weekendThreshold = $unit->weekend_threshold ?? '08:30';
    $sundayThreshold = $unit->sunday_threshold ?? '11:00'; // Dynamic with fallback

    // Get day of week (0 = Sunday, 6 = Saturday)
    $userDayOfWeek = $userTime->dayOfWeek;

    // Parse weekend day configuration - handle both integer and string inputs
    $weekendDays = [];
    if (isset($unit->weekend_day)) {
        $weekendDays = is_array($unit->weekend_day)
            ? $unit->weekend_day
            : [$unit->weekend_day];
    } else {
        $weekendDays = [Carbon::SATURDAY]; // Default to Saturday
    }

    // Check if it's a weekend
    $isWeekend = in_array($userDayOfWeek, $weekendDays);

    // Check if it's a holiday
    $isHoliday = Holiday::whereDate('date', $userTime->toDateString())->exists();

    // Decide which threshold to use
    if ($userDayOfWeek === Carbon::SUNDAY) {
        $lateThreshold = $sundayThreshold; // Prefer unit setting, fallback to 11:00
    } elseif ($isWeekend || $isHoliday) {
        $lateThreshold = $weekendThreshold;
    } else {
        $lateThreshold = $defaultLateThreshold;
    }

    // Create a Carbon instance for the threshold time on the same day
    $thresholdTime = Carbon::parse(
        $userTime->toDateString() . ' ' . $lateThreshold,
        $unit->timezone
    )->setTimezone('UTC');

    Log::info('Evaluating lateness', [
        'clock_in_time_utc' => $clockInTime,
        'user_time' => $userTime->toDateTimeString(),
        'is_weekend' => $isWeekend,
        'is_holiday' => $isHoliday,
        'threshold_local' => $lateThreshold,
        'threshold_utc' => $thresholdTime->toDateTimeString(),
    ]);

    // Determine if the user is late
    return Carbon::parse($clockInTime)->greaterThan($thresholdTime);
}


    // public function isLateFromZkteco($clockInTime, $unit)
    // {
    //     // ... paste the isLateFromZkteco function here
    // }



    public function isLateFromZkteco($clockInTime, $unit)
{
    if (!$unit) {
        Log::warning('Missing unit for lateness check');
        return false;
    }

    $userTime = Carbon::parse($clockInTime)->setTimezone($unit->timezone);

    $defaultLateThreshold = $unit->late_threshold ?? '08:00';
    $weekendThreshold = $unit->weekend_threshold ?? '08:30';
    $sundayThreshold = '11:00'; // Hardcoded as requested

    $userDayOfWeek = $userTime->dayOfWeek;

    $weekendDays = is_array($unit->weekend_day)
        ? $unit->weekend_day
        : [$unit->weekend_day ?? Carbon::SATURDAY];

    $isWeekend = in_array($userDayOfWeek, $weekendDays);
    $isHoliday = Holiday::whereDate('date', $userTime->toDateString())->exists();

    // Apply fixed Sunday threshold
    if ($userDayOfWeek === Carbon::SUNDAY) {
        $lateThreshold = $sundayThreshold;
    } elseif ($isWeekend || $isHoliday) {
        $lateThreshold = $weekendThreshold;
    } else {
        $lateThreshold = $defaultLateThreshold;
    }

    $thresholdTime = Carbon::parse(
        $userTime->toDateString() . ' ' . $lateThreshold,
        $unit->timezone
    )->setTimezone('UTC');

    Log::info('Evaluating lateness for ZKTeco', [
        'user_time' => $userTime->toDateTimeString(),
        'threshold' => $thresholdTime->toDateTimeString(),
    ]);

    return Carbon::parse($clockInTime)->greaterThan($thresholdTime);
}

}
