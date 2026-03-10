<?php

namespace App\Notifications;

use App\Models\Movement;
use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use Illuminate\Support\Facades\Schema;

class MovementTrackerUpdatedByHandlerNotification extends Notification
{
    use Queueable;

    private int $movementId;

    private string $artworkTitle;

    private string $fromLocation;

    private string $toLocation;

    private string $status;

    private string $reason;

    private ?string $dateOut;

    private ?string $expectedReturnDate;

    private string $updatedByName;

    public function __construct(Movement $movement, User $updatedBy)
    {
        $movement->loadMissing('artwork');

        $this->movementId = (int) $movement->id;
        $this->artworkTitle = (string) ($movement->artwork?->title ?? 'Unknown artwork');
        $this->fromLocation = (string) $movement->from_location;
        $this->toLocation = (string) $movement->to_location;
        $this->status = (string) $movement->status;
        $this->reason = (string) $movement->reason;
        $this->dateOut = $movement->date_out?->format('Y-m-d');
        $this->expectedReturnDate = $movement->expected_return_date?->format('Y-m-d');
        $this->updatedByName = (string) $updatedBy->name;
    }

    public function via(object $notifiable): array
    {
        if (! (bool) ($notifiable->notification_movement_alerts ?? true)) {
            return [];
        }

        $channels = [];

        if ((bool) ($notifiable->notification_delivery_browser ?? true) && Schema::hasTable('notifications')) {
            $channels[] = 'database';
        }

        if ((bool) ($notifiable->notification_delivery_email ?? true)) {
            $channels[] = 'mail';
        }

        return $channels;
    }

    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject('Movement Tracker Updated by Handler')
            ->greeting('Hello '.$notifiable->name.',')
            ->line('A handler updated an assigned movement tracker record.')
            ->line('Updated by: '.$this->updatedByName)
            ->line('Artwork: '.$this->artworkTitle)
            ->line('From: '.$this->fromLocation)
            ->line('To: '.$this->toLocation)
            ->line('Status: '.$this->status)
            ->line('Reason: '.$this->reason)
            ->line('Date out: '.($this->dateOut ?? '-'))
            ->line('Expected return: '.($this->expectedReturnDate ?? '-'))
            ->action('Open Movement Tracker', route('movements.index'));
    }

    public function toArray(object $notifiable): array
    {
        return [
            'movement_id' => $this->movementId,
            'title' => 'Movement tracker updated',
            'message' => $this->updatedByName.' updated a movement tracker record.',
            'artwork_title' => $this->artworkTitle,
            'from_location' => $this->fromLocation,
            'to_location' => $this->toLocation,
            'status' => $this->status,
            'reason' => $this->reason,
            'date_out' => $this->dateOut,
            'expected_return_date' => $this->expectedReturnDate,
            'updated_by' => $this->updatedByName,
            'url' => route('movements.index'),
        ];
    }
}
