<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;

class RequisitionApprovedNotification extends Notification implements ShouldQueue
{
    use Queueable;

    protected $requisition;

    /**
     * Create a new notification instance.
     *
     * @param $requisition
     */
    public function __construct($requisition)
    {
        $this->requisition = $requisition;
    }

    /**
     * Get the notification's delivery channels.
     *
     * @param mixed $notifiable
     * @return array
     */
    public function via($notifiable)
    {
        return ['mail', 'database']; // Sends both email and stores it in the database
    }

    /**
     * Get the mail representation of the notification.
     *
     * @param mixed $notifiable
     * @return \Illuminate\Notifications\Messages\MailMessage
     */
    public function toMail($notifiable)
    {
        return (new MailMessage)
            ->subject('Requisition Approved')
            ->greeting('Hello ' . $notifiable->firstname . ',')
            // ->greeting('Hello ' . $this->requisition->user->firstname)
            // ->line('Your requisition with ID #' . $this->requisition->id . ' has been approved.')
            ->line('Requisition with ID #' . $this->requisition->id . ' has been approved.')
            ->line('Status: Approved')
            ->action('View Requisition', url('/api/v1/requisitions/' . $this->requisition->id . '/pdf'))
            ->line('Thank you for using our system!');
    }

    /**
     * Get the array representation of the notification for database storage.
     *
     * @param mixed $notifiable
     * @return array
     */
    public function toArray($notifiable)
    {
        return [
            'requisition_id' => $this->requisition->id,
            'status' => 'Approved',
            'message' => 'Your requisition has been approved.',
        ];
    }
}



