<?php

namespace App\Notifications;

use App\Models\Leave;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class TaskAssignedNotification extends Notification
{
    use Queueable;

    protected $leave;
    protected $task;

    /**
     * Create a new notification instance.
     *
     * @param Leave $leave
     * @param array $task
     */
    public function __construct(Leave $leave, array $task)
    {
        $this->leave = $leave;
        $this->task = $task;
    }

    /**
     * Get the notification's delivery channels.
     *
     * @param  mixed  $notifiable
     * @return array
     */
    public function via($notifiable)
    {
        return ['mail', 'database'];
    }

    /**
     * Get the mail representation of the notification.
     *
     * @param  mixed  $notifiable
     * @return \Illuminate\Notifications\Messages\MailMessage
     */
    public function toMail($notifiable)
    {
        $applicant = $this->leave->user->firstname . ' ' . $this->leave->user->lastname;
        
        return (new MailMessage)
            ->subject('Task Assignment During Leave Period')
            ->greeting('Hello ' . $notifiable->firstname . ',')
            ->line($applicant . ' will be on leave from ' . $this->leave->from . ' to ' . $this->leave->to . '.')
            ->line('You have been assigned the following task during their absence:')
            ->line('Task: ' . $this->task['task_description'])
            ->line('Please ensure this responsibility is handled appropriately during the leave period.')
            ->action('View Leave Details', url('/leaves/' . $this->leave->id))
            ->line('Thank you for your cooperation.');
    }

    /**
     * Get the array representation of the notification.
     *
     * @param  mixed  $notifiable
     * @return array
     */
    public function toArray($notifiable)
    {
        return [
            'leave_id' => $this->leave->id,
            'applicant' => $this->leave->user->firstname . ' ' . $this->leave->user->lastname,
            'from_date' => $this->leave->from,
            'to_date' => $this->leave->to,
            'task' => $this->task['task_description'],
        ];
    }
}