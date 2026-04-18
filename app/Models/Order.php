<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Order extends Model
{
    public const STATUSES = [
        "pending",
        "confirmed",
        "delivered",
        "cancelled",
    ];

    public const PAYMENT_METHODS = [
        "cod",
        "bank_transfer",
        "demo_card",
    ];

    public const PAYMENT_STATUSES = [
        "unpaid",
        "pending",
        "paid",
        "failed",
        "refunded",
    ];

    protected $fillable = [
        "user_id",
        "store_id",
        "total_amount",
        "subtotal_amount",
        "status",
        "source",
        "payment_method",
        "payment_status",
        "transaction_id",
        "delivery_name",
        "delivery_email",
        "delivery_phone",
        "delivery_address",
        "customer_note",
        "discount_code",
        "discount_amount",
    ];

    protected $casts = [
        "total_amount" => "decimal:2",
        "subtotal_amount" => "decimal:2",
        "discount_amount" => "decimal:2",
    ];

    public function items()
    {
        return $this->hasMany(OrderItem::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function scopeLatestFirst($query)
    {
        return $query->latest("created_at");
    }

    public function syncInventoryForStatusChange(string $newStatus): void
    {
        if ($this->status === $newStatus) {
            return;
        }

        if ($newStatus === "cancelled" && $this->status !== "cancelled") {
            foreach ($this->items()->with("product")->get() as $item) {
                if ($item->product) {
                    $item->product->increment("stock", $item->quantity);
                }
            }
        }

        if ($this->status === "cancelled" && $newStatus !== "cancelled") {
            foreach ($this->items()->with("product")->get() as $item) {
                if ($item->product) {
                    $item->product->decrement("stock", min($item->quantity, $item->product->stock));
                }
            }
        }

        $this->status = $newStatus;

        if ($newStatus === "delivered" && $this->payment_method === "cod" && $this->payment_status === "unpaid") {
            $this->payment_status = "paid";
        }
    }

    public function paymentStatusBadgeClass(): string
    {
        return match ($this->payment_status) {
            "paid" => "success",
            "pending" => "warning",
            "failed", "refunded" => "danger",
            default => "secondary",
        };
    }

    public function statusBadgeClass(): string
    {
        return match ($this->status) {
            "pending" => "warning",
            "confirmed" => "info",
            "delivered" => "success",
            "cancelled" => "danger",
            default => "secondary",
        };
    }
}
