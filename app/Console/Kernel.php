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

        // $schedule->job(new \App\Jobs\AddMonthlyLeaveBalance)->monthlyOn(1, '00:00') // Runs on the 1st day of each month at midnight

        //     ->onSuccess(function () {
        //         Log::info("AddMonthlyLeaveBalance job dispatched successfully.");
        //     })
        //     ->onFailure(function (\Throwable $exception) { // Explicitly define \Throwable
        //         Log::error("Failed to dispatch job: " . $exception->getMessage());
        //     });
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
