<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class CreditTransferred extends Notification
{
    use Queueable;

    public function __construct(public string $message) {}

    public function via($notifiable)
    {
        return ['mail', 'database'];
    }

    public function toMail($notifiable)
    {
        return (new \Illuminate\Notifications\Messages\MailMessage)
                    ->subject('Credit Transfer Notification')
                    ->line($this->message);
    }

    public function toDatabase($notifiable)
    {
        return [
            'title'=>'Credit Transfer Notification',
            'text' => $this->message,
        ];
    }
}
