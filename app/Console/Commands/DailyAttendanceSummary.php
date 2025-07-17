<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Leave;
use App\Models\User;
use App\Models\Attendance;
use App\Models\Holiday; 
use Carbon\Carbon;
use App\Notifications\DailyAttendanceSummaryNotification;

class DailyAttendanceSummary extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:daily-attendance-summary';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Generate daily attendance summary with dynamic thresholds and holiday handling';

    public function handle()
    {
        $today = Carbon::today();
        $todayString = $today->toDateString();
        $dayOfWeek = $today->dayOfWeek; // 0 = Sunday, 1 = Monday, ..., 6 = Saturday

        // Get users with their unit and department
        $users = User::with(['unit', 'department'])->get()->groupBy(fn($u) => optional($u->unit)->name ?? 'Unknown Unit');

        // Get attendance records
        $attendances = Attendance::with('user')
            ->where('attendance_date', $todayString)
            ->where('is_present', 1)
            ->get()
            ->keyBy('user_id');

        // Get users on leave today
        $onLeaveUserIds = Leave::whereDate('from', '<=', $todayString)
            ->whereDate('to', '>=', $todayString)
            ->where('status', 'approved') // Assuming you have status field
            ->pluck('user_id')
            ->toArray();

        // Get holidays for today (assuming Holiday model has unit_id, date, and name fields)
        $holidays = Holiday::where('date', $todayString)
            ->get()
            ->keyBy('unit_id');

        $summary = [];

        foreach ($users as $unit => $unitUsers) {
            // Get unit ID for holiday checking
            $unitId = $unitUsers->first()->unit_id ?? null;
            
            // Check if today is a holiday for this unit
            $isHoliday = isset($holidays[$unitId]);
            
            // Determine late threshold based on day and holiday status
            $lateThreshold = $this->getLateThreshold($dayOfWeek, $isHoliday);

            $unitSummary = [
                'present' => 0,
                'on_time' => 0,
                'late' => 0,
                'on_leave' => 0,
                'absent' => 0,
                'holiday' => 0,
                'is_holiday' => $isHoliday,
                'holiday_name' => $isHoliday ? $holidays[$unitId]->name : null,
                'late_threshold' => $lateThreshold->format('H:i'),
            ];

            foreach ($unitUsers as $user) {
                // Check if user is on approved leave
                if (in_array($user->id, $onLeaveUserIds)) {
                    $unitSummary['on_leave']++;
                    continue;
                }

                $attendance = $attendances->get($user->id);

                if (!$attendance) {
                    // If it's a holiday and user didn't clock in, mark as holiday
                    if ($isHoliday) {
                        $unitSummary['holiday']++;
                    } else {
                        $unitSummary['absent']++;
                    }
                } else {
                    $unitSummary['present']++;

                    $clockIn = Carbon::parse($attendance->clock_in_time);
                    if ($clockIn->lte($lateThreshold)) {
                        $unitSummary['on_time']++;
                    } else {
                        $unitSummary['late']++;
                    }
                }
            }

            $summary[$unit] = $unitSummary;
        }

        // Send to HR users
        $hrUsers = User::whereHas('department', function ($q) {
            $q->where('name', 'Human Resources');
        })->get();

        foreach ($hrUsers as $user) {
            $user->notify(new DailyAttendanceSummaryNotification($todayString, $summary));
        }

        $this->info("Detailed daily attendance summary emailed to HR.");
        
        // Display summary in console for verification
        $this->displaySummary($summary, $todayString);
    }

    /**
     * Get late threshold based on day of week and holiday status
     *
     * @param int $dayOfWeek
     * @param bool $isHoliday
     * @return Carbon
     */
    private function getLateThreshold($dayOfWeek, $isHoliday)
    {
        if ($isHoliday) {
            return Carbon::createFromTime(8, 30, 0); // 08:30 for holidays
        }

        switch ($dayOfWeek) {
            case 0: // Sunday
                return Carbon::createFromTime(11, 0, 0); // 11:00
            case 6: // Saturday
                return Carbon::createFromTime(8, 30, 0); // 08:30
            default: // Monday to Friday (1-5)
                return Carbon::createFromTime(8, 0, 0); // 08:00
        }
    }

    /**
     * Display summary in console for verification
     *
     * @param array $summary
     * @param string $date
     */
    private function displaySummary($summary, $date)
    {
        $this->info("\n=== Daily Attendance Summary for {$date} ===");
        
        foreach ($summary as $unit => $data) {
            $this->info("\n--- {$unit} ---");
            if ($data['is_holiday']) {
                $this->warn("🎉 Holiday: {$data['holiday_name']}");
            }
            $this->info("Late Threshold: {$data['late_threshold']}");
            $this->info("Present: {$data['present']} | On Time: {$data['on_time']} | Late: {$data['late']}");
            $this->info("On Leave: {$data['on_leave']} | Absent: {$data['absent']} | Holiday: {$data['holiday']}");
        }
    }
}