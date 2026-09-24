<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class UserOtpMail extends Mailable
{
    use Queueable, SerializesModels;

    public string $otp;
    public string $ipAddress;
    public string $time;
    public string $userName;
    public string $projectName;

    public function __construct(string $otp, ?string $ipAddress = null, ?string $userName = null, ?string $projectName = null)
    {
        $this->otp = $otp;
        $this->ipAddress = $ipAddress ?: (request()->ip() ?: '127.0.0.1');
        $this->time = now()->format('Y-m-d H:i:s');
        $this->userName = $userName ?: 'المسؤول';
        $this->projectName = $projectName ?: config('app.name', 'نظام تقييم وتطوير الموظفين');
    }

    public function build()
    {
        return $this->subject('رمز التحقق الثنائي (OTP) - ' . $this->projectName)
                    ->view('emails.user-otp');
    }
}
