<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use Illuminate\Support\Facades\Log;

class DailyAttendanceSummaryNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public $summary;
    public $date;

    public function __construct($date, $summary)
    {
        $this->date = $date;
        $this->summary = $summary;
        Log::info('DailyAttendanceSummaryNotification constructed', [
            'date' => $date,
            'summary' => $summary
        ]);
    }

    public function via($notifiable)
    {
        Log::info('Notification channels determined', [
            'notifiable' => $notifiable
        ]);
        return ['mail', 'database'];
    }

    public function toMail($notifiable)
    {
        Log::info('Preparing MailMessage for DailyAttendanceSummaryNotification', [
            'notifiable' => $notifiable,
            'date' => $this->date,
            'summary' => $this->summary
        ]);

        $mail = (new MailMessage)
            ->subject("Daily Attendance Summary - {$this->date}")
            ->line("Here's the summary for {$this->date}:");

        foreach ($this->summary as $unit => $data) {
            Log::info('Adding summary for unit', [
                'unit' => $unit,
                'data' => $data
            ]);
            $mail->line("🧭 {$unit}")
                ->line("- Present: {$data['present']}")
                ->line("- On Time: {$data['on_time']}")
                ->line("- Late: {$data['late']}")
                ->line("- On Leave: {$data['on_leave']}")
                ->line("- Absent: {$data['absent']}")
                ->line("- Holiday: {$data['holiday']}");
        }

        return $mail->line("Regards,")->line("Boxleo HRM System");
    }

    public function toArray($notifiable)
    {
        Log::info('Converting DailyAttendanceSummaryNotification to array', [
            'notifiable' => $notifiable,
            'date' => $this->date,
            'summary' => $this->summary
        ]);
        return [
            'date' => $this->date,
            'summary' => $this->summary
        ];
    }
}
