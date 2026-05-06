<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class LoginOtpMail extends Mailable
{
    use Queueable, SerializesModels;

    public $mailData;

    public function __construct($mailData)
    {
        $this->mailData = $mailData;
    }

    public function build()
    {
        return $this->subject('Your Login OTP')
            ->view('emails.login-otp')
            ->with([
                'name' => $this->mailData['name'],
                'otp'  => $this->mailData['otp'],
            ]);
    }
}