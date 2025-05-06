<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class RifleCancelledNotification extends Notification
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
                    ->line('Amount: ' . $this->details['amount'])
                    ->line('Transaction ID: ' . $this->details['transaction_id']);
    }

    public function toArray($notifiable)
    {
        return [
            'title' => $this->details['title'],
            'text' => $this->details['text'],
            'amount' => $this->details['amount'],
            'transaction_id' => $this->details['transaction_id'],
        ];
    }
}
