<?php

namespace App\Notifications;

use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class NewUserRegistrationNotification extends Notification
{
    use Queueable;

    public function __construct(private readonly User $user)
    {
    }

    public function via(object $notifiable): array
    {
        return ['database'];
    }

    public function toArray(object $notifiable): array
    {
        return [
            'user_id' => $this->user->id,
            'name' => $this->user->name,
            'email' => $this->user->email,
            'requested_role' => optional($this->user->roleRelation)->name,
            'message' => 'New user registration requires approval.',
        ];
    }
}
