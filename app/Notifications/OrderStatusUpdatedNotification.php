<?php

namespace App\Notifications;

use App\Models\Order;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class OrderStatusUpdatedNotification extends Notification
{
    use Queueable;

    public function __construct(private readonly Order $order)
    {
    }

    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject('Order #' . $this->order->id . ' status updated')
            ->greeting('Hello ' . ($notifiable->name ?? 'there') . ',')
            ->line('Your order status is now "' . ucfirst($this->order->status) . '".')
            ->line('Payment status: ' . ucfirst($this->order->payment_status ?? 'unpaid'))
            ->action('View Order', route('profile.orders.show', $this->order));
    }
}
