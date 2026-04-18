<?php

namespace App\Notifications;

use App\Models\Store;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class StoreApprovedNotification extends Notification
{
    use Queueable;

    public function __construct(private readonly Store $store)
    {
    }

    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject('Your store has been approved')
            ->greeting('Hello ' . ($notifiable->name ?? 'Store Owner') . ',')
            ->line('Your store "' . ($this->store->name ?? ('Store #' . $this->store->id)) . '" is now approved.')
            ->line('You can now log in to the store dashboard and start managing products and orders.')
            ->action('Open Store Login', route('store.login'));
    }
}
