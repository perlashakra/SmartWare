<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\Facades\App;
use Carbon\Carbon;

class QueuedVerifyEmail extends Notification implements ShouldQueue
{
    use Queueable;

    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        // 1. Set locale based on user preference for the email content
        App::setLocale($notifiable->language_preference ?? 'en');

        // 2. Build the signed URL that your Controller's 'verify' method expects
        $verificationUrl = URL::temporarySignedRoute(
            'verification.verify', // Ensure this matches your Route::name()
            Carbon::now()->addMinutes(60),
            [
                'id' => $notifiable->getKey(),
                'hash' => sha1($notifiable->getEmailForVerification()),
            ]
        );

        return (new MailMessage)
            ->subject(__('auth.verify_email_subject'))
            ->view('emails.verify-email', [ // Point to your custom blade view
                'url' => $verificationUrl,
                'user' => $notifiable
            ]);
    }
}
