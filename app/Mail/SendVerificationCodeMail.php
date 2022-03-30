<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Auth;

class SendVerificationCodeMail extends Mailable
{
    use Queueable, SerializesModels;


    protected $code;

    /**
     * Create a new message instance.
     *
     * @return void
     */
    public function __construct($verification_code)
    {
        $this->code = $verification_code;
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
        $verification_code = $this->code;
        return $this->markdown('emails.verification-code', compact('verification_code'));
    }
}
