<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use Illuminate\Notifications\Messages\MailMessage;

class ReferralCommissionEarned extends Notification
{
    use Queueable;

    protected $details;

    public function __construct($details)
    {
        $this->details = $details;
    }

    public function via($notifiable)
    {
        return ['database', 'mail'];
    }

    public function toMail($notifiable)
    {
        return (new MailMessage)
            ->subject($this->details['title'])
            ->line($this->details['text'])
            ->line('Commission Amount: ' . $this->details['amount'])
            ->line('Thank you for referring users to our platform!');
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
