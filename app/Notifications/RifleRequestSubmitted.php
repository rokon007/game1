<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class RifleRequestSubmitted extends Notification
{
    use Queueable;

    public $requestData;

    public function __construct($requestData)
    {
        $this->requestData = $requestData;
    }

    public function via($notifiable)
    {
        return ['mail', 'database'];
    }

    public function toMail($notifiable)
    {
        return (new MailMessage)
            ->subject('New Rifle Balance Request Submitted')
            ->line('User: ' . $this->requestData['user_name'])
            ->line('Amount: ' . $this->requestData['amount_rifle'])
            ->line('Method: ' . $this->requestData['sending_method'])
            ->line('Mobile: ' . $this->requestData['sending_mobile'])
            ->line('Transaction ID: ' . $this->requestData['transaction_id']);
    }

    public function toArray($notifiable)
    {
        return [
            'title' => 'New Rifle Balance Request',
            'user' => $this->requestData['user_name'],
            'amount' => $this->requestData['amount_rifle'],
            'method' => $this->requestData['sending_method'],
            'transaction_id' => $this->requestData['transaction_id'],
            'admin_link' => $this->requestData['admin_link'],
        ];
    }
}
