<?php

namespace App\Notifications;

use App\Models\HajariGameInvitation;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class GameInvitationNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(
        public HajariGameInvitation $invitation
    ) {}

    public function via($notifiable): array
    {
        return ['mail', 'database'];
    }

    public function toMail($notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject('Hazari Game Invitation')
            ->greeting('Hello ' . $notifiable->name . '!')
            ->line('You have been invited to join a Hazari card game.')
            ->line('Game: ' . $this->invitation->hajarigame->title)
            ->line('Bid Amount: ৳' . number_format($this->invitation->hajarigame->bid_amount, 2))
            ->line('Scheduled: ' . $this->invitation->hajarigame->scheduled_at->format('M d, Y h:i A'))
            ->action('View Invitation', route('games.invitation', $this->invitation))
            ->line('This invitation will expire in 24 hours.');
    }

    public function toArray($notifiable): array
    {
        return [
            'type' => 'game_invitation',
            'game_id' => $this->invitation->game_id,
            'invitation_id' => $this->invitation->id,
            'inviter_name' => $this->invitation->inviter->name,
            'game_title' => $this->invitation->hajarigame->title,
            'bid_amount' => $this->invitation->hajarigame->bid_amount,
            'scheduled_at' => $this->invitation->hajarigame->scheduled_at
        ];
    }
}
