<?php

namespace App\Notifications;

use App\Models\Movement;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class MovementTrackerAssignedNotification extends Notification
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

    private ?string $assignedBy;

    public function __construct(Movement $movement, ?string $assignedBy = null)
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
        $this->assignedBy = $assignedBy;
    }

    public function via(object $notifiable): array
    {
        if (! (bool) ($notifiable->notification_movement_alerts ?? true)) {
            return [];
        }

        $channels = [];

        if ((bool) ($notifiable->notification_delivery_browser ?? true)) {
            $channels[] = 'database';
        }

        if ((bool) ($notifiable->notification_delivery_email ?? true)) {
            $channels[] = 'mail';
        }

        return $channels;
    }

    public function toMail(object $notifiable): MailMessage
    {
        $assignedByLine = $this->assignedBy
            ? 'Assigned by: '.$this->assignedBy
            : 'A movement assignment has been created for you.';

        return (new MailMessage)
            ->from((string) config('mail.from.address'), (string) config('mail.from.name'))
            ->subject('New Movement Tracker Assignment')
            ->greeting('Hello '.$notifiable->name.',')
            ->line('You have been assigned as the responsible handler for an artwork movement.')
            ->line('Artwork: '.$this->artworkTitle)
            ->line('From: '.$this->fromLocation)
            ->line('To: '.$this->toLocation)
            ->line('Status: '.$this->status)
            ->line('Reason: '.$this->reason)
            ->line($assignedByLine)
            ->line('Date out: '.($this->dateOut ?? '-'))
            ->line('Expected return: '.($this->expectedReturnDate ?? '-'))
            ->action('Open Movement Tracker', route('movements.index'));
    }

    public function toArray(object $notifiable): array
    {
        return [
            'movement_id' => $this->movementId,
            'title' => 'New movement assignment',
            'message' => 'You were assigned to handle movement for '.$this->artworkTitle.'.',
            'artwork_title' => $this->artworkTitle,
            'from_location' => $this->fromLocation,
            'to_location' => $this->toLocation,
            'status' => $this->status,
            'reason' => $this->reason,
            'date_out' => $this->dateOut,
            'expected_return_date' => $this->expectedReturnDate,
            'assigned_by' => $this->assignedBy,
            'url' => route('movements.index'),
        ];
    }
}
