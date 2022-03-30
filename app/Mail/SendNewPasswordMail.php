<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class SendNewPasswordMail extends Mailable
{
    use Queueable, SerializesModels;

    
    protected $newPass;

    /**
     * Create a new message instance.
     *
     * @return void
     */
    public function __construct($newPass)
    {
        $this->newPass = $newPass;
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
        $newPass = $this->newPass;
        return $this->markdown('emails.new-password', compact('newPass'));
    }
}
