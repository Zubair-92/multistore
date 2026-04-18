<?php

namespace App\Notifications;

use App\Models\Order;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class NewOrderPlacedNotification extends Notification
{
    use Queueable;

    public function __construct(private readonly Order $order, private readonly string $audience = 'customer')
    {
    }

    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        $message = (new MailMessage)
            ->subject('Order #' . $this->order->id . ' has been placed')
            ->greeting('Hello ' . ($notifiable->name ?? 'there') . ',');

        if ($this->audience === 'store') {
            return $message
                ->line('A new order that includes your store products has been placed.')
                ->line('Order total: $' . number_format((float) $this->order->total_amount, 2))
                ->action('View Store Orders', route('store.orders'));
        }

        return $message
            ->line('Your order has been placed successfully.')
            ->line('We saved your delivery details and payment status with this order.')
            ->action('View Order', route('profile.orders.show', $this->order));
    }
}
