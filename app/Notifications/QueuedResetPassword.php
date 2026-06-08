<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\App;

class QueuedResetPassword extends Notification implements ShouldQueue
{
    use Queueable, SerializesModels;

    /**
     * The password reset token.
     *
     * @var string
     */
    public $token;

    /**
     * Create a new notification instance.
     */
    public function __construct($token)
    {
        $this->token = $token;
    }

    /**
     * Get the notification's delivery channels.
     */
    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    /**
     * Get the mail representation of the notification.
     */
    public function toMail(object $notifiable): MailMessage
    {
        // 1. Enforce user language preferences dynamically
        App::setLocale($notifiable->language_preference ?? 'en');

        // 2. Build the target URL for your Flutter app or web handler landing page
        // We pass both the token and the email as query parameters as required by the broker
        // Add /api right before /password/reset
        $resetUrl = url('/api/password/reset?token=' . $this->token . '&email=' . urlencode($notifiable->getEmailForPasswordReset()));

        return (new MailMessage)
            ->subject(__('auth.reset_password_subject') ?? 'Reset Password Notification')
            ->view('emails.reset-password', [
                'url' => $resetUrl,
                'user' => $notifiable
            ]);
    }
}
