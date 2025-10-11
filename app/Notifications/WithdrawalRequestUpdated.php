<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class WithdrawalRequestUpdated extends Notification
{
    use Queueable;

    public $data;

    public function __construct($data)
    {
        $this->data = $data;
    }

    public function via($notifiable)
    {
        return ['database'];
    }

    public function toArray($notifiable)
    {
        return [
            'title' => 'Withdrawal Request ' . ucfirst($this->data['status']),
            'message' => 'Your withdrawal request of ' . $this->data['amount'] . '৳ has been ' . $this->data['status'],
            'request_id' => $this->data['request_id'],
            'status' => $this->data['status'],
            'user_link' => route('withdrawal.history'),
        ];
    }
}
