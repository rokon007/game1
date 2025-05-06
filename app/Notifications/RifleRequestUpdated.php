<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class RifleRequestUpdated extends Notification
{
    use Queueable;

    public $data;

    public function __construct($data)
    {
        $this->data = $data;
    }

    public function via($notifiable)
    {
        return ['database', 'mail']; // চাইলে শুধু 'database' রাখতে পারেন
    }

    public function toMail($notifiable)
    {
        return (new MailMessage)
                    ->subject($this->data['title'])
                    ->line("User: {$this->data['user']}")
                    ->line("Amount: {$this->data['amount']}")
                    ->line("Method: {$this->data['method']}")
                    ->line("Transaction ID: {$this->data['transaction_id']}")
                    ->line('Thank you for using our application!');
    }

    public function toArray($notifiable)
    {
        return $this->data;
    }
}
