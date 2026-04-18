<?php

namespace App\Notifications;

use App\Models\Product;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class LowStockAlertNotification extends Notification
{
    use Queueable;

    public function __construct(private readonly Product $product)
    {
    }

    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject('Low stock alert for ' . ($this->product->name ?? ('Product #' . $this->product->id)))
            ->greeting('Hello ' . ($notifiable->name ?? 'Store Owner') . ',')
            ->line('Your product "' . ($this->product->name ?? ('Product #' . $this->product->id)) . '" is low on stock.')
            ->line('Current stock: ' . $this->product->stock)
            ->action('Manage Products', route('store.products'));
    }
}
