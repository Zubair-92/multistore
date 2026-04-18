<?php

namespace App\Support;

use App\Models\Coupon;
use Illuminate\Support\Collection;

class CartPricing
{
    public static function calculate(Collection $items, ?Coupon $coupon = null): array
    {
        $subtotal = round((float) $items->sum(function ($item) {
            $quantity = (float) (is_array($item) ? ($item['quantity'] ?? 0) : ($item->quantity ?? 0));
            $price = (float) (is_array($item) ? ($item['price'] ?? 0) : ($item->price ?? 0));

            return $quantity * $price;
        }), 2);
        $discount = 0.0;
        $appliedCoupon = null;

        if ($coupon && $coupon->isAvailableForAmount($subtotal)) {
            $discount = $coupon->discountFor($subtotal);
            $appliedCoupon = $coupon;
        }

        return [
            'subtotal' => $subtotal,
            'discount' => $discount,
            'total' => round(max(0, $subtotal - $discount), 2),
            'coupon' => $appliedCoupon,
        ];
    }
}
