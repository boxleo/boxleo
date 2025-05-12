<?php

namespace App\Notifications;

use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class LeaveCreatedNotification extends Notification
{
    protected $leave;

    public function __construct($leave)
    {
        $this->leave = $leave;
    }

    public function toMail($notifiable)
    {
        $approverFirstName = $notifiable->firstname ?: 'Approver';

        $this->leave->load('user');

        return (new MailMessage)
            ->subject('New Leave Application: ' . $this->leave->user->firstname . ' ' . $this->leave->user->lastname)
            ->greeting('Hello ' . $approverFirstName . ',')
            ->line('A new leave is awaiting your approval.')
            ->line('Applicant Name: ' . $this->leave->user->firstname . ' ' . $this->leave->user->lastname)
            ->line('Leave Type: ' . $this->leave->leave_type->name)
            ->line('Duration: ' . $this->leave->from . ' to ' . $this->leave->to)
            ->line('Leave Days: ' . $this->leave->days)
            ->line('Application Date: ' . $this->leave->created_at)
            ->line('Comment: ' . $this->leave->comment)
            ->action('View Leave', url('/leave-requests'))
            ->line('Thank you!');
    }

    public function via($notifiable)
    {
        return ['mail'];
    }
}
