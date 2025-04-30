<?php

namespace App\Jobs;

use App\Models\Attendance;
use App\Models\Leave;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class DailyAttendanceJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * Create a new job instance.
     */
    public function __construct()
    {
        //
    }

  public function handle()
{
    Log::info('Running DailyAttendanceJob at ' . now());

    $date = Carbon::today();
    $dayOfWeek = $date->format('l'); // e.g. Monday, Tuesday...

    $users = User::all(); // or filter for only active users

    foreach ($users as $user) {
        Log::info("Processing user: {$user->name} ({$user->id})");

        // Check if user already has attendance for the day
        $hasAttendance = Attendance::where('user_id', $user->id)
            ->whereDate('attendance_date', $date)
            ->exists();

        if (!$hasAttendance) {
            Log::info("User {$user->name} ({$user->id}) does not have attendance for today");

            // Check if user is on leave during the current day
            $onLeave = Leave::where('user_id', $user->id)
                ->whereDate('from', '<=', $date)
                ->whereDate('to', '>=', $date)
                ->exists();

            Log::info("User {$user->name} ({$user->id}) is " . ($onLeave ? 'on leave' : 'not on leave'));

            $status = $onLeave ? 'leave' : 'absent';

            // If it's Sunday and not on leave, skip marking absent
            if ($dayOfWeek === 'Sunday' && !$onLeave) {
                Log::info("It's Sunday and user is not on leave, skipping...");
                continue;
            }

            Attendance::create([
                'user_id' => $user->id,
                'attendance_date' => $date,
                'clock_in_time' => '00:00:00',
                'clock_out_time' => '00:00:00',
                'hours_worked' => 0,
                'status' => $status,
                'is_present' => 0,
                'notes' => $onLeave ? 'Auto-marked as on leave' : 'Auto-marked as absent',
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            Log::info("Created attendance for user {$user->name} ({$user->id})");
        }
    }

    Log::info('Finished running DailyAttendanceJob at ' . now());
}

}
