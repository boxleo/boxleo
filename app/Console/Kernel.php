<?php

namespace App\Console;

use App\Jobs\AddMonthlyLeaveBalance;
use App\Models\Leave;
use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;
use Illuminate\Support\Facades\Log;


class Kernel extends ConsoleKernel
{
   
    protected function schedule(Schedule $schedule)
    {
        // Log::info("Schedule is running...");

        $schedule->call(function () {
            Leave::where('to', '<', now())->update(['is_active' => false]);
        })->daily();



         $schedule->command('leave:reminder')->dailyAt('07:30');
         $schedule->command('leave:reminder')->dailyAt('16:30')
            ->before(function () {
                Log::info("leave:reminder command is about to run...");
            })
            ->after(function () {
                Log::info("leave:reminder command has executed.");
            });

            $schedule->job(new AddMonthlyLeaveBalance)
            ->dailyAt('00:00')
            ->when(function () {
                return now()->endOfMonth()->isToday();
            })
            ->onSuccess(function () {
                Log::info("✅ AddMonthlyLeaveBalance job dispatched successfully on " . now()->toDateTimeString());
            })
            ->onFailure(function (\Throwable $exception) {
                Log::error("❌ Failed to dispatch AddMonthlyLeaveBalance job on " . now()->toDateTimeString() . ": " . $exception->getMessage());
            });


            // $schedule->job(new \App\Jobs\DailyAttendanceJob)->everyTwoMinutes();

            $schedule->job(new \App\Jobs\DailyAttendanceJob)->dailyAt('23:59');



    }

    /**
     * Register the commands for the application.
     */
    protected function commands(): void
    {
        $this->load(__DIR__ . '/Commands');

        require base_path('routes/console.php');
    }
}
