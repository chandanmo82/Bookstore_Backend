<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class ForgotPassword extends Notification implements ShouldQueue
{
    use Queueable;
    public $email;
    public $token;

    /**
     * Create a new notification instance.
     *
     * @return void
     */
    public function __construct($email , $token)
    {
        $this->email=$email;
        $this->token=$token;
    }

    /**
     * Get the notification's delivery channels.
     *
     * @param  mixed  $notifiable
     * @return array
     */
    public function via($notifiable)
    {
        return ['mail'];
    }

    /**
     * Get the mail representation of the notification.
     *
     * @param  mixed  $notifiable
     * @return \Illuminate\Notifications\Messages\MailMessage
     */
    public function toMail($notifiable)
    {
        $url = url("/api/auth/resetpassword/".$this->token);
        return (new MailMessage)
            ->line('you are receiving this email because we want the reset the password of particular email id')
            ->action('Reset Password', url($url))
            ->line('Link is valid up to 12 hours')
            ->line("if you dont want to reset the password,no further action is required");
    }
    /**
     * Get the array representation of the notification.
     *
     * @param  mixed  $notifiable
     * @return array
     */
    public function toArray($notifiable)
    {
        return [
            //
        ];
    }
}
