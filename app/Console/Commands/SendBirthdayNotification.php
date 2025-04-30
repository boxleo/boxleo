<?php

namespace App\Console\Commands;

use Carbon\Carbon;
use App\Models\UserDetail;
use App\Notifications\BirthdayNotification;
use Illuminate\Support\Facades\Log;
use Illuminate\Console\Command;
use App\Models\User;
use App\Http\Controllers\Util\SMSUtil;
use App\Http\Controllers\Util\UGSMSUtil;
use App\Http\Controllers\Util\TZSMSUtil;

class SendBirthdayNotification extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:send-birthday-notification';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Send birthday wishes to users whose birthday is today';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        Log::info('🎂 Birthday check started.');

        $today = Carbon::today();

        // Fetch users whose date_of_birth matches today's month and day
        $users = UserDetail::whereNotNull('date_of_birth')
            ->whereMonth('date_of_birth', $today->month)
            ->whereDay('date_of_birth', $today->day)
            ->with('user')
            ->get();

        if ($users->isEmpty()) {
            Log::info('No birthdays today.');
            return;
        }

        foreach ($users as $detail) {
            // $user->notify(new BirthdayNotification());
            // Log::info("🎉 Birthday wish sent to {$user->firstname} (ID: {$user->id})");
            if ($detail->user) {
                $user = $detail->user;
                $detail->user->notify(new BirthdayNotification());
                 // Send SMS
                // $message = "Happy Birthday {$user->firstname}! 🎉 - From the Boxleo Team";
                $message = "Happy Birthday {$user->firstname}! 🎉\n" .
                            "The Boxleo Courier Services team wishes you a day filled with happiness and a year filled with joy.\n" .
                            "Enjoy your special day!";
                try {
                    switch ($user->unit_id) {
                        case 1:
                            (new SMSUtil())->sendSMS($user->phone, $message);
                            break;
                        case 2:
                            (new UGSMSUtil())->sendSMS($user->phone, $message);
                            break;
                        case 3:
                            (new TZSMSUtil())->sendSMS($user->phone, $message);
                            break;
                        default:
                            Log::warning("Unknown unit_id ({$user->unit_id}) for user ID {$user->id}, SMS not sent.");
                    }
                    Log::info("📱 Birthday SMS sent to {$user->phone}");
                } catch (\Exception $e) {
                    Log::error("❌ Failed to send SMS to {$user->phone}: " . $e->getMessage());
                }

                Log::info("🎉 Birthday wish sent to {$detail->user->firstname} (ID: {$detail->user->id})");
            }
        }

        Log::info('🎂 Birthday check completed.');
    }
}
