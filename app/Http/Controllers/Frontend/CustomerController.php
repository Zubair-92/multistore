<?php

namespace App\Http\Controllers\Frontend;

use App\Http\Controllers\Controller;
use App\Models\Order;

class CustomerController extends Controller
{
    public function profile()
    {
        $orders = Order::where('user_id', auth()->id())
                   ->orderBy('created_at', 'desc')
                   ->get();

        return view('frontend.customer.profile', compact('orders'));
    }
}
