<?php

namespace App\Services;

use App\Mail\SendNewPasswordMail;
use App\Mail\SendVerificationCodeMail;
use Illuminate\Support\Facades\Mail;

class EmailServices {

    static public function SendVCode($to, $code)
    {
        Mail::to($to)->queue(new SendVerificationCodeMail($code));
    }

    static public function SendNewPassword($to, $newPass)
    {
        Mail::to($to)->queue(new SendNewPasswordMail($newPass));
    }

}