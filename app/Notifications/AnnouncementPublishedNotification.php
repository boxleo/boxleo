<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class AnnouncementPublishedNotification extends Notification
{
    use Queueable;

    protected $announcement;

    /**
     * Create a new notification instance.
     *
     * @param $announcement
     */

    /**
     * Create a new notification instance.
     */
    public function __construct($announcement)
    {
        $this->announcement = $announcement;
    }

    /**
     * Get the notification's delivery channels.
     *@param object $notifiable
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        return ['mail', 'database'];
    }

    /**
     * Get the mail representation of the notification.
     *
     * @return \Illuminate\Notifications\Messages\MailMessage
     * @param object $notifiable
     */
    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject('New Announcement Published')
            ->greeting('Hello ' . $notifiable->firstname . ',')
            ->line('A new announcement has been published.')
            ->line('Subject: ' . $this->announcement->subject)
            ->line('Description: ' . \Str::limit($this->announcement->description, 100))
            ->action('View Announcement', url('/announcements/' ))
            ->line('Thank you for staying informed.');
    }

    /**
     * Get the array representation of the notification.
     *
     * @param object $notifiable
     * @return array<string, mixed>
     */
    public function toArray(object $notifiable): array
    {
        return [
            'announcement_id' => $this->announcement->id,
            'subject' => $this->announcement->subject,
            'message' => 'A new announcement has been published.',
        ];
    }
}
