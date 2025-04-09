<?php

namespace App\Mail;

use App\Models\Complaint;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class ComplaintNotification extends Mailable
{
    use Queueable, SerializesModels;

    public $complaint;
    public $user;

    /**
     * Create a new message instance.
     *
     * @return void
     */
    // public function __construct(Complaint $complaint, $user)
    // {
    //     $this->complaint = $complaint;
    //     $this->user = $user;
    // }


    public function __construct(Complaint $complaint,  $user)
{
    $this->complaint = $complaint;
    $this->user = $user;
    $this->complaint->addressed_to = is_array($complaint->addressed_to) ? $complaint->addressed_to : json_decode($complaint->addressed_to, true);
    $this->complaint->followers = is_array($complaint->followers) ? $complaint->followers : json_decode($complaint->followers, true);
}
    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
        return $this->subject('New Suggestion Raised')
            ->view('emails.complaint-notification');
    }
}
