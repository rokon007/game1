<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class WithdrawalRequestSubmitted extends Notification
{
    use Queueable;

    public $data;

    public function __construct($data)
    {
        $this->data = $data;
    }

    public function via($notifiable)
    {
        return ['mail','database'];
    }

    public function toMail($notifiable)
    {
        return (new MailMessage)
            ->subject('New Withdrawal Request')
            ->line('User: ' . $this->data['user_name'])
            ->line('Amount: ' . $this->data['amount'])
            ->line('Method: ' . $this->data['method'])
            ->line('Transaction ID: ' . $this->data['request_id']);
    }

    public function toArray($notifiable)
    {
        return [
            'title' => 'New Withdrawal Request',
            'message' => $this->data['user_name'] . ' has submitted a withdrawal request of ' . $this->data['amount'] . '৳ via ' . $this->data['method'],
            'user_id' => $this->data['user_id'],
            'request_id' => $this->data['request_id'],
            // 'admin_link' => route('admin.withdrawals.show', $this->data['request_id']),
        ];
    }
}
