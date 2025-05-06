<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class RifleAcceptedNotification extends Notification
{
    use Queueable;

    public $details;

    public function __construct($details)
    {
        $this->details = $details;
    }

    public function via($notifiable)
    {
        return ['mail', 'database'];
    }

    public function toMail($notifiable)
    {
        return (new MailMessage)
                    ->subject($this->details['title'])
                    ->line($this->details['text'])
                    ->line('Amount: ' . $this->details['amount']);
    }

    public function toArray($notifiable)
    {
        return [
            'title' => $this->details['title'],
            'text' => $this->details['text'],
            'amount' => $this->details['amount'],
        ];
    }

}
