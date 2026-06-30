<?php

namespace App\Mail;

use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class ResetPasswordOtpMail extends Mailable implements ShouldQueue
{
    use Queueable, SerializesModels;

    public int $otp;
    public User $user;

    /**
     * Create a new message instance.
     */
    public function __construct(int $otp, User $user)
    {
        $this->otp = $otp;
        $this->user = $user;
    }

    /**
     * Get the message envelope (Subject and configuration).
     */
    public function envelope(): Envelope
    {
        return new Envelope(
            subject: __('auth.reset_password_subject'),
        );
    }

    /**
     * Get the message content definition (The Blade view layout).
     */
    public function content(): Content
    {
        return new Content(
            view: 'emails.reset-password-otp',
            with: [
                'otp' => $this->otp,
                'user' => $this->user,
            ],
        );
    }
}
