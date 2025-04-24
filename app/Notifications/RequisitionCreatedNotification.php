<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class RequisitionCreatedNotification extends Notification
{
    use Queueable;

    protected $requisition;


    /**
     * Create a new notification instance.
     */
    public function __construct($requisition)
    {
        //
        $this->requisition = $requisition;
    }

    /**
     * Get the notification's delivery channels.
     *
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    /**
     * Get the mail representation of the notification.
     */
    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)

            ->greeting('Hello ' . $notifiable->firstname)
            ->subject('New Requisition Created')
            ->line('A new requisition has been created and is pending your approval.')
            ->line('Status: ' . $this->requisition->status)
            // ->action('View Requisition', url('/requisitions'))
            ->action('View Requisition', url('/api/v1/requisitions/' . $this->requisition->id . '/pdf'))



            ->line('Thank you for using our application!');
    }

    /**
     * Get the array representation of the notification.
     *
     * @return array<string, mixed>
     */
    public function toArray(object $notifiable): array
    {
        return [
            //
        ];
    }
}
