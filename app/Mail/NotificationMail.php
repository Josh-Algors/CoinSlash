<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class NotificationMail extends Mailable
{
    use Queueable, SerializesModels;

    public $username;
    public $code;
    
    /**
     * Create a new message instance.
     *
     * @return void
     */
    public function __construct($username, $code)
    {
        $this->username = $username;
        $this->code = $code;
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
        return $this->subject("OTP Code - Verification")->view('emails.generic');
        // return $this->view('view.name');
    }
}
